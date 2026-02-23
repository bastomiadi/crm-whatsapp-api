@extends('layouts.app')

@section('title', 'WebSocket')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-800">WebSocket</h1>
        <p class="text-gray-500 mt-1">Monitor WebSocket connections and events</p>
    </div>
    
    <!-- WebSocket Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Connections</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">
                        {{ $wsStats['data']['totalConnections'] ?? 0 }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active Rooms</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">
                        {{ isset($wsStats['data']['rooms']) ? count($wsStats['data']['rooms']) : 0 }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    <p class="text-xl font-bold mt-1 text-green-600">Active</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Rooms List -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Active Rooms</h2>
            <button onclick="refreshStats()" class="text-sm text-whatsapp-dark hover:underline">Refresh</button>
        </div>
        <div id="roomsList" class="p-4">
            @if(isset($wsStats['data']['rooms']) && !empty($wsStats['data']['rooms']))
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($wsStats['data']['rooms'] as $room => $count)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-sm text-gray-700">{{ $room }}</span>
                            <span class="bg-whatsapp-light/20 text-whatsapp-dark text-xs px-2 py-1 rounded-full">{{ $count }} connections</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-4">No active rooms</p>
            @endif
        </div>
    </div>
    
    <!-- WebSocket Connection Info -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-800 mb-4">How to Connect</h2>
        <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
            <pre class="text-sm text-green-400"><code>// JavaScript WebSocket Connection Example
const ws = new WebSocket('ws://localhost:3000');

ws.onopen = () => {
    console.log('Connected to WebSocket');
    
    // Join a session room to receive events
    ws.send(JSON.stringify({
        type: 'subscribe',
        sessionId: 'your-session-id'
    }));
};

ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    console.log('Event:', data.event, data);
    
    // Handle different event types
    switch(data.event) {
        case 'qr':
            // Display QR code: data.qrCode (base64 image)
            break;
        case 'connection.update':
            // Connection status changed
            // data.status: 'connected' | 'disconnected' | 'connecting'
            break;
        case 'message':
            // New message received
            break;
        case 'message.update':
            // Message status updated (sent, delivered, read)
            break;
    }
};

ws.onerror = (error) => {
    console.error('WebSocket error:', error);
};

ws.onclose = () => {
    console.log('WebSocket disconnected');
};</code></pre>
        </div>
    </div>
    
    <!-- Event Types -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-800 mb-4">Available Events</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-medium text-gray-800 mb-2">qr</h3>
                <p class="text-sm text-gray-600 mb-2">Emitted when QR code is ready for scanning</p>
                <div class="bg-gray-50 rounded p-2 text-xs font-mono">
                    { "sessionId": "mysession", "qr": "...", "qrCode": "data:image/png;base64,..." }
                </div>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-medium text-gray-800 mb-2">connection.update</h3>
                <p class="text-sm text-gray-600 mb-2">Connection status changes</p>
                <div class="bg-gray-50 rounded p-2 text-xs font-mono">
                    { "sessionId": "mysession", "status": "connected", "phoneNumber": "628..." }
                </div>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-medium text-gray-800 mb-2">message</h3>
                <p class="text-sm text-gray-600 mb-2">New incoming message</p>
                <div class="bg-gray-50 rounded p-2 text-xs font-mono">
                    { "sessionId": "mysession", "message": { "id": "...", "chatId": "...", ... } }
                </div>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-medium text-gray-800 mb-2">message.update</h3>
                <p class="text-sm text-gray-600 mb-2">Message status updates</p>
                <div class="bg-gray-50 rounded p-2 text-xs font-mono">
                    { "sessionId": "mysession", "messageId": "...", "status": "delivered" }
                </div>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-medium text-gray-800 mb-2">presence.update</h3>
                <p class="text-sm text-gray-600 mb-2">Typing/presence indicators</p>
                <div class="bg-gray-50 rounded p-2 text-xs font-mono">
                    { "sessionId": "mysession", "chatId": "...", "presence": "composing" }
                </div>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-medium text-gray-800 mb-2">group.participants</h3>
                <p class="text-sm text-gray-600 mb-2">Group participant changes</p>
                <div class="bg-gray-50 rounded p-2 text-xs font-mono">
                    { "sessionId": "mysession", "groupId": "...", "action": "add", "participants": [...] }
                </div>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-medium text-gray-800 mb-2">call</h3>
                <p class="text-sm text-gray-600 mb-2">Incoming call events</p>
                <div class="bg-gray-50 rounded p-2 text-xs font-mono">
                    { "sessionId": "mysession", "call": { "id": "...", "from": "...", "isVideo": false } }
                </div>
            </div>
        </div>
    </div>
    
    <!-- Live Event Monitor -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Live Event Monitor</h2>
            <div class="flex items-center space-x-2">
                <span id="connectionStatus" class="flex items-center text-sm">
                    <span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span>
                    Disconnected
                </span>
                <button onclick="toggleMonitor()" id="monitorBtn" class="px-4 py-1.5 bg-whatsapp-light text-white text-sm rounded-lg hover:bg-whatsapp-dark">
                    Start Monitor
                </button>
            </div>
        </div>
        <div id="eventLog" class="p-4 h-64 overflow-y-auto bg-gray-900 font-mono text-sm">
            <p class="text-gray-500">Click "Start Monitor" to begin receiving events...</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let monitorWs = null;
    let isMonitoring = false;
    
    async function refreshStats() {
        try {
            const response = await fetch('{{ route("api.websocket.stats") }}');
            const result = await response.json();
            
            if (result.success && result.data) {
                // Update stats display
                const rooms = result.data.rooms || {};
                let html = '';
                
                if (Object.keys(rooms).length > 0) {
                    html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
                    for (const [room, count] of Object.entries(rooms)) {
                        html += `
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-sm text-gray-700">${room}</span>
                                    <span class="bg-whatsapp-light/20 text-whatsapp-dark text-xs px-2 py-1 rounded-full">${count} connections</span>
                                </div>
                            </div>
                        `;
                    }
                    html += '</div>';
                } else {
                    html = '<p class="text-center text-gray-500 py-4">No active rooms</p>';
                }
                
                document.getElementById('roomsList').innerHTML = html;
                showToast('Stats refreshed', 'success');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    }
    
    function toggleMonitor() {
        if (isMonitoring) {
            stopMonitor();
        } else {
            startMonitor();
        }
    }
    
    function startMonitor() {
        const wsUrl = '{{ config("chatery.api_url") }}'.replace('http', 'ws');
        
        try {
            monitorWs = new WebSocket(wsUrl);
            
            monitorWs.onopen = () => {
                isMonitoring = true;
                updateConnectionStatus(true);
                logEvent('system', 'Connected to WebSocket server');
                
                // Subscribe to all events
                monitorWs.send(JSON.stringify({ type: 'subscribe', sessionId: '*' }));
            };
            
            monitorWs.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    logEvent(data.event || 'unknown', data);
                } catch (e) {
                    logEvent('raw', event.data);
                }
            };
            
            monitorWs.onerror = (error) => {
                logEvent('error', 'WebSocket error');
            };
            
            monitorWs.onclose = () => {
                isMonitoring = false;
                updateConnectionStatus(false);
                logEvent('system', 'Disconnected from WebSocket server');
            };
        } catch (error) {
            showToast('Error connecting to WebSocket: ' + error.message, 'error');
        }
    }
    
    function stopMonitor() {
        if (monitorWs) {
            monitorWs.close();
            monitorWs = null;
        }
        isMonitoring = false;
        updateConnectionStatus(false);
    }
    
    function updateConnectionStatus(connected) {
        const status = document.getElementById('connectionStatus');
        const btn = document.getElementById('monitorBtn');
        
        if (connected) {
            status.innerHTML = '<span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>Connected';
            btn.textContent = 'Stop Monitor';
            btn.classList.remove('bg-whatsapp-light', 'hover:bg-whatsapp-dark');
            btn.classList.add('bg-red-500', 'hover:bg-red-600');
        } else {
            status.innerHTML = '<span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span>Disconnected';
            btn.textContent = 'Start Monitor';
            btn.classList.remove('bg-red-500', 'hover:bg-red-600');
            btn.classList.add('bg-whatsapp-light', 'hover:bg-whatsapp-dark');
        }
    }
    
    function logEvent(type, data) {
        const log = document.getElementById('eventLog');
        const time = new Date().toLocaleTimeString();
        const colors = {
            'system': 'text-yellow-400',
            'qr': 'text-blue-400',
            'connection.update': 'text-green-400',
            'message': 'text-purple-400',
            'message.update': 'text-cyan-400',
            'presence.update': 'text-orange-400',
            'error': 'text-red-400',
            'default': 'text-gray-300'
        };
        
        const color = colors[type] || colors['default'];
        const content = typeof data === 'object' ? JSON.stringify(data, null, 2) : data;
        
        const entry = document.createElement('div');
        entry.className = 'mb-2';
        entry.innerHTML = `<span class="text-gray-500">[${time}]</span> <span class="${color}">[${type}]</span>\n<pre class="text-gray-300 text-xs ml-4 overflow-x-auto">${escapeHtml(content)}</pre>`;
        
        log.appendChild(entry);
        log.scrollTop = log.scrollHeight;
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
@endpush
@endsection
