@extends('layouts.app')

@section('title', 'Send Message - Ticket')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Send Message</h1>
            <p class="text-gray-500 mt-1">Send message to ticket contact</p>
        </div>
        <a href="{{ route('crm.tickets.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Tickets
        </a>
    </div>

    @if($ticket && $ticket->contact)
    <!-- Contact Info -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                <span class="text-lg text-gray-600">{{ $ticket->contact->initials ?? '?' }}</span>
            </div>
            <div>
                <p class="font-medium text-gray-800">{{ $ticket->contact->display_name ?? 'Unknown' }}</p>
                <p class="text-sm text-gray-500">{{ $ticket->contact->phone ?? '-' }}</p>
            </div>
            <div class="ml-auto text-right">
                <p class="text-sm text-gray-500">Ticket</p>
                <p class="font-medium text-gray-800">{{ $ticket->ticket_number }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Message Types -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-800">Message Type</h2>
            </div>
            <div class="p-4 space-y-2">
                <button onclick="selectMessageType('text')" class="message-type-btn w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors bg-whatsapp-light/10 border-l-4 border-whatsapp-light" data-type="text">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span class="font-medium">Text</span>
                </button>
                
                <button onclick="selectMessageType('image')" class="message-type-btn w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors" data-type="image">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="font-medium">Image</span>
                </button>
                
                <button onclick="selectMessageType('document')" class="message-type-btn w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors" data-type="document">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="font-medium">Document</span>
                </button>
                
                <button onclick="selectMessageType('audio')" class="message-type-btn w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors" data-type="audio">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                    <span class="font-medium">Audio</span>
                </button>
                
                <button onclick="selectMessageType('location')" class="message-type-btn w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors" data-type="location">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="font-medium">Location</span>
                </button>
                
                <button onclick="selectMessageType('contact')" class="message-type-btn w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors" data-type="contact">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">Contact</span>
                </button>
                
                <button onclick="selectMessageType('poll')" class="message-type-btn w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors" data-type="poll">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="font-medium">Poll</span>
                </button>
            </div>
        </div>
        
        <!-- Message Form -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-800">Compose Message</h2>
            </div>
            <form id="messageForm" class="p-6 space-y-4">
                <!-- Session Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Session *</label>
                    <select name="sessionId" id="sessionId" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
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
                
                <!-- Recipient (Hidden - pre-filled from ticket) -->
                <input type="hidden" name="chatId" value="{{ $ticket->contact->phone ?? '' }}">
                <input type="hidden" name="ticketId" value="{{ $ticket->id ?? '' }}">
                
                <!-- Recipient Display -->
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-sm text-gray-500">Recipient</p>
                    <p class="font-medium text-gray-800">{{ $ticket->contact->phone ?? 'Not available' }}</p>
                </div>
                
                <!-- Text Message Fields -->
                <div id="textFields" class="message-fields space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                        <textarea name="message" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                            placeholder="Type your message here..."></textarea>
                    </div>
                </div>
                
                <!-- Image Fields -->
                <div id="imageFields" class="message-fields space-y-4 hidden">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image URL *</label>
                        <input type="url" name="imageUrl"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                            placeholder="https://example.com/image.jpg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                        <textarea name="caption" rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                            placeholder="Image caption (optional)"></textarea>
                    </div>
                </div>
                
                <!-- Document Fields -->
                <div id="documentFields" class="message-fields space-y-4 hidden">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Document URL *</label>
                        <input type="url" name="documentUrl"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                            placeholder="https://example.com/document.pdf">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filename *</label>
                        <input type="text" name="filename"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                            placeholder="document.pdf">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                        <textarea name="docCaption" rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                            placeholder="Document caption (optional)"></textarea>
                    </div>
                </div>
                
                <!-- Audio Fields -->
                <div id="audioFields" class="message-fields space-y-4 hidden">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                        <p class="text-sm text-yellow-800">
                            <strong>Note:</strong> Audio must be in OGG format (.ogg). WhatsApp only supports OGG audio files with Opus codec.
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Audio URL (OGG format) *</label>
                        <input type="url" name="audioUrl"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                            placeholder="https://example.com/audio.ogg">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="ptt" id="ptt" class="h-4 w-4 text-whatsapp-light focus:ring-whatsapp-light border-gray-300 rounded">
                        <label for="ptt" class="ml-2 block text-sm text-gray-700">Send as voice note (PTT)</label>
                    </div>
                </div>
                
                <!-- Location Fields -->
                <div id="locationFields" class="message-fields space-y-4 hidden">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Latitude *</label>
                            <input type="number" step="any" name="latitude"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                                placeholder="-6.2088">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Longitude *</label>
                            <input type="number" step="any" name="longitude"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                                placeholder="106.8456">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location Name</label>
                        <input type="text" name="locationName"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                            placeholder="Jakarta, Indonesia">
                    </div>
                </div>
                
                <!-- Contact Fields -->
                <div id="contactFields" class="message-fields space-y-4 hidden">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Name *</label>
                        <input type="text" name="contactName"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                            placeholder="John Doe">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone *</label>
                        <input type="text" name="contactPhone"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                            placeholder="628987654321">
                    </div>
                </div>
                
                <!-- Poll Fields -->
                <div id="pollFields" class="message-fields space-y-4 hidden">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Question *</label>
                        <input type="text" name="question"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                            placeholder="What is your favorite color?">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Options (one per line, 2-12 options) *</label>
                        <textarea name="options" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                            placeholder="Red&#10;Blue&#10;Green&#10;Yellow"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Selectable Count</label>
                        <input type="number" name="selectableCount" value="1" min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">1 for single choice, higher for multiple choice</p>
                    </div>
                </div>
                
                <!-- Common Options -->
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Typing Time (ms)</label>
                        <input type="number" name="typingTime" value="0" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reply To (Message ID)</label>
                        <input type="text" name="replyTo"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                            placeholder="Optional">
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="flex justify-end pt-4 space-x-3">
                    <a href="{{ route('crm.tickets.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="flex items-center space-x-2 px-6 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span>Send Message</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Send Message Modal for Tickets -->
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
        <form id="sendMessageModalForm" class="p-6 space-y-4">
            <!-- Session Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Session *</label>
                <select name="modalSessionId" id="modalSessionId" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
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
            
            <!-- Recipient (Hidden - pre-filled from ticket) -->
            <input type="hidden" name="modalChatId" id="modalChatId" value="">
            <input type="hidden" name="modalTicketId" id="modalTicketId" value="">
            
            <!-- Recipient Display -->
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-sm text-gray-500">Recipient</p>
                <p class="font-medium text-gray-800" id="modalRecipientDisplay">-</p>
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
                    <textarea name="modalMessage" id="modalMessage" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="Type your message here..."></textarea>
                </div>
            </div>
            
            <!-- Image Fields -->
            <div id="modalImageFields" class="modal-message-fields space-y-4 hidden">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image URL *</label>
                    <input type="url" name="modalImageUrl" id="modalImageUrl"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="https://example.com/image.jpg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                    <textarea name="modalCaption" id="modalCaption" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="Image caption (optional)"></textarea>
                </div>
            </div>
            
            <!-- Document Fields -->
            <div id="modalDocumentFields" class="modal-message-fields space-y-4 hidden">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Document URL *</label>
                    <input type="url" name="modalDocumentUrl" id="modalDocumentUrl"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="https://example.com/document.pdf">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filename *</label>
                    <input type="text" name="modalFilename" id="modalFilename"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="document.pdf">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                    <textarea name="modalDocCaption" id="modalDocCaption" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="Document caption (optional)"></textarea>
                </div>
            </div>
            
            <!-- Submit Button -->
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

@push('scripts')
<script>
    let currentMessageType = 'text';
    let currentModalMessageType = 'text';
    
    function selectMessageType(type) {
        currentMessageType = type;
        
        // Update button styles
        document.querySelectorAll('.message-type-btn').forEach(btn => {
            btn.classList.remove('bg-whatsapp-light/10', 'border-l-4', 'border-whatsapp-light');
            if (btn.dataset.type === type) {
                btn.classList.add('bg-whatsapp-light/10', 'border-l-4', 'border-whatsapp-light');
            }
        });
        
        // Show/hide fields
        document.querySelectorAll('.message-fields').forEach(field => {
            field.classList.add('hidden');
        });
        document.getElementById(type + 'Fields').classList.remove('hidden');
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
    
    document.getElementById('messageForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const sessionId = formData.get('sessionId');
        const chatId = formData.get('chatId');
        const typingTime = parseInt(formData.get('typingTime')) || 0;
        const replyTo = formData.get('replyTo') || null;
        
        let endpoint = '';
        let data = { sessionId, chatId, typingTime, replyTo };
        
        switch (currentMessageType) {
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
                data.caption = formData.get('docCaption');
                break;
            case 'audio':
                endpoint = '{{ route("api.send.audio") }}';
                data.audioUrl = formData.get('audioUrl');
                data.ptt = formData.get('ptt') === 'on';
                break;
            case 'location':
                endpoint = '{{ route("api.send.location") }}';
                data.latitude = parseFloat(formData.get('latitude'));
                data.longitude = parseFloat(formData.get('longitude'));
                data.name = formData.get('locationName');
                break;
            case 'contact':
                endpoint = '{{ route("api.send.contact") }}';
                data.contactName = formData.get('contactName');
                data.contactPhone = formData.get('contactPhone');
                break;
            case 'poll':
                endpoint = '{{ route("api.send.poll") }}';
                data.question = formData.get('question');
                data.options = formData.get('options').split('\n').filter(o => o.trim());
                data.selectableCount = parseInt(formData.get('selectableCount')) || 1;
                break;
        }
        
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Message sent successfully!', 'success');
                this.reset();
            } else {
                showToast(result.message || 'Failed to send message', 'error');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    });

    // Modal form submission
    document.getElementById('sendMessageModalForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const sessionId = formData.get('modalSessionId');
        const chatId = formData.get('modalChatId');
        const typingTime = 0;
        const replyTo = null;
        
        let endpoint = '';
        let data = { sessionId, chatId, typingTime, replyTo };
        
        switch (currentModalMessageType) {
            case 'text':
                endpoint = '{{ route("api.send.text") }}';
                data.message = formData.get('modalMessage');
                break;
            case 'image':
                endpoint = '{{ route("api.send.image") }}';
                data.imageUrl = formData.get('modalImageUrl');
                data.caption = formData.get('modalCaption');
                break;
            case 'document':
                endpoint = '{{ route("api.send.document") }}';
                data.documentUrl = formData.get('modalDocumentUrl');
                data.filename = formData.get('modalFilename');
                data.caption = formData.get('modalDocCaption');
                break;
        }
        
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
@endsection
