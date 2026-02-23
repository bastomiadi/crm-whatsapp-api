@extends('layouts.app')

@section('title', 'Chatbots')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Chatbots</h1>
            <p class="text-gray-500 mt-1">Manage chatbot flows and automated responses</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(!Auth::user()->canViewAllData())
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full">
                    <i class="fas fa-filter mr-1"></i> Showing My Chatbots Only
                </span>
            @endif
            <button onclick="openModal('createChatbotModal')" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                <i class="fas fa-plus mr-2"></i> New Chatbot
            </button>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $chatbotsCollection = $chatbots instanceof \Illuminate\Pagination\LengthAwarePaginator ? $chatbots->items() : $chatbots;
            $chatbotsArray = is_array($chatbotsCollection) ? $chatbotsCollection : (is_iterable($chatbotsCollection) ? iterator_to_array($chatbotsCollection) : []);
            $active = collect($chatbotsArray)->where('status', 'active')->count();
            $draft = collect($chatbotsArray)->where('status', 'draft')->count();
            $totalSessions = \App\Models\ChatbotSession::count();
            $activeSessions = \App\Models\ChatbotSession::active()->count();
            $handoverRate = $totalSessions > 0 ? round(($activeSessions / $totalSessions) * 100, 1) : 0;
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active</p>
                    <p class="text-2xl font-bold text-green-600">{{ $active }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-robot text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Draft</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $draft }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                    <i class="fas fa-edit text-yellow-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Sessions</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $totalSessions }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-comments text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Handover Rate</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $handoverRate }}%</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-handshake text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Chatbots List -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($chatbots as $chatbot)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-whatsapp-light/20 flex items-center justify-center">
                            <i class="fas fa-robot text-whatsapp-dark"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">{{ $chatbot->name }}</h3>
                            <p class="text-xs text-gray-500">{{ count($chatbot->keywords ?? []) }} trigger keywords</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full {{ [
                        'active' => 'bg-green-100 text-green-800',
                        'draft' => 'bg-yellow-100 text-yellow-800',
                        'inactive' => 'bg-gray-100 text-gray-800',
                    ][$chatbot->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($chatbot->status) }}
                    </span>
                </div>
            </div>
            
            <div class="p-4">
                <p class="text-sm text-gray-600 mb-3">{{ $chatbot->description }}</p>
                
                <!-- Keywords -->
                <div class="flex flex-wrap gap-1 mb-3">
                    @foreach(($chatbot->keywords ?? []) as $keyword)
                    <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded">{{ $keyword }}</span>
                    @endforeach
                </div>
                
                <!-- Flow Preview -->
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 mb-2">Flow Steps: {{ count($chatbot->flows ?? []) }}</p>
                    <div class="flex items-center space-x-2 text-xs text-gray-600">
                        <span class="px-2 py-1 bg-white rounded border">Start</span>
                        <i class="fas fa-arrow-right"></i>
                        <span class="px-2 py-1 bg-white rounded border">...</span>
                        <i class="fas fa-arrow-right"></i>
                        <span class="px-2 py-1 bg-white rounded border">End</span>
                    </div>
                </div>
            </div>
            
            <div class="p-4 border-t border-gray-100 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    @if($chatbot->handover_enabled)
                    <span class="text-xs text-gray-500"><i class="fas fa-handshake mr-1"></i>Handover enabled</span>
                    @endif
                </div>
                <div class="flex space-x-2">
                    <button onclick="editChatbot('{{ $chatbot->id }}')" class="text-blue-600 hover:text-blue-900 text-sm">Edit</button>
                    @if($chatbot->status === 'draft' || $chatbot->status === 'inactive')
                    <form action="{{ route('crm.chatbots.activate', $chatbot->id) }}" method="POST" class="inline chatbot-action-form">
                        @csrf
                        <button type="submit" class="text-green-600 hover:text-green-900 text-sm">Activate</button>
                    </form>
                    @elseif($chatbot->status === 'active')
                    <form action="{{ route('crm.chatbots.deactivate', $chatbot->id) }}" method="POST" class="inline chatbot-action-form">
                        @csrf
                        <button type="submit" class="text-yellow-600 hover:text-yellow-900 text-sm">Deactivate</button>
                    </form>
                    @endif
                    <button onclick="viewSessions('{{ $chatbot->id }}')" class="text-purple-600 hover:text-purple-900 text-sm">Sessions</button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-gray-500">
            <i class="fas fa-robot text-4xl mb-4 text-gray-300"></i>
            <p>No chatbots found. Create your first chatbot!</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Create Chatbot Modal -->
<div id="createChatbotModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Create New Chatbot</h3>
        </div>
        <form action="{{ route('crm.chatbots.store') }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trigger Keywords (comma separated)</label>
                    <input type="text" name="keywords_string" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="menu, help, start">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Flow Definition (JSON)</label>
                    <textarea name="flows" rows="10" class="w-full px-4 py-2 border border-gray-300 rounded-lg font-mono text-sm" placeholder='[{"id": "start", "type": "message", "content": "Hello!", "next": "end"}, {"id": "end", "type": "end"}]'></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Response</label>
                    <textarea name="default_response" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Sorry, I didn't understand that."></textarea>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="handover_enabled" id="handover_enabled" checked class="h-4 w-4 text-whatsapp-light focus:ring-whatsapp-light border-gray-300 rounded">
                    <label for="handover_enabled" class="ml-2 text-sm text-gray-700">Enable handover to human agent</label>
                </div>
            </div>
            <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('createChatbotModal')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark">Create Chatbot</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.getElementById(id).classList.add('flex');
    }
    
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }
    
    function editChatbot(id) {
        window.location.href = '/crm/chatbots/' + id + '/edit';
    }
    
    function viewSessions(id) {
        window.location.href = '/crm/chatbots/' + id + '/sessions';
    }
    
    // Handle Activate/Deactivate forms
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.chatbot-action-form').forEach(function(form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const action = form.querySelector('button').textContent.trim();
                const confirmMsg = action === 'Activate' ? 'Activate this chatbot?' : 'Deactivate this chatbot?';
                
                if (!confirm(confirmMsg)) return;
                
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showToast('Chatbot ' + (action === 'Activate' ? 'activated' : 'deactivated') + ' successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(result.message || 'Failed to update chatbot', 'error');
                    }
                } catch (error) {
                    showToast('Error: ' + error.message, 'error');
                }
            });
        });
    });
    
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} z-50`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
</script>
@endpush
