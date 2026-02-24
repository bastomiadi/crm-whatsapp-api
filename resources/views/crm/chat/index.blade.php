@extends('layouts.app')

@section('title', 'Chat Messaging')

@section('content')
<div class="space-y-6">
    <!-- API Error Alert -->
    @if(isset($apiError) && $apiError)
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
            <div>
                <p class="font-medium text-red-800">API Connection Error</p>
                <p class="text-sm text-red-600">{{ $apiError }}</p>
            </div>
            <a href="{{ route('crm.chat.test-api') }}" target="_blank" class="ml-auto px-3 py-1 bg-red-100 text-red-700 rounded-lg text-sm hover:bg-red-200">
                Test API
            </a>
        </div>
    </div>
    @endif
    <!-- Page Header -->
    <div id="chatPageHeader" class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Chat Messaging</h1>
            <p class="text-gray-500 mt-1">Manage customer conversations</p>
        </div>
        <div class="flex items-center space-x-2">
            <!-- Test WebSocket Button -->
            <button onclick="ChateryWebSocket.test()" class="px-3 py-2 border border-purple-300 rounded-lg text-sm text-purple-700 hover:bg-purple-50" title="Test WebSocket Connection">
                <i class="fas fa-bolt mr-1"></i> WebSocket
            </button>
            <!-- Test API Button -->
            <button onclick="testApiConnection()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50" title="Test API Connection">
                <i class="fas fa-plug mr-1"></i> Test API
            </button>
            <!-- Session Filter -->
            <select id="sessionFilter" onchange="filterBySession(this.value)" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-whatsapp-light">
                <option value="">All Sessions</option>
                @if(isset($sessions))
                    @foreach($sessions as $session)
                    <option value="{{ $session['sessionId'] ?? $session['session_id'] ?? '' }}" {{ ($sessionId ?? '') == ($session['sessionId'] ?? $session['session_id'] ?? '') ? 'selected' : '' }}>
                        {{ $session['sessionId'] ?? $session['session_id'] ?? 'Unknown' }} 
                        ({{ $session['status'] ?? 'unknown' }})
                    </option>
                    @endforeach
                @endif
            </select>
            <button onclick="openModal('newChatModal')" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                <i class="fas fa-plus mr-2"></i> New Chat
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Conversations</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($totalConversations) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-comments text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Unread</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($unreadConversations) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <i class="fas fa-envelope text-red-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Assigned to Me</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($assignedToMe) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-user text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active</p>
                    <p class="text-2xl font-bold text-whatsapp-light">{{ \App\Models\ChatConversation::where('status', 'active')->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-whatsapp-light bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-circle text-whatsapp-light"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Chat Interface -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Conversation List -->
        <div class="bg-white rounded-xl shadow-sm h-[600px] flex flex-col">
            <div class="p-4 border-b border-gray-200 flex-shrink-0">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-gray-800">Conversations</h2>
                    <span class="text-xs text-gray-500">{{ $totalConversations }} total</span>
                </div>
                <div class="flex space-x-2">
                    <button onclick="filterConversations('all')" id="filterAll" class="flex-1 px-2 py-1 text-xs rounded-lg bg-whatsapp-light text-white">All</button>
                    <button onclick="filterConversations('individuals')" id="filterIndividuals" class="flex-1 px-2 py-1 text-xs rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200">Individuals</button>
                    <button onclick="filterConversations('groups')" id="filterGroups" class="flex-1 px-2 py-1 text-xs rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200">Groups</button>
                </div>
            </div>
            <div id="conversationList" class="divide-y divide-gray-100 flex-1 overflow-y-auto">
                @forelse($conversations as $conversation)
                @php
                    $conversationId = $conversation->id ?? ($conversation->is_api_only ? 'api_' . $conversation->phone : null);
                    $unreadCount = $conversation->unread_count ?? ($conversation->api_unread_count ?? 0);
                    $assignedTo = $conversation->assigned_to ?? ($conversation->assignedTo->id ?? null);
                    $profilePicture = $conversation->profile_picture ?? null;
                    $isGroup = $conversation->is_group ?? false;
                @endphp
                <div onclick="selectConversation({{ $conversationId }})" 
                     class="p-4 hover:bg-gray-50 cursor-pointer transition-colors conversation-item"
                     data-id="{{ $conversationId }}"
                     data-unread="{{ $unreadCount }}"
                     data-assigned="{{ $assignedTo }}"
                     data-is-group="{{ $isGroup ? 'true' : 'false' }}">
                    <div class="flex items-start space-x-3">
                        @if($profilePicture)
                        <img src="{{ $profilePicture }}" alt="{{ $conversation->name ?? $conversation->phone }}" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                        @else
                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-gray-500"></i>
                        </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="font-medium text-gray-800 truncate">{{ $conversation->name ?? $conversation->phone }}</h3>
                                @if($unreadCount > 0)
                                <span class="px-2 py-0.5 text-xs bg-red-500 text-white rounded-full">{{ $unreadCount }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 truncate">
                                {{ $conversation->latestMessage?->message ?? $conversation->api_last_message ?? 'No messages' }}
                            </p>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-gray-400">{{ $conversation->last_message_at?->diffForHumans() }}</span>
                                @if(isset($conversation->assignedTo) && $conversation->assignedTo)
                                <span class="text-xs text-gray-400"><i class="fas fa-user mr-1"></i>{{ $conversation->assignedTo->name }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-comments text-4xl mb-3 text-gray-300"></i>
                    <p>No conversations yet</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Chat Area -->
        <div class="lg:col-span-3 bg-white rounded-xl shadow-sm flex flex-col h-[600px]">
            <!-- Chat Header -->
            <div id="chatHeader" class="p-4 border-b border-gray-200 hidden">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-whatsapp-light bg-opacity-20 flex items-center justify-center">
                            <i class="fas fa-user text-whatsapp-light text-lg"></i>
                        </div>
                        <div>
                            <h3 id="chatUserName" class="font-semibold text-gray-800 text-lg"></h3>
                            <p id="chatUserPhone" class="text-sm text-gray-500"></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button onclick="openAssignModal()" class="px-3 py-2 text-sm bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg flex items-center" title="Assign">
                            <i class="fas fa-user-plus mr-2"></i> Assign
                        </button>
                        <button onclick="closeConversation()" class="px-3 py-2 text-sm bg-green-50 text-green-600 hover:bg-green-100 rounded-lg flex items-center" title="Close">
                            <i class="fas fa-check-circle mr-2"></i> Close
                        </button>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div id="messageList" class="flex-1 p-4 overflow-y-auto bg-gray-50">
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-comments text-5xl mb-3 text-gray-300"></i>
                    <p class="text-lg">Select a conversation to view messages</p>
                </div>
            </div>

            <!-- Quick Replies Panel -->
            <div id="quickRepliesPanel" class="px-4 py-2 border-t border-gray-200 bg-gray-50 hidden">
                <div class="flex items-center space-x-2 overflow-x-auto pb-2">
                    <span class="text-xs text-gray-500 whitespace-nowrap font-medium">Quick:</span>
                    <div id="quickRepliesList" class="flex space-x-2"></div>
                </div>
            </div>

            <!-- Message Input -->
            <div id="messageInput" class="p-4 border-t border-gray-200 bg-white hidden">
                <!-- Hidden file inputs -->
                <input type="file" id="imageInput" class="hidden" accept="image/*">
                <input type="file" id="documentInput" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">
                <input type="file" id="audioInput" class="hidden" accept="audio/*,.mp3,.wav,.ogg">
                
                <form id="sendMessageForm" onsubmit="sendMessage(event)">
                    <div class="flex items-end space-x-3">
                        <!-- Attachment Dropdown -->
                        <div class="relative">
                            <button type="button" onclick="toggleAttachmentMenu()" class="p-3 text-gray-500 hover:text-whatsapp-light hover:bg-gray-100 rounded-lg" title="Attach File">
                                <i class="fas fa-paperclip text-lg"></i>
                            </button>
                            <!-- Attachment Menu -->
                            <div id="attachmentMenu" class="hidden absolute bottom-full left-0 mb-2 bg-white rounded-lg shadow-lg border border-gray-200 py-2 min-w-[180px]">
                                <button type="button" onclick="openAttachmentModal('image')" class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center">
                                    <i class="fas fa-image text-green-500 mr-3"></i> Image
                                </button>
                                <button type="button" onclick="openAttachmentModal('document')" class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center">
                                    <i class="fas fa-file text-blue-500 mr-3"></i> Document
                                </button>
                                <button type="button" onclick="openAttachmentModal('audio')" class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center">
                                    <i class="fas fa-microphone text-purple-500 mr-3"></i> Audio
                                </button>
                                <button type="button" onclick="openAttachmentModal('location')" class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center">
                                    <i class="fas fa-map-marker-alt text-red-500 mr-3"></i> Location
                                </button>
                                <button type="button" onclick="openAttachmentModal('contact')" class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center">
                                    <i class="fas fa-user text-yellow-500 mr-3"></i> Contact
                                </button>
                                <button type="button" onclick="openAttachmentModal('poll')" class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center">
                                    <i class="fas fa-poll text-indigo-500 mr-3"></i> Poll
                                </button>
                            </div>
                        </div>
                        <button type="button" onclick="toggleQuickReplies()" class="p-3 text-gray-500 hover:text-whatsapp-light hover:bg-gray-100 rounded-lg" title="Quick Replies">
                            <i class="fas fa-bolt text-lg"></i>
                        </button>
                        <div class="flex-1">
                            <textarea id="messageText" rows="1" placeholder="Type a message..." 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent resize-none text-base"
                                onkeydown="handleEnter(event)"></textarea>
                        </div>
                        <button type="submit" class="px-6 py-3 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors flex items-center">
                            <i class="fas fa-paper-plane text-lg"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- New Chat Modal -->
<div id="newChatModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-plus mr-2 text-whatsapp-light"></i> Start New Chat
                </h3>
                <button onclick="closeModal('newChatModal')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <form id="newChatForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search Contact</label>
                <input type="text" id="contactSearch" oninput="searchContacts(this.value)" placeholder="Search by name or phone..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light">
                <div id="searchResults" class="mt-2 max-h-40 overflow-y-auto hidden"></div>
            </div>
            <div id="selectedContact" class="hidden">
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-user text-gray-500"></i>
                    </div>
                    <div>
                        <p id="selectedContactName" class="font-medium text-gray-800"></p>
                        <p id="selectedContactPhone" class="text-sm text-gray-500"></p>
                    </div>
                </div>
                <input type="hidden" id="selectedContactId">
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeModal('newChatModal')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark">
                    Start Chat
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Modal -->
<div id="assignModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-sm w-full mx-4">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Assign Conversation</h3>
        </div>
        <div class="p-6">
            <select id="assignUser" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">Select User</option>
                @php $users = \App\Models\User::all(); @endphp
                @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <div class="flex space-x-3 mt-4">
                <button onclick="closeModal('assignModal')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg">Cancel</button>
                <button onclick="assignConversation()" class="flex-1 px-4 py-2 bg-whatsapp-light text-white rounded-lg">Assign</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.socket.io/4.7.4/socket.io.min.js"></script>
<script src="{{ asset('js/websocket.js') }}"></script>
<script>
// Make currentChatId accessible globally for WebSocket
window.currentChatId = null;
// Get session from URL parameter or PHP variable
const urlParams = new URLSearchParams(window.location.search);
window.currentSession = urlParams.get('session') || '{{ $sessionId ?? "" }}';

// Auto-refresh conversation list every 3 seconds when session is selected
// AND auto-refresh messages every 3 seconds when conversation is selected
let conversationRefreshInterval = null;
let messagesRefreshInterval = null;
let lastConversationUpdate = 0;

// Load conversations from API for real-time refresh
async function loadConversationsFromApi(sessionId = null) {
    try {
        const url = '{{ route("crm.chat.conversations") }}' + (sessionId ? '?session=' + sessionId : '');
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success && result.conversations) {
            renderRefreshedConversations(result.conversations);
            
            // Update unread count if changed
            const unreadElement = document.querySelector('.bg-red-100 + .text-red-600, [class*="text-red-600"]');
            if (unreadElement && result.unread_count !== undefined) {
                updateUnreadCountDisplay(result.unread_count);
            }
            
            lastConversationUpdate = result.timestamp;
        }
    } catch (error) {
        console.error('Error loading conversations:', error);
    }
}

// Render refreshed conversations (merge with existing)
function renderRefreshedConversations(conversations) {
    const container = document.getElementById('conversationList');
    if (!container || !conversations || conversations.length === 0) return;
    
    // Sort conversations by last_message_at
    conversations.sort((a, b) => {
        const dateA = new Date(a.last_message_at);
        const dateB = new Date(b.last_message_at);
        return dateB - dateA;
    });
    
    // Build HTML for conversations
    let html = '';
    conversations.forEach(conv => {
        const convId = conv.id || (conv.is_api_only ? 'api_' + conv.phone : null);
        if (!convId) return;
        
        const unreadCount = conv.unread_count || 0;
        const assignedTo = conv.assigned_to || (conv.assigned_to_id ?? null);
        const isGroup = conv.is_group || false;
        const profilePicture = conv.profile_picture || null;
        const name = conv.name || conv.phone || 'Unknown';
        const lastMessage = conv.latest_message?.message || conv.api_last_message || 'No messages';
        const lastMessageAt = conv.last_message_at ? formatTimeAgo(conv.last_message_at) : '';
        
        html += `
        <div onclick="selectConversation(${convId})" 
             class="p-4 hover:bg-gray-50 cursor-pointer transition-colors conversation-item"
             data-id="${convId}"
             data-unread="${unreadCount}"
             data-assigned="${assignedTo}"
             data-is-group="${isGroup ? 'true' : 'false'}">
            <div class="flex items-start space-x-3">
                ${profilePicture 
                    ? `<img src="${profilePicture}" alt="${name}" class="w-10 h-10 rounded-full object-cover flex-shrink-0">`
                    : `<div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user text-gray-500"></i>
                       </div>`
                }
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium text-gray-800 truncate">${name}</h3>
                        ${unreadCount > 0 ? `<span class="px-2 py-0.5 text-xs bg-red-500 text-white rounded-full">${unreadCount}</span>` : ''}
                    </div>
                    <p class="text-sm text-gray-500 truncate">${lastMessage}</p>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-gray-400">${lastMessageAt}</span>
                    </div>
                </div>
            </div>
        </div>`;
    });
    
    if (html) {
        container.innerHTML = html;
    }
}

// Update unread count display
function updateUnreadCountDisplay(count) {
    // Find and update all unread count displays
    const cards = document.querySelectorAll('.grid.grid-cols-1.md\\:grid-cols-4');
    cards.forEach(card => {
        const unreadElements = card.querySelectorAll('.text-red-600');
        unreadElements.forEach(el => {
            // Check if it's the unread count (usually in a stats card)
            if (el.textContent.match(/^\\d+$/)) {
                el.textContent = count;
            }
        });
    });
}

// Format time for display
function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 1) return 'Just now';
    if (minutes < 60) return minutes + 'm ago';
    if (hours < 24) return hours + 'h ago';
    if (days < 7) return days + 'd ago';
    
    return date.toLocaleDateString();
}

function startConversationRefresh() {
    if (conversationRefreshInterval) return;
    
    console.log('[Chat] Starting automatic conversation refresh');
    conversationRefreshInterval = setInterval(function() {
        const sessionId = window.currentSession || '{{ $sessionId ?? "" }}';
        
        // Always refresh database conversations (works with or without session)
        if (typeof loadConversationsFromApi === 'function') {
            loadConversationsFromApi(sessionId || null);
        }
        
        // Also refresh API chats if session is selected
        if (sessionId && typeof loadChatsFromApi === 'function') {
            loadChatsFromApi(sessionId);
        }
    }, 3000); // Refresh every 3 seconds
}

function startMessagesRefresh() {
    if (messagesRefreshInterval) return;
    
    console.log('[Chat] Starting automatic messages refresh');
    messagesRefreshInterval = setInterval(function() {
        if (window.currentChatId && window.currentSession) {
            // Extract phone number properly from chatId
            var phone = window.currentChatId.toString()
                .replace('@lid', '')
                .replace('@s.whatsapp.net', '')
                .replace('@g.us', '')
                .replace('lid:', '');
            
            // Create proper chatId for API (with @s.whatsapp.net)
            var apiChatId = phone + '@s.whatsapp.net';
            
            console.log('[Chat] Polling messages for:', phone, 'chatId:', apiChatId);
            
            // Use loadMessagesFromApi with proper chatId format
            if (typeof loadMessagesFromApi === 'function') {
                loadMessagesFromApi(window.currentSession, apiChatId);
            }
        }
    }, 2000); // Refresh every 2 seconds for faster real-time feel
}

function stopConversationRefresh() {
    if (conversationRefreshInterval) {
        clearInterval(conversationRefreshInterval);
        conversationRefreshInterval = null;
    }
}

function stopMessagesRefresh() {
    if (messagesRefreshInterval) {
        clearInterval(messagesRefreshInterval);
        messagesRefreshInterval = null;
    }
}

// SSE is disabled - using WebSocket and Polling for real-time updates instead
let eventSource = null;

function initSSE() {
    console.log('[SSE] SSE is disabled - using WebSocket and Polling for real-time updates');
    // SSE is disabled - WebSocket and Polling will handle real-time updates
    return;
}

// Handle webhook events from SSE
function handleWebhookEvent(data) {
    console.log('[Chat] Handling webhook event:', data);
    
    const type = data.type;
    const eventData = data.data || {};
    const sessionId = data.sessionId;
    
    if (type === 'message') {
        // Reload conversation list
        if (typeof loadChatsFromApi === 'function') {
            loadChatsFromApi(sessionId);
        }
        
        // If viewing this conversation, append new message smoothly
        const fromPhone = eventData.from ? eventData.from.replace('@s.whatsapp.net', '').replace('@g.us', '') : '';
        const currentPhone = window.currentChatId ? window.currentChatId.replace('@s.whatsapp.net', '').replace('@g.us', '') : '';
        
        if (currentPhone && fromPhone === currentPhone) {
            console.log('[Chat] Appending new message via SSE for:', fromPhone);
            
            // Create message object from webhook data
            var newMessage = {
                content: eventData.content || '',
                caption: eventData.caption || '',
                timestamp: eventData.timestamp || Math.floor(Date.now() / 1000),
                fromMe: false, // Inbound message
                from: eventData.from,
                pushName: eventData.pushName
            };
            
            // Check message type
            if (eventData.type === 'imageMessage' || eventData.imageMessage) {
                newMessage.content = { imageMessage: eventData.imageMessage || eventData };
            } else if (eventData.type === 'documentMessage' || eventData.documentMessage) {
                newMessage.content = { documentMessage: eventData.documentMessage || eventData };
            } else if (eventData.type === 'audioMessage' || eventData.audioMessage) {
                newMessage.content = { audioMessage: eventData.audioMessage || eventData };
            } else if (eventData.type === 'videoMessage' || eventData.videoMessage) {
                newMessage.content = { videoMessage: eventData.videoMessage || eventData };
            }
            
            // Append the new message
            if (typeof appendMessage === 'function') {
                appendMessage(newMessage, currentProfilePicture);
            } else if (typeof loadAndMergeMessages === 'function') {
                // Fallback to reload if append function not available
                loadAndMergeMessages(sessionId, window.currentChatId, fromPhone);
            }
        }
        
        // Show notification
        const pushName = eventData.pushName || fromPhone;
        const content = eventData.content || '';
        showNewMessageNotification({ data: eventData }, fromPhone);
    }
}

// Show notification for new message
function showNewMessageNotification(msg, fromPhone) {
    // Play notification sound
    playNotificationSound();
    
    // Update unread count in header
    updateUnreadCount();
}

// Play notification sound
function playNotificationSound() {
    try {
        const audio = new Audio('data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YU' + 'A'.repeat(100));
        audio.volume = 0.3;
        audio.play().catch(e => {});
    } catch (e) {
        // Ignore audio errors
    }
}

// Show browser notification if permission granted
function showBrowserNotification(from, message) {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification('New Message from ' + from, {
            body: message.substring(0, 100),
            icon: '/favicon.ico'
        });
    }
}

// Request notification permission on page load
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}

// Update unread count in stats
async function updateUnreadCount() {
    try {
        const response = await fetch('{{ route("crm.chat.unread-count") }}');
        const data = await response.json();
        
        const unreadElement = document.querySelector('.unread-count');
        if (unreadElement && data.unread !== undefined) {
            unreadElement.textContent = data.unread;
        }
    } catch (error) {
        console.error('Error updating unread count:', error);
    }
}

// Polling fallback for when WebSocket is not available
let pollInterval = null;
let lastMessageId = 0;

// Start polling for new messages (fallback when WebSocket fails)
function startPolling(intervalMs = 5000) {
    if (pollInterval) return;
    
    console.log('[Chat] Starting polling for new messages every', intervalMs, 'ms');
    pollInterval = setInterval(async function() {
        await checkForNewMessages();
    }, intervalMs);
}

// Stop polling
function stopPolling() {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
        console.log('[Chat] Polling stopped');
    }
}

// Check for new messages via API
async function checkForNewMessages() {
    try {
        const sessionId = window.currentSession || '{{ $sessionId ?? "" }}';
        if (!sessionId) return;
        
        const response = await fetch(`/crm/chat/check-new-messages?session=${sessionId}&last_id=${lastMessageId}`);
        const data = await response.json();
        
        if (data.new_messages && data.new_messages.length > 0) {
            console.log('[Chat] New messages found via polling:', data.new_messages.length);
            
            // Update lastMessageId
            const latestMsg = data.new_messages[data.new_messages.length - 1];
            lastMessageId = latestMsg.id;
            
            // Process each new message
            data.new_messages.forEach(msg => {
                handleNewMessage(msg, sessionId);
            });
        }
    } catch (error) {
        console.error('[Chat] Error checking new messages:', error);
    }
}

// Handle new message notification
function handleNewMessage(msg, sessionId) {
    const fromPhone = msg.from ? msg.from.replace('@s.whatsapp.net', '').replace('@g.us', '') : '';
    const currentPhone = window.currentChatId ? window.currentChatId.replace('@s.whatsapp.net', '').replace('@g.us', '') : '';
    
    console.log('[Chat] handleNewMessage - from:', fromPhone, 'current:', currentPhone);
    
    // If viewing this conversation, append new message smoothly
    if (currentPhone && fromPhone === currentPhone) {
        console.log('[Chat] Appending new message via polling for active conversation:', fromPhone);
        
        // Append the new message
        if (typeof appendMessage === 'function') {
            appendMessage(msg, currentProfilePicture);
        } else if (typeof loadAndMergeMessages === 'function') {
            // Fallback to reload if append function not available
            loadAndMergeMessages(sessionId, window.currentChatId, fromPhone);
        }
    } else {
        // Auto-switch to the conversation with new message and show it!
        console.log('[Chat] Auto-opening conversation with new message from:', fromPhone);
        
        // If we have sessionId, try to select the chat automatically
        if (sessionId && fromPhone) {
            // Get the chat ID format for API
            const chatId = fromPhone + '@s.whatsapp.net';
            
            // Get chat details from the conversation list to find profile picture
            const conversationItem = document.querySelector(`[data-chat-id*="${fromPhone}"]`);
            let profilePicture = null;
            
            // Try to find profile picture from conversation list
            if (conversationItem) {
                const img = conversationItem.querySelector('img');
                if (img) {
                    profilePicture = img.src;
                }
            }
            
            // Automatically select and show this conversation
            // Use the database conversation ID format if available
            selectApiChat(chatId, fromPhone, fromPhone, profilePicture);
            
            // Ensure chat container is visible
            const chatContainer = document.querySelector('.chat-messages');
            if (chatContainer) {
                chatContainer.style.display = 'block';
                chatContainer.style.visibility = 'visible';
            }
            
            // Also show the sidebar if it's hidden
            const chatList = document.querySelector('.chat-list');
            if (chatList) {
                chatList.style.display = 'block';
            }
            
            // Show browser notification for new message
            const messagePreview = msg.content ? msg.content.substring(0, 50) : 'New message';
            if (typeof showBrowserNotification === 'function') {
                showBrowserNotification(fromPhone, messagePreview);
            }
        }
    }
    
    // Always reload conversation list to show new message in real-time
    if (sessionId && typeof loadChatsFromApi === 'function') {
        loadChatsFromApi(sessionId);
    }
}

// Show notification for new message
function showNewMessageNotification(msg, fromPhone) {
    // Create notification badge if not exists
    let badge = document.getElementById('unread-badge-' + fromPhone);
    if (!badge) {
        // Add notification indicator to the conversation list
        const conversationItem = document.querySelector(`[data-phone="${fromPhone}"]`);
        if (conversationItem) {
            conversationItem.classList.add('bg-yellow-50');
            const unreadDot = document.createElement('span');
            unreadDot.id = 'unread-badge-' + fromPhone;
            unreadDot.className = 'absolute top-2 right-2 w-3 h-3 bg-red-500 rounded-full';
            conversationItem.style.position = 'relative';
            conversationItem.appendChild(unreadDot);
        }
    }
    
    // Show browser notification if permitted
    if (Notification.permission === 'granted') {
        new Notification('New Message', {
            body: `New message from ${fromPhone}`,
            icon: '/favicon.ico'
        });
    }
    
    // Play notification sound
    playNotificationSound();
    
    // Update unread count in header
    updateUnreadCount();
}

// Play notification sound
function playNotificationSound() {
    try {
        const audio = new Audio('data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YU' + 'A'.repeat(100));
        audio.volume = 0.3;
        // Note: In production, use actual sound file
    } catch (e) {
        // Ignore audio errors
    }
}

// Update unread count in stats
async function updateUnreadCount() {
    try {
        const response = await fetch('{{ route("crm.chat.unread-count") }}');
        const data = await response.json();
        
        // Update the unread count display if it exists
        const unreadElement = document.querySelector('.unread-count');
        if (unreadElement && data.unread !== undefined) {
            unreadElement.textContent = data.unread;
        }
    } catch (error) {
        console.error('Error updating unread count:', error);
    }
}

// Request notification permission
async function requestNotificationPermission() {
    if ('Notification' in window && Notification.permission === 'default') {
        await Notification.requestPermission();
    }
}

// Listen for WebSocket events - incoming messages
document.addEventListener('chatery.message', function(e) {
    var data = e.detail;
    console.log('[Chat] WebSocket message received:', data);
    
    if (!data || !data.data) return;
    
    var msgData = data.data;
    var fromPhone = msgData.from ? msgData.from.replace('@s.whatsapp.net', '').replace('@g.us', '') : '';
    var sessionId = msgData.sessionId || window.currentSession;
    
    console.log('[Chat] Message from:', fromPhone, 'Current chat:', window.currentChatId, 'Session:', sessionId);
    
    // Get current viewing phone
    var currentPhone = '';
    if (window.currentChatId) {
        currentPhone = window.currentChatId.replace('@s.whatsapp.net', '').replace('@g.us', '');
    }
    
    // If viewing this conversation, append new message smoothly
    // Otherwise, auto-switch to the conversation with new message
    if (currentPhone && fromPhone === currentPhone) {
        console.log('[Chat] Appending new message for:', fromPhone);
        
        // Create message object from WebSocket data
        var newMessage = {
            content: msgData.content || '',
            caption: msgData.caption || '',
            timestamp: msgData.timestamp || Math.floor(Date.now() / 1000),
            fromMe: false, // Inbound message
            from: msgData.from,
            pushName: msgData.pushName
        };
        
        // Check message type
        if (msgData.type === 'imageMessage' || msgData.imageMessage) {
            newMessage.content = { imageMessage: msgData.imageMessage || msgData };
        } else if (msgData.type === 'documentMessage' || msgData.documentMessage) {
            newMessage.content = { documentMessage: msgData.documentMessage || msgData };
        } else if (msgData.type === 'audioMessage' || msgData.audioMessage) {
            newMessage.content = { audioMessage: msgData.audioMessage || msgData };
        } else if (msgData.type === 'videoMessage' || msgData.videoMessage) {
            newMessage.content = { videoMessage: msgData.videoMessage || msgData };
        }
        
        // Append the new message using the appendMessage function
        if (typeof appendMessage === 'function') {
            appendMessage(newMessage, currentProfilePicture);
        } else if (typeof loadAndMergeMessages === 'function') {
            // Fallback to reload if append function not available
            loadAndMergeMessages(sessionId, window.currentChatId, fromPhone);
        }
    } else {
        // Auto-switch to the conversation with new message
        console.log('[Chat] Auto-opening conversation with new message from:', fromPhone);
        
        if (sessionId && fromPhone) {
            const chatId = fromPhone + '@s.whatsapp.net';
            
            // Get chat details from the conversation list to find profile picture
            const conversationItem = document.querySelector(`[data-chat-id*="${fromPhone}"]`);
            let profilePicture = null;
            
            // Try to find profile picture from conversation list
            if (conversationItem) {
                const img = conversationItem.querySelector('img');
                if (img) {
                    profilePicture = img.src;
                }
            }
            
            // Automatically select and show this conversation
            selectApiChat(chatId, fromPhone, fromPhone, profilePicture);
            
            // Ensure chat container is visible (in case sidebar is hidden or minimized)
            const chatContainer = document.querySelector('.chat-messages');
            if (chatContainer) {
                chatContainer.style.display = 'block';
                chatContainer.style.visibility = 'visible';
            }
            
            // Also show the sidebar if it's hidden
            const chatList = document.querySelector('.chat-list');
            if (chatList) {
                chatList.style.display = 'block';
            }
            
            // Show browser notification for new message
            const messagePreview = msgData.content ? msgData.content.substring(0, 50) : 'New message';
            if (typeof showBrowserNotification === 'function') {
                showBrowserNotification(fromPhone, messagePreview);
            }
        }
    }
    
    // Always reload conversation list to show new message in real-time
    if (sessionId && typeof loadChatsFromApi === 'function') {
        console.log('[Chat] Reloading conversation list for session:', sessionId);
        loadChatsFromApi(sessionId);
    }
});

// Listen for conversation update events
document.addEventListener('chatery.conversation.update', function(e) {
    var sessionId = window.currentSession || '{{ $sessionId ?? "" }}';
    if (sessionId && typeof loadChatsFromApi === 'function') {
        loadChatsFromApi(sessionId);
    }
});
let currentConversation = null;
let currentSession = '{{ $sessionId ?? "" }}';
let currentChatId = null;
let currentProfilePicture = null;
let quickReplies = [];
let lastLoadedMessageCount = 0;

// Store profile picture globally when selecting a conversation
function setCurrentProfilePicture(profilePicture) {
    currentProfilePicture = profilePicture;
    
    // Also update the chat header with profile picture
    const headerContainer = document.querySelector('#chatHeader .flex.items-center.space-x-3');
    if (headerContainer) {
        const existingImg = headerContainer.querySelector('img');
        const existingDiv = headerContainer.querySelector('.rounded-full:not(.bg-whatsapp-light)');
        
        let profileHtml = '';
        if (profilePicture) {
            profileHtml = `<img src="${profilePicture}" alt="Profile" class="w-12 h-12 rounded-full object-cover">`;
        } else {
            profileHtml = `<div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                <i class="fas fa-user text-gray-500 text-lg"></i>
            </div>`;
        }
        
        // Replace the first child (either img or div)
        if (existingImg) {
            existingImg.outerHTML = profileHtml;
        } else if (existingDiv) {
            existingDiv.outerHTML = profileHtml;
        }
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadQuickReplies();
    
    // Request notification permission
    requestNotificationPermission();
    
    // Initialize SSE for webhook-based real-time updates
    initSSE();
    
    // Start automatic conversation list refresh (for real-time updates)
    startConversationRefresh();
    
    // Try to initialize WebSocket
    if (typeof ChateryWebSocket !== 'undefined') {
        try {
            // Pass the current session to WebSocket
            var wsSessionId = window.currentSession || '{{ $sessionId ?? "" }}';
            ChateryWebSocket.init(wsSessionId);
            console.log('[Chat] WebSocket initialized with session:', wsSessionId);
        } catch (e) {
            console.log('[Chat] WebSocket init failed:', e);
        }
    }
    
    // If session is selected (from URL or PHP), load chats from API
    if (window.currentSession) {
        loadChatsFromApi(window.currentSession);
    }
    
    // Cleanup on page leave
    window.addEventListener('beforeunload', function() {
        stopConversationRefresh();
        stopMessagesRefresh();
        stopPolling();
        if (eventSource) {
            eventSource.close();
        }
    });
});

function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.getElementById(id).classList.add('flex');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).classList.remove('flex');
}

async function testApiConnection() {
    try {
        const response = await fetch('{{ route("crm.chat.test-api") }}');
        const result = await response.json();
        
        if (result.success) {
            alert('API Connection Successful!\nSessions found: ' + result.sessions_count);
        } else {
            alert('API Connection Failed: ' + result.message);
        }
    } catch (error) {
        alert('API Connection Error: ' + error.message);
    }
}

async function loadQuickReplies() {
    try {
        const response = await fetch('{{ route("crm.chat.quick-replies") }}');
        console.log('Quick replies response status:', response.status);
        
        if (!response.ok) {
            console.error('Quick replies HTTP error:', response.status);
            return;
        }
        
        const result = await response.json();
        console.log('Quick replies result:', result);
        
        if (result.success) {
            quickReplies = result.data;
            renderQuickReplies();
        }
    } catch (error) {
        console.error('Error loading quick replies:', error);
    }
}

async function loadChatsFromApi(sessionId) {
    try {
        console.log('Loading chats for session:', sessionId);
        const response = await fetch(`/crm/chat/chats?session_id=${encodeURIComponent(sessionId)}`);
        const result = await response.json();
        console.log('API response:', result);
        
        // Handle different response formats
        let chatsData = [];
        if (result.success) {
            // Chatery API returns { success: true, data: { chats: [...] } }
            if (result.data && result.data.chats) {
                chatsData = result.data.chats;
            } else if (Array.isArray(result.data)) {
                chatsData = result.data;
            }
        }
        
        console.log('Chats data:', chatsData);
        
        if (chatsData && chatsData.length > 0) {
            renderApiChats(chatsData);
        } else {
            document.getElementById('conversationList').innerHTML = 
                '<div class="p-8 text-center text-gray-500"><p>No chats found for this session</p></div>';
        }
    } catch (error) {
        console.error('Error loading chats from API:', error);
        document.getElementById('conversationList').innerHTML = 
            '<div class="p-8 text-center text-red-500"><p>Error: ' + error.message + '</p></div>';
    }
}

// Load contacts from API for real-time contact updates
async function loadContactsFromApi(sessionId) {
    try {
        console.log('[Chat] Loading contacts for session:', sessionId);
        const response = await fetch(`/crm/chat/contacts?session_id=${encodeURIComponent(sessionId)}`);
        const result = await response.json();
        console.log('[Chat] Contacts API response:', result);
        
        // Handle different response formats
        let contactsData = [];
        if (result.success) {
            if (result.data && result.data.contacts) {
                contactsData = result.data.contacts;
            } else if (Array.isArray(result.data)) {
                contactsData = result.data;
            }
        }
        
        console.log('[Chat] Contacts data:', contactsData);
        
        // Dispatch event for contact list update
        var event = new CustomEvent('chatery.contacts.loaded', { detail: { contacts: contactsData, sessionId: sessionId } });
        document.dispatchEvent(event);
    } catch (error) {
        console.error('[Chat] Error loading contacts from API:', error);
    }
}

function renderApiChats(chats) {
    const container = document.getElementById('conversationList');
    
    if (!chats || chats.length === 0) {
        container.innerHTML = '<div class="p-8 text-center text-gray-500"><p>No chats found</p></div>';
        return;
    }
    
    container.innerHTML = chats.map(chat => {
        // Handle different field names from Chatery API
        const chatId = chat.id || chat.chatId || '';
        const name = chat.name || chat.id?.split('@')[0] || chat.phone || 'Unknown';
        const phone = chat.id || chat.phone || '';
        const unreadCount = chat.unreadCount || chat.unread || 0;
        const lastMessage = chat.lastMessage?.content?.conversation || chat.lastMessage?.message?.conversation || chat.lastMessage || '';
        const profilePicture = chat.profilePicture || chat.profilePictureUrl || null;
        const isGroup = chat.isGroup === true || chat.isGroup === 'true' || (chat.id && chat.id.includes('@g.us'));
        
        // Profile picture HTML
        let profileHtml = '';
        const profilePicValue = profilePicture ? profilePicture.replace(/'/g, "\\'") : '';
        if (profilePicture) {
            profileHtml = `<img src="${profilePicture}" alt="${name}" class="w-10 h-10 rounded-full object-cover">`;
        } else if (isGroup) {
            profileHtml = `<div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-users text-green-500"></i>
            </div>`;
        } else {
            profileHtml = `<div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user text-gray-500"></i>
            </div>`;
        }
        
        return `
        <div onclick="selectApiChat('${chatId}', '${name}', '${phone}', '${profilePicValue}')" 
             class="p-4 hover:bg-gray-50 cursor-pointer transition-colors conversation-item"
             data-chat-id="${chatId}"
             data-unread="${unreadCount}"
             data-assigned=""
             data-is-group="${isGroup}">
            <div class="flex items-start space-x-3">
                ${profileHtml}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium text-gray-800 truncate">${name}</h3>
                        ${unreadCount > 0 ? `<span class="px-2 py-0.5 text-xs bg-red-500 text-white rounded-full">${unreadCount}</span>` : ''}
                    </div>
                    <p class="text-sm text-gray-500 truncate">${lastMessage || 'No messages'}</p>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-gray-400"></span>
                    </div>
                </div>
            </div>
        </div>`;
    }).join('');
}

function formatTime(timestamp) {
    if (!timestamp) return '';
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
    if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
    return date.toLocaleDateString();
}

async function selectApiChat(chatId, name, phone, profilePicture = null) {
    currentChatId = chatId;
    currentConversation = null;
    
    // Set profile picture globally
    currentProfilePicture = profilePicture;
    
    // Update header with profile picture
    setCurrentProfilePicture(profilePicture);
    
    // Start auto-refresh for messages
    startMessagesRefresh();
    
    // Extract phone number from chatId (remove @s.whatsapp.net)
    let phoneNumber = phone;
    if (phoneNumber && phoneNumber.includes('@s.whatsapp.net')) {
        phoneNumber = phoneNumber.replace('@s.whatsapp.net', '');
    } else if (chatId && chatId.includes('@s.whatsapp.net')) {
        phoneNumber = chatId.replace('@s.whatsapp.net', '');
    }
    
    // Try to find conversation by phone in database
    if (phoneNumber) {
        try {
            const response = await fetch(`/crm/chat/by-phone/${encodeURIComponent(phoneNumber)}`);
            const result = await response.json();
            if (result.success && result.data) {
                currentConversation = result.data.id;
            }
        } catch (e) {
            console.log('No database conversation found for phone:', phoneNumber);
        }
    }
    
    // Show chat area
    document.getElementById('chatHeader').classList.remove('hidden');
    document.getElementById('messageInput').classList.remove('hidden');
    document.getElementById('quickRepliesPanel').classList.remove('hidden');
    
    document.getElementById('chatUserName').textContent = name || phoneNumber || phone;
    document.getElementById('chatUserPhone').textContent = phoneNumber || phone;
    
    // Load messages from both API and database, then merge
    loadAndMergeMessages(currentSession, chatId, phoneNumber);
}

async function loadMessagesFromApi(sessionId, chatId) {
    try {
        console.log('Loading messages for chat:', chatId);
        const response = await fetch(`/crm/chat/messages?session_id=${encodeURIComponent(sessionId)}&chat_id=${encodeURIComponent(chatId)}`);
        const result = await response.json();
        console.log('Messages API response:', result);
        
        // Handle different response formats
        let messagesData = [];
        if (result.success) {
            // Chatery API returns { success: true, data: { messages: [...] } }
            if (result.data && result.data.messages) {
                messagesData = result.data.messages;
            } else if (Array.isArray(result.data)) {
                messagesData = result.data;
            }
        }
        
        console.log('Messages data:', messagesData);
        
        if (messagesData && messagesData.length > 0) {
            renderApiMessages(messagesData);
        } else {
            document.getElementById('messageList').innerHTML = 
                '<div class="text-center text-gray-500 py-8"><p>No messages yet</p></div>';
        }
    } catch (error) {
        console.error('Error loading messages:', error);
    }
}

// Load and merge messages from both API and database
async function loadAndMergeMessages(sessionId, chatId, phoneNumber) {
    try {
        let apiMessages = [];
        let dbMessages = [];
        
        // Load API messages if session and chatId available
        if (sessionId && chatId) {
            const apiResponse = await fetch(`/crm/chat/messages?session_id=${encodeURIComponent(sessionId)}&chat_id=${encodeURIComponent(chatId)}`);
            const apiResult = await apiResponse.json();
            
            if (apiResult.success) {
                if (apiResult.data && apiResult.data.messages) {
                    apiMessages = apiResult.data.messages;
                } else if (Array.isArray(apiResult.data)) {
                    apiMessages = apiResult.data;
                }
            }
        }
        
        // Load database messages if we have a conversation
        if (currentConversation) {
            const dbResponse = await fetch(`/crm/chat/${currentConversation}/messages`);
            const dbResult = await dbResponse.json();
            
            if (dbResult.success && dbResult.data) {
                dbMessages = dbResult.data;
            }
        }
        
        // Merge and deduplicate messages
        const merged = mergeMessages(apiMessages, dbMessages);
        
        if (merged.length > 0) {
            renderMergedMessages(merged);
        } else {
            document.getElementById('messageList').innerHTML = 
                '<div class="text-center text-gray-500 py-8"><p>No messages yet</p></div>';
        }
    } catch (error) {
        console.error('Error loading merged messages:', error);
        // Fallback to API only
        loadMessagesFromApi(sessionId, chatId);
    }
}

// Merge messages from API and database, removing duplicates
function mergeMessages(apiMessages, dbMessages) {
    const merged = [];
    const seen = new Set();
    
    // Convert and add database messages
    for (const msg of dbMessages) {
        const key = msg.message_id || `db_${msg.id}`;
        if (!seen.has(key)) {
            seen.add(key);
            merged.push({
                ...msg,
                timestamp: msg.created_at ? new Date(msg.created_at).getTime() / 1000 : 0,
                fromMe: msg.direction === 'outbound',
                content: msg.message,
                source: 'database'
            });
        }
    }
    
    // Add API messages
    for (const msg of apiMessages) {
        const key = msg.id || `api_${msg.timestamp}`;
        if (!seen.has(key)) {
            seen.add(key);
            merged.push({
                ...msg,
                source: 'api'
            });
        }
    }
    
    // Sort by timestamp
    return merged.sort((a, b) => (a.timestamp || 0) - (b.timestamp || 0));
}

// Render merged messages
function renderMergedMessages(messages) {
    const container = document.getElementById('messageList');
    
    if (!messages || messages.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-500 py-8"><p>No messages yet</p></div>';
        return;
    }
    
    container.innerHTML = messages.map(msg => {
        // Handle different message formats
        const isFromMe = msg.fromMe || msg.direction === 'outbound';
        
        // Extract message content and type
        let content = '';
        let messageType = 'text';
        let mediaUrl = '';
        let caption = '';
        
        // Check for image
        if (msg.content?.imageMessage || msg.message?.imageMessage) {
            messageType = 'image';
            mediaUrl = msg.content?.imageMessage?.url || msg.message?.imageMessage?.url || msg.content?.imageMessage?.thumbnailDirectPath || '';
            caption = msg.content?.imageMessage?.caption || msg.message?.imageMessage?.caption || '';
        }
        // Check for document
        else if (msg.content?.documentMessage || msg.message?.documentMessage) {
            messageType = 'document';
            mediaUrl = msg.content?.documentMessage?.url || msg.message?.documentMessage?.url || '';
            const fileName = msg.content?.documentMessage?.fileName || msg.message?.documentMessage?.fileName || 'Document';
            caption = msg.content?.documentMessage?.caption || msg.message?.documentMessage?.caption || fileName;
        }
        // Check for audio
        else if (msg.content?.audioMessage || msg.message?.audioMessage) {
            messageType = 'audio';
            mediaUrl = msg.content?.audioMessage?.url || msg.message?.audioMessage?.url || '';
        }
        // Check for video
        else if (msg.content?.videoMessage || msg.message?.videoMessage) {
            messageType = 'video';
            mediaUrl = msg.content?.videoMessage?.url || msg.message?.videoMessage?.url || '';
            caption = msg.content?.videoMessage?.caption || msg.message?.videoMessage?.caption || '';
        }
        // Check for text message
        else if (msg.content?.conversation) {
            content = msg.content.conversation;
        } else if (msg.message?.conversation) {
            content = msg.message.conversation;
        } else if (msg.content?.extendedTextMessage?.text) {
            content = msg.content.extendedTextMessage.text;
        } else if (msg.message?.extendedTextMessage?.text) {
            content = msg.message.extendedTextMessage.text;
        } else if (msg.content?.text) {
            content = msg.content.text;
        } else if (msg.message?.text) {
            content = msg.message.text;
        } else if (msg.body) {
            content = msg.body;
        } else if (msg.text) {
            content = msg.text;
        } else if (msg.content && typeof msg.content === 'string') {
            content = msg.content;
        } else if (msg.message && typeof msg.message === 'string') {
            content = msg.message;
        }
        
        // Format timestamp
        let timeStr = '';
        if (msg.timestamp) {
            const date = new Date(msg.timestamp * 1000);
            timeStr = date.toLocaleTimeString();
        }
        
        // Render based on message type
        let messageContent = '';
        
        if (messageType === 'image' && mediaUrl) {
            messageContent = `
                ${caption ? `<p class="mb-2">${caption}</p>` : ''}
                <img src="${mediaUrl}" alt="Image" class="max-w-full rounded" style="max-height: 200px;" onclick="window.open('${mediaUrl}', '_blank')">
            `;
        } else if (messageType === 'document' && mediaUrl) {
            messageContent = `
                <a href="${mediaUrl}" target="_blank" class="flex items-center p-2 bg-gray-100 rounded hover:bg-gray-200">
                    <i class="fas fa-file text-blue-500 text-2xl mr-3"></i>
                    <span class="text-blue-600">${caption || 'Document'}</span>
                </a>
            `;
        } else if (messageType === 'audio' && mediaUrl) {
            messageContent = `
                <audio controls class="w-full h-10">
                    <source src="${mediaUrl}" type="audio/ogg">
                    Your browser does not support the audio element.
                </audio>
            `;
        } else if (messageType === 'video' && mediaUrl) {
            messageContent = `
                ${caption ? `<p class="mb-2">${caption}</p>` : ''}
                <video src="${mediaUrl}" controls class="max-w-full rounded" style="max-height: 200px;"></video>
            `;
        } else {
            messageContent = `<p>${content || '[Message]'}</p>`;
        }
        
        return `
        <div class="flex mb-4 ${isFromMe ? 'justify-end' : 'justify-start'}">
            <div class="max-w-[70%] ${isFromMe ? 'bg-whatsapp-light text-white' : 'bg-gray-100 text-gray-800'} rounded-lg px-4 py-2">
                ${messageContent}
                <p class="text-xs ${isFromMe ? 'text-white opacity-70' : 'text-gray-400'} mt-1">
                    ${timeStr}
                </p>
            </div>
        </div>`;
    }).join('');
    
    container.scrollTop = container.scrollHeight;
}

function renderApiMessages(messages) {
    const container = document.getElementById('messageList');
    
    if (!messages || messages.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-500 py-8"><p>No messages yet</p></div>';
        return;
    }
    
    // Sort messages by timestamp (oldest first for chat display)
    const sortedMessages = [...messages].sort((a, b) => (a.timestamp || 0) - (b.timestamp || 0));
    
    container.innerHTML = sortedMessages.map(msg => {
        // Handle different message formats from Chatery API
        const isFromMe = msg.fromMe || msg.direction === 'outbound';
        
        // Extract message content and type
        let content = '';
        let messageType = 'text';
        let mediaUrl = '';
        let caption = '';
        
        // Check for image
        if (msg.content?.imageMessage || msg.message?.imageMessage) {
            messageType = 'image';
            mediaUrl = msg.content?.imageMessage?.url || msg.message?.imageMessage?.url || msg.content?.imageMessage?.thumbnailDirectPath || '';
            caption = msg.content?.imageMessage?.caption || msg.message?.imageMessage?.caption || '';
        }
        // Check for document
        else if (msg.content?.documentMessage || msg.message?.documentMessage) {
            messageType = 'document';
            mediaUrl = msg.content?.documentMessage?.url || msg.message?.documentMessage?.url || '';
            const fileName = msg.content?.documentMessage?.fileName || msg.message?.documentMessage?.fileName || 'Document';
            caption = msg.content?.documentMessage?.caption || msg.message?.documentMessage?.caption || fileName;
        }
        // Check for audio
        else if (msg.content?.audioMessage || msg.message?.audioMessage) {
            messageType = 'audio';
            mediaUrl = msg.content?.audioMessage?.url || msg.message?.audioMessage?.url || '';
        }
        // Check for video
        else if (msg.content?.videoMessage || msg.message?.videoMessage) {
            messageType = 'video';
            mediaUrl = msg.content?.videoMessage?.url || msg.message?.videoMessage?.url || '';
            caption = msg.content?.videoMessage?.caption || msg.message?.videoMessage?.caption || '';
        }
        // Check for text message
        else if (msg.content?.conversation) {
            content = msg.content.conversation;
        } else if (msg.message?.conversation) {
            content = msg.message.conversation;
        } else if (msg.content?.extendedTextMessage?.text) {
            content = msg.content.extendedTextMessage.text;
        } else if (msg.message?.extendedTextMessage?.text) {
            content = msg.message.extendedTextMessage.text;
        } else if (msg.content?.text) {
            content = msg.content.text;
        } else if (msg.message?.text) {
            content = msg.message.text;
        } else if (msg.body) {
            content = msg.body;
        } else if (msg.text) {
            content = msg.text;
        } else if (typeof msg.content === 'string') {
            content = msg.content;
        } else {
            content = msg.message || '[Message]';
        }
        
        // Format timestamp
        let timeStr = '';
        if (msg.timestamp) {
            const date = new Date(msg.timestamp * 1000);
            timeStr = date.toLocaleTimeString();
        }
        
        // Render based on message type
        let messageContent = '';
        
        if (messageType === 'image' && mediaUrl) {
            messageContent = `
                ${caption ? `<p class="mb-2">${caption}</p>` : ''}
                <img src="${mediaUrl}" alt="Image" class="max-w-full rounded" style="max-height: 200px;" onclick="window.open('${mediaUrl}', '_blank')">
            `;
        } else if (messageType === 'document' && mediaUrl) {
            messageContent = `
                <a href="${mediaUrl}" target="_blank" class="flex items-center p-2 bg-gray-100 rounded hover:bg-gray-200">
                    <i class="fas fa-file text-blue-500 text-2xl mr-3"></i>
                    <span class="text-blue-600">${caption || 'Document'}</span>
                </a>
            `;
        } else if (messageType === 'audio' && mediaUrl) {
            messageContent = `
                <audio controls class="w-full h-10">
                    <source src="${mediaUrl}" type="audio/ogg">
                    Your browser does not support the audio element.
                </audio>
            `;
        } else if (messageType === 'video' && mediaUrl) {
            messageContent = `
                ${caption ? `<p class="mb-2">${caption}</p>` : ''}
                <video src="${mediaUrl}" controls class="max-w-full rounded" style="max-height: 200px;"></video>
            `;
        } else {
            messageContent = `<p>${content || '[Message]'}</p>`;
        }
        
        return `
        <div class="flex mb-4 ${isFromMe ? 'justify-end' : 'justify-start'}">
            <div class="max-w-[70%] ${isFromMe ? 'bg-whatsapp-light text-white' : 'bg-gray-100 text-gray-800'} rounded-lg px-4 py-2">
                ${messageContent}
                <p class="text-xs ${isFromMe ? 'text-white opacity-70' : 'text-gray-400'} mt-1">
                    ${timeStr}
                </p>
            </div>
        </div>`;
    }).join('');
    
    // Scroll to bottom
    container.scrollTop = container.scrollHeight;
}

// Helper function to get profile picture HTML
function getProfilePictureHtml(profilePicture) {
    if (profilePicture) {
        return `<img src="${profilePicture}" alt="Profile" class="w-8 h-8 rounded-full object-cover">`;
    }
    return `<div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-user text-gray-500 text-sm"></i>
    </div>`;
}

// Function to append a single message (for real-time updates)
function appendMessage(msg, profilePicture = null) {
    const container = document.getElementById('messageList');
    if (!container) return;
    
    const isFromMe = msg.fromMe || msg.direction === 'outbound';
    
    // Extract message content and type
    let content = '';
    let messageType = 'text';
    let mediaUrl = '';
    let caption = '';
    
    // Check for image
    if (msg.content?.imageMessage || msg.message?.imageMessage) {
        messageType = 'image';
        mediaUrl = msg.content?.imageMessage?.url || msg.message?.imageMessage?.url || msg.content?.imageMessage?.thumbnailDirectPath || '';
        caption = msg.content?.imageMessage?.caption || msg.message?.imageMessage?.caption || '';
    }
    // Check for document
    else if (msg.content?.documentMessage || msg.message?.documentMessage) {
        messageType = 'document';
        mediaUrl = msg.content?.documentMessage?.url || msg.message?.documentMessage?.url || '';
        const fileName = msg.content?.documentMessage?.fileName || msg.message?.documentMessage?.fileName || 'Document';
        caption = msg.content?.documentMessage?.caption || msg.message?.documentMessage?.caption || fileName;
    }
    // Check for audio
    else if (msg.content?.audioMessage || msg.message?.audioMessage) {
        messageType = 'audio';
        mediaUrl = msg.content?.audioMessage?.url || msg.message?.audioMessage?.url || '';
    }
    // Check for video
    else if (msg.content?.videoMessage || msg.message?.videoMessage) {
        messageType = 'video';
        mediaUrl = msg.content?.videoMessage?.url || msg.message?.videoMessage?.url || '';
        caption = msg.content?.videoMessage?.caption || msg.message?.videoMessage?.caption || '';
    }
    // Check for text message
    else if (msg.content?.conversation) {
        content = msg.content.conversation;
    } else if (msg.message?.conversation) {
        content = msg.message.conversation;
    } else if (msg.content?.extendedTextMessage?.text) {
        content = msg.content.extendedTextMessage.text;
    } else if (msg.message?.extendedTextMessage?.text) {
        content = msg.message.extendedTextMessage.text;
    } else if (msg.content?.text) {
        content = msg.content.text;
    } else if (msg.message?.text) {
        content = msg.message.text;
    } else if (msg.body) {
        content = msg.body;
    } else if (msg.text) {
        content = msg.text;
    } else if (msg.content && typeof msg.content === 'string') {
        content = msg.content;
    } else if (msg.message && typeof msg.message === 'string') {
        content = msg.message;
    }
    
    // Format timestamp
    let timeStr = '';
    if (msg.timestamp) {
        const date = new Date(msg.timestamp * 1000);
        timeStr = date.toLocaleTimeString();
    }
    
    // Render based on message type
    let messageContent = '';
    
    if (messageType === 'image' && mediaUrl) {
        messageContent = `
            ${caption ? `<p class="mb-2">${caption}</p>` : ''}
            <img src="${mediaUrl}" alt="Image" class="max-w-full rounded" style="max-height: 200px;" onclick="window.open('${mediaUrl}', '_blank')">
        `;
    } else if (messageType === 'document' && mediaUrl) {
        messageContent = `
            <a href="${mediaUrl}" target="_blank" class="flex items-center p-2 bg-gray-100 rounded hover:bg-gray-200">
                <i class="fas fa-file text-blue-500 text-xl mr-2"></i>
                <span class="text-blue-600 text-sm">${caption || 'Document'}</span>
            </a>
        `;
    } else if (messageType === 'audio' && mediaUrl) {
        messageContent = `
            <audio controls class="w-full h-10">
                <source src="${mediaUrl}" type="audio/ogg">
                Your browser does not support the audio element.
            </audio>
        `;
    } else if (messageType === 'video' && mediaUrl) {
        messageContent = `
            ${caption ? `<p class="mb-2">${caption}</p>` : ''}
            <video src="${mediaUrl}" controls class="max-w-full rounded" style="max-height: 200px;"></video>
        `;
    } else {
        messageContent = `<p>${content || '[Message]'}</p>`;
    }
    
    // Use provided profilePicture or fallback to currentProfilePicture
    const profilePic = profilePicture || currentProfilePicture;
    
    const messageHtml = `
    <div class="flex mb-4 ${isFromMe ? 'justify-end' : 'justify-start'}">
        ${!isFromMe ? `
        <div class="flex-shrink-0 mr-2">
            ${getProfilePictureHtml(profilePic)}
        </div>
        ` : ''}
        <div class="max-w-[70%] ${isFromMe ? 'bg-whatsapp-light text-white' : 'bg-gray-100 text-gray-800'} rounded-lg px-4 py-2">
            ${messageContent}
            <p class="text-xs ${isFromMe ? 'text-white opacity-70' : 'text-gray-400'} mt-1">
                ${timeStr}
            </p>
        </div>
    </div>`;
    
    // Append to container
    container.insertAdjacentHTML('beforeend', messageHtml);
    
    // Scroll to bottom
    container.scrollTop = container.scrollHeight;
}

function renderQuickReplies() {
    const container = document.getElementById('quickRepliesList');
    container.innerHTML = quickReplies.map(reply => 
        `<button onclick="useQuickReply('${reply.content.replace(/'/g, "\\'")}')" 
                class="px-3 py-1 text-xs bg-white border border-gray-200 rounded-full hover:bg-whatsapp-light hover:text-white whitespace-nowrap">
            ${reply.name}
        </button>`
    ).join('');
}

function useQuickReply(content) {
    document.getElementById('messageText').value = content;
    document.getElementById('messageText').focus();
}

function toggleAttachmentMenu() {
    document.getElementById('attachmentMenu').classList.toggle('hidden');
}

function toggleQuickReplies() {
    const panel = document.getElementById('quickRepliesPanel');
    panel.classList.toggle('hidden');
    document.getElementById('attachmentMenu').classList.add('hidden');
}

// Close attachment menu when clicking outside
document.addEventListener('click', function(e) {
    const attachmentMenu = document.getElementById('attachmentMenu');
    const attachmentBtn = attachmentMenu?.previousElementSibling;
    if (attachmentMenu && !attachmentMenu.contains(e.target) && !attachmentBtn?.contains(e.target)) {
        attachmentMenu.classList.add('hidden');
    }
});

function openAttachmentModal(type) {
    document.getElementById('attachmentMenu').classList.add('hidden');
    
    const chatId = currentChatId || currentConversation;
    if (!chatId) {
        alert('Please select a conversation first');
        return;
    }
    
    // Show modal based on type
    if (type === 'image') {
        document.getElementById('imageInput').click();
    } else if (type === 'document') {
        document.getElementById('documentInput').click();
    } else if (type === 'audio') {
        document.getElementById('audioInput').click();
    } else if (type === 'location') {
        const lat = prompt('Enter latitude:');
        const lng = prompt('Enter longitude:');
        if (lat && lng) sendLocation(lat, lng);
    } else if (type === 'contact') {
        const phone = prompt('Enter contact phone number:');
        if (phone) sendContact(phone);
    } else if (type === 'poll') {
        const question = prompt('Enter poll question:');
        if (question) sendPoll(question);
    }
}

async function sendLocation(lat, lng) {
    const chatId = currentChatId || currentConversation;
    if (!chatId) return;
    
    try {
        const response = await fetch('{{ route("api.send.location") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                sessionId: '{{ $sessionId ?? '' }}',
                chatId: currentChatId || chatId,
                latitude: lat,
                longitude: lng
            })
        });
        const result = await response.json();
        if (result.success) {
            alert('Location sent!');
            if (currentChatId) {
                loadMessagesFromApi('{{ $sessionId ?? '' }}', currentChatId);
            }
        }
    } catch (error) {
        console.error('Error sending location:', error);
    }
}

async function sendContact(phone) {
    const chatId = currentChatId || currentConversation;
    if (!chatId) return;
    
    try {
        const response = await fetch('{{ route("api.send.contact") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                sessionId: '{{ $sessionId ?? '' }}',
                chatId: currentChatId || chatId,
                phoneNumber: phone
            })
        });
        const result = await response.json();
        if (result.success) {
            alert('Contact sent!');
            if (currentChatId) {
                loadMessagesFromApi('{{ $sessionId ?? '' }}', currentChatId);
            }
        }
    } catch (error) {
        console.error('Error sending contact:', error);
    }
}

async function sendPoll(question) {
    const chatId = currentChatId || currentConversation;
    if (!chatId) return;
    
    const options = prompt('Enter poll options (comma separated):');
    if (!options) return;
    
    try {
        const response = await fetch('{{ route("api.send.poll") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                sessionId: '{{ $sessionId ?? '' }}',
                chatId: currentChatId || chatId,
                question: question,
                options: options.split(',').map(o => o.trim())
            })
        });
        const result = await response.json();
        if (result.success) {
            alert('Poll sent!');
            if (currentChatId) {
                loadMessagesFromApi('{{ $sessionId ?? '' }}', currentChatId);
            }
        }
    } catch (error) {
        console.error('Error sending poll:', error);
    }
}

// File input handlers
document.getElementById('imageInput')?.addEventListener('change', async function(e) {
    if (this.files.length > 0) {
        await sendFile(this.files[0], 'image');
    }
});

document.getElementById('documentInput')?.addEventListener('change', async function(e) {
    if (this.files.length > 0) {
        await sendFile(this.files[0], 'document');
    }
});

document.getElementById('audioInput')?.addEventListener('change', async function(e) {
    if (this.files.length > 0) {
        await sendFile(this.files[0], 'audio');
    }
});

async function sendFile(file, type) {
    const chatId = currentChatId || currentConversation;
    if (!chatId) {
        alert('Please select a conversation first');
        return;
    }
    
    // Upload file first to get URL
    const formData = new FormData();
    formData.append('file', file);
    
    try {
        // Upload the file
        const uploadResponse = await fetch('{{ route("crm.chat.upload") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        const uploadResult = await uploadResponse.json();
        
        if (!uploadResult.success) {
            alert('Failed to upload file: ' + (uploadResult.message || 'Unknown error'));
            return;
        }
        
        const fileUrl = uploadResult.data.url;
        const fileName = uploadResult.data.filename;
        const mimeType = uploadResult.data.mimeType;
        const sessionId = '{{ $sessionId ?? '' }}';
        
        // Now send the file using the URL
        let sendEndpoint = '';
        let sendData = {};
        
        if (type === 'image') {
            sendEndpoint = '{{ route("api.send.image") }}';
            sendData = {
                sessionId: sessionId,
                chatId: currentChatId || chatId,
                imageUrl: fileUrl
            };
        } else if (type === 'document') {
            sendEndpoint = '{{ route("api.send.document") }}';
            sendData = {
                sessionId: sessionId,
                chatId: currentChatId || chatId,
                documentUrl: fileUrl,
                filename: fileName,
                mimetype: mimeType
            };
        } else if (type === 'audio') {
            sendEndpoint = '{{ route("api.send.audio") }}';
            sendData = {
                sessionId: sessionId,
                chatId: currentChatId || chatId,
                audioUrl: fileUrl
            };
        }
        
        const sendResponse = await fetch(sendEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(sendData)
        });
        
        const sendResult = await sendResponse.json();
        
        if (sendResult.success) {
            alert(type.charAt(0).toUpperCase() + type.slice(1) + ' sent!');
            if (currentChatId) {
                loadMessagesFromApi(sessionId, currentChatId);
            }
        } else {
            alert('Error: ' + (sendResult.message || 'Failed to send ' + type));
        }
    } catch (error) {
        console.error('Error sending file:', error);
        alert('Error sending file: ' + error.message);
    }
}

async function selectConversation(id) {
    // Check if this is an API-only conversation (starts with "api_")
    if (typeof id === 'string' && id.startsWith('api_')) {
        const phoneNumber = id.replace('api_', '');
        const sessionId = '{{ $sessionId ?? '' }}';
        
        // Show chat area
        document.getElementById('chatHeader').classList.remove('hidden');
        document.getElementById('messageInput').classList.remove('hidden');
        document.getElementById('quickRepliesPanel').classList.remove('hidden');
        
        // Show phone as name and use chatId for API
        document.getElementById('chatUserName').textContent = phoneNumber;
        document.getElementById('chatUserPhone').textContent = phoneNumber;
        
        // Reset profile picture for API-only chats
        setCurrentProfilePicture(null);
        
        // Try to find database conversation by phone
        try {
            const dbResponse = await fetch(`/crm/chat/by-phone/${encodeURIComponent(phoneNumber)}`);
            const dbResult = await dbResponse.json();
            if (dbResult.success) {
                currentConversation = dbResult.data.id;
                // Set profile picture from contact if available
                if (dbResult.data.profile_picture) {
                    setCurrentProfilePicture(dbResult.data.profile_picture);
                }
            }
        } catch (e) {
            console.log('No database conversation');
        }
        
        // Load messages from API
        if (sessionId) {
            const chatId = phoneNumber + '@s.whatsapp.net';
            loadMessagesFromApi(sessionId, chatId);
        }
        return;
    }
    
    currentConversation = id;
    
    // Show chat area
    document.getElementById('chatHeader').classList.remove('hidden');
    document.getElementById('messageInput').classList.remove('hidden');
    document.getElementById('quickRepliesPanel').classList.remove('hidden');
    
    try {
        const response = await fetch(`/crm/chat/${id}`);
        const result = await response.json();
        
        if (result.success) {
            const conversation = result.data;
            document.getElementById('chatUserName').textContent = conversation.name || conversation.phone;
            document.getElementById('chatUserPhone').textContent = conversation.phone;
            
            // Set profile picture from conversation
            if (conversation.profile_picture) {
                setCurrentProfilePicture(conversation.profile_picture);
            } else if (conversation.contact && conversation.contact.avatar) {
                setCurrentProfilePicture(conversation.contact.avatar);
            }
            
            // Mark as read
            await fetch(`/crm/chat/${id}/read`, { method: 'POST' });
            
            renderMessages(conversation.messages);
        }
    } catch (error) {
        console.error('Error loading conversation:', error);
    }
}

function renderMessages(messages) {
    const container = document.getElementById('messageList');
    
    if (!messages || messages.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-500 py-8"><p>No messages yet</p></div>';
        return;
    }
    
    container.innerHTML = messages.map(msg => {
        const isFromMe = msg.direction === 'outbound';
        return `
        <div class="flex mb-4 ${isFromMe ? 'justify-end' : 'justify-start'}">
            ${!isFromMe ? `
            <div class="flex-shrink-0 mr-2">
                ${getProfilePictureHtml(currentProfilePicture)}
            </div>
            ` : ''}
            <div class="max-w-[70%] ${isFromMe ? 'bg-whatsapp-light text-white' : 'bg-gray-100 text-gray-800'} rounded-lg px-4 py-2">
                <p>${msg.message}</p>
                <p class="text-xs ${isFromMe ? 'text-white opacity-70' : 'text-gray-400'} mt-1">
                    ${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                </p>
            </div>
        </div>`;
    }).join('');
    
    container.scrollTop = container.scrollHeight;
}

async function sendMessage(e) {
    e.preventDefault();
    
    // Can send via database conversation or via API chat
    const chatId = currentChatId || currentConversation;
    if (!chatId) {
        alert('Please select a conversation first');
        return;
    }
    
    const message = document.getElementById('messageText').value.trim();
    if (!message) return;
    
    try {
        // If using API chat (currentChatId), send via API and also save to database
        if (currentChatId) {
            const sessionId = '{{ $sessionId ?? '' }}';
            
            // First, try to find or create conversation in database
            let phoneNumber = document.getElementById('chatUserPhone')?.textContent || '';
            
            // If we have a conversation ID from the merge, use it
            if (currentConversation) {
                // Send via database API and reload merged messages
                const response = await fetch(`/crm/chat/${currentConversation}/send`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message })
                });
                
                const result = await response.json();
                if (result.success) {
                    document.getElementById('messageText').value = '';
                    // Reload merged messages
                    loadAndMergeMessages(sessionId, currentChatId, phoneNumber);
                }
            } else {
                // No database conversation yet, send via API only
                const response = await fetch('{{ route("api.send.text") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        sessionId: sessionId,
                        chatId: currentChatId,
                        message: message
                    })
                });
                
                const result = await response.json();
                if (result.success) {
                    document.getElementById('messageText').value = '';
                    loadMessagesFromApi(sessionId, currentChatId);
                }
            }
        } else {
            // Using database conversation
            const response = await fetch(`/crm/chat/${currentConversation}/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message })
            });
            
            const result = await response.json();
            
            if (result.success) {
                document.getElementById('messageText').value = '';
                selectConversation(currentConversation);
            }
        }
    } catch (error) {
        console.error('Error sending message:', error);
    }
}

function handleEnter(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage(e);
    }
}

async function searchContacts(query) {
    if (query.length < 2) {
        document.getElementById('searchResults').classList.add('hidden');
        return;
    }
    
    try {
        const response = await fetch(`/crm/chat/contacts?search=${encodeURIComponent(query)}`);
        const result = await response.json();
        
        if (result.success) {
            const container = document.getElementById('searchResults');
            container.innerHTML = result.data.map(contact => `
                <div onclick="selectContact(${contact.id}, '${contact.name}', '${contact.phone}')" 
                     class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100">
                    <p class="font-medium text-gray-800">${contact.name}</p>
                    <p class="text-sm text-gray-500">${contact.phone}</p>
                </div>
            `).join('');
            container.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error searching contacts:', error);
    }
}

function selectContact(id, name, phone) {
    document.getElementById('contactSearch').value = '';
    document.getElementById('searchResults').classList.add('hidden');
    document.getElementById('selectedContact').classList.remove('hidden');
    document.getElementById('selectedContactName').textContent = name;
    document.getElementById('selectedContactPhone').textContent = phone;
    document.getElementById('selectedContactId').value = id;
}

document.getElementById('newChatForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const contactId = document.getElementById('selectedContactId').value;
    if (!contactId) {
        alert('Please select a contact');
        return;
    }
    
    try {
        const response = await fetch('{{ route("crm.chat.create") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ contact_id: contactId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeModal('newChatModal');
            location.reload();
        }
    } catch (error) {
        console.error('Error creating chat:', error);
    }
});

function openAssignModal() {
    openModal('assignModal');
}

async function assignConversation() {
    const userId = document.getElementById('assignUser').value;
    if (!userId) return;
    
    try {
        const response = await fetch(`/crm/chat/${currentConversation}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ assigned_to: userId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeModal('assignModal');
            location.reload();
        }
    } catch (error) {
        console.error('Error assigning conversation:', error);
    }
}

async function closeConversation() {
    if (!confirm('Close this conversation?')) return;
    
    try {
        const response = await fetch(`/crm/chat/${currentConversation}/close`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        }
    } catch (error) {
        console.error('Error closing conversation:', error);
    }
}

function filterConversations(type) {
    // Update button styles
    const buttons = ['filterAll', 'filterIndividuals', 'filterGroups'];
    buttons.forEach(id => {
        const btn = document.getElementById(id);
        if (btn) {
            if (id === 'filter' + type.charAt(0).toUpperCase() + type.slice(1)) {
                btn.classList.remove('bg-gray-100', 'text-gray-600');
                btn.classList.add('bg-whatsapp-light', 'text-white');
            } else {
                btn.classList.remove('bg-whatsapp-light', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-600');
            }
        }
    });
    
    const items = document.querySelectorAll('.conversation-item');
    
    items.forEach(item => {
        let show = true;
        const isGroup = item.dataset.isGroup === 'true';
        const unread = parseInt(item.dataset.unread) || 0;
        const assigned = item.dataset.assigned;
        
        if (type === 'individuals') {
            show = !isGroup;
        } else if (type === 'groups') {
            show = isGroup;
        } else if (type === 'unread') {
            show = unread > 0;
        } else if (type === 'mine') {
            show = assigned == '{{ auth()->id() }}';
        }
        
        item.style.display = show ? '' : 'none';
    });
}

function filterBySession(sessionId) {
    window.currentSession = sessionId;
    if (sessionId) {
        // Load from Chatery API
        window.location.href = `{{ route('crm.chat.index') }}?session=${encodeURIComponent(sessionId)}`;
    } else {
        window.location.href = '{{ route("crm.chat.index") }}';
    }
}

function handleFileSelect(e) {
    const file = e.target.files[0];
    if (file) {
        // TODO: Upload file and send
        alert('File upload will be implemented');
    }
}
</script>
@endpush
@endsection
