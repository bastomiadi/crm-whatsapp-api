@extends('layouts.app')

@section('title', 'Chat History')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Chat History</h1>
        <p class="text-gray-500 mt-1">View chats, messages, and contacts</p>
    </div>
    
    <!-- Session Selection -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-end space-x-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Session</label>
                <select id="historySessionId" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="">Select a session</option>
                    @if(isset($sessions['data']))
                        @foreach($sessions['data'] as $session)
                        <option value="{{ $session['sessionId'] }}">{{ $session['sessionId'] }} {{ $session['status'] === 'connected' ? '(Connected)' : '(' . $session['status'] . ')' }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <button onclick="loadChats()" class="px-6 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                Load Chats
            </button>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chat List -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">Chats</h2>
                <select id="chatType" onchange="loadChats()" class="text-sm border border-gray-300 rounded-lg px-2 py-1">
                    <option value="all">All</option>
                    <option value="individual">Individual</option>
                    <option value="group">Groups</option>
                </select>
            </div>
            <div id="chatList" class="divide-y divide-gray-100 max-h-[600px] overflow-y-auto">
                <div class="p-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p>Select a session to view chats</p>
                </div>
            </div>
        </div>
        
        <!-- Messages -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">Messages</h2>
                <div id="chatInfo" class="text-sm text-gray-500"></div>
            </div>
            <div id="messageList" class="p-4 max-h-[500px] overflow-y-auto">
                <div class="text-center text-gray-500 py-8">
                    <p>Select a chat to view messages</p>
                </div>
            </div>
            <div id="messageActions" class="p-4 border-t border-gray-200 hidden">
                <button onclick="markAsRead()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm">
                    Mark as Read
                </button>
            </div>
        </div>
    </div>
    
    <!-- Contacts Section -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Contacts</h2>
            <div class="flex items-center space-x-2">
                <input type="text" id="contactSearch" placeholder="Search contacts..." class="text-sm border border-gray-300 rounded-lg px-3 py-1.5" onkeyup="searchContacts()">
                <button onclick="loadContacts()" class="text-sm text-whatsapp-dark hover:underline">Refresh</button>
            </div>
        </div>
        <div id="contactList" class="p-4">
            <div class="text-center text-gray-500 py-4">
                <p>Select a session to view contacts</p>
            </div>
        </div>
    </div>
    
    <!-- Profile Picture Lookup -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-800 mb-4">Get Profile Picture</h2>
        <div class="flex items-end space-x-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <input type="text" id="profilePhone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent" placeholder="628123456789">
            </div>
            <button onclick="getProfilePicture()" class="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors">
                Get Picture
            </button>
        </div>
        <div id="profileResult" class="mt-4 hidden">
            <img id="profileImage" src="" alt="Profile Picture" class="w-24 h-24 rounded-full object-cover">
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentSessionId = null;
    let currentChatId = null;
    let contacts = [];
    
    async function loadChats() {
        const sessionId = document.getElementById('historySessionId').value;
        const type = document.getElementById('chatType').value;
        
        if (!sessionId) {
            showToast('Please select a session', 'error');
            return;
        }
        
        currentSessionId = sessionId;
        
        try {
            const response = await fetch('{{ route("api.chats.overview") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sessionId, type, limit: 50 })
            });
            
            const result = await response.json();
            console.log('Chats response:', result);
            
            if (result.success) {
                // Handle different response structures
                let chats = result.data || result.chats || [];
                if (!Array.isArray(chats)) {
                    chats = chats.chats || chats.data || [];
                }
                renderChatList(chats);
            } else {
                document.getElementById('chatList').innerHTML = '<p class="p-4 text-center text-gray-500">' + (result.message || 'No chats found') + '</p>';
            }
        } catch (error) {
            console.error('Load chats error:', error);
            showToast('Error: ' + error.message, 'error');
        }
    }
    
    function renderChatList(chats) {
        if (!Array.isArray(chats) || chats.length === 0) {
            document.getElementById('chatList').innerHTML = '<p class="p-4 text-center text-gray-500">No chats found</p>';
            return;
        }
        
        let html = '';
        
        chats.forEach(chat => {
            const isGroup = chat.id.includes('@g.us');
            const name = chat.name || chat.id.split('@')[0];
            const unread = chat.unreadCount || 0;
            const lastMsg = chat.lastMessage?.content?.conversation || chat.lastMessage?.message?.conversation || '';
            
            html += `
                <div class="p-4 hover:bg-gray-50 cursor-pointer transition-colors" onclick="loadMessages('${chat.id}')">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center ${isGroup ? 'bg-purple-100' : 'bg-gray-100'}">
                            ${isGroup ? 
                                '<svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>' :
                                '<span class="text-gray-600 font-medium">' + name.charAt(0).toUpperCase() + '</span>'
                            }
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="font-medium text-gray-800 truncate">${name}</p>
                                ${unread > 0 ? '<span class="bg-whatsapp-light text-white text-xs px-2 py-0.5 rounded-full">' + unread + '</span>' : ''}
                            </div>
                            <p class="text-sm text-gray-500 truncate">${lastMsg.substring(0, 50)}${lastMsg.length > 50 ? '...' : ''}</p>
                        </div>
                    </div>
                </div>
            `;
        });
        
        document.getElementById('chatList').innerHTML = html || '<p class="p-4 text-center text-gray-500">No chats found</p>';
    }
    
    async function loadMessages(chatId) {
        currentChatId = chatId;
        
        try {
            const response = await fetch('{{ route("api.chats.messages") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sessionId: currentSessionId, chatId, limit: 50 })
            });
            
            const result = await response.json();
            console.log('Messages response:', result);
            
            if (result.success) {
                let messages = result.data || result.messages || [];
                if (!Array.isArray(messages)) {
                    messages = messages.messages || messages.data || [];
                }
                renderMessages(messages);
                document.getElementById('messageActions').classList.remove('hidden');
                document.getElementById('chatInfo').textContent = chatId.split('@')[0];
            } else {
                document.getElementById('messageList').innerHTML = '<p class="text-center text-gray-500">' + (result.message || 'No messages found') + '</p>';
            }
        } catch (error) {
            console.error('Load messages error:', error);
            showToast('Error: ' + error.message, 'error');
        }
    }
    
    function renderMessages(messages) {
        if (!Array.isArray(messages) || messages.length === 0) {
            document.getElementById('messageList').innerHTML = '<p class="text-center text-gray-500">No messages found</p>';
            return;
        }
        
        let html = '<div class="space-y-4">';
        
        messages.forEach(msg => {
            console.log('Message object:', msg); // Debug log
            
            const isFromMe = msg.fromMe;
            const msgType = msg.type || msg.message?.messageType || '';
            
            // Extract text content from various possible structures
            let content = '';
            
            // Try different message content paths
            if (msg.content?.conversation) {
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
            } else if (msg.message?.ephemeralMessage?.message?.extendedTextMessage?.text) {
                content = msg.message.ephemeralMessage.message.extendedTextMessage.text;
            } else if (msg.message?.ephemeralMessage?.message?.conversation) {
                content = msg.message.ephemeralMessage.message.conversation;
            } else if (typeof msg.content === 'string') {
                content = msg.content;
            } else if (typeof msg.message === 'string') {
                content = msg.message;
            } else {
                // Handle different message types
                if (msgType === 'image' || msg.message?.imageMessage) {
                    content = '📷 Image' + (msg.message?.imageMessage?.caption ? ': ' + msg.message.imageMessage.caption : '');
                } else if (msgType === 'video' || msg.message?.videoMessage) {
                    content = '🎥 Video' + (msg.message?.videoMessage?.caption ? ': ' + msg.message.videoMessage.caption : '');
                } else if (msgType === 'audio' || msg.message?.audioMessage) {
                    content = '🎵 Audio';
                } else if (msgType === 'document' || msg.message?.documentMessage) {
                    content = '📄 Document: ' + (msg.message?.documentMessage?.fileName || 'File');
                } else if (msgType === 'sticker' || msg.message?.stickerMessage) {
                    content = '🏷️ Sticker';
                } else if (msgType === 'location' || msg.message?.locationMessage) {
                    content = '📍 Location';
                } else if (msgType === 'contact' || msg.message?.contactMessage) {
                    content = '👤 Contact';
                } else if (msgType === 'poll' || msg.message?.pollCreationMessage) {
                    content = '📊 Poll: ' + (msg.message?.pollCreationMessage?.name || '');
                } else {
                    content = '[Message]';
                }
            }
            
            const time = msg.timestamp ? new Date(msg.timestamp * 1000).toLocaleTimeString() : '';
            
            html += `
                <div class="flex ${isFromMe ? 'justify-end' : 'justify-start'}">
                    <div class="max-w-[70%] ${isFromMe ? 'bg-whatsapp-light/20' : 'bg-gray-100'} rounded-lg px-4 py-2">
                        <p class="text-sm text-gray-800">${escapeHtml(content)}</p>
                        <p class="text-xs text-gray-500 mt-1 text-right">${time}</p>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        document.getElementById('messageList').innerHTML = html;
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    async function markAsRead() {
        if (!currentChatId) return;
        
        try {
            const response = await fetch('{{ route("api.chats.mark-read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sessionId: currentSessionId, chatId: currentChatId })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Chat marked as read', 'success');
            } else {
                showToast(result.message || 'Failed to mark as read', 'error');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    }
    
    async function loadContacts() {
        if (!currentSessionId) {
            showToast('Please select a session first', 'error');
            return;
        }
        
        try {
            const response = await fetch('{{ route("api.contacts") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sessionId: currentSessionId, limit: 100 })
            });
            
            const result = await response.json();
            console.log('Contacts response:', result);
            
            if (result.success) {
                let contactList = result.data || result.contacts || [];
                if (!Array.isArray(contactList)) {
                    contactList = contactList.contacts || contactList.data || [];
                }
                contacts = contactList;
                renderContacts(contacts);
            } else {
                document.getElementById('contactList').innerHTML = '<p class="text-center text-gray-500">' + (result.message || 'No contacts found') + '</p>';
            }
        } catch (error) {
            console.error('Load contacts error:', error);
            showToast('Error: ' + error.message, 'error');
        }
    }
    
    function renderContacts(contactList) {
        if (!Array.isArray(contactList) || contactList.length === 0) {
            document.getElementById('contactList').innerHTML = '<p class="text-center text-gray-500">No contacts found</p>';
            return;
        }
        
        let html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
        
        contactList.forEach(contact => {
            const id = contact.id || '';
            const name = contact.name || contact.notify || id.split('@')[0];
            
            html += `
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                        <span class="text-gray-600 font-medium">${name.charAt(0).toUpperCase()}</span>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">${name}</p>
                        <p class="text-xs text-gray-500">${id.split('@')[0]}</p>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        document.getElementById('contactList').innerHTML = html || '<p class="text-center text-gray-500">No contacts found</p>';
    }
    
    function searchContacts() {
        const query = document.getElementById('contactSearch').value.toLowerCase();
        
        if (!query) {
            renderContacts(contacts);
            return;
        }
        
        const filtered = contacts.filter(c => {
            const name = (c.name || c.notify || '').toLowerCase();
            const id = (c.id || '').toLowerCase();
            return name.includes(query) || id.includes(query);
        });
        
        renderContacts(filtered);
    }
    
    async function getProfilePicture() {
        const phone = document.getElementById('profilePhone').value;
        
        if (!currentSessionId || !phone) {
            showToast('Please select a session and enter a phone number', 'error');
            return;
        }
        
        try {
            const response = await fetch('{{ route("api.profile-picture") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sessionId: currentSessionId, phone })
            });
            
            const result = await response.json();
            
            if (result.success && result.data && result.data.url) {
                document.getElementById('profileResult').classList.remove('hidden');
                document.getElementById('profileImage').src = result.data.url;
            } else {
                showToast('Profile picture not found', 'error');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    }
</script>
@endpush
@endsection
