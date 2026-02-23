@extends('layouts.app')

@section('title', 'Contact Details')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('crm.contacts.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left text-gray-600"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $contact->display_name }}</h1>
                <p class="text-gray-500 mt-1">Contact Details</p>
            </div>
        </div>
        <div class="flex space-x-2">
            <button onclick="document.getElementById('editModal').classList.remove('hidden')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-edit mr-2"></i> Edit
            </button>
            <button onclick="sendMessage('{{ $contact->phone }}')" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                <i class="fas fa-paper-plane mr-2"></i> Send Message
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Contact Info Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-whatsapp-light to-whatsapp-dark px-6 py-8 text-white text-center">
                    <div class="w-20 h-20 bg-white/20 rounded-full mx-auto flex items-center justify-center mb-4">
                        <span class="text-2xl font-bold">{{ $contact->initials }}</span>
                    </div>
                    <h2 class="text-xl font-bold">{{ $contact->display_name }}</h2>
                    <p class="text-white/80">{{ $contact->phone }}</p>
                    <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-medium
                        @if($contact->status === 'active') bg-green-400/30
                        @elseif($contact->status === 'inactive') bg-gray-400/30
                        @else bg-red-400/30 @endif">
                        {{ ucfirst($contact->status) }}
                    </span>
                </div>

                <!-- Details -->
                <div class="p-6 space-y-4">
                    @if($contact->email)
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <div>
                            <p class="text-xs text-gray-500">Email</p>
                            <p class="text-sm text-gray-800">{{ $contact->email }}</p>
                        </div>
                    </div>
                    @endif

                    @if($contact->company)
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <div>
                            <p class="text-xs text-gray-500">Company</p>
                            <p class="text-sm text-gray-800">{{ $contact->company }}</p>
                        </div>
                    </div>
                    @endif

                    @if($contact->address)
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <div>
                            <p class="text-xs text-gray-500">Address</p>
                            <p class="text-sm text-gray-800">{{ $contact->address }}</p>
                        </div>
                    </div>
                    @endif

                    @if($contact->segment)
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <div>
                            <p class="text-xs text-gray-500">Segment</p>
                            <span class="px-2 py-1 text-xs font-medium rounded-full" style="background-color: {{ $contact->segment->color }}20; color: {{ $contact->segment->color }}">
                                {{ $contact->segment->name }}
                            </span>
                        </div>
                    </div>
                    @endif

                    <!-- Tags -->
                    @if($contact->tags && $contact->tags->count() > 0)
                    <div>
                        <p class="text-xs text-gray-500 mb-2">Tags</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach($contact->tags as $tag)
                            <span class="px-2 py-0.5 text-xs rounded-full" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                {{ $tag->name }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Stats Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistics</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-2xl font-bold text-gray-800">{{ $contact->orders ? $contact->orders->count() : 0 }}</p>
                        <p class="text-xs text-gray-500">Total Orders</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($contact->total_spent ?? 0, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500">Total Spent</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-2xl font-bold text-gray-800">{{ $contact->open_tickets_count ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Open Tickets</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-2xl font-bold text-gray-800">{{ $contact->interactions ? $contact->interactions->count() : 0 }}</p>
                        <p class="text-xs text-gray-500">Interactions</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Notes Section -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Notes</h3>
                <form action="{{ route('crm.contacts.update', $contact) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_update_notes" value="1">
                    <textarea name="notes" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent" placeholder="Add notes about this contact...">{{ $contact->notes }}</textarea>
                    <div class="mt-3 flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors text-sm">
                            Save Notes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Recent Interactions -->
            <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Interactions</h3>
                <div class="space-y-3">
                    @forelse($contact->interactions ? $contact->interactions->take(5) : [] as $interaction)
                    <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            @if($interaction->type === 'call')
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            @elseif($interaction->type === 'email')
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            @else
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="font-medium text-gray-800 text-sm">{{ ucfirst($interaction->type) }}</p>
                                <span class="text-xs text-gray-500">{{ $interaction->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">{{ $interaction->summary }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <p>No interactions yet</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Orders</h3>
                @if($contact->orders && $contact->orders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($contact->orders ? $contact->orders->take(5) : [] as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-800">#{{ $order->id }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $order->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                                        @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                                        @elseif($order->status === 'delivered') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <p>No orders yet</p>
                </div>
                @endif
            </div>

            <!-- Recent Tickets -->
            <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Tickets</h3>
                @if($contact->tickets && $contact->tickets->count() > 0)
                <div class="space-y-3">
                    @foreach($contact->tickets ? $contact->tickets->take(5) : [] as $ticket)
                    <a href="{{ route('crm.tickets.show', $ticket) }}" class="block p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-800">{{ $ticket->subject }}</p>
                                <p class="text-sm text-gray-500 mt-1">#{{ $ticket->id }} • {{ $ticket->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                @if($ticket->status === 'open') bg-red-100 text-red-800
                                @elseif($ticket->status === 'in_progress') bg-yellow-100 text-yellow-800
                                @elseif($ticket->status === 'waiting_customer') bg-blue-100 text-blue-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <p>No tickets yet</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Contact Modal -->
<div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Edit Contact</h3>
                <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <form action="{{ route('crm.contacts.update', $contact) }}" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" value="{{ $contact->name }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" name="phone" value="{{ $contact->phone }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ $contact->email }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                <input type="text" name="company" value="{{ $contact->company }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea name="address" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">{{ $contact->address }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Segment</label>
                <select name="segment_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="">No Segment</option>
                    @foreach($segments as $segment)
                    <option value="{{ $segment->id }}" {{ $contact->segment_id == $segment->id ? 'selected' : '' }}>{{ $segment->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="active" {{ $contact->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $contact->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="blocked" {{ $contact->status === 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Send Message Modal -->
<div id="messageModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Send WhatsApp Message</h3>
                <button onclick="closeMessageModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <form id="messageForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                <input type="text" id="messageTo" readonly class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea id="messageContent" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent" placeholder="Type your message..."></textarea>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="closeMessageModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                    Send Message
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function sendMessage(phone) {
    document.getElementById('messageTo').value = phone;
    document.getElementById('messageModal').classList.remove('hidden');
}

function closeMessageModal() {
    document.getElementById('messageModal').classList.add('hidden');
    document.getElementById('messageContent').value = '';
}

document.getElementById('messageForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const to = document.getElementById('messageTo').value;
    const message = document.getElementById('messageContent').value;
    
    if (!message.trim()) {
        alert('Please enter a message');
        return;
    }
    
    try {
        const response = await fetch('{{ route("api.send.text") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                sessionId: 'default',
                chatId: to,
                message: message
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Message sent successfully!');
            closeMessageModal();
        } else {
            alert(result.message || 'Failed to send message');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});
</script>
@endsection
