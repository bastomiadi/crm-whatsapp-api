@extends('layouts.app')

@section('title', 'Sessions')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Sessions</h1>
            <p class="text-gray-500 mt-1">Manage your WhatsApp sessions</p>
        </div>
        <button onclick="openCreateModal()" class="flex items-center space-x-2 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>New Session</span>
        </button>
    </div>
    
    <!-- Sessions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @if(isset($sessions['data']) && count($sessions['data']) > 0)
            @foreach($sessions['data'] as $session)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-whatsapp-light to-whatsapp-dark rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-lg">{{ strtoupper(substr($session['sessionId'], 0, 1)) }}</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">{{ $session['sessionId'] }}</h3>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                    @if($session['status'] === 'connected') bg-green-100 text-green-800
                                    @elseif($session['status'] === 'connecting') bg-yellow-100 text-yellow-800
                                    @elseif($session['status'] === 'qr_ready') bg-blue-100 text-blue-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ $session['status'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center text-gray-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <span>{{ $session['phoneNumber'] ?? 'Not connected' }}</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>{{ $session['name'] ?? '-' }}</span>
                        </div>
                    </div>
                    
                    @if(isset($session['metadata']) && !empty($session['metadata']))
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs text-gray-500 mb-2">Metadata:</p>
                        <div class="bg-gray-50 rounded p-2 text-xs font-mono text-gray-600 overflow-x-auto">
                            {{ json_encode($session['metadata'], JSON_PRETTY_PRINT) }}
                        </div>
                    </div>
                    @endif
                </div>
                
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <div class="flex space-x-2">
                        @if($session['status'] === 'qr_ready')
                        <button onclick="showQrCode('{{ $session['sessionId'] }}')" class="px-3 py-1.5 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 transition-colors">
                            Show QR
                        </button>
                        @elseif($session['status'] === 'disconnected')
                        <button onclick="connectSession('{{ $session['sessionId'] }}')" class="px-3 py-1.5 bg-whatsapp-light text-white text-sm rounded-lg hover:bg-whatsapp-dark transition-colors">
                            Connect
                        </button>
                        @endif
                        
                        @if($session['status'] === 'connected')
                        <button onclick="showQrCode('{{ $session['sessionId'] }}')" class="px-3 py-1.5 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300 transition-colors">
                            QR Code
                        </button>
                        @endif
                    </div>
                    <button onclick="deleteSession('{{ $session['sessionId'] }}')" class="px-3 py-1.5 text-red-600 hover:bg-red-50 text-sm rounded-lg transition-colors">
                        Delete
                    </button>
                </div>
            </div>
            @endforeach
        @else
        <div class="col-span-full bg-white rounded-xl shadow-sm p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">No Sessions Found</h3>
            <p class="text-gray-500 mb-4">Create your first WhatsApp session to get started.</p>
            <button onclick="openCreateModal()" class="inline-flex items-center space-x-2 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Create Session</span>
            </button>
        </div>
        @endif
    </div>
</div>

<!-- Create Session Modal -->
<div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800">Create New Session</h2>
                <button onclick="closeCreateModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <form id="createForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Session ID</label>
                <input type="text" name="sessionId" id="newSessionId" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                    placeholder="e.g., mysession, business1">
                <p class="text-xs text-gray-500 mt-1">Unique identifier for this session</p>
            </div>
            
            <!-- <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Metadata (Optional)</label>
                <textarea name="metadata" rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent font-mono text-sm"
                    placeholder='{"userId": "123", "plan": "premium"}'></textarea>
            </div> -->
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Webhook URL (Optional)</label>
                <input type="url" name="webhookUrl" id="webhookUrl"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                    placeholder="https://your-server.com/webhook"
                    value="{{ $chateryWebhook ?? '' }}">
            </div>
            
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeCreateModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark">
                    Create Session
                </button>
            </div>
        </form>
    </div>
</div>

<!-- QR Code Modal -->
<div id="qrModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-sm w-full">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800">QR Code</h2>
                <button onclick="closeQrModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="p-6 text-center">
            <p id="qrSessionId" class="text-sm text-gray-500 mb-4">Session: <span class="font-medium"></span></p>
            <div id="qrCodeContainer" class="flex items-center justify-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-whatsapp-light"></div>
            </div>
            <p class="text-xs text-gray-400 mt-4">Scan with WhatsApp on your phone</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Create Modal
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
    }
    
    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
        document.getElementById('createForm').reset();
    }
    
    // QR Modal
    function openQrModal() {
        document.getElementById('qrModal').classList.remove('hidden');
    }
    
    function closeQrModal() {
        document.getElementById('qrModal').classList.add('hidden');
    }
    
    // Create Session
    document.getElementById('createForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const sessionId = document.getElementById('newSessionId').value;
        const webhookUrlInput = this.querySelector('[name="webhookUrl"]');
        const webhookUrl = webhookUrlInput.value.trim();
        const defaultWebhookUrl = '{{ $chateryWebhook ?? '' }}';
        const currentUserId = {{ $currentUserId ?? 'null' }};
        
        let metadata = {};
        let webhooks = [];
        
        // Auto-add created_by to metadata
        metadata['created_by'] = currentUserId;
        
        // Use webhook URL from input, or fall back to default from .env
        const finalWebhookUrl = webhookUrl || defaultWebhookUrl;
        
        if (finalWebhookUrl) {
            webhooks.push({ url: finalWebhookUrl, events: [] });
        }
        
        try {
            const response = await fetch('{{ route("api.sessions.connect", ["sessionId" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', sessionId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    sessionId: sessionId,
                    metadata: metadata,
                    webhooks: webhooks
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Session created successfully', 'success');
                closeCreateModal();
                // Auto-refresh page to show new session, then user can click QR code
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.message || 'Failed to create session', 'error');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    });
    
    // Connect Session
    async function connectSession(sessionId) {
        try {
            const response = await fetch('{{ route("api.sessions.connect", ["sessionId" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', sessionId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sessionId: sessionId })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Session connecting...', 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showToast(result.message || 'Failed to connect session', 'error');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    }
    
// QR Code auto-refresh interval
let qrRefreshInterval = null;

// Show QR Code with auto-refresh
async function showQrCode(sessionId) {
openQrModal();
document.getElementById('qrSessionId').querySelector('span').textContent = sessionId;

// Function to fetch QR code
async function fetchQrCode() {
document.getElementById('qrCodeContainer').innerHTML = '<div class="animate-spin rounded-full h-12 w-12 border-b-2 border-whatsapp-light"></div';

try {
const response = await fetch('{{ route("api.sessions.qr") }}', {
method: 'POST',
headers: {
'Content-Type': 'application/json',
'X-CSRF-TOKEN': '{{ csrf_token() }}'
},
body: JSON.stringify({ sessionId: sessionId })
});

const result = await response.json();

if (result.success && result.data && result.data.qrCode) {
document.getElementById('qrCodeContainer').innerHTML = `<img src="${result.data.qrCode}" alt="QR Code" class="w-48 h-48">`;
// Check if connected
if (result.data.connected) {
document.getElementById('qrCodeContainer').innerHTML = '<p class="text-green-500">Session connected!</p';
if (qrRefreshInterval) {
clearInterval(qrRefreshInterval);
qrRefreshInterval = null;
}
setTimeout(() => location.reload(), 2000);
}
} else if (result.data && result.data.connected) {
document.getElementById('qrCodeContainer').innerHTML = '<p class="text-green-500">Session connected!</p';
if (qrRefreshInterval) {
clearInterval(qrRefreshInterval);
qrRefreshInterval = null;
}
setTimeout(() => location.reload(), 2000);
} else {
document.getElementById('qrCodeContainer').innerHTML = '<p class="text-red-500">QR Code not available. Session may already be connected.</p';
}
} catch (error) {
document.getElementById('qrCodeContainer').innerHTML = '<p class="text-red-500">Error loading QR code</p';
}
}

// Initial fetch
await fetchQrCode();

// Auto-refresh every 30 seconds
qrRefreshInterval = setInterval(fetchQrCode, 30000);
}

// Close QR modal and stop auto-refresh
function closeQrModal() {
if (qrRefreshInterval) {
clearInterval(qrRefreshInterval);
qrRefreshInterval = null;
}
document.getElementById('qrModal').classList.add('hidden');
}    
    // Delete Session
    async function deleteSession(sessionId) {
        if (!confirm(`Are you sure you want to delete session "${sessionId}"?`)) {
            return;
        }
        
        try {
            const response = await fetch('{{ route("api.sessions.delete") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sessionId: sessionId })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Session deleted successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.message || 'Failed to delete session', 'error');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    }
</script>
@endpush
@endsection
