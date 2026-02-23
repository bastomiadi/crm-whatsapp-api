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
    wsUrl: 'http://localhost:3000',
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
    
    // Change session
    changeSession: function(sessionId) {
        this.currentSessionId = sessionId;
        console.log('[WS] Changed session to:', sessionId);
    },
    
    // Setup event handlers
    setupEventHandlers: function() {
        var self = this;
        
        // Message Events
        this.socket.on('message', function(data) {
            console.log('[WS] ===== MESSAGE EVENT RECEIVED =====');
            console.log('[WS] Full data:', data);
            console.log('[WS] SessionId:', data.sessionId);
            console.log('[WS] Current sessionId:', self.currentSessionId);
            console.log('[WS] Window currentSession:', window.currentSession);
            
            // Get message content
            var msgData = data.message || {};
            var fromPhone = msgData.senderPhone || msgData.sender || '';
            fromPhone = fromPhone.replace('@lid', '').replace('@s.whatsapp.net', '').replace('@g.us', '');
            console.log('[WS] From phone:', fromPhone);
            
            // Show notification
            var pushName = msgData.senderName || fromPhone;
            var content = msgData.content || '';
            self.showNotification('New message from ' + pushName + ': ' + (content.substring(0, 30) || '[Media]'), 'message');
            self.playNotificationSound();
            
            // Get current viewing chat
            console.log('[WS] Current chat id:', window.currentChatId);
            
            // Force reload chat list and messages
            console.log('[WS] Reloading everything...');
            
            // Try to reload API chats
            if (typeof loadChatsFromApi === 'function') {
                var sessionToUse = data.sessionId || self.currentSessionId || window.currentSession;
                console.log('[WS] Calling loadChatsFromApi with session:', sessionToUse);
                loadChatsFromApi(sessionToUse);
            }
            
            // If there's a current chat, reload it
            if (window.currentChatId) {
                var currentPhone = window.currentChatId.replace('@lid', '').replace('@s.whatsapp.net', '').replace('@g.us', '');
                console.log('[WS] Current phone:', currentPhone);
                
                if (fromPhone === currentPhone) {
                    console.log('[WS] Phone matches! Reloading messages...');
                    if (typeof loadMessagesFromApi === 'function') {
                        loadMessagesFromApi(data.sessionId, window.currentChatId);
                    }
                    if (typeof loadAndMergeMessages === 'function') {
                        loadAndMergeMessages(data.sessionId, window.currentChatId, fromPhone);
                    }
                }
            }
            
            // Also trigger custom event
            var event = new CustomEvent('chatery.message', { detail: data });
            document.dispatchEvent(event);
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
        
        // Message Events
        this.socket.on('message', function(data) {
            console.log('[WS] New message received:', data);
            self.handleIncomingMessage(data);
        });
        
        this.socket.on('message.sent', function(data) {
            console.log('[WS] Message sent:', data);
        });
        
        this.socket.on('message.update', function(data) {
            console.log('[WS] Message updated:', data);
        });
        
        // Connection Events
        this.socket.on('connection.update', function(data) {
            console.log('[WS] Connection update:', data);
            self.handleConnectionUpdate(data);
        });
        
        this.socket.on('qr.update', function(data) {
            console.log('[WS] QR update:', data);
            if (data.data && data.data.qr) {
                self.showQrModal(data.sessionId, data.data.qr);
            }
        });
        
        this.socket.on('session.ready', function(data) {
            console.log('[WS] Session ready:', data);
            self.showNotification('Session ' + data.sessionId + ' is ready', 'success');
        });
        
        this.socket.on('session.disconnected', function(data) {
            console.log('[WS] Session disconnected:', data);
            self.showNotification('Session ' + data.sessionId + ' disconnected', 'warning');
        });
        
        // Presence Events
        this.socket.on('presence.update', function(data) {
            console.log('[WS] Presence update:', data);
        });
        
        // Chat Update Events - contains conversation list
        this.socket.on('chat.update', function(data) {
            console.log('[WS] Chat update:', data);
            self.handleChatUpdate(data);
        });
        
        // Contact Update Events
        this.socket.on('contact.update', function(data) {
            console.log('[WS] Contact update:', data);
        });
        
        // Group Events
        this.socket.on('group.update', function(data) {
            console.log('[WS] Group update:', data);
        });
        
        this.socket.on('group.participants.update', function(data) {
            console.log('[WS] Group participants update:', data);
        });
    },
    
    // Handle incoming message
    handleIncomingMessage: function(data) {
        // Data structure from Chatery WebSocket test page:
        // { sessionId: 'bastomi', timestamp: '...', message: { id, chatId, fromMe, sender, senderPhone, senderName, timestamp, type, content, ... } }
        
        console.log('[WS] handleIncomingMessage called with:', data);
        
        var msgData = data.message;
        if (!msgData) {
            console.log('[WS] No message data found in message property');
            // Try other formats
            msgData = data.data?.message || data;
            console.log('[WS] Tried alternative format:', msgData);
        }
        
        if (!msgData) {
            console.log('[WS] No message data found at all');
            return;
        }
        
        // Extract sender info
        var fromPhone = msgData.senderPhone || msgData.sender || '';
        fromPhone = fromPhone.replace('@lid', '').replace('@s.whatsapp.net', '').replace('@g.us', '');
        var pushName = msgData.senderName || msgData.pushName || fromPhone;
        var content = msgData.content || msgData.message?.conversation || '';
        
        console.log('[WS] Message from:', pushName, '(' + fromPhone + '):', content);
        
        // Show notification
        this.showNotification('New message from ' + pushName + ': ' + (content.substring(0, 30) || '[Media]'), 'message');
        
        // Play notification sound
        this.playNotificationSound();
        
        // Reload conversation list
        this.reloadConversationList();
        
        // Check if this message is from the currently viewed conversation
        var currentPhone = '';
        if (typeof window.currentChatId !== 'undefined' && window.currentChatId) {
            currentPhone = window.currentChatId.replace('@lid', '').replace('@s.whatsapp.net', '').replace('@g.us', '');
        }
        
        console.log('[WS] Current viewing phone:', currentPhone);
        
        // If viewing this conversation, reload messages
        if (currentPhone && fromPhone === currentPhone) {
            console.log('[WS] Reloading messages for:', fromPhone);
            if (typeof loadMessagesFromApi === 'function') {
                loadMessagesFromApi(data.sessionId, window.currentChatId);
            }
            if (typeof loadAndMergeMessages === 'function') {
                loadAndMergeMessages(data.sessionId, window.currentChatId, fromPhone);
            }
        }
        
        // Trigger custom event for other scripts
        var event = new CustomEvent('chatery.message', { detail: data });
        document.dispatchEvent(event);
    },
    
    // Handle chat update (conversation list)
    handleChatUpdate: function(data) {
        console.log('[WS] Chat update received:', data);
        // Reload conversation list when chat is updated
        this.reloadConversationList();
    },
    
    // Reload conversation list
    reloadConversationList: function() {
        var sessionId = this.currentSessionId || (typeof currentSession !== 'undefined' ? currentSession : '');
        
        // If using API chat, reload the chat list
        if (sessionId && typeof loadChatsFromApi === 'function') {
            loadChatsFromApi(sessionId);
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
