@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Support Tickets</h1>
            <p class="text-gray-500 mt-1">Manage customer support tickets</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(!Auth::user()->canViewAllData())
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full">
                    <i class="fas fa-filter mr-1"></i> Showing My Tickets Only
                </span>
            @endif
            <button onclick="openModal('createTicketModal')" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                <i class="fas fa-plus mr-2"></i> New Ticket
            </button>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        @php
            $ticketQuery = \App\Models\Ticket::query();
            if (!Auth::user()->canViewAllData()) {
                $ticketQuery->where(function ($q) {
                    $q->where('assigned_to', Auth::id())
                      ->orWhere('created_by', Auth::id());
                });
            }
            $openCount = (clone $ticketQuery)->where('status', 'open')->count();
            $inProgressCount = (clone $ticketQuery)->where('status', 'in_progress')->count();
            $waitingCount = (clone $ticketQuery)->where('status', 'waiting_customer')->count();
            $resolvedCount = (clone $ticketQuery)->whereIn('status', ['resolved', 'closed'])->count();
            $totalCount = (clone $ticketQuery)->count();
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Open</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($openCount) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <i class="fas fa-exclamation-circle text-red-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">In Progress</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ number_format($inProgressCount) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                    <i class="fas fa-spinner text-yellow-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Waiting</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($waitingCount) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-clock text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Resolved</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($resolvedCount) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($totalCount) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-ticket-alt text-gray-600"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            <div class="w-48">
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="waiting_customer" {{ request('status') === 'waiting_customer' ? 'selected' : '' }}>Waiting Customer</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div class="w-40">
                <select name="priority" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="">All Priority</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors">
                <i class="fas fa-filter mr-2"></i> Filter
            </button>
        </form>
    </div>
    
    <!-- Tickets Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-800">All Tickets</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($tickets as $ticket)
                    <tr class="hover:bg-gray-50 {{ $ticket->is_overdue ? 'bg-red-50' : '' }}">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-800">{{ $ticket->subject }}</p>
                                <p class="text-sm text-gray-500">{{ $ticket->ticket_number }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                    <span class="text-xs text-gray-600">{{ $ticket->contact->initials ?? '?' }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $ticket->contact->display_name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500">{{ $ticket->contact->phone ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'open' => 'red',
                                    'in_progress' => 'yellow',
                                    'waiting_customer' => 'blue',
                                    'resolved' => 'green',
                                    'closed' => 'gray'
                                ];
                                $statusColor = $statusColors[$ticket->status] ?? 'gray';
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $priorityColors = [
                                    'low' => 'gray',
                                    'medium' => 'blue',
                                    'high' => 'orange',
                                    'urgent' => 'red'
                                ];
                                $priorityColor = $priorityColors[$ticket->priority] ?? 'gray';
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $priorityColor }}-100 text-{{ $priorityColor }}-800">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($ticket->agent)
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
                                    <span class="text-xs text-gray-600">{{ substr($ticket->agent->name, 0, 1) }}</span>
                                </div>
                                <span class="text-sm text-gray-600">{{ $ticket->agent->name }}</span>
                            </div>
                            @else
                            <span class="text-sm text-gray-400">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                            {{ $ticket->created_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <button onclick="openViewTicketModal('{{ $ticket->id }}', '{{ $ticket->ticket_number }}', '{{ $ticket->subject }}', '{{ $ticket->contact->display_name ?? 'Unknown' }}', '{{ $ticket->contact->phone ?? '' }}', '{{ $ticket->status }}', '{{ $ticket->priority }}', '{{ $ticket->created_at->format('d M Y H:i') }}', '{{ $ticket->js_description }}')" class="text-whatsapp-dark hover:text-whatsapp-light text-sm" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($ticket->status !== 'closed')
                                <button onclick="openSendMessageModal('{{ $ticket->id }}', '{{ $ticket->contact->phone ?? '' }}', '{{ $ticket->contact->display_name ?? 'Unknown' }}')" class="text-blue-600 hover:text-blue-400 text-sm" title="Send Message">
                                    <i class="fas fa-comment"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                </svg>
                                <p>No tickets found</p>
                                <button onclick="openModal('createTicketModal')" class="mt-2 text-whatsapp-dark hover:underline">Create your first ticket</button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($tickets->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $tickets->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Create Ticket Modal -->
<div id="createTicketModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Create New Ticket</h3>
                <button onclick="closeModal('createTicketModal')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <form id="createTicketForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact <span class="text-red-500">*</span></label>
                <select name="contact_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="">Select Contact</option>
                    @foreach(\App\Models\Contact::active()->get() as $contact)
                    <option value="{{ $contact->id }}">{{ $contact->display_name }} ({{ $contact->phone }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject <span class="text-red-500">*</span></label>
                <input type="text" name="subject" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                <textarea name="description" rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select name="priority" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                        <option value="general">General</option>
                        <option value="support">Support</option>
                        <option value="complaint">Complaint</option>
                        <option value="sales">Sales</option>
                        <option value="feedback">Feedback</option>
                    </select>
                </div>
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeModal('createTicketModal')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                    Create Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Ticket Modal -->
<div id="viewTicketModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Ticket Details</h3>
                <button onclick="closeModal('viewTicketModal')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <span id="viewTicketNumber" class="text-sm text-gray-500 font-mono"></span>
                    <div class="flex space-x-2">
                        <span id="viewTicketStatus" class="px-2 py-1 text-xs font-medium rounded-full"></span>
                        <span id="viewTicketPriority" class="px-2 py-1 text-xs font-medium rounded-full"></span>
                    </div>
                </div>
                <h4 id="viewTicketSubject" class="text-lg font-semibold text-gray-800 mb-2"></h4>
                <p id="viewTicketDescription" class="text-sm text-gray-600"></p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Contact</p>
                    <p id="viewContactName" class="font-medium text-gray-800"></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Phone</p>
                    <p id="viewContactPhone" class="font-medium text-gray-800"></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Created</p>
                    <p id="viewTicketCreated" class="font-medium text-gray-800"></p>
                </div>
            </div>
            
            <div class="flex justify-end pt-4 space-x-3 border-t">
                <button onclick="closeModal('viewTicketModal')" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Close
                </button>
                <a id="viewTicketLink" href="" class="px-6 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                    View Full Details
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Send Message Modal -->
<div id="sendMessageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Send Message</h3>
                <button onclick="closeSendMessageModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <form id="sendMessageForm" class="p-6 space-y-4">
            <input type="hidden" name="ticketId" id="modalTicketId" value="">
            <input type="hidden" name="chatId" id="modalChatId" value="">
            
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-sm text-gray-500">Recipient</p>
                <p class="font-medium text-gray-800" id="modalRecipientName">-</p>
                <p class="text-sm text-gray-600" id="modalRecipientPhone">-</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Session *</label>
                <select name="sessionId" id="modalSessionId" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="">Select a session</option>
                    @if(isset($sessions['data']))
                        @foreach($sessions['data'] as $session)
                        <option value="{{ $session['sessionId'] }}" {{ $session['status'] === 'connected' ? '' : 'disabled' }}>
                            {{ $session['sessionId'] }} {{ $session['status'] === 'connected' ? '(Connected)' : '(' . $session['status'] . ')' }}
                        </option>
                        @endforeach
                    @endif
                </select>
            </div>
            
            <!-- Message Type Tabs -->
            <div class="flex space-x-2 border-b border-gray-200 pb-2">
                <button type="button" onclick="selectModalMessageType('text')" class="modal-type-btn px-3 py-1 text-sm rounded-md bg-whatsapp-light text-white" data-type="text">Text</button>
                <button type="button" onclick="selectModalMessageType('image')" class="modal-type-btn px-3 py-1 text-sm rounded-md hover:bg-gray-100" data-type="image">Image</button>
                <button type="button" onclick="selectModalMessageType('document')" class="modal-type-btn px-3 py-1 text-sm rounded-md hover:bg-gray-100" data-type="document">Document</button>
            </div>
            
            <!-- Text Message Fields -->
            <div id="modalTextFields" class="modal-message-fields space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                    <textarea name="message" id="modalMessage" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="Type your message here..."></textarea>
                </div>
            </div>
            
            <!-- Image Fields -->
            <div id="modalImageFields" class="modal-message-fields space-y-4 hidden">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image URL *</label>
                    <input type="url" name="imageUrl" id="modalImageUrl"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="https://example.com/image.jpg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                    <textarea name="caption" id="modalCaption" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="Image caption (optional)"></textarea>
                </div>
            </div>
            
            <!-- Document Fields -->
            <div id="modalDocumentFields" class="modal-message-fields space-y-4 hidden">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Document URL *</label>
                    <input type="url" name="documentUrl" id="modalDocumentUrl"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="https://example.com/document.pdf">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filename *</label>
                    <input type="text" name="filename" id="modalFilename"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="document.pdf">
                </div>
            </div>
            
            <div class="flex justify-end pt-4 space-x-3">
                <button type="button" onclick="closeSendMessageModal()" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                    Send Message
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.getElementById(id).classList.add('flex');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).classList.remove('flex');
}

let currentModalMessageType = 'text';

function openSendMessageModal(ticketId, phone, name) {
    if (!phone) {
        showToast('No phone number available for this contact', 'error');
        return;
    }
    
    document.getElementById('modalTicketId').value = ticketId;
    document.getElementById('modalChatId').value = phone;
    document.getElementById('modalRecipientName').textContent = name;
    document.getElementById('modalRecipientPhone').textContent = phone;
    
    // Reset form
    document.getElementById('sendMessageForm').reset();
    selectModalMessageType('text');
    
    // Open modal
    openModal('sendMessageModal');
}

function closeSendMessageModal() {
    closeModal('sendMessageModal');
}

function openViewTicketModal(id, ticketNumber, subject, contactName, contactPhone, status, priority, created, description) {
    document.getElementById('viewTicketNumber').textContent = ticketNumber;
    document.getElementById('viewTicketSubject').textContent = subject;
    document.getElementById('viewTicketDescription').textContent = description;
    document.getElementById('viewContactName').textContent = contactName;
    document.getElementById('viewContactPhone').textContent = contactPhone;
    document.getElementById('viewTicketCreated').textContent = created;
    
    // Status badge
    const statusEl = document.getElementById('viewTicketStatus');
    statusEl.textContent = status.replace('_', ' ');
    statusEl.className = 'px-2 py-1 text-xs font-medium rounded-full';
    if (status === 'open') {
        statusEl.classList.add('bg-red-100', 'text-red-800');
    } else if (status === 'in_progress') {
        statusEl.classList.add('bg-yellow-100', 'text-yellow-800');
    } else if (status === 'waiting_customer') {
        statusEl.classList.add('bg-blue-100', 'text-blue-800');
    } else if (status === 'resolved') {
        statusEl.classList.add('bg-green-100', 'text-green-800');
    } else {
        statusEl.classList.add('bg-gray-100', 'text-gray-800');
    }
    
    // Priority badge
    const priorityEl = document.getElementById('viewTicketPriority');
    priorityEl.textContent = priority;
    priorityEl.className = 'px-2 py-1 text-xs font-medium rounded-full';
    if (priority === 'urgent') {
        priorityEl.classList.add('bg-red-100', 'text-red-800');
    } else if (priority === 'high') {
        priorityEl.classList.add('bg-orange-100', 'text-orange-800');
    } else if (priority === 'medium') {
        priorityEl.classList.add('bg-yellow-100', 'text-yellow-800');
    } else {
        priorityEl.classList.add('bg-gray-100', 'text-gray-800');
    }
    
    // Set link to full details page
    document.getElementById('viewTicketLink').href = '/crm/tickets/' + id;
    
    openModal('viewTicketModal');
}

function selectModalMessageType(type) {
    currentModalMessageType = type;
    
    // Update button styles
    document.querySelectorAll('.modal-type-btn').forEach(btn => {
        btn.classList.remove('bg-whatsapp-light', 'text-white');
        btn.classList.add('hover:bg-gray-100');
        if (btn.dataset.type === type) {
            btn.classList.add('bg-whatsapp-light', 'text-white');
            btn.classList.remove('hover:bg-gray-100');
        }
    });
    
    // Show/hide fields
    document.querySelectorAll('.modal-message-fields').forEach(field => {
        field.classList.add('hidden');
    });
    document.getElementById('modal' + type.charAt(0).toUpperCase() + type.slice(1) + 'Fields').classList.remove('hidden');
}

document.getElementById('createTicketForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => data[key] = value);
    
    try {
        const response = await fetch('{{ route("crm.tickets.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Ticket created successfully!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('Failed to create ticket: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Error creating ticket: ' + error.message, 'error');
    }
});

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} z-50`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Send Message Modal Form Submission
document.getElementById('sendMessageForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const sessionId = formData.get('sessionId');
    const chatId = formData.get('chatId');
    const typingTime = 0;
    const replyTo = null;
    
    let endpoint = '';
    let data = { sessionId, chatId, typingTime, replyTo };
    
    switch (currentModalMessageType) {
        case 'text':
            endpoint = '{{ route("api.send.text") }}';
            data.message = formData.get('message');
            break;
        case 'image':
            endpoint = '{{ route("api.send.image") }}';
            data.imageUrl = formData.get('imageUrl');
            data.caption = formData.get('caption');
            break;
        case 'document':
            endpoint = '{{ route("api.send.document") }}';
            data.documentUrl = formData.get('documentUrl');
            data.filename = formData.get('filename');
            break;
    }
    
    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Message sent successfully!', 'success');
            closeSendMessageModal();
        } else {
            showToast(result.message || 'Failed to send message', 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
});
</script>
@endpush
