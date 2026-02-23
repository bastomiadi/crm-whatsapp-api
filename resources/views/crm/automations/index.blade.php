@extends('layouts.app')

@section('title', 'Automations')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Automations</h1>
            <p class="text-gray-500 mt-1">Create automated workflows and triggers</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(!Auth::user()->canViewAllData())
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full">
                    <i class="fas fa-filter mr-1"></i> Showing My Automations Only
                </span>
            @endif
            <button onclick="openModal('createAutomationModal')" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                <i class="fas fa-plus mr-2"></i> New Automation
            </button>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $automationsCollection = $automations instanceof \Illuminate\Pagination\LengthAwarePaginator ? $automations->items() : $automations;
            $automationsArray = is_array($automationsCollection) ? $automationsCollection : (is_iterable($automationsCollection) ? iterator_to_array($automationsCollection) : []);
            $active = collect($automationsArray)->where('is_active', true)->count();
            $inactive = collect($automationsArray)->where('is_active', false)->count();
            $totalExecutions = collect($automationsArray)->sum('execution_count');
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active</p>
                    <p class="text-2xl font-bold text-green-600">{{ $active }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-play text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Inactive</p>
                    <p class="text-2xl font-bold text-gray-600">{{ $inactive }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-pause text-gray-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Executions</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $totalExecutions }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-bolt text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Trigger Types</p>
                    <p class="text-2xl font-bold text-purple-600">{{ collect($automations)->unique('trigger_type')->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-code-branch text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Automations List -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-800">All Automations</h2>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($automations as $automation)
            <div class="p-4 hover:bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $automation->is_active ? 'bg-green-100' : 'bg-gray-100' }}">
                            <i class="fas fa-robot {{ $automation->is_active ? 'text-green-600' : 'text-gray-400' }}"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-800">{{ $automation->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $automation->description }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ str_replace('_', ' ', ucwords($automation->trigger_type)) }}
                            </span>
                            <p class="text-xs text-gray-500 mt-1">{{ $automation->execution_count }} executions</p>
                        </div>
                        <form action="{{ route('crm.automations.toggle', $automation->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $automation->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $automation->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Actions Preview -->
                <div class="mt-4 ml-14">
                    <div class="flex flex-wrap gap-2">
                        @foreach($automation->actions ?? [] as $action)
                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">
                            <i class="fas fa-arrow-right mr-1"></i>
                            {{ $action['type'] ?? 'action' }}
                        </span>
                        @endforeach
                    </div>
                </div>
                
                <!-- Last Execution -->
                @if($automation->last_executed_at)
                <div class="mt-2 ml-14 text-xs text-gray-400">
                    Last executed: {{ $automation->last_executed_at->diffForHumans() }}
                </div>
                @endif
            </div>
            @empty
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-robot text-4xl mb-4 text-gray-300"></i>
                <p>No automations found. Create your first automation!</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Create Automation Modal -->
<div id="createAutomationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Create New Automation</h3>
        </div>
        <form action="{{ route('crm.automations.store') }}" method="POST">
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trigger Type</label>
                    <select name="trigger_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="contact_created">Contact Created</option>
                        <option value="contact_tagged">Contact Tagged</option>
                        <option value="order_created">Order Created</option>
                        <option value="order_status_changed">Order Status Changed</option>
                        <option value="ticket_created">Ticket Created</option>
                        <option value="ticket_status_changed">Ticket Status Changed</option>
                        <option value="message_received">Message Received</option>
                        <option value="keyword_detected">Keyword Detected</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="webhook">Webhook</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trigger Config (JSON)</label>
                    <textarea name="trigger_config" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg font-mono text-sm" placeholder='{"keywords": ["hello", "hi"]}'></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Actions (JSON)</label>
                    <textarea name="actions" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg font-mono text-sm" placeholder='[{"type": "send_message", "config": {"message": "Hello!"}}]'></textarea>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" checked class="h-4 w-4 text-whatsapp-light focus:ring-whatsapp-light border-gray-300 rounded">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
                </div>
            </div>
            <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('createAutomationModal')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark">Create Automation</button>
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
    
    // Handle create automation form submission
    document.querySelector('#createAutomationModal form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('{{ route("crm.automations.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });
            
            let result;
            try {
                result = await response.json();
            } catch (e) {
                const text = await response.text();
                alert('Server error: ' + text.substring(0, 200));
                return;
            }
            
            if (result.success) {
                alert('Automation created successfully!');
                closeModal('createAutomationModal');
                location.reload();
            } else {
                alert(result.message || 'Failed to create automation');
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    });
    
    // Handle toggle automation
    document.querySelectorAll('form[action*="toggle"]').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message || 'Failed to toggle automation');
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    });
</script>
@endpush
