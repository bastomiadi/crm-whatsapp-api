@extends('layouts.app')

@section('title', 'Ticket Details')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <nav class="flex items-center space-x-2 text-sm text-gray-500">
            <a href="{{ route('crm.tickets.index') }}" class="hover:text-gray-700">Tickets</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-900">#{{ $ticket->id }}</span>
        </nav>
        <a href="{{ route('crm.tickets.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Tickets
        </a>
    </div>

    <!-- Ticket Header -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                    <span class="text-sm text-gray-500 font-mono">{{ $ticket->ticket_number }}</span>
                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                        @if($ticket->status === 'open') bg-red-100 text-red-800
                        @elseif($ticket->status === 'in_progress') bg-yellow-100 text-yellow-800
                        @elseif($ticket->status === 'waiting_customer') bg-blue-100 text-blue-800
                        @elseif($ticket->status === 'resolved') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                    </span>
                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                        @if($ticket->priority === 'urgent') bg-red-100 text-red-800
                        @elseif($ticket->priority === 'high') bg-orange-100 text-orange-800
                        @elseif($ticket->priority === 'medium') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                </div>
                <h1 class="text-xl font-bold text-gray-800">{{ $ticket->subject }}</h1>
                <p class="text-sm text-gray-500 mt-1">Created {{ $ticket->created_at->format('d M Y H:i') }}</p>
            </div>
            <div class="flex space-x-2">
                <button onclick="openModal('statusModal')" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-900 transition-colors">
                    <i class="fas fa-sync-alt mr-2"></i> Update Status
                </button>
                @if($ticket->contact && $ticket->contact->phone)
                <button onclick="openModal('whatsappModal')" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition-colors">
                    <i class="fab fa-whatsapp mr-2"></i> Send WhatsApp
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Left Sidebar -->
        <div class="space-y-6">
            <!-- Contact Info -->
            @if($ticket->contact)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b bg-gray-50">
                    <h3 class="font-semibold text-gray-800 text-sm">Contact Information</h3>
                </div>
                <div class="p-4">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                            <span class="text-gray-600 font-medium">{{ $ticket->contact->initials }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $ticket->contact->display_name }}</p>
                            <p class="text-sm text-gray-500">{{ $ticket->contact->phone }}</p>
                        </div>
                    </div>
                    @if($ticket->contact->email)
                    <div class="text-sm">
                        <p class="text-gray-500">Email</p>
                        <p class="text-gray-800">{{ $ticket->contact->email }}</p>
                    </div>
                    @endif
                    <a href="{{ route('crm.contacts.show', $ticket->contact) }}" class="mt-3 block text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition-colors">
                        View Contact Profile
                    </a>
                </div>
            </div>
            @endif

            <!-- Ticket Details -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b bg-gray-50">
                    <h3 class="font-semibold text-gray-800 text-sm">Ticket Details</h3>
                </div>
                <div class="p-4 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Category</span>
                        <span class="text-gray-800">{{ ucfirst($ticket->category ?? 'General') }}</span>
                    </div>
                    @if($ticket->assigned_to)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Assigned To</span>
                        <span class="text-gray-800">{{ $ticket->assignedUser->name ?? 'Unknown' }}</span>
                    </div>
                    @else
                    <div class="flex justify-between">
                        <span class="text-gray-500">Assigned To</span>
                        <span class="text-gray-400">Unassigned</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500">Last Updated</span>
                        <span class="text-gray-800">{{ $ticket->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b bg-gray-50">
                    <h3 class="font-semibold text-gray-800 text-sm">Quick Actions</h3>
                </div>
                <div class="p-4 space-y-2">
                    <a href="{{ route('crm.tickets.send-message', $ticket) }}" class="flex items-center justify-center px-4 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm hover:bg-blue-200 transition-colors">
                        <i class="fas fa-envelope mr-2"></i> Send Message
                    </a>
                </div>
            </div>
        </div>

        <!-- Conversation -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Conversation</h3>
                    <span class="text-sm text-gray-500">{{ $ticket->messages->count() }} messages</span>
                </div>

                <!-- Messages List -->
                <div class="p-6 space-y-4 max-h-[500px] overflow-y-auto" id="messagesContainer">
                    @forelse($ticket->messages as $message)
                    <div class="flex {{ $message->is_internal ? 'justify-center' : ($message->sender_type === 'contact' ? 'justify-start' : 'justify-end') }}">
                        @if($message->is_internal)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 max-w-lg w-full">
                            <div class="flex items-center space-x-2 mb-2">
                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span class="text-xs font-medium text-yellow-800">Internal Note</span>
                            </div>
                            <p class="text-sm text-gray-700">{{ $message->message }}</p>
                            <p class="text-xs text-gray-500 mt-2">{{ $message->created_at->format('d M Y H:i') }} • {{ $message->sender->name ?? 'System' }}</p>
                        </div>
                        @else
                        <div class="{{ $message->sender_type === 'contact' ? 'bg-gray-100' : 'bg-whatsapp-light text-white' }} rounded-lg p-4 max-w-lg">
                            <p class="text-sm whitespace-pre-wrap">{{ $message->message }}</p>
                            <p class="text-xs {{ $message->sender_type === 'contact' ? 'text-gray-500' : 'text-white/70' }} mt-2">
                                {{ $message->created_at->format('H:i') }} • {{ $message->sender_type === 'contact' ? ($ticket->contact->display_name ?? 'Customer') : ($message->sender->name ?? 'Agent') }}
                            </p>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-12 text-gray-500">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p>No messages yet</p>
                        <p class="text-sm mt-1">Start the conversation below</p>
                    </div>
                    @endforelse
                </div>

                <!-- Reply Form -->
                <div class="px-6 py-4 border-t bg-gray-50">
                    <form id="replyForm" action="{{ route('crm.tickets.reply', $ticket) }}" method="POST">
                        @csrf
                        <div class="space-y-3">
                            <div class="flex items-center space-x-2">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300 text-yellow-500 focus:ring-yellow-500">
                                    <span class="ml-2 text-sm text-gray-600">Internal Note (not visible to customer)</span>
                                </label>
                            </div>
                            <div>
                                <textarea name="message" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent" placeholder="Type your reply..." required></textarea>
                            </div>
                            <div class="flex items-center justify-end space-x-3">
                                <button type="submit" class="px-6 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                    <i class="fas fa-paper-plane mr-2"></i> Send Reply
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Update Ticket Status</h3>
                <button onclick="closeModal('statusModal')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <form action="{{ route('crm.tickets.status', $ticket) }}" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="waiting_customer" {{ $ticket->status === 'waiting_customer' ? 'selected' : '' }}>Waiting Customer</option>
                    <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeModal('statusModal')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                    Update Status
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Send WhatsApp Modal -->
<div id="whatsappModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Send WhatsApp Message</h3>
                <button onclick="closeModal('whatsappModal')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <form id="whatsappForm" class="p-6 space-y-4">
            @csrf
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-sm text-gray-500">Recipient</p>
                <p class="font-medium text-gray-800">{{ $ticket->contact->display_name ?? 'Unknown' }}</p>
                <p class="text-sm text-gray-600">{{ $ticket->contact->phone ?? '-' }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Session *</label>
                <select name="sessionId" id="whatsappSessionId" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
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
                <button type="button" onclick="selectWhatsAppMessageType('text')" class="whatsapp-type-btn px-3 py-1 text-sm rounded-md bg-whatsapp-light text-white" data-type="text">Text</button>
                <button type="button" onclick="selectWhatsAppMessageType('image')" class="whatsapp-type-btn px-3 py-1 text-sm rounded-md hover:bg-gray-100" data-type="image">Image</button>
                <button type="button" onclick="selectWhatsAppMessageType('document')" class="whatsapp-type-btn px-3 py-1 text-sm rounded-md hover:bg-gray-100" data-type="document">Document</button>
            </div>
            
            <!-- Text Message Fields -->
            <div id="whatsappTextFields" class="whatsapp-message-fields space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                    <textarea name="message" id="whatsappMessage" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="Type your message here..."></textarea>
                </div>
            </div>
            
            <!-- Image Fields -->
            <div id="whatsappImageFields" class="whatsapp-message-fields space-y-4 hidden">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image URL *</label>
                    <input type="url" name="imageUrl" id="whatsappImageUrl"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="https://example.com/image.jpg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                    <textarea name="caption" id="whatsappCaption" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="Image caption (optional)"></textarea>
                </div>
            </div>
            
            <!-- Document Fields -->
            <div id="whatsappDocumentFields" class="whatsapp-message-fields space-y-4 hidden">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Document URL *</label>
                    <input type="url" name="documentUrl" id="whatsappDocumentUrl"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="https://example.com/document.pdf">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filename *</label>
                    <input type="text" name="filename" id="whatsappFilename"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="document.pdf">
                </div>
            </div>
            
            <div class="flex justify-end pt-4 space-x-3">
                <button type="button" onclick="closeModal('whatsappModal')" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Send Message
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Scroll to bottom of messages
const messagesContainer = document.getElementById('messagesContainer');
if (messagesContainer) {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Modal functions
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.getElementById(id).classList.add('flex');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).classList.remove('flex');
}

let currentWhatsAppMessageType = 'text';

function selectWhatsAppMessageType(type) {
    currentWhatsAppMessageType = type;
    
    // Update button styles
    document.querySelectorAll('.whatsapp-type-btn').forEach(btn => {
        btn.classList.remove('bg-whatsapp-light', 'text-white');
        btn.classList.add('hover:bg-gray-100');
        if (btn.dataset.type === type) {
            btn.classList.add('bg-whatsapp-light', 'text-white');
            btn.classList.remove('hover:bg-gray-100');
        }
    });
    
    // Show/hide fields
    document.querySelectorAll('.whatsapp-message-fields').forEach(field => {
        field.classList.add('hidden');
    });
    document.getElementById('whatsapp' + type.charAt(0).toUpperCase() + type.slice(1) + 'Fields').classList.remove('hidden');
}

// WhatsApp form submission
document.getElementById('whatsappForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const sessionId = formData.get('sessionId');
    const chatId = '{{ $ticket->contact->phone ?? '' }}';
    const typingTime = 0;
    const replyTo = null;
    
    if (!chatId) {
        alert('No phone number available for this contact');
        return;
    }
    
    let endpoint = '';
    let data = { sessionId, chatId, typingTime, replyTo };
    
    switch (currentWhatsAppMessageType) {
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
            alert('Message sent successfully!');
            closeModal('whatsappModal');
            // Also save as ticket reply
            const replyForm = document.getElementById('replyForm');
            const replyMessage = replyForm.querySelector('textarea[name="message"]');
            replyMessage.value = formData.get('message') || '[Media Message Sent]';
            replyForm.submit();
        } else {
            alert(result.message || 'Failed to send message');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});
</script>
@endpush
@endsection
