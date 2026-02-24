/**
 * Chatery WebSocket Real-time Messaging
 * 
 * This file provides WebSocket functionality for real-time chat
 * using the Chatery WhatsApp API.
 * 
 * WebSocket URL: http://localhost:3000 (configurable)
 */

// WebSocket Configuration
var ChateryWebSocket = {
    socket: null,
    wsUrl: (window.location.protocol === 'https:' ? 'https://' : 'http://') + window.location.host + ':3000',
    connected: false,
    reconnectAttempts: 0,
    maxReconnectAttempts: 5,
    currentSessionId: '',
    
    // Initialize WebSocket connection with optional sessionId
    init: function(sessionId) {
        console.log('[WS] Initializing WebSocket with URL:', this.wsUrl);
        
        // Store sessionId for later use
        if (sessionId) {
            this.currentSessionId = sessionId;
        } else if (typeof currentSession !== 'undefined' && currentSession) {
            this.currentSessionId = currentSession;
        } else if (typeof window.currentSession !== 'undefined' && window.currentSession) {
            this.currentSessionId = window.currentSession;
        }
        
        console.log('[WS] Session ID:', this.currentSessionId);
        
        if (this.socket && this.socket.connected) {
            console.log('[WS] Already connected');
            return;
        }
        
        this.updateStatus('connecting', 'Connecting...');
        
        try {
            // Connect with sessionId as query parameter
            this.socket = io(this.wsUrl, {
                transports: ['websocket', 'polling'],
                reconnection: true,
                reconnectionAttempts: this.maxReconnectAttempts,
                reconnectionDelay: 1000,
                reconnectionDelayMax: 5000,
                query: {
                    sessionId: this.currentSessionId
                }
            });
            
            console.log('[WS] Socket created, setting up handlers...');
            this.setupEventHandlers();
            
        } catch (error) {
            console.error('[WS] Failed to initialize WebSocket:', error);
            this.updateStatus('disconnected', 'Init Failed');
        }
    },
    
    // Subscribe to specific session
    subscribe: function(sessionId) {
        this.currentSessionId = sessionId;
        if (this.socket && this.socket.connected) {
            this.socket.emit('subscribe', { sessionId: sessionId });
            console.log('[WS] Subscribed to session:', sessionId);
        }
    },
    
    // Subscribe to current session on connect
    subscribeToCurrentSession: function() {
        var sessionId = this.currentSessionId || (typeof window.currentSession !== 'undefined' ? window.currentSession : null);
        console.log('[WS] subscribeToCurrentSession - sessionId:', sessionId);
        if (sessionId && this.socket && this.socket.connected) {
            this.subscribe(sessionId);
        } else {
            console.log('[WS] Cannot subscribe - sessionId:', sessionId, 'socket connected:', this.socket ? this.socket.connected : 'no socket');
        }
    },
    
    // Change session
    changeSession: function(sessionId) {
        this.currentSessionId = sessionId;
        console.log('[WS] Changed session to:', sessionId);
    },
    
    // Setup event handlers
    setupEventHandlers: function() {
        var self = this;
        
        // Message Events - from official docs format
        this.socket.on('message', function(data) {
            console.log('[WS] ===== MESSAGE EVENT RECEIVED =====');
            console.log('[WS] Full data:', data);
            console.log('[WS] SessionId:', data.sessionId);
            console.log('[WS] Type:', data.type);
            
            // Handle different message formats - check if message is in data.data or directly in data.message
            var msgData = null;
            var sessionId = data.sessionId;
            
            // Format 1: Official docs format - data.data contains message
            if (data.data && data.data.content !== undefined) {
                msgData = data.data;
            }
            // Format 2: User's WebSocket format - data.message contains message
            else if (data.message) {
                msgData = data.message;
            }
            
            if (!msgData) {
                console.log('[WS] No message data found');
                return;
            }
            
            // Extract phone number from various formats
            var fromPhone = msgData.senderPhone || msgData.from || msgData.sender || '';
            fromPhone = fromPhone.toString().replace('@lid', '').replace('@s.whatsapp.net', '').replace('@g.us', '');
            console.log('[WS] From phone:', fromPhone);
            
            // Get push name
            var pushName = msgData.senderName || msgData.pushName || fromPhone;
            var content = msgData.content || '';
            
            // Show notification
            self.showNotification('New message from ' + pushName + ': ' + (content.substring(0, 30) || '[Media]'), 'message');
            self.playNotificationSound();
            
            console.log('[WS] Current chat id:', window.currentChatId);
            console.log('[WS] Current session id:', sessionId);
            
            // Extract phone number and create proper API chatId format
            if (window.currentChatId) {
                var phone = window.currentChatId.toString()
                    .replace('@lid', '')
                    .replace('@s.whatsapp.net', '')
                    .replace('@g.us', '')
                    .replace('lid:', '');
                var apiChatId = phone + '@s.whatsapp.net';
                console.log('[WS] Reloading messages with chatId:', apiChatId);
                
                if (typeof loadMessagesFromApi === 'function') {
                    loadMessagesFromApi(sessionId, apiChatId);
                }
            }
            
            // Also reload conversation list to update unread count
            if (typeof loadChatsFromApi === 'function') {
                loadChatsFromApi(sessionId);
            }
            
            // Dispatch custom event
            var event = new CustomEvent('chatery.message', { detail: data });
            document.dispatchEvent(event);
            
            return; // Skip the rest - we've already handled it by reloading
        });
        
        // Listen for ALL events (for debugging)
        this.socket.onAny(function(eventName, ...args) {
            console.log('[WS] Event:', eventName, args);
            
            // Handle message events specifically
            if (eventName === 'message') {
                self.handleIncomingMessage(args[0]);
            }
            // Handle connection update
            else if (eventName === 'connection.update') {
                self.handleConnectionUpdate(args[0]);
            }
        });
        
        // Connection Events
        this.socket.on('connect', function() {
            console.log('[WS] Connected to Chatery WebSocket');
            self.connected = true;
            self.reconnectAttempts = 0;
            self.updateStatus('connected', 'Real-time Active');
            self.showNotification('Connected to real-time chat', 'success');
            
            // Auto-subscribe to current session
            self.subscribeToCurrentSession();
        });
        
        this.socket.on('disconnect', function(reason) {
            console.log('[WS] Disconnected:', reason);
            self.connected = false;
            self.updateStatus('disconnected', 'Disconnected');
        });
        
        this.socket.on('connect_error', function(error) {
            console.error('[WS] Connection error:', error);
            self.reconnectAttempts++;
            self.updateStatus('disconnected', 'Connection Error');
        });
        
        this.socket.on('reconnect', function(attemptNumber) {
            console.log('[WS] Reconnected after', attemptNumber, 'attempts');
            self.connected = true;
            self.updateStatus('connected', 'Reconnected');
        });
        
        this.socket.on('reconnect_failed', function() {
            console.error('[WS] Failed to reconnect after', self.maxReconnectAttempts, 'attempts');
            self.updateStatus('disconnected', 'Connection Failed');
        });
        
        // Message sent event
        this.socket.on('message.sent', function(data) {
            console.log('[WS] Message sent:', data);
        });
        
        this.socket.on('message.update', function(data) {
            console.log('[WS] Message updated:', data);
        });
        
        // Connection update event
        this.socket.on('connection.update', function(data) {
            console.log('[WS] Connection update:', data);
            self.handleConnectionUpdate(data);
        });
        
        // QR update event
        this.socket.on('qr.update', function(data) {
            console.log('[WS] QR update:', data);
            if (data.data && data.data.qr) {
                self.showQrModal(data.sessionId, data.data.qr);
            }
        });
        
        // Session ready event
        this.socket.on('session.ready', function(data) {
            console.log('[WS] Session ready:', data);
            self.showNotification('Session ' + data.sessionId + ' is ready', 'success');
        });
        
        // Session disconnected event
        this.socket.on('session.disconnected', function(data) {
            console.log('[WS] Session disconnected:', data);
            self.showNotification('Session ' + data.sessionId + ' disconnected', 'warning');
        });
        
        // Contact update event - update contact list in real-time
        this.socket.on('contact.update', function(data) {
            console.log('[WS] Contact update:', data);
            
            // Handle the contact update format from user's WebSocket
            // Format: { sessionId: "...", timestamp: "...", contacts: [ { id: "...", notify: "..." } ] }
            var contacts = data.contacts || (data.data ? data.data.contacts : null);
            var sessionId = data.sessionId || self.currentSessionId;
            
            if (contacts && contacts.length > 0) {
                console.log('[WS] Contacts updated:', contacts);
                
                // Update contact list in the UI if the function exists
                if (typeof loadContactsFromApi === 'function') {
                    loadContactsFromApi(sessionId);
                }
                
                // Dispatch custom event for contact update
                var event = new CustomEvent('chatery.contact.update', { detail: data });
                document.dispatchEvent(event);
            }
        });
        
        // Chat update event - contains conversation list with new messages
        this.socket.on('chat.update', function(data) {
            console.log('[WS] Chat update:', data);
            
            // Handle the chat update format from user's WebSocket
            // Format: { sessionId: "...", timestamp: "...", chats: [ { id: "...", messages: [...], conversationTimestamp: ..., unreadCount: ... } ] }
            var chats = data.chats || (data.data ? data.data.chats : null);
            var sessionId = data.sessionId || self.currentSessionId;
            
            if (chats && chats.length > 0) {
                console.log('[WS] Chats updated:', chats);
                
                // Check if there's a new message in the chat update
                var currentPhone = window.currentChatId ? window.currentChatId.replace('@lid', '').replace('@s.whatsapp.net', '').replace('@g.us', '') : '';
                
                for (var i = 0; i < chats.length; i++) {
                    var chat = chats[i];
                    var chatId = chat.id ? chat.id.replace('@lid', '').replace('@s.whatsapp.net', '').replace('@g.us', '') : '';
                    
                    // If this chat is currently open, try to append new messages
                    if (chatId === currentPhone && chat.messages && chat.messages.length > 0) {
                        console.log('[WS] New message in open chat, appending...');
                        
                        // Get the latest message
                        var latestMsg = chat.messages[chat.messages.length - 1];
                        var msgData = latestMsg.message || latestMsg;
                        
                        // Create message object for appendMessage
                        var newMessage = {
                            content: msgData.conversation || msgData.extendedTextMessage?.text || '',
                            caption: '',
                            timestamp: chat.conversationTimestamp || Math.floor(Date.now() / 1000),
                            fromMe: false,
                            from: chatId + '@lid',
                            pushName: msgData.pushName || '',
                            message: msgData
                        };
                        
                        // Try to append the message
                        if (typeof appendMessage === 'function') {
                            appendMessage(newMessage, currentProfilePicture);
                        }
                    }
                }
                
                // Reload conversation list
                self.reloadConversationList(sessionId);
                
                // Also reload messages if viewing any conversation
                if (window.currentChatId && typeof loadMessagesFromApi === 'function') {
                    loadMessagesFromApi(sessionId, window.currentChatId);
                }
                
                // Dispatch custom event for chat update
                var event = new CustomEvent('chatery.chat.update', { detail: data });
                document.dispatchEvent(event);
            }
        });
        
        // Presence update event - show typing, online, etc.
        this.socket.on('presence.update', function(data) {
            console.log('[WS] Presence update:', data);
            
            // Handle the presence update format from user's WebSocket
            // Format: { sessionId: "...", timestamp: "...", presence: { id: "...", presences: { "...": { lastKnownPresence: "composing" } } } }
            var presenceData = data.presence || (data.data ? data.data.presence : null);
            var sessionId = data.sessionId || self.currentSessionId;
            
            if (presenceData && presenceData.presences) {
                console.log('[WS] Presence data:', presenceData.presences);
                
                // Update typing indicator in UI
                for (var contactId in presenceData.presences) {
                    var presence = presenceData.presences[contactId];
                    var phone = contactId.replace('@lid', '').replace('@s.whatsapp.net', '').replace('@g.us', '');
                    
                    // Show typing indicator if composing
                    if (presence.lastKnownPresence === 'composing') {
                        self.showTypingIndicator(phone);
                    } else if (presence.lastKnownPresence === 'paused') {
                        self.hideTypingIndicator(phone);
                    }
                }
                
                // Dispatch custom event for presence update
                var event = new CustomEvent('chatery.presence.update', { detail: data });
                document.dispatchEvent(event);
            }
        });
        
        // Group update event
        this.socket.on('group.update', function(data) {
            console.log('[WS] Group update:', data);
            // Reload conversation list when group info is updated
            self.reloadConversationList(data.sessionId);
        });
        
        // Group participants update event
        this.socket.on('group.participants.update', function(data) {
            console.log('[WS] Group participants update:', data);
            // Reload conversation list when group participants change
            self.reloadConversationList(data.sessionId);
        });
        
        // Message sent event
        this.socket.on('message.sent', function(data) {
            console.log('[WS] Message sent:', data);
            // Reload messages when we send a message
            if (window.currentChatId && typeof loadMessagesFromApi === 'function') {
                var sessionId = data.sessionId || self.currentSessionId;
                loadMessagesFromApi(sessionId, window.currentChatId);
            }
        });
        
        // Message update event (delivered, read, etc.)
        this.socket.on('message.update', function(data) {
            console.log('[WS] Message updated:', data);
            // Reload messages to show updated status
            if (window.currentChatId && typeof loadMessagesFromApi === 'function') {
                var sessionId = data.sessionId || self.currentSessionId;
                loadMessagesFromApi(sessionId, window.currentChatId);
            }
        });
        
        // Message delete event
        this.socket.on('message.delete', function(data) {
            console.log('[WS] Message deleted:', data);
            // Reload messages when a message is deleted
            if (window.currentChatId && typeof loadMessagesFromApi === 'function') {
                var sessionId = data.sessionId || self.currentSessionId;
                loadMessagesFromApi(sessionId, window.currentChatId);
            }
        });
        
        // Message reaction event
        this.socket.on('message.reaction', function(data) {
            console.log('[WS] Message reaction:', data);
            // Reload messages to show reactions
            if (window.currentChatId && typeof loadMessagesFromApi === 'function') {
                var sessionId = data.sessionId || self.currentSessionId;
                loadMessagesFromApi(sessionId, window.currentChatId);
            }
        });
        
        // Pong response (keep-alive)
        this.socket.on('pong', function(data) {
            console.log('[WS] Pong:', data);
        });
        
        // Subscribed event
        this.socket.on('subscribed', function(data) {
            console.log('[WS] Subscribed:', data);
            self.showNotification('Subscribed to session: ' + data.sessionId, 'success');
        });
    },
    
    // Reload conversation list
    reloadConversationList: function(sessionId) {
        var sessId = sessionId || this.currentSessionId || (typeof currentSession !== 'undefined' ? currentSession : '') || window.currentSession;
        
        // If using API chat, reload the chat list
        if (sessId && typeof loadChatsFromApi === 'function') {
            loadChatsFromApi(sessId);
        }
        
        // Also try to reload database conversations
        // This will refresh the conversation list in the sidebar
        var convList = document.getElementById('conversationList');
        if (convList) {
            // Trigger a reload by dispatching custom event
            var event = new CustomEvent('chatery.conversation.update');
            document.dispatchEvent(event);
        }
    },
    
    // Show typing indicator for a contact
    showTypingIndicator: function(phone) {
        var typingEl = document.getElementById('typing-' + phone);
        if (!typingEl) {
            // Create typing indicator element
            typingEl = document.createElement('div');
            typingEl.id = 'typing-' + phone;
            typingEl.className = 'typing-indicator flex items-center space-x-1 p-2 text-gray-500 text-sm';
            typingEl.innerHTML = '<span class="typing-dot">•</span><span class="typing-dot">•</span><span class="typing-dot">•</span><span class="ml-2">typing...</span>';
            
            // Try to find the conversation item and append typing indicator
            var convItem = document.querySelector(`[data-chat-id*="${phone}"]`);
            if (convItem) {
                convItem.appendChild(typingEl);
            }
        }
        typingEl.style.display = 'flex';
    },
    
    // Hide typing indicator for a contact
    hideTypingIndicator: function(phone) {
        var typingEl = document.getElementById('typing-' + phone);
        if (typingEl) {
            typingEl.style.display = 'none';
        }
    },
    
    // Play notification sound
    playNotificationSound: function() {
        try {
            // Simple beep using AudioContext
            var audioContext = new (window.AudioContext || window.webkitAudioContext)();
            var oscillator = audioContext.createOscillator();
            var gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            gainNode.gain.value = 0.1;
            
            oscillator.start();
            oscillator.stop(audioContext.currentTime + 0.1);
        } catch (e) {
            // Ignore audio errors
        }
    },
    
    // Handle connection update
    handleConnectionUpdate: function(data) {
        // Update session status in dropdown
        var sessionSelect = document.getElementById('sessionFilter');
        if (sessionSelect && data.sessionId) {
            var option = sessionSelect.querySelector('option[value="' + data.sessionId + '"]');
            if (option) {
                var status = data.data.status;
                option.textContent = data.sessionId + ' (' + status + ')';
                
                if (status === 'connected') {
                    option.classList.add('text-green-600');
                } else if (status === 'disconnected') {
                    option.classList.add('text-red-600');
                }
            }
        }
    },
    
    // Update connection status indicator
    updateStatus: function(status, message) {
        var statusEl = document.getElementById('ws-status');
        
        if (!statusEl) {
            // Create status indicator if not exists
            var header = document.querySelector('.flex.items-center.justify-between');
            if (header) {
                var indicator = document.createElement('div');
                indicator.id = 'ws-status';
                indicator.className = 'flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-medium';
                header.insertBefore(indicator, header.firstChild);
                statusEl = indicator;
            }
        }
        
        if (statusEl) {
            var statusClasses = {
                'connected': 'bg-green-100 text-green-800',
                'disconnected': 'bg-red-100 text-red-800',
                'connecting': 'bg-yellow-100 text-yellow-800'
            };
            
            var icon = status === 'connected' ? '<i class="fas fa-circle text-xs"></i>' : 
                       status === 'connecting' ? '<i class="fas fa-sync-alt text-xs animate-spin"></i>' : 
                       '<i class="fas fa-circle text-xs"></i>';
            
            statusEl.className = 'flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-medium ' + (statusClasses[status] || statusClasses.disconnected);
            statusEl.innerHTML = icon + ' <span>' + message + '</span>';
        }
    },
    
    // Show notification
    showNotification: function(message, type) {
        var notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-y-0 ';
        
        if (type === 'success') {
            notification.className += 'bg-green-500 text-white';
        } else if (type === 'warning') {
            notification.className += 'bg-yellow-500 text-white';
        } else if (type === 'error') {
            notification.className += 'bg-red-500 text-white';
        } else {
            notification.className += 'bg-gray-700 text-white';
        }
        
        var icon = type === 'success' ? 'check-circle' : 
                   type === 'warning' ? 'exclamation-triangle' : 
                   type === 'error' ? 'times-circle' : 
                   'info-circle';
        
        notification.innerHTML = '<div class="flex items-center"><i class="fas fa-' + icon + ' mr-2"></i><span>' + message + '</span></div>';
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(function() {
            notification.style.opacity = '0';
            setTimeout(function() { notification.remove(); }, 300);
        }, 3000);
    },
    
    // Show QR code modal
    showQrModal: function(sessionId, qrCode) {
        var modal = document.getElementById('qrModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'qrModal';
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden';
            modal.innerHTML = '<div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">' +
                '<div class="flex justify-between items-center mb-4">' +
                '<h3 class="text-lg font-bold">Scan QR Code</h3>' +
                '<button onclick="ChateryWebSocket.closeQrModal()" class="text-gray-500 hover:text-gray-700">' +
                '<i class="fas fa-times"></i></button></div>' +
                '<div class="text-center"><p class="text-sm text-gray-600 mb-4">Session: <span id="qrSessionId"></span></p>' +
                '<img id="qrImage" src="" alt="QR Code" class="mx-auto max-w-[250px]"></div></div>';
            document.body.appendChild(modal);
        }
        
        document.getElementById('qrSessionId').textContent = sessionId;
        document.getElementById('qrImage').src = 'data:image/png;base64,' + qrCode;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },
    
    // Close QR modal
    closeQrModal: function() {
        var modal = document.getElementById('qrModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    },
    
    // Test connection
    test: function() {
        if (this.socket && this.socket.connected) {
            alert('WebSocket is connected!\n\nSocket ID: ' + (this.socket.id || 'N/A') + '\nSession: ' + (this.currentSessionId || 'All Sessions'));
        } else {
            alert('WebSocket is not connected. Trying to reconnect...');
            this.init(this.currentSessionId);
        }
    }
};

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        // Get sessionId from the page if available
        var sessionId = '';
        if (typeof currentSession !== 'undefined') {
            sessionId = currentSession;
        }
        ChateryWebSocket.init(sessionId);
        
        // Listen for session filter changes
        var sessionFilter = document.getElementById('sessionFilter');
        if (sessionFilter) {
            sessionFilter.addEventListener('change', function() {
                ChateryWebSocket.changeSession(this.value);
            });
        }
    });
} else {
    // DOM already loaded
    var sessionId = '';
    if (typeof currentSession !== 'undefined') {
        sessionId = currentSession;
    }
    ChateryWebSocket.init(sessionId);
    
    // Listen for session filter changes
    var sessionFilter = document.getElementById('sessionFilter');
    if (sessionFilter) {
        sessionFilter.addEventListener('change', function() {
            ChateryWebSocket.changeSession(this.value);
        });
    }
}
