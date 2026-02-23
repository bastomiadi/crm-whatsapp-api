@extends('layouts.app')

@section('title', 'Bulk Messaging')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Bulk Messaging</h1>
        <p class="text-gray-500 mt-1">Send messages to multiple recipients (max 100 per request)</p>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Message Types -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-800">Message Type</h2>
            </div>
            <div class="p-4 space-y-2">
                <button onclick="selectBulkType('text')" class="bulk-type-btn w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors bg-whatsapp-light/10 border-l-4 border-whatsapp-light" data-type="text">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span class="font-medium">Text</span>
                </button>
                
                <button onclick="selectBulkType('image')" class="bulk-type-btn w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors" data-type="image">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="font-medium">Image</span>
                </button>
                
                <button onclick="selectBulkType('document')" class="bulk-type-btn w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors" data-type="document">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="font-medium">Document</span>
                </button>
            </div>
        </div>
        
        <!-- Bulk Message Form -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-800">Send Bulk Message</h2>
            </div>
            <form id="bulkForm" class="p-6 space-y-4">
                <!-- Session Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Session *</label>
                    <select name="sessionId" id="bulkSessionId" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
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
                
                <!-- Recipients -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recipients (one per line, max 100) *</label>
                    <textarea name="recipients" id="bulkRecipients" rows="5" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent font-mono text-sm"
                        placeholder="628123456789&#10;628987654321&#10;628111222333"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Phone numbers without + symbol, one per line</p>
                </div>
                
                <!-- Text Fields -->
                <div id="bulkTextFields" class="bulk-fields space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                        <textarea name="message" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                            placeholder="Type your message here..."></textarea>
                    </div>
                </div>
                
                <!-- Image Fields -->
                <div id="bulkImageFields" class="bulk-fields space-y-4 hidden">
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
                <div id="bulkDocumentFields" class="bulk-fields space-y-4 hidden">
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
                
                <!-- Options -->
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Delay Between Messages (ms)</label>
                        <input type="number" name="delayBetweenMessages" value="1000" min="500"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Typing Time (ms)</label>
                        <input type="number" name="typingTime" value="0" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="flex justify-end pt-4">
                    <button type="submit" class="flex items-center space-x-2 px-6 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span>Start Bulk Send</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Job Status Checker -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-800 mb-4">Check Job Status</h2>
        <div class="flex items-end space-x-4 mb-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Job ID</label>
                <input type="text" id="jobIdInput" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent" placeholder="bulk_1704326400000_abc123def">
            </div>
            <button onclick="checkJobStatus()" class="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors">
                Check Status
            </button>
        </div>
        <div id="jobStatusResult" class="hidden">
            <div class="bg-gray-50 rounded-lg p-4">
                <pre id="jobStatusContent" class="text-sm font-mono text-gray-700 overflow-x-auto"></pre>
            </div>
        </div>
    </div>
    
    <!-- Recent Jobs -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Recent Bulk Jobs</h2>
            <button onclick="loadRecentJobs()" class="text-sm text-whatsapp-dark hover:underline">Refresh</button>
        </div>
        <div class="p-4">
            <div class="flex items-end space-x-4 mb-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Session</label>
                    <select id="recentJobsSession" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                        <option value="">Select a session</option>
                        @if(isset($sessions['data']))
                            @foreach($sessions['data'] as $session)
                            <option value="{{ $session['sessionId'] }}">{{ $session['sessionId'] }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <button onclick="loadRecentJobs()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Load Jobs
                </button>
            </div>
            <div id="recentJobsContainer">
                <p class="text-gray-500 text-center py-4">Select a session to view recent jobs</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentBulkType = 'text';
    let currentJobId = null;
    
    function selectBulkType(type) {
        currentBulkType = type;
        
        document.querySelectorAll('.bulk-type-btn').forEach(btn => {
            btn.classList.remove('bg-whatsapp-light/10', 'border-l-4', 'border-whatsapp-light');
            if (btn.dataset.type === type) {
                btn.classList.add('bg-whatsapp-light/10', 'border-l-4', 'border-whatsapp-light');
            }
        });
        
        document.querySelectorAll('.bulk-fields').forEach(field => {
            field.classList.add('hidden');
        });
        document.getElementById('bulk' + type.charAt(0).toUpperCase() + type.slice(1) + 'Fields').classList.remove('hidden');
    }
    
    document.getElementById('bulkForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const sessionId = formData.get('sessionId');
        const recipientsText = formData.get('recipients');
        const recipients = recipientsText.split('\n').map(r => r.trim()).filter(r => r);
        const delayBetweenMessages = parseInt(formData.get('delayBetweenMessages')) || 1000;
        const typingTime = parseInt(formData.get('typingTime')) || 0;
        
        if (recipients.length > 100) {
            showToast('Maximum 100 recipients allowed', 'error');
            return;
        }
        
        let endpoint = '';
        let data = { sessionId, recipients, delayBetweenMessages, typingTime };
        
        switch (currentBulkType) {
            case 'text':
                endpoint = '{{ route("api.bulk.text") }}';
                data.message = formData.get('message');
                break;
            case 'image':
                endpoint = '{{ route("api.bulk.image") }}';
                data.imageUrl = formData.get('imageUrl');
                data.caption = formData.get('caption');
                break;
            case 'document':
                endpoint = '{{ route("api.bulk.document") }}';
                data.documentUrl = formData.get('documentUrl');
                data.filename = formData.get('filename');
                data.caption = formData.get('docCaption');
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
                currentJobId = result.data?.jobId;
                showToast('Bulk job started! Job ID: ' + currentJobId, 'success');
                document.getElementById('jobIdInput').value = currentJobId;
                
                // Auto check status
                setTimeout(() => checkJobStatus(), 2000);
            } else {
                showToast(result.message || 'Failed to start bulk job', 'error');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    });
    
    async function checkJobStatus() {
        const jobId = document.getElementById('jobIdInput').value;
        
        if (!jobId) {
            showToast('Please enter a Job ID', 'error');
            return;
        }
        
        try {
            const response = await fetch('{{ route("api.bulk.status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ jobId })
            });
            
            const result = await response.json();
            
            document.getElementById('jobStatusResult').classList.remove('hidden');
            document.getElementById('jobStatusContent').textContent = JSON.stringify(result, null, 2);
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    }
    
    async function loadRecentJobs() {
        const sessionId = document.getElementById('recentJobsSession').value;
        
        if (!sessionId) {
            showToast('Please select a session', 'error');
            return;
        }
        
        try {
            const response = await fetch('{{ route("api.bulk.jobs") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sessionId })
            });
            
            const result = await response.json();
            
            if (result.success && result.data) {
                let html = '<div class="space-y-3">';
                
                if (result.data.length === 0) {
                    html += '<p class="text-gray-500 text-center py-4">No bulk jobs found for this session</p>';
                } else {
                    result.data.forEach(job => {
                        const progress = job.progress || 0;
                        const statusColor = job.status === 'completed' ? 'green' : job.status === 'processing' ? 'blue' : 'gray';
                        
                        html += `
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-mono text-sm text-gray-600">${job.jobId}</span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-${statusColor}-100 text-${statusColor}-800">${job.status}</span>
                                </div>
                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                    <span>Type: ${job.type}</span>
                                    <span>Total: ${job.total}</span>
                                    <span>Sent: ${job.sent}</span>
                                    <span>Failed: ${job.failed}</span>
                                </div>
                                <div class="mt-2">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-whatsapp-light h-2 rounded-full" style="width: ${progress}%"></div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
                
                html += '</div>';
                document.getElementById('recentJobsContainer').innerHTML = html;
            } else {
                document.getElementById('recentJobsContainer').innerHTML = '<p class="text-red-500 text-center py-4">Failed to load jobs</p>';
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    }
</script>
@endpush
@endsection
