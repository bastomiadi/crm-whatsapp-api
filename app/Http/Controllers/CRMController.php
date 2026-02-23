<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Segment;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\Campaign;
use App\Models\MessageTemplate;
use App\Models\Automation;
use App\Models\Chatbot;
use App\Models\ChatbotSession;
use App\Models\Interaction;
use App\Models\Tag;
use App\Models\Product;
use App\Models\QuickReply;
use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Services\ChateryApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CRMController extends Controller
{
    /** @var ChateryApiService */
    protected $api;

    public function __construct(ChateryApiService $api)
    {
        $this->api = $api;
    }

    // ==================== DASHBOARD ====================
    public function dashboard()
    {
        $user = auth()->user();
        $canViewAll = $user->canViewAllData();
        
        // Base query builders
        $contactsQuery = Contact::query();
        $ordersQuery = Order::query();
        $ticketsQuery = Ticket::query();
        $campaignsQuery = Campaign::query();
        $automationsQuery = Automation::query();
        $chatbotsQuery = Chatbot::query();
        
        // Apply user filtering if not admin
        if (!$canViewAll) {
            $contactsQuery->where('created_by', $user->id);
            $ordersQuery->where('created_by', $user->id);
            $ticketsQuery->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
            $campaignsQuery->where('created_by', $user->id);
            $automationsQuery->where('created_by', $user->id);
            $chatbotsQuery->where('created_by', $user->id);
        }
        
        $stats = [
            'total_contacts' => $contactsQuery->count(),
            'active_contacts' => $contactsQuery->clone()->where('status', 'active')->count(),
            'total_orders' => $ordersQuery->count(),
            'pending_orders' => $ordersQuery->clone()->where('status', 'pending')->count(),
            'open_tickets' => $ticketsQuery->clone()->whereNotIn('status', ['closed', 'resolved'])->count(),
            'active_campaigns' => $campaignsQuery->clone()->where('status', 'running')->count(),
            'active_automations' => $automationsQuery->clone()->where('is_active', true)->count(),
            'active_chatbots' => $chatbotsQuery->clone()->where('status', 'active')->count(),
        ];

        $recentContacts = $contactsQuery->clone()->latest()->take(5)->get();
        $recentOrders = $ordersQuery->clone()->with('contact')->latest()->take(5)->get();
        $recentTickets = $ticketsQuery->clone()->with('contact', 'agent')->latest()->take(5)->get();

        return view('crm.dashboard', compact('stats', 'recentContacts', 'recentOrders', 'recentTickets'));
    }

    // ==================== CONTACTS ====================
    public function contacts(Request $request)
    {
        $query = Contact::with(['segment', 'tags']);

        // Filter by user if not super admin
        $user = auth()->user();
        if (!$user->canViewAllData()) {
            $query->where('created_by', $user->id);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->segment) {
            $query->where('segment_id', $request->segment);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->tag) {
            $query->whereJsonContains('tags', $request->tag);
        }

        $contacts = $query->latest()->paginate(20);
        $segments = Segment::all();
        $tags = Tag::all();
        
        // Get sessions for send message modal
        $api = app(ChateryApiService::class);
        $sessions = $api->getSessions();

        // Filter sessions by user ownership (if not admin)
        $user = Auth::user();
        $canViewAll = $user && $user->canViewAllData();
        
        if (!$canViewAll && $user) {
            $userId = $user->id;
            if (isset($sessions["data"])) {
                $sessions["data"] = array_filter($sessions["data"], function($session) use ($userId) {
                    $metadata = $session["metadata"] ?? [];
                    return isset($metadata["created_by"]) && $metadata["created_by"] == $userId;
                });
            }
        }

        return view('crm.contacts.index', compact('contacts', 'segments', 'tags', 'sessions'));
    }

    public function createContact()
    {
        $segments = Segment::all();
        $tags = Tag::all();
        return view('crm.contacts.create', compact('segments', 'tags'));
    }

    public function storeContact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string|unique:contacts,phone',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'segment_id' => 'nullable|exists:segments,id',
            'tags' => 'nullable|array',
            'custom_fields' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $contact = Contact::create(array_merge($validated, [
            'created_by' => auth()->id(),
        ]));

        if (!empty($validated['tags'])) {
            $contact->tags()->sync($validated['tags']);
        }

        return response()->json(['success' => true, 'data' => $contact]);
    }

    public function showContact(Contact $contact)
    {
        $contact->load(['segment', 'tags', 'orders', 'tickets', 'interactions']);
        $segments = Segment::all();
        $tags = Tag::all();
        
        // Get sessions for modals
        $api = app(ChateryApiService::class);
        $sessions = $api->getSessions();

        // Filter sessions by user ownership (if not admin)
        $user = Auth::user();
        $canViewAll = $user && $user->canViewAllData();
        
        if (!$canViewAll && $user) {
            $userId = $user->id;
            if (isset($sessions["data"])) {
                $sessions["data"] = array_filter($sessions["data"], function($session) use ($userId) {
                    $metadata = $session["metadata"] ?? [];
                    return isset($metadata["created_by"]) && $metadata["created_by"] == $userId;
                });
            }
        }
        
        return view('crm.contacts.show', compact('contact', 'segments', 'tags', 'sessions'));
    }

    public function sendContactMessage(Contact $contact)
    {
        $api = app(ChateryApiService::class);
        $sessions = $api->getSessions();

        // Filter sessions by user ownership (if not admin)
        $user = Auth::user();
        $canViewAll = $user && $user->canViewAllData();
        
        if (!$canViewAll && $user) {
            $userId = $user->id;
            if (isset($sessions["data"])) {
                $sessions["data"] = array_filter($sessions["data"], function($session) use ($userId) {
                    $metadata = $session["metadata"] ?? [];
                    return isset($metadata["created_by"]) && $metadata["created_by"] == $userId;
                });
            }
        }
        return view('crm.contacts.send-message', compact('contact', 'sessions'));
    }

    public function updateContact(Request $request, Contact $contact): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'segment_id' => 'nullable|exists:segments,id',
            'status' => 'nullable|in:active,inactive,blocked',
            'tags' => 'nullable|array',
            'custom_fields' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $contact->update($validated);

        if (isset($validated['tags'])) {
            $contact->tags()->sync($validated['tags']);
        }

        return response()->json(['success' => true, 'data' => $contact]);
    }

    public function destroyContact(Contact $contact): JsonResponse
    {
        $contact->delete();
        return response()->json(['success' => true]);
    }

    public function importContacts(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx',
        ]);

        // Handle CSV/Excel import
        // Implementation would use Laravel Excel or similar

        return response()->json(['success' => true, 'message' => 'Import started']);
    }

    // ==================== SEGMENTS ====================
    public function segments()
    {
        $query = Segment::withCount('contacts');
        
        // Filter by user if not super admin
        $user = auth()->user();
        if (!$user->canViewAllData()) {
            $query->where('created_by', $user->id);
        }
        
        $segments = $query->paginate(20);
        return view('crm.segments.index', compact('segments'));
    }

    public function storeSegment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'criteria' => 'nullable|array',
            'is_dynamic' => 'nullable|boolean',
        ]);

        $segment = Segment::create($validated);
        return response()->json(['success' => true, 'data' => $segment]);
    }

    public function updateSegment(Request $request, Segment $segment): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'criteria' => 'nullable|array',
        ]);

        $segment->update($validated);
        return response()->json(['success' => true, 'data' => $segment]);
    }

    public function destroySegment(Segment $segment): JsonResponse
    {
        $segment->delete();
        return response()->json(['success' => true]);
    }

    // ==================== ORDERS ====================
    public function orders(Request $request)
    {
        $query = Order::query();

        // Filter by user if not super admin
        $user = auth()->user();
        if (!$user->canViewAllData()) {
            $query->where('created_by', $user->id);
        }

        // Get filtered query for stats
        $statsQuery = clone $query;
        $stats = [
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'processing' => (clone $statsQuery)->where('status', 'processing')->count(),
            'shipped' => (clone $statsQuery)->where('status', 'shipped')->count(),
            'delivered' => (clone $statsQuery)->where('status', 'delivered')->count(),
        ];

        // Apply additional filters
        $filteredQuery = clone $query;
        if ($request->status) {
            $filteredQuery->where('status', $request->status);
        }

        if ($request->search) {
            $filteredQuery->where('order_number', 'like', "%{$request->search}%");
        }

        $orders = $filteredQuery->with('contact')->latest()->paginate(20);
        
        // Check if user can view all data
        $canViewAll = $user->canViewAllData();
        
        return view('crm.orders.index', compact('orders', 'stats', 'canViewAll'));
    }

    public function createOrder()
    {
        $contacts = Contact::active()->get();
        $products = Product::active()->get();
        
        // Get sessions for WhatsApp modal
        $api = app(ChateryApiService::class);
        $sessions = $api->getSessions();

        // Filter sessions by user ownership (if not admin)
        $user = Auth::user();
        $canViewAll = $user && $user->canViewAllData();
        
        if (!$canViewAll && $user) {
            $userId = $user->id;
            if (isset($sessions["data"])) {
                $sessions["data"] = array_filter($sessions["data"], function($session) use ($userId) {
                    $metadata = $session["metadata"] ?? [];
                    return isset($metadata["created_by"]) && $metadata["created_by"] == $userId;
                });
            }
        }
        
        return view('crm.orders.create', compact('contacts', 'products', 'sessions'));
    }

    public function storeOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'items' => 'required|array',
            'shipping_address' => 'nullable|string',
            'shipping_method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $order = DB::transaction(function () use ($validated) {
            $totalAmount = collect($validated['items'])->sum(function ($item) {
                return $item['price'] * $item['quantity'];
            });

            $order = Order::create([
                'contact_id' => $validated['contact_id'],
                'order_number' => 'ORD-' . date('Ymd') . '-' . str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT),
                'items' => $validated['items'],
                'total_amount' => $totalAmount,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'shipping_method' => $validated['shipping_method'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'ordered_at' => now(),
                'created_by' => auth()->id(),
            ]);

            return $order;
        });

        return response()->json(['success' => true, 'data' => $order]);
    }

    public function updateOrderStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
            'tracking_number' => 'nullable|string',
        ]);

        $order->update($validated);

        // Trigger automation for order status change
        // This would trigger the automation service

        return response()->json(['success' => true, 'data' => $order]);
    }

    // ==================== TICKETS ====================
    public function tickets(Request $request)
    {
        $query = Ticket::with(['contact', 'agent']);

        // Filter by user if not super admin
        $user = auth()->user();
        if (!$user->canViewAllData()) {
            // Non-admin users see tickets assigned to them or created by them
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id);
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $tickets = $query->latest()->paginate(20);
        
        // Get sessions for modals
        $api = app(ChateryApiService::class);
        $sessions = $api->getSessions();

        // Filter sessions by user ownership (if not admin)
        $user = Auth::user();
        $canViewAll = $user && $user->canViewAllData();
        
        if (!$canViewAll && $user) {
            $userId = $user->id;
            if (isset($sessions["data"])) {
                $sessions["data"] = array_filter($sessions["data"], function($session) use ($userId) {
                    $metadata = $session["metadata"] ?? [];
                    return isset($metadata["created_by"]) && $metadata["created_by"] == $userId;
                });
            }
        }
        
        return view('crm.tickets.index', compact('tickets', 'sessions'));
    }

    public function showTicket(Ticket $ticket)
    {
        $ticket->load(['contact', 'agent', 'messages.user', 'messages.contact']);
        
        // Get sessions for WhatsApp modal
        $api = app(ChateryApiService::class);
        $sessions = $api->getSessions();

        // Filter sessions by user ownership (if not admin)
        $user = Auth::user();
        $canViewAll = $user && $user->canViewAllData();
        
        if (!$canViewAll && $user) {
            $userId = $user->id;
            if (isset($sessions["data"])) {
                $sessions["data"] = array_filter($sessions["data"], function($session) use ($userId) {
                    $metadata = $session["metadata"] ?? [];
                    return isset($metadata["created_by"]) && $metadata["created_by"] == $userId;
                });
            }
        }
        
        return view('crm.tickets.show', compact('ticket', 'sessions'));
    }

    public function sendTicketMessage(Ticket $ticket)
    {
        $ticket->load(['contact', 'agent']);
        $api = app(ChateryApiService::class);
        $sessions = $api->getSessions();

        // Filter sessions by user ownership (if not admin)
        $user = Auth::user();
        $canViewAll = $user && $user->canViewAllData();
        
        if (!$canViewAll && $user) {
            $userId = $user->id;
            if (isset($sessions["data"])) {
                $sessions["data"] = array_filter($sessions["data"], function($session) use ($userId) {
                    $metadata = $session["metadata"] ?? [];
                    return isset($metadata["created_by"]) && $metadata["created_by"] == $userId;
                });
            }
        }
        return view('crm.tickets.send-message', compact('ticket', 'sessions'));
    }

    public function storeTicket(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'category' => 'nullable|in:general,support,complaint,sales,feedback',
        ]);

        $ticketData = $validated;
        $ticketData['ticket_number'] = 'TKT-' . date('Ymd') . '-' . str_pad(Ticket::count() + 1, 4, '0', STR_PAD_LEFT);
        $ticketData['status'] = 'open';
        $ticket = Ticket::create($ticketData);

        // Create first message
        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'contact_id' => $validated['contact_id'],
            'message' => $validated['description'],
            'is_from_customer' => true,
        ]);

        return response()->json(['success' => true, 'data' => $ticket]);
    }

    public function replyTicket(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'is_internal' => 'nullable|boolean',
        ]);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'is_internal' => $validated['is_internal'] ?? false,
            'is_from_customer' => false,
        ]);

        // Update ticket status if first response
        if (!$ticket->first_response_at) {
            $ticket->update([
                'first_response_at' => now(),
                'response_time' => $ticket->created_at->diffInMinutes(now()),
            ]);
        }

        // Send message via WhatsApp if not internal
        if (!$validated['is_internal']) {
            // Send via Chatery API
        }

        return response()->json(['success' => true, 'data' => $message]);
    }

    public function assignTicket(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $ticket->update($validated);
        return response()->json(['success' => true, 'data' => $ticket]);
    }

    public function updateTicketStatus(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,waiting_customer,resolved,closed',
            'resolution_notes' => 'nullable|string',
        ]);

        $updateData = $validated;

        if ($validated['status'] === 'resolved') {
            $updateData['resolved_at'] = now();
        }

        if ($validated['status'] === 'closed') {
            $updateData['closed_at'] = now();
        }

        $ticket->update($updateData);
        return response()->json(['success' => true, 'data' => $ticket]);
    }

    // ==================== CAMPAIGNS ====================
    public function campaigns()
    {
        $query = Campaign::with(['template', 'creator']);
        
        // Filter by user if not super admin
        $user = auth()->user();
        if (!$user->canViewAllData()) {
            $query->where('created_by', $user->id);
        }
        
        $campaigns = $query->latest()->paginate(20);
        $templates = MessageTemplate::approved()->get();
        $segments = Segment::all();
        return view('crm.campaigns.index', compact('campaigns', 'templates', 'segments'));
    }

    public function storeCampaign(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:broadcast,sequence,trigger',
            'template_id' => 'nullable|exists:message_templates,id',
            'target_segments' => 'nullable|array',
            'target_tags' => 'nullable|array',
            'scheduled_at' => 'nullable|date',
            'settings' => 'nullable|array',
        ]);

        $campaignData = $validated;
        $campaignData['status'] = $validated['scheduled_at'] ? 'scheduled' : 'draft';
        $campaignData['created_by'] = auth()->id();
        $campaign = Campaign::create($campaignData);

        return response()->json(['success' => true, 'data' => $campaign]);
    }

    public function startCampaign(Campaign $campaign): JsonResponse
    {
        if ($campaign->status !== 'draft' && $campaign->status !== 'paused') {
            return response()->json(['success' => false, 'message' => 'Campaign cannot be started'], 400);
        }

        // Get target contacts
        $query = Contact::active();

        if (!empty($campaign->target_segments)) {
            $query->whereIn('segment_id', $campaign->target_segments);
        }

        if (!empty($campaign->target_tags)) {
            $query->where(function ($q) use ($campaign) {
                foreach ($campaign->target_tags as $tag) {
                    $q->orWhereJsonContains('tags', $tag);
                }
            });
        }

        $contacts = $query->get();
        $campaign->update([
            'status' => 'running',
            'started_at' => now(),
            'total_recipients' => $contacts->count(),
        ]);

        // Queue campaign messages
        // This would dispatch jobs to send messages

        return response()->json(['success' => true, 'data' => $campaign]);
    }

    public function pauseCampaign(Campaign $campaign): JsonResponse
    {
        $campaign->update(['status' => 'paused']);
        return response()->json(['success' => true, 'data' => $campaign]);
    }

    // ==================== TEMPLATES ====================
    public function templates()
    {
        $query = MessageTemplate::with('creator');
        
        // Filter by user if not super admin
        $user = auth()->user();
        if (!$user->canViewAllData()) {
            $query->where('created_by', $user->id);
        }
        
        $templates = $query->latest()->paginate(20);
        return view('crm.templates.index', compact('templates'));
    }

    public function storeTemplate(Request $request): JsonResponse
    {
        // Handle variables_string conversion to array
        if ($request->has('variables_string') && $request->variables_string) {
            $variables = array_map('trim', explode(',', $request->variables_string));
            $request->merge(['variables' => $variables]);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,image,document,location,contact',
            'category' => 'required|string',
            'content' => 'required|string',
            'media_url' => 'nullable|url',
            'variables' => 'nullable|array',
            'buttons' => 'nullable|array',
        ]);

        $templateData = $validated;
        $templateData['created_by'] = auth()->id();
        $template = MessageTemplate::create($templateData);

        return response()->json(['success' => true, 'data' => $template]);
    }

    public function approveTemplate(MessageTemplate $template): JsonResponse
    {
        $template->update([
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        return response()->json(['success' => true, 'data' => $template]);
    }

    // ==================== AUTOMATIONS ====================
    public function automations()
    {
        $query = Automation::with('creator');
        
        // Filter by user if not super admin
        $user = auth()->user();
        if (!$user->canViewAllData()) {
            $query->where('created_by', $user->id);
        }
        
        $automations = $query->latest()->paginate(20);
        return view('crm.automations.index', compact('automations'));
    }

    public function storeAutomation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_type' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Parse JSON fields if they're strings
        $automationData = $validated;
        $automationData['is_active'] = $validated['is_active'] ?? true;
        $automationData['created_by'] = auth()->id();
        $automationData['trigger_config'] = [];
        $automationData['conditions'] = [];
        $automationData['actions'] = [];
        
        // Parse trigger_config if provided and is a JSON string
        if ($request->has('trigger_config') && $request->trigger_config) {
            $triggerConfig = is_string($request->trigger_config) ? json_decode($request->trigger_config, true) : $request->trigger_config;
            if (json_last_error() === JSON_ERROR_NONE) {
                $automationData['trigger_config'] = $triggerConfig;
            }
        }
        
        // Parse conditions if provided and is a JSON string
        if ($request->has('conditions') && $request->conditions) {
            $conditions = is_string($request->conditions) ? json_decode($request->conditions, true) : $request->conditions;
            if (json_last_error() === JSON_ERROR_NONE) {
                $automationData['conditions'] = $conditions;
            }
        }
        
        // Parse actions if provided and is a JSON string
        if ($request->has('actions') && $request->actions) {
            $actions = is_string($request->actions) ? json_decode($request->actions, true) : $request->actions;
            if (json_last_error() === JSON_ERROR_NONE) {
                $automationData['actions'] = $actions;
            }
        }

        $automation = Automation::create($automationData);

        return response()->json(['success' => true, 'data' => $automation]);
    }

    public function toggleAutomation(Automation $automation): JsonResponse
    {
        $automation->update(['is_active' => !$automation->is_active]);
        return response()->json(['success' => true, 'data' => $automation]);
    }

    // ==================== CHATBOTS ====================
    public function chatbots()
    {
        $query = Chatbot::with('creator');
        
        // Filter by user if not super admin
        $user = auth()->user();
        if (!$user->canViewAllData()) {
            $query->where('created_by', $user->id);
        }
        
        $chatbots = $query->latest()->paginate(20);
        return view('crm.chatbots.index', compact('chatbots'));
    }

    public function storeChatbot(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'flows' => 'required|array',
            'keywords' => 'nullable|array',
            'default_response' => 'nullable|array',
            'fallback_response' => 'nullable|array',
            'handover_enabled' => 'nullable|boolean',
            'handover_to' => 'nullable|exists:users,id',
            'working_hours' => 'nullable|array',
        ]);

        $chatbotData = $validated;
        $chatbotData['status'] = 'draft';
        $chatbotData['created_by'] = auth()->id();
        $chatbot = Chatbot::create($chatbotData);

        return response()->json(['success' => true, 'data' => $chatbot]);
    }

    public function activateChatbot(Chatbot $chatbot): JsonResponse
    {
        $chatbot->update(['status' => 'active']);
        return response()->json(['success' => true, 'data' => $chatbot]);
    }

    public function deactivateChatbot(Chatbot $chatbot): JsonResponse
    {
        $chatbot->update(['status' => 'inactive']);
        return response()->json(['success' => true, 'data' => $chatbot]);
    }

    // ==================== PRODUCTS ====================
    public function products()
    {
        $query = Product::query();
        
        // Filter by user if not super admin
        $user = auth()->user();
        if (!$user->canViewAllData()) {
            $query->where('created_by', $user->id);
        }
        
        $products = $query->latest()->paginate(20);
        return view('crm.products.index', compact('products'));
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'stock' => 'required|integer|min:0',
            'image_url' => 'nullable|url',
            'category' => 'nullable|string',
            'attributes' => 'nullable|array',
        ]);

        $product = Product::create($validated);
        return response()->json(['success' => true, 'data' => $product]);
    }

    // ==================== ANALYTICS ====================
    public function analytics()
    {
        $days = request()->get('range', 30);
        $user = auth()->user();
        $userFilter = !$user->canViewAllData() ? $user->id : null;
        
        // Basic stats
        $messagesQuery = Interaction::whereDate('created_at', '>=', now()->subDays($days));
        if ($userFilter) {
            $messagesQuery->where('user_id', $userFilter);
        }
        $stats = [
            'messages_sent' => (clone $messagesQuery)->outbound()->count(),
            'messages_received' => (clone $messagesQuery)->inbound()->count(),
            'delivery_rate' => $this->calculateDeliveryRate($userFilter),
            'response_rate' => $this->calculateResponseRate($userFilter),
        ];

        // Tickets stats
        $ticketsQuery = \App\Models\Ticket::whereDate('created_at', '>=', now()->subDays($days));
        if ($userFilter) {
            $ticketsQuery->where(function ($q) use ($userFilter) {
                $q->where('assigned_to', $userFilter)->orWhere('created_by', $userFilter);
            });
        }
        $ticketsCreated = (clone $ticketsQuery)->count();
        $ticketsResolved = (clone $ticketsQuery)->whereIn('status', ['resolved', 'closed'])->whereDate('updated_at', '>=', now()->subDays($days))->count();

        // Message volume data for chart (last 7 days)
        $messageVolume = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $sentQuery = Interaction::outbound()->whereDate('created_at', $date);
            if ($userFilter) {
                $sentQuery->where('user_id', $userFilter);
            }
            $receivedQuery = Interaction::inbound()->whereDate('created_at', $date);
            if ($userFilter) {
                $receivedQuery->where('user_id', $userFilter);
            }
            $messageVolume->push([
                'day' => $date->format('D'),
                'date' => $date->format('Y-m-d'),
                'sent' => $sentQuery->count(),
                'received' => $receivedQuery->count(),
            ]);
        }

        // Response time data (average response time per day)
        $responseTimeData = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $ticketQuery = \App\Models\Ticket::whereDate('created_at', $date)
                ->whereNotNull('resolved_at');
            if ($userFilter) {
                $ticketQuery->where(function ($q) use ($userFilter) {
                    $q->where('assigned_to', $userFilter)->orWhere('created_by', $userFilter);
                });
            }
            $avgResponseTime = $ticketQuery
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) as avg_time')
                ->value('avg_time') ?? 0;
            $responseTimeData->push([
                'day' => $date->format('D'),
                'minutes' => round($avgResponseTime),
            ]);
        }

        // Delivery/Read stats
        $outboundQuery = Interaction::outbound()->whereDate('created_at', '>=', now()->subDays($days));
        if ($userFilter) {
            $outboundQuery->where('user_id', $userFilter);
        }
        $totalSent = (clone $outboundQuery)->count();
        $delivered = (clone $outboundQuery)->whereIn('status', ['delivered', 'read'])->count();
        $read = (clone $outboundQuery)->where('status', 'read')->count();
        $replied = (clone $outboundQuery)
            ->whereHas('contact.interactions', function ($q) use ($days) {
                $q->inbound()->whereDate('created_at', '>=', now()->subDays($days));
            })->count();

        // Top campaigns
        $campaignQuery = \App\Models\Campaign::whereDate('created_at', '>=', now()->subDays($days));
        if ($userFilter) {
            $campaignQuery->where('created_by', $userFilter);
        }
        $topCampaigns = $campaignQuery
            ->orderByDesc('sent_count')
            ->limit(4)
            ->get()
            ->map(function ($campaign) {
                $responseRate = $campaign->sent_count > 0 ? round(($campaign->replied_count / $campaign->sent_count) * 100) : 0;
                return [
                    'name' => $campaign->name,
                    'sent' => $campaign->sent_count,
                    'rate' => $responseRate,
                ];
            });

        // Top agents (users who resolved most tickets)
        $topAgents = \App\Models\Ticket::whereIn('status', ['resolved', 'closed'])
            ->whereDate('updated_at', '>=', now()->subDays($days))
            ->whereNotNull('assigned_to')
            ->selectRaw('assigned_to, COUNT(*) as tickets_resolved')
            ->groupBy('assigned_to')
            ->orderByDesc('tickets_resolved')
            ->limit(4)
            ->get()
            ->map(function ($ticket) {
                $user = \App\Models\User::find($ticket->assigned_to);
                return [
                    'name' => $user->name ?? 'Unknown',
                    'tickets' => $ticket->tickets_resolved,
                    'rating' => rand(45, 50) / 10, // Placeholder rating
                ];
            });

        return view('crm.analytics.index', compact(
            'stats', 'ticketsCreated', 'ticketsResolved', 'messageVolume',
            'responseTimeData', 'totalSent', 'delivered', 'read', 'replied',
            'topCampaigns', 'topAgents', 'days'
        ));
    }

    private function calculateDeliveryRate($userFilter = null)
    {
        $query = Interaction::outbound();
        if ($userFilter) {
            $query->where('user_id', $userFilter);
        }
        $total = $query->count();
        if ($total === 0) return 0;
        
        $deliveredQuery = Interaction::outbound()->whereIn('status', ['delivered', 'read']);
        if ($userFilter) {
            $deliveredQuery->where('user_id', $userFilter);
        }
        $delivered = $deliveredQuery->count();
        return round(($delivered / $total) * 100, 1);
    }

    private function calculateResponseRate($userFilter = null)
    {
        $query = Interaction::outbound();
        if ($userFilter) {
            $query->where('user_id', $userFilter);
        }
        $sent = $query->count();
        if ($sent === 0) return 0;
        
        $contactQuery = Contact::whereNotNull('last_contacted_at')
            ->whereHas('interactions', function ($q) {
                $q->inbound()->where('created_at', '>', now()->subDays(7));
            });
        if ($userFilter) {
            $contactQuery->where('created_by', $userFilter);
        }
        $replied = $contactQuery->count();
        
        return round(($replied / $sent) * 100, 1);
    }

    // ==================== QUICK REPLIES ====================
    public function quickReplies()
    {
        $query = QuickReply::query();
        
        // Filter by user if not super admin and column exists
        $user = auth()->user();
        if (!$user->canViewAllData() && \Schema::hasColumn('quick_replies', 'created_by')) {
            $query->where('created_by', $user->id);
        }
        
        $replies = $query->latest()->paginate(20);
        return view('crm.quick-replies.index', compact('replies'));
    }

    public function storeQuickReply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string',
        ]);

        $reply = QuickReply::create([
            'name' => $validated['name'],
            'content' => $validated['content'],
            'category' => $validated['category'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'data' => $reply]);
    }

    public function showQuickReply($id): JsonResponse
    {
        $reply = QuickReply::findOrFail($id);
        return response()->json(['success' => true, 'data' => $reply]);
    }

    public function updateQuickReply(Request $request, $id): JsonResponse
    {
        $reply = QuickReply::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string',
        ]);

        $reply->update($validated);
        
        return response()->json(['success' => true, 'data' => $reply]);
    }

    public function destroyQuickReply($id): JsonResponse
    {
        $reply = QuickReply::findOrFail($id);
        $reply->delete();
        
        return response()->json(['success' => true, 'message' => 'Quick reply deleted']);
    }

    // ==================== CONTACT ASSIGNMENTS ====================
    public function assignContact(Request $request, $id): JsonResponse
    {
        $contact = Contact::findOrFail($id);
        
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $contact->update([
            'assigned_to' => $validated['assigned_to'],
        ]);

        $contact->interactions()->create([
            'type' => 'system',
            'summary' => 'Contact assigned to ' . ($contact->assignedTo->name ?? 'Unknown'),
            'notes' => 'Assigned by ' . auth()->user()->name,
            'created_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Contact assigned successfully']);
    }

    public function bulkAssignContact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_ids' => 'required|array',
            'assigned_to' => 'required|exists:users,id',
        ]);

        $contacts = Contact::whereIn('id', $validated['contact_ids'])->get();
        $user = \App\Models\User::find($validated['assigned_to']);
        
        foreach ($contacts as $contact) {
            $contact->update(['assigned_to' => $validated['assigned_to']]);
            
            $contact->interactions()->create([
                'type' => 'system',
                'summary' => 'Contact assigned to ' . $user->name,
                'notes' => 'Bulk assigned by ' . auth()->user()->name,
                'created_by' => auth()->id(),
            ]);
        }

        return response()->json(['success' => true, 'message' => count($contacts) . ' contacts assigned']);
    }

    // ==================== EXPORT FUNCTIONS ====================
    public function exportContacts(Request $request)
    {
        $contacts = Contact::with(['tags', 'assignedTo']);
        
        if ($request->has('filter')) {
            $filter = $request->filter;
            if ($filter === 'assigned') {
                $contacts->whereNotNull('assigned_to');
            } elseif ($filter === 'unassigned') {
                $contacts->whereNull('assigned_to');
            } elseif ($filter === 'recent') {
                $contacts->where('created_at', '>=', now()->subDays(7));
            }
        }
        
        $contacts = $contacts->get();
        
        $filename = 'contacts_' . date('Y-m-d') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];
        
        $callback = function() use ($contacts) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Phone', 'Email', 'Company', 'Assigned To', 'Tags', 'Created At']);
            
            foreach ($contacts as $contact) {
                fputcsv($file, [
                    $contact->id,
                    $contact->name,
                    $contact->phone,
                    $contact->email ?? '',
                    $contact->company ?? '',
                    $contact->assignedTo->name ?? 'Unassigned',
                    $contact->tags->pluck('name')->implode(', '),
                    $contact->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function exportProducts(Request $request)
    {
        $products = Product::query();
        
        if ($request->has('category')) {
            $products->where('category', $request->category);
        }
        
        $products = $products->get();
        
        $filename = 'products_' . date('Y-m-d') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];
        
        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'SKU', 'Category', 'Price', 'Stock', 'Created At']);
            
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->sku ?? '',
                    $product->category ?? '',
                    $product->price,
                    $product->stock ?? 0,
                    $product->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function exportOrders(Request $request)
    {
        $orders = Order::with(['contact']);
        
        if ($request->has('status')) {
            $orders->where('status', $request->status);
        }
        
        if ($request->has('date_from')) {
            $orders->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $orders->whereDate('created_at', '<=', $request->date_to);
        }
        
        $orders = $orders->get();
        
        $filename = 'orders_' . date('Y-m-d') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];
        
        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Contact', 'Total', 'Status', 'Payment Status', 'Created At']);
            
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->contact->name ?? 'Unknown',
                    $order->total,
                    $order->status,
                    $order->payment_status ?? 'N/A',
                    $order->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    // ==================== BULK OPERATIONS ====================
    public function bulkDeleteContacts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_ids' => 'required|array',
        ]);

        $count = Contact::whereIn('id', $validated['contact_ids'])->delete();

        return response()->json(['success' => true, 'message' => "$count contacts deleted"]);
    }

    public function bulkDeleteProducts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_ids' => 'required|array',
        ]);

        $count = Product::whereIn('id', $validated['product_ids'])->delete();

        return response()->json(['success' => true, 'message' => "$count products deleted"]);
    }

    public function bulkDeleteOrders(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_ids' => 'required|array',
        ]);

        $count = Order::whereIn('id', $validated['order_ids'])->delete();

        return response()->json(['success' => true, 'message' => "$count orders deleted"]);
    }

    // ==================== ACTIVITY LOG ====================
    public function activityLog()
    {
        $activities = [];
        $user = auth()->user();
        $userFilter = !$user->canViewAllData() ? $user->id : null;
        
        // Recent contacts - filter by user
        $contactsQuery = Contact::query();
        if ($userFilter) {
            $contactsQuery->where('created_by', $userFilter);
        }
        $recentContacts = $contactsQuery->latest()->take(10)->get()->map(function($c) {
            return [
                'type' => 'contact',
                'action' => 'created',
                'title' => $c->name,
                'description' => 'New contact added',
                'user' => 'System',
                'created_at' => $c->created_at,
            ];
        });
        
        // Recent interactions - filter by user
        $interactionsQuery = Interaction::with(['contact', 'user']);
        if ($userFilter) {
            $interactionsQuery->where('user_id', $userFilter);
        }
        $recentInteractions = $interactionsQuery->latest()->take(10)->get()->map(function($i) {
            return [
                'type' => 'interaction',
                'action' => $i->type,
                'title' => $i->contact->name ?? 'Unknown',
                'description' => $i->summary,
                'user' => $i->user->name ?? 'System',
                'created_at' => $i->created_at,
            ];
        });
        
        // Recent orders - filter by user
        $ordersQuery = Order::with(['contact']);
        if ($userFilter) {
            $ordersQuery->where('created_by', $userFilter);
        }
        $recentOrders = $ordersQuery->latest()->take(10)->get()->map(function($o) {
            return [
                'type' => 'order',
                'action' => $o->status,
                'title' => 'Order #' . $o->id,
                'description' => 'Rp ' . number_format($o->total),
                'user' => $o->contact->name ?? 'Unknown',
                'created_at' => $o->created_at,
            ];
        });
        
        $activities = $recentContacts->concat($recentInteractions)->concat($recentOrders)
            ->sortByDesc('created_at')
            ->take(50);
        
        return view('crm.activity.index', compact('activities'));
    }

    // ==================== TAGS MANAGEMENT ====================
    public function tags()
    {
        $query = Tag::withCount('contacts');
        
        // Filter by user if not super admin and column exists
        $user = auth()->user();
        if (!$user->canViewAllData() && \Schema::hasColumn('tags', 'created_by')) {
            $query->where('created_by', $user->id);
        }
        
        $tags = $query->latest()->paginate(20);
        return view('crm.tags.index', compact('tags'));
    }

    public function storeTag(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:500',
        ]);

        $tag = Tag::create([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'color' => $validated['color'] ?? '#6366f1',
            'description' => $validated['description'] ?? '',
        ]);

        return response()->json(['success' => true, 'message' => 'Tag created successfully', 'tag' => $tag]);
    }

    public function updateTag(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->id,
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:500',
        ]);

        $tag->update([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'color' => $validated['color'] ?? '#6366f1',
            'description' => $validated['description'] ?? '',
        ]);

        return response()->json(['success' => true, 'message' => 'Tag updated successfully', 'tag' => $tag]);
    }

    public function destroyTag(Tag $tag)
    {
        $tag->delete();
        return response()->json(['success' => true, 'message' => 'Tag deleted successfully']);
    }

    // ==================== CATEGORIES MANAGEMENT ====================
    public function categories()
    {
        $query = Category::withCount('products');
        
        // Filter by user if not super admin
        $user = auth()->user();
        if (!$user->canViewAllData()) {
            $query->where('created_by', $user->id);
        }
        
        $categories = $query->latest()->paginate(20);
        return view('crm.categories.index', compact('categories'));
    }

    public function showCategory(Category $category): JsonResponse
    {
        return response()->json(['success' => true, 'category' => $category]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'description' => $validated['description'] ?? '',
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Category created successfully', 'category' => $category]);
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'description' => $validated['description'] ?? '',
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json(['success' => true, 'message' => 'Category updated successfully', 'category' => $category]);
    }

    public function destroyCategory(Category $category)
    {
        $category->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted successfully']);
    }

    // ==================== CONTACT NOTES ====================
    public function contactNotes(Contact $contact)
    {
        $contact->load(['segment', 'assignedTo', 'tags']);
        $notes = is_array($contact->notes) ? $contact->notes : [];
        return view('crm.contacts.notes', compact('contact', 'notes'));
    }

    public function storeContactNote(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'note' => 'required|string|max:5000',
        ]);

        $notes = is_array($contact->notes) ? $contact->notes : [];
        $notes[] = [
            'id' => uniqid(),
            'content' => $validated['note'],
            'created_by' => auth()->user()->name,
            'created_at' => now()->toISOString(),
        ];

        $contact->update(['notes' => $notes]);

        return response()->json(['success' => true, 'message' => 'Note added successfully']);
    }

    public function deleteContactNote(Contact $contact, $noteId)
    {
        $notes = is_array($contact->notes) ? $contact->notes : [];
        $notes = array_filter($notes, function($note) use ($noteId) {
            return $note['id'] != $noteId;
        });

        $contact->update(['notes' => array_values($notes)]);

        return response()->json(['success' => true, 'message' => 'Note deleted successfully']);
    }

    // ==================== TASKS/FOLLOW-UPS ====================
    public function tasks()
    {
        $status = request()->get('status', 'all');
        $priority = request()->get('priority', 'all');
        $assigned = request()->get('assigned', 'all');

        $query = Task::with(['contact', 'assignedTo', 'createdBy']);
        
        // Filter by user if not super admin
        $user = auth()->user();
        if (!$user->canViewAllData()) {
            // Non-admin users can only see tasks assigned to them or created by them
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id);
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($priority !== 'all') {
            $query->where('priority', $priority);
        }

        if ($assigned === 'me') {
            $query->where('assigned_to', auth()->id());
        } elseif ($assigned !== 'all') {
            $query->where('assigned_to', $assigned);
        }

        $tasks = $query->orderBy('due_date', 'asc')->paginate(20);
        $users = User::where('id', '!=', 1)->get();
        $contacts = Contact::active()->get();

        // Check if user can view all data
        $user = auth()->user();
        $canViewAll = $user->canViewAllData();

        // Filter stats by user if not admin
        $statsQuery = Task::query();
        if (!$canViewAll) {
            $statsQuery->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id);
            });
        }
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->pending()->count(),
            'overdue' => (clone $statsQuery)->overdue()->count(),
            'due_today' => (clone $statsQuery)->dueToday()->count(),
        ];

        return view('crm.tasks.index', compact('tasks', 'users', 'contacts', 'stats', 'status', 'priority', 'assigned', 'canViewAll'));
    }

    public function storeTask(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'contact_id' => 'nullable|exists:contacts,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
        }

        $task = Task::create(array_merge($validated, [
            'status' => Task::STATUS_PENDING,
            'created_by' => $userId,
        ]));

        return response()->json(['success' => true, 'message' => 'Task created successfully', 'task' => $task]);
    }

    public function updateTask(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'contact_id' => 'nullable|exists:contacts,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $task->update($validated);

        return response()->json(['success' => true, 'message' => 'Task updated successfully', 'task' => $task]);
    }

    public function updateTaskStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $task->update(['status' => $validated['status']]);

        return response()->json(['success' => true, 'message' => 'Task status updated successfully']);
    }

    public function destroyTask(Task $task)
    {
        $task->delete();
        return response()->json(['success' => true, 'message' => 'Task deleted successfully']);
    }

    // ==================== USER MANAGEMENT ====================
    public function users()
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }
        $users = User::with('roles')->paginate(20);
        $roles = \App\Models\Role::with('permissions')->get();
        return view('crm.users.index', compact('users', 'roles'));
    }

    public function storeUser(Request $request)
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create($validated);
        
        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        return redirect()->route('crm.users.index')->with('success', 'User created successfully');
    }

    public function editUser(User $user)
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }

        $roles = \App\Models\Role::all();
        return view('crm.users.edit', compact('user', 'roles'));
    }

    public function updateUser(Request $request, User $user)
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        if ($request->password) {
            $validated['password'] = $request->password;
        }

        $user->update($validated);

        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        return redirect()->route('crm.users.index')->with('success', 'User updated successfully');
    }

    public function destroyUser(User $user)
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }

        $user->delete();
        return response()->json(['success' => true, 'message' => 'User deleted successfully']);
    }

    // ==================== DEALS PIPELINE ====================
    public function deals()
    {
        // Check if user can view all data
        $user = auth()->user();
        $canViewAll = $user->canViewAllData();

        // Filter deals by user if not admin
        $query = \App\Models\Deal::with('contact', 'owner');
        if (!$canViewAll) {
            $query->where('assigned_to', $user->id);
        }
        
        $deals = $query->orderBy('created_at', 'desc')->get();
        $contacts = Contact::active()->get();
        $stages = \App\Models\Deal::getStages();
        $stageColors = [
            'lead' => '#6b7280',
            'qualified' => '#3b82f6',
            'proposal' => '#f59e0b',
            'negotiation' => '#8b5cf6',
            'closed_won' => '#22c55e',
            'closed_lost' => '#ef4444',
        ];
        
        $stats = [
            'total' => $deals->count(),
            'total_value' => $deals->sum('value'),
            'won' => $deals->where('stage', 'closed_won')->count(),
            'win_rate' => $deals->whereIn('stage', ['closed_won', 'closed_lost'])->count() > 0 
                ? round($deals->where('stage', 'closed_won')->count() / $deals->whereIn('stage', ['closed_won', 'closed_lost'])->count() * 100, 1)
                : 0,
        ];

        return view('crm.deals.index', compact('deals', 'contacts', 'stages', 'stageColors', 'stats', 'canViewAll'));
    }

    public function storeDeal(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'contact_id' => 'required|exists:contacts,id',
            'value' => 'required|numeric|min:0',
            'probability' => 'nullable|integer|min:0|max:100',
            'stage' => 'required|in:lead,qualified,proposal,negotiation,closed_won,closed_lost',
            'source' => 'nullable|in:website,referral,social_media,campaign,direct,other',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['assigned_to'] = auth()->id();
        \App\Models\Deal::create($validated);

        return redirect()->route('crm.deals.index')->with('success', 'Deal created successfully');
    }

    public function showDeal(\App\Models\Deal $deal)
    {
        $deal->load('contact', 'owner');
        return view('crm.deals.show', compact('deal'));
    }

    public function updateDeal(Request $request, \App\Models\Deal $deal)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'contact_id' => 'required|exists:contacts,id',
            'value' => 'required|numeric|min:0',
            'probability' => 'nullable|integer|min:0|max:100',
            'stage' => 'required|in:lead,qualified,proposal,negotiation,closed_won,closed_lost',
            'source' => 'nullable|in:website,referral,social_media,campaign,direct,other',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $deal->update($validated);

        return redirect()->route('crm.deals.index')->with('success', 'Deal updated successfully');
    }

    public function destroyDeal(\App\Models\Deal $deal)
    {
        $deal->delete();
        return response()->json(['success' => true, 'message' => 'Deal deleted successfully']);
    }

    public function updateDealStage(Request $request, \App\Models\Deal $deal)
    {
        $validated = $request->validate([
            'stage' => 'required|in:lead,qualified,proposal,negotiation,closed_won,closed_lost',
        ]);

        $deal->update([
            'stage' => $validated['stage'],
            'actual_close_date' => in_array($validated['stage'], ['closed_won', 'closed_lost']) ? now() : null,
        ]);

        return response()->json(['success' => true]);
    }

    // ==================== SURVEYS ====================
    public function surveys()
    {
        // Check if user can view all data
        $user = auth()->user();
        $canViewAll = $user->canViewAllData();

        // Filter surveys by user if not admin
        $query = \App\Models\Survey::with('responses');
        if (!$canViewAll && \Schema::hasColumn('surveys', 'created_by')) {
            $query->where('created_by', $user->id);
        }
        
        $surveys = $query->paginate(20);
        
        // Filter stats by user if not admin
        $responsesQuery = \App\Models\SurveyResponse::query();
        if (!$canViewAll && \Schema::hasColumn('surveys', 'created_by')) {
            $surveyIds = \App\Models\Survey::where('created_by', $user->id)->pluck('id');
            $responsesQuery->whereIn('survey_id', $surveyIds);
        }
        $totalResponses = (clone $responsesQuery)->count();
        
        $npsSurveysQuery = \App\Models\Survey::where('type', 'nps')->where('status', 'active');
        if (!$canViewAll && \Schema::hasColumn('surveys', 'created_by')) {
            $npsSurveysQuery->where('created_by', $user->id);
        }
        $npsSurveys = $npsSurveysQuery->get();
        $avgNps = $npsSurveys->count() > 0 ? round($npsSurveys->avg('nps_score'), 1) : null;

        return view('crm.surveys.index', compact('surveys', 'totalResponses', 'avgNps', 'canViewAll'));
    }

    public function storeSurvey(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:nps,satisfaction,feedback',
            'status' => 'required|in:draft,active,closed',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
        ]);

        \App\Models\Survey::create($validated);

        return redirect()->route('crm.surveys.index')->with('success', 'Survey created successfully');
    }

    public function showSurvey(\App\Models\Survey $survey)
    {
        $survey->load('responses.contact');
        return view('crm.surveys.show', compact('survey'));
    }

    public function editSurvey(\App\Models\Survey $survey)
    {
        return view('crm.surveys.edit', compact('survey'));
    }

    public function updateSurvey(Request $request, \App\Models\Survey $survey)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:nps,satisfaction,feedback',
            'status' => 'required|in:draft,active,closed',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
        ]);

        $survey->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Survey updated successfully']);
        }

        return redirect()->route('crm.surveys.index')->with('success', 'Survey updated successfully');
    }

    public function destroySurvey(\App\Models\Survey $survey)
    {
        $survey->delete();
        return response()->json(['success' => true, 'message' => 'Survey deleted successfully']);
    }

    public function activateSurvey(\App\Models\Survey $survey)
    {
        $survey->update(['status' => 'active']);
        return response()->json(['success' => true]);
    }

    public function closeSurvey(\App\Models\Survey $survey)
    {
        $survey->update(['status' => 'closed']);
        return response()->json(['success' => true]);
    }

    // ==================== AUDIT LOGS ====================
    public function auditLogs()
    {
        // Check if user can view all data
        $user = auth()->user();
        $canViewAll = $user->canViewAllData();

        // Filter logs by user if not admin
        $query = \App\Models\AuditLog::with('user');
        if (!$canViewAll) {
            $query->where('user_id', $user->id);
        }
        
        $logs = $query->orderBy('created_at', 'desc')->paginate(50);
        
        // Filter stats by user if not admin
        $statsQuery = \App\Models\AuditLog::query();
        if (!$canViewAll) {
            $statsQuery->where('user_id', $user->id);
        }
        
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'today' => (clone $statsQuery)->whereDate('created_at', today())->count(),
            'week' => (clone $statsQuery)->where('created_at', '>=', now()->startOfWeek())->count(),
            'month' => (clone $statsQuery)->where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        return view('crm.audit-logs.index', compact('logs', 'stats', 'canViewAll'));
    }

    // ==================== DUPLICATE DETECTION ====================
    public function duplicates()
    {
        // For duplicate detection, we show all data since duplicates span across all users' contacts
        $user = auth()->user();
        $canViewAll = $user->canViewAllData();

        $service = new \App\Services\DuplicateDetectionService();
        $stats = $service->getStatistics();
        
        $phoneDuplicates = $service->findDuplicatePhones();
        $emailDuplicates = $service->findDuplicateEmails();
        $similarNames = $service->findSimilarNames();

        // Add similar names to stats
        $stats['similar_names'] = $similarNames->flatten()->count();

        return view('crm.duplicates.index', compact('stats', 'phoneDuplicates', 'emailDuplicates', 'similarNames', 'canViewAll'));
    }

    public function scanDuplicates()
    {
        return $this->duplicates();
    }

    public function mergeContacts(Request $request)
    {
        $keepId = $request->keep_id;
        
        // Get all IDs except keep_id from the form
        $mergeIds = [];
        foreach ($request->all() as $key => $value) {
            if ($key !== 'keep_id' && $key !== '_token' && is_numeric($key)) {
                $mergeIds[] = (int) $key;
            }
        }

        if (empty($mergeIds)) {
            return redirect()->route('crm.duplicates.index')->with('error', 'No contacts to merge');
        }

        $service = new \App\Services\DuplicateDetectionService();
        $service->mergeContacts($keepId, $mergeIds);

        return redirect()->route('crm.duplicates.index')->with('success', 'Contacts merged successfully');
    }

    // ==================== PRODUCTS CRUD ====================
    public function createProduct()
    {
        $categories = \App\Models\Category::active()->get();
        return view('crm.products.create', compact('categories'));
    }

    public function editProduct(\App\Models\Product $product)
    {
        $categories = \App\Models\Category::active()->get();
        return response()->json(['product' => $product, 'categories' => $categories]);
    }

    public function updateProduct(Request $request, \App\Models\Product $product): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'stock' => 'required|integer|min:0',
            'image_url' => 'nullable|url',
            'category' => 'nullable|string',
            'attributes' => 'nullable|array',
            'is_active' => 'nullable|in:on,off,1,0,true,false',
        ]);

        // Convert is_active to boolean
        if (isset($validated['is_active'])) {
            $validated['is_active'] = in_array($validated['is_active'], ['on', '1', 'true', true]);
        } else {
            $validated['is_active'] = false;
        }

        $product->update($validated);
        return response()->json(['success' => true, 'message' => 'Product updated successfully']);
    }

    public function destroyProduct(\App\Models\Product $product)
    {
        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
    }

    // ==================== SEGMENTS CRUD ====================
    public function createSegment()
    {
        return view('crm.segments.create');
    }

    public function showSegment(\App\Models\Segment $segment)
    {
        $segment->load('contacts');
        return response()->json(['segment' => $segment]);
    }

    public function editSegment(\App\Models\Segment $segment)
    {
        return response()->json(['segment' => $segment]);
    }

    // ==================== ORDERS CRUD ====================
    public function showOrder(Order $order)
    {
        return view('crm.orders.show', compact('order'));
    }

    public function editOrder(Order $order)
    {
        $contacts = Contact::active()->get();
        $products = Product::active()->get();
        return view('crm.orders.edit', compact('order', 'contacts', 'products'));
    }

    public function updateOrder(Request $request, Order $order)
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'items' => 'required|array',
            'shipping_address' => 'nullable|string',
            'shipping_method' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
        ]);

        $totalAmount = collect($validated['items'])->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        $order->update(array_merge($validated, ['total_amount' => $totalAmount]));

        return redirect()->route('crm.orders.index')->with('success', 'Order updated successfully');
    }

    public function destroyOrder(Order $order)
    {
        $order->delete();
        return response()->json(['success' => true, 'message' => 'Order deleted successfully']);
    }

    // ==================== TEMPLATES CRUD ====================
    public function createTemplate()
    {
        return view('crm.templates.create');
    }

    public function showTemplate(\App\Models\MessageTemplate $template)
    {
        return view('crm.templates.show', compact('template'));
    }

    public function editTemplate(\App\Models\MessageTemplate $template)
    {
        return view('crm.templates.edit', compact('template'));
    }

    public function updateTemplate(Request $request, \App\Models\MessageTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,image,document,location,contact',
            'category' => 'required|string',
            'content' => 'required|string',
            'media_url' => 'nullable|url',
            'variables' => 'nullable|array',
            'buttons' => 'nullable|array',
        ]);

        $template->update($validated);
        return redirect()->route('crm.templates.index')->with('success', 'Template updated successfully');
    }

    public function destroyTemplate(\App\Models\MessageTemplate $template)
    {
        $template->delete();
        return response()->json(['success' => true, 'message' => 'Template deleted successfully']);
    }

    // ==================== CAMPAIGNS CRUD ====================
    public function createCampaign()
    {
        $templates = \App\Models\MessageTemplate::approved()->get();
        $segments = Segment::all();
        return view('crm.campaigns.create', compact('templates', 'segments'));
    }

    public function showCampaign(\App\Models\Campaign $campaign)
    {
        $campaign->load(['template', 'creator']);
        return view('crm.campaigns.show', compact('campaign'));
    }

    public function campaignStats(\App\Models\Campaign $campaign)
    {
        $campaign->load(['template', 'creator']);
        return view('crm.campaigns.stats', compact('campaign'));
    }

    public function editCampaign(\App\Models\Campaign $campaign)
    {
        $templates = \App\Models\MessageTemplate::approved()->get();
        $segments = Segment::all();
        return view('crm.campaigns.edit', compact('campaign', 'templates', 'segments'));
    }

    public function updateCampaign(Request $request, \App\Models\Campaign $campaign)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:broadcast,sequence,trigger',
            'template_id' => 'nullable|exists:message_templates,id',
            'target_segments' => 'nullable|array',
            'target_tags' => 'nullable|array',
            'scheduled_at' => 'nullable|date',
            'settings' => 'nullable|array',
        ]);

        $campaign->update($validated);
        return redirect()->route('crm.campaigns.index')->with('success', 'Campaign updated successfully');
    }

    public function destroyCampaign(\App\Models\Campaign $campaign)
    {
        $campaign->delete();
        return response()->json(['success' => true, 'message' => 'Campaign deleted successfully']);
    }

    // ==================== AUTOMATIONS CRUD ====================
    public function createAutomation()
    {
        return view('crm.automations.create');
    }

    public function showAutomation(Automation $automation)
    {
        $automation->load('creator');
        return view('crm.automations.show', compact('automation'));
    }

    public function editAutomation(Automation $automation)
    {
        return view('crm.automations.edit', compact('automation'));
    }

    public function updateAutomation(Request $request, Automation $automation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_type' => 'required|string',
            'trigger_config' => 'nullable|array',
            'conditions' => 'nullable|array',
            'actions' => 'required|array',
            'is_active' => 'nullable|boolean',
        ]);

        $automation->update($validated);
        return redirect()->route('crm.automations.index')->with('success', 'Automation updated successfully');
    }

    public function destroyAutomation(Automation $automation)
    {
        $automation->delete();
        return response()->json(['success' => true, 'message' => 'Automation deleted successfully']);
    }

    // ==================== CHATBOTS CRUD ====================
    public function createChatbot()
    {
        $users = User::where('id', '!=', 1)->get();
        return view('crm.chatbots.create', compact('users'));
    }

    public function showChatbot(Chatbot $chatbot)
    {
        $chatbot->load('creator');
        return view('crm.chatbots.show', compact('chatbot'));
    }

    public function editChatbot(Chatbot $chatbot)
    {
        $users = User::where('id', '!=', 1)->get();
        return view('crm.chatbots.edit', compact('chatbot', 'users'));
    }

    public function chatbotSessions(Chatbot $chatbot)
    {
        $sessions = ChatbotSession::where('chatbot_id', $chatbot->id)->latest()->paginate(20);
        return view('crm.chatbots.sessions', compact('chatbot', 'sessions'));
    }

    public function updateChatbot(Request $request, Chatbot $chatbot)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'flows' => 'required|array',
            'keywords' => 'nullable|array',
            'default_response' => 'nullable|array',
            'fallback_response' => 'nullable|array',
            'handover_enabled' => 'nullable|boolean',
            'handover_to' => 'nullable|exists:users,id',
            'working_hours' => 'nullable|array',
        ]);

        $chatbot->update($validated);
        return redirect()->route('crm.chatbots.index')->with('success', 'Chatbot updated successfully');
    }

    public function destroyChatbot(Chatbot $chatbot)
    {
        $chatbot->delete();
        return response()->json(['success' => true, 'message' => 'Chatbot deleted successfully']);
    }

    // ==================== ROLES MANAGEMENT ====================
    public function roles()
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }
        $roles = Role::with('permissions')->paginate(20);
        return view('crm.roles.index', compact('roles'));
    }

    public function createRole()
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }

        $permissions = Permission::all()->groupBy('module');
        return view('crm.roles.create', compact('permissions'));
    }

    public function storeRole(Request $request)
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'slug' => 'required|string|max:255|unique:roles,slug',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'is_default' => 'nullable|boolean',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create($validated);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('crm.roles.index')->with('success', 'Role created successfully');
    }

    public function editRole(Role $role)
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }

        $permissions = Permission::all()->groupBy('module');
        $role->load('permissions');
        return view('crm.roles.edit', compact('role', 'permissions'));
    }

    public function getRoleData(Role $role)
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }

        return response()->json([
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => $role->description,
            'permissions' => $role->permissions->pluck('id')->toArray()
        ]);
    }

    public function updateRole(Request $request, Role $role)
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'slug' => 'required|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'is_default' => 'nullable|boolean',
            'permissions' => 'nullable|array',
        ]);

        $role->update($validated);

        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('crm.roles.index')->with('success', 'Role updated successfully');
    }

    public function destroyRole(Role $role)
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }

        if ($role->users()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete role with assigned users'], 400);
        }

        $role->delete();
        return response()->json(['success' => true, 'message' => 'Role deleted successfully']);
    }

    // ==================== PERMISSIONS MANAGEMENT ====================
    public function permissions()
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }
        $permissions = Permission::all()->groupBy('module');
        return view('crm.permissions.index', compact('permissions'));
    }

    public function createPermission()
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }

        return view('crm.permissions.create');
    }

    public function storePermission(Request $request)
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'slug' => 'required|string|max:255|unique:permissions,slug',
            'description' => 'nullable|string',
            'module' => 'nullable|string|max:255',
        ]);

        Permission::create($validated);
        return redirect()->route('crm.permissions.index')->with('success', 'Permission created successfully');
    }

    public function editPermission(Permission $permission)
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }

        return view('crm.permissions.edit', compact('permission'));
    }

    public function getPermissionData(Permission $permission)
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, "Unauthorized access.");
        }

        return response()->json([
            "id" => $permission->id,
            "name" => $permission->name,
            "slug" => $permission->slug,
            "description" => $permission->description,
            "module" => $permission->module
        ]);
    }

    public function updatePermission(Request $request, Permission $permission)
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'slug' => 'required|string|max:255|unique:permissions,slug,' . $permission->id,
            'description' => 'nullable|string',
            'module' => 'nullable|string|max:255',
        ]);

        $permission->update($validated);
        return redirect()->route('crm.permissions.index')->with('success', 'Permission updated successfully');
    }

    public function destroyPermission(Permission $permission)
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }

        $permission->delete();
        return response()->json(['success' => true, 'message' => 'Permission deleted successfully']);
    }

    // ==================== USER CREATE ====================
    public function createUser()
    {
        if (!Auth::user()->canViewAllData()) {
            abort(403, 'Unauthorized access.');
        }

        $roles = Role::all();
        return view('crm.users.create', compact('roles'));
    }
}
