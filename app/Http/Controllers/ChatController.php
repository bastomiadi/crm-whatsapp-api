<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Contact;
use App\Models\QuickReply;
use App\Models\User;
use App\Services\ChateryApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ChatController extends Controller
{
    protected $chatery;
    
    public function __construct(ChateryApiService $chatery)
    {
        $this->chatery = $chatery;
    }

    public function index(Request $request): View
    {
        $sessionId = $request->get('session');
        
        // Get sessions from Chatery
        $sessions = $this->getSessions();

        // Fetch conversations from Chatery API if session is selected
        $apiChats = [];
        $apiError = null;
        if ($sessionId) {
            try {
                $apiChats = $this->fetchChatsFromApi($sessionId);
            } catch (\Exception $e) {
                $apiError = $e->getMessage();
            }
        }
        
        // Get database conversations
        $query = ChatConversation::with(['contact', 'assignedTo', 'latestMessage'])
            ->active();
            
        if ($sessionId) {
            $query->where('session_id', $sessionId);
        }
        
        $dbConversations = $query->orderBy('last_message_at', 'desc')->get();

        // Merge API chats with database conversations
        $conversations = $this->mergeConversations($dbConversations, $apiChats);

        $totalConversations = count($conversations);
        $unreadConversations = ChatConversation::unread()->count();
        $assignedToMe = ChatConversation::where('assigned_to', auth()->id())->count();

        return view('crm.chat.index', compact(
            'conversations',
            'totalConversations',
            'unreadConversations',
            'assignedToMe',
            'sessions',
            'sessionId',
            'apiChats',
            'apiError'
        ));
    }
    
    // Test API connection
    public function testApi(): JsonResponse
    {
        try {
            $sessions = $this->getSessions();
            return response()->json([
                'success' => true,
                'message' => 'API connection successful',
                'sessions_count' => count($sessions),
                'sessions' => $sessions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'API connection failed: ' . $e->getMessage()
            ]);
        }
    }
    
    // Get conversations list for real-time refresh
    public function getConversations(Request $request): JsonResponse
    {
        try {
            $sessionId = $request->query('session');
            $lastUpdate = $request->query('last_update', 0);
            
            // Get database conversations with relationships
            $query = ChatConversation::with(['contact', 'assignedTo', 'latestMessage'])
                ->active();
                
            if ($sessionId) {
                $query->where('session_id', $sessionId);
            }
            
            // Only get updated conversations since last update
            if ($lastUpdate > 0) {
                $query->where('updated_at', '>', date('Y-m-d H:i:s', $lastUpdate));
            }
            
            $conversations = $query->orderBy('last_message_at', 'desc')
                ->limit(50)
                ->get();
            
            // Format conversations for JSON response
            $formattedConversations = $conversations->map(function($conv) {
                return [
                    'id' => $conv->id,
                    'phone' => $conv->phone,
                    'name' => $conv->name,
                    'session_id' => $conv->session_id,
                    'contact_id' => $conv->contact_id,
                    'assigned_to' => $conv->assigned_to,
                    'status' => $conv->status,
                    'unread_count' => $conv->unread_count,
                    'last_message_at' => $conv->last_message_at ? $conv->last_message_at->toIso8601String() : null,
                    'created_at' => $conv->created_at ? $conv->created_at->toIso8601String() : null,
                    'updated_at' => $conv->updated_at ? $conv->updated_at->toIso8601String() : null,
                    'is_api_only' => false,
                    'latest_message' => $conv->latestMessage ? [
                        'id' => $conv->latestMessage->id,
                        'message' => $conv->latestMessage->message,
                        'direction' => $conv->latestMessage->direction,
                        'created_at' => $conv->latestMessage->created_at ? $conv->latestMessage->created_at->toIso8601String() : null,
                    ] : null,
                    'assigned_to_id' => $conv->assignedTo ? $conv->assignedTo->id : null,
                ];
            });
            
            // Get unread count
            $unreadCount = ChatConversation::unread()->count();
            
            return response()->json([
                'success' => true,
                'conversations' => $formattedConversations,
                'unread_count' => $unreadCount,
                'timestamp' => time()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    // Check for new messages (for polling)
    public function checkNewMessages(Request $request): JsonResponse
    {
        try {
            $sessionId = $request->query('session');
            $lastId = $request->query('last_id', 0);
            
            $query = ChatMessage::where('id', '>', $lastId);
            
            if ($sessionId) {
                $query->where('session_id', $sessionId);
            }
            
            $newMessages = $query->orderBy('id', 'asc')
                ->limit(50)
                ->get();
            
            return response()->json([
                'success' => true,
                'new_messages' => $newMessages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    // Get unread conversation count
    public function getUnreadCount(): JsonResponse
    {
        try {
            $unreadCount = ChatConversation::where('unread_count', '>', 0)->count();
            
            return response()->json([
                'success' => true,
                'unread' => $unreadCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    protected function getSessions()
    {
        try {
            $response = $this->chatery->getSessions();
            \Log::info('Sessions API response:', $response);
            return $response['data'] ?? [];
        } catch (\Exception $e) {
            \Log::error('Error fetching sessions: ' . $e->getMessage());
            return [];
        }
    }
    
    protected function fetchChatsFromApi(string $sessionId): array
    {
        try {
            \Log::info('Fetching chats for session:', ['sessionId' => $sessionId]);
            $response = $this->chatery->getChatOverview([
                'sessionId' => $sessionId,
                'limit' => 50,
            ]);
            \Log::info('Chats API response:', $response);
            return $response['data'] ?? [];
        } catch (\Exception $e) {
            \Log::error('Error fetching chats: ' . $e->getMessage());
            return [];
        }
    }
    
    protected function mergeConversations($dbConversations, array $apiChats): array
    {
        // Build phone to conversation mapping from database
        $phoneMap = [];
        foreach ($dbConversations as $conv) {
            $phoneMap[$conv->phone] = $conv;
        }
        
        // Process API chats and merge with database
        $merged = [];
        foreach ($dbConversations as $conv) {
            $merged[$conv->phone] = $conv;
        }
        
        // Add API chats that don't exist in database
        foreach ($apiChats as $apiChat) {
            // Extract phone from chat ID (e.g., "62812345678@s.whatsapp.net" -> "62812345678")
            $chatId = $apiChat['id'] ?? '';
            $phone = $chatId;
            if (str_contains($phone, '@s.whatsapp.net')) {
                $phone = str_replace('@s.whatsapp.net', '', $phone);
            }
            
            // Get profile picture and isGroup from API
            $profilePicture = $apiChat['profilePicture'] ?? $apiChat['profilePictureUrl'] ?? null;
            $isGroup = $apiChat['isGroup'] ?? false;
            
            // If phone exists in database, update it with API info
            if (isset($merged[$phone])) {
                $merged[$phone]->api_unread_count = $apiChat['unreadCount'] ?? 0;
                $merged[$phone]->api_last_message = $apiChat['lastMessage'] ?? '';
                $merged[$phone]->profile_picture = $profilePicture;
                $merged[$phone]->is_group = $isGroup;
            } else {
                // Create a virtual conversation from API
                $merged[$phone] = (object) [
                    'id' => 'api_' . $phone,
                    'phone' => $phone,
                    'name' => $apiChat['name'] ?? $phone,
                    'api_unread_count' => $apiChat['unreadCount'] ?? 0,
                    'api_last_message' => $apiChat['lastMessage'] ?? '',
                    'profile_picture' => $profilePicture,
                    'is_group' => $isGroup,
                    'last_message_at' => now(),
                    'is_api_only' => true,
                ];
            }
        }
        
        // Sort by last_message_at
        usort($merged, function($a, $b) {
            return ($b->last_message_at ?? now())->compareTo($a->last_message_at ?? now());
        });
        
        return array_values($merged);
    }

    public function show(ChatConversation $conversation): JsonResponse
    {
        $conversation->load(['messages', 'contact', 'assignedTo']);
        
        // Mark as read
        $conversation->markAsRead();
        
        return response()->json([
            'success' => true,
            'data' => $conversation
        ]);
    }

    public function getMessages(ChatConversation $conversation): JsonResponse
    {
        $messages = $conversation->messages()->orderBy('created_at', 'asc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    public function sendMessage(Request $request, ChatConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'media_url' => 'nullable|string',
            'media_type' => 'nullable|string',
        ]);

        // Create outbound message
        $message = $conversation->addMessage(
            $validated['message'],
            'outbound',
            [
                'media_url' => $validated['media_url'] ?? null,
                'media_type' => $validated['media_type'] ?? null,
            ]
        );

        $message->update([
            'user_id' => auth()->id(),
            'status' => 'sent',
        ]);

        // TODO: Send via Chatery API
        // $this->chateryService->sendMessage($conversation->phone, $validated['message']);

        return response()->json([
            'success' => true,
            'data' => $message,
            'message' => 'Message sent successfully'
        ]);
    }

    public function assign(Request $request, ChatConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $conversation->update([
            'assigned_to' => $validated['assigned_to'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Conversation assigned successfully'
        ]);
    }

    public function close(ChatConversation $conversation): JsonResponse
    {
        $conversation->update([
            'status' => 'closed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Conversation closed'
        ]);
    }

    public function getQuickReplies(): JsonResponse
    {
        $quickReplies = QuickReply::all();
        
        return response()->json([
            'success' => true,
            'data' => $quickReplies
        ]);
    }

    public function getContacts(Request $request): JsonResponse
    {
        $search = $request->get('search', '');
        
        $contacts = Contact::where('name', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%")
            ->limit(10)
            ->get(['id', 'name', 'phone']);

        return response()->json([
            'success' => true,
            'data' => $contacts
        ]);
    }

    public function createFromContact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
        ]);

        $contact = Contact::findOrFail($validated['contact_id']);

        // Check if conversation exists
        $conversation = ChatConversation::where('contact_id', $contact->id)
            ->where('status', 'active')
            ->first();

        if (!$conversation) {
            $conversation = ChatConversation::create([
                'phone' => $contact->phone,
                'name' => $contact->name,
                'contact_id' => $contact->id,
                'status' => 'active',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $conversation
        ]);
    }

    public function markRead(ChatConversation $conversation): JsonResponse
    {
        $conversation->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Marked as read'
        ]);
    }

    public function getUsers(): JsonResponse
    {
        $users = User::all(['id', 'name', 'email']);
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function getSessionsList(): JsonResponse
    {
        $sessions = $this->getSessions();
        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }
    
    public function getChats(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);
        
        $chats = $this->fetchChatsFromApi($request->session_id);
        
        return response()->json([
            'success' => true,
            'data' => $chats
        ]);
    }
    
    public function getChatMessages(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'chat_id' => 'required|string',
        ]);
        
        try {
            $response = $this->chatery->getMessages([
                'sessionId' => $request->session_id,
                'chatId' => $request->chat_id,
                'limit' => 50,
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $response['data'] ?? []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    // Webhook handler to store incoming messages
    public function webhook(Request $request): JsonResponse
    {
        $data = $request->all();
        
        // Log webhook data for debugging
        \Log::info('Webhook received:', $data);
        
        // Chatery webhook format: { sessionId, type, timestamp, data: { ... } }
        $eventType = $data['type'] ?? '';
        $sessionId = $data['sessionId'] ?? null;
        $eventData = $data['data'] ?? [];
        
        // Handle message events
        if ($eventType === 'message') {
            $phone = $eventData['from'] ?? '';
            $messageText = $eventData['content'] ?? $eventData['caption'] ?? '';
            $messageId = $eventData['id'] ?? null;
            $pushName = $eventData['pushName'] ?? null;
            $messageType = $eventData['type'] ?? 'text';
            
            // Extract phone number without @s.whatsapp.net
            if (str_contains($phone, '@s.whatsapp.net')) {
                $phone = str_replace('@s.whatsapp.net', '', $phone);
            }
            
            if (!$phone || !$messageText) {
                \Log::warning('Webhook received invalid message data:', $eventData);
                return response()->json(['success' => false, 'message' => 'Invalid message data']);
            }
            
            // Find contact
            $contact = Contact::where('phone', $phone)->first();
            $contactId = $contact ? $contact->id : null;

            // Find or create conversation
            $conversation = ChatConversation::firstOrCreate(
                ['phone' => $phone],
                [
                    'name' => $pushName ?? ($contact ? $contact->name : $phone),
                    'contact_id' => $contactId,
                    'session_id' => $sessionId,
                    'status' => 'active',
                ]
            );
            
            // Update session_id if provided
            if ($sessionId && !$conversation->session_id) {
                $conversation->update(['session_id' => $sessionId]);
            }

            // Add message
            $message = $conversation->addMessage($messageText, 'inbound', [
                'message_id' => $messageId,
                'message_type' => $messageType,
                'session_id' => $sessionId,
            ]);

            // Update contact if exists
            if ($contact) {
                $contact->update(['last_contacted_at' => now()]);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'conversation_id' => $conversation->id,
                    'message_id' => $message->id,
                ]
            ]);
        }
        
        // Handle connection.update events
        if ($eventType === 'connection.update') {
            $status = $eventData['status'] ?? '';
            $phoneNumber = $eventData['phoneNumber'] ?? null;
            $name = $eventData['name'] ?? null;
            
            \Log::info('Connection update received:', [
                'sessionId' => $sessionId,
                'status' => $status,
                'phoneNumber' => $phoneNumber,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Connection update processed'
            ]);
        }
        
        // Handle other event types
        return response()->json([
            'success' => true,
            'message' => 'Event received: ' . $eventType
        ]);
    }
    
    // Get conversation by phone number
    public function getByPhone(string $phone): JsonResponse
    {
        // Clean phone number
        $phone = urldecode($phone);
        if (str_contains($phone, '@s.whatsapp.net')) {
            $phone = str_replace('@s.whatsapp.net', '', $phone);
        }
        
        $conversation = ChatConversation::with(['contact', 'assignedTo', 'latestMessage'])
            ->where('phone', $phone)
            ->first();
        
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'data' => $conversation
        ]);
    }
    
    // Upload file and return URL
    public function uploadFile(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // max 10MB
        ]);
        
        $file = $request->file('file');
        
        // Store in public storage
        $path = $file->store('uploads/chat', 'public');
        
        // Generate full URL
        $url = asset('storage/' . $path);
        
        return response()->json([
            'success' => true,
            'data' => [
                'url' => $url,
                'filename' => $file->getClientOriginalName(),
                'mimeType' => $file->getMimeType(),
            ]
        ]);
    }
}
