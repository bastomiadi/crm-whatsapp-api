@extends('layouts.app')

@section('title', 'Campaigns')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Campaigns</h1>
            <p class="text-gray-500 mt-1">Manage your marketing campaigns and broadcasts</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(!Auth::user()->canViewAllData())
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full">
                    <i class="fas fa-filter mr-1"></i> Showing My Campaigns Only
                </span>
            @endif
            <button onclick="openModal('createModal')" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                <i class="fas fa-plus mr-2"></i> New Campaign
            </button>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $campaignQuery = \App\Models\Campaign::query();
            if (!Auth::user()->canViewAllData()) {
                $campaignQuery->where('created_by', Auth::id());
            }
            $totalCampaigns = (clone $campaignQuery)->count();
            $runningCampaigns = (clone $campaignQuery)->where('status', 'running')->count();
            $scheduledCampaigns = (clone $campaignQuery)->where('status', 'scheduled')->count();
            $completedCampaigns = (clone $campaignQuery)->where('status', 'completed')->count();
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Campaigns</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($totalCampaigns) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-bullhorn text-gray-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Running</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($runningCampaigns) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-play text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Scheduled</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($scheduledCampaigns) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-clock text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Completed</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($completedCampaigns) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-check-circle text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search campaigns..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            <div class="w-48">
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="running" {{ request('status') === 'running' ? 'selected' : '' }}>Running</option>
                    <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="w-40">
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="">All Types</option>
                    <option value="broadcast" {{ request('type') === 'broadcast' ? 'selected' : '' }}>Broadcast</option>
                    <option value="sequence" {{ request('type') === 'sequence' ? 'selected' : '' }}>Sequence</option>
                    <option value="trigger" {{ request('type') === 'trigger' ? 'selected' : '' }}>Trigger-based</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors">
                <i class="fas fa-filter mr-2"></i> Filter
            </button>
        </form>
    </div>
    
    <!-- Campaigns Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-800">All Campaigns</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campaign</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stats</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($campaigns as $campaign)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-800">{{ $campaign->name }}</p>
                                <p class="text-sm text-gray-500 truncate max-w-[200px]">{{ $campaign->description ?? 'No description' }}</p>
                                @if($campaign->scheduled_at)
                                <p class="text-xs text-gray-400 mt-1">
                                    <i class="fas fa-calendar mr-1"></i>{{ $campaign->scheduled_at->format('M d, Y H:i') }}
                                </p>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                {{ ucfirst($campaign->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'draft' => 'gray',
                                    'scheduled' => 'blue',
                                    'running' => 'green',
                                    'paused' => 'yellow',
                                    'completed' => 'purple',
                                    'cancelled' => 'red',
                                ];
                                $color = $statusColors[$campaign->status] ?? 'gray';
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                                {{ ucfirst($campaign->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="w-36">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>{{ number_format($campaign->sent_count) }}/{{ number_format($campaign->total_recipients) }}</span>
                                    <span>{{ $campaign->progress_percentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-whatsapp-light h-2 rounded-full transition-all" style="width: {{ min($campaign->progress_percentage, 100) }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600 space-y-1">
                                <p class="flex items-center">
                                    <i class="fas fa-check text-green-500 w-4 mr-1"></i>
                                    Delivered: {{ number_format($campaign->delivered_count) }} ({{ $campaign->delivery_rate }}%)
                                </p>
                                <p class="flex items-center">
                                    <i class="fas fa-eye text-blue-500 w-4 mr-1"></i>
                                    Read: {{ number_format($campaign->read_count) }} ({{ $campaign->read_rate }}%)
                                </p>
                                <p class="flex items-center">
                                    <i class="fas fa-reply text-purple-500 w-4 mr-1"></i>
                                    Replied: {{ number_format($campaign->replied_count) }} ({{ $campaign->reply_rate }}%)
                                </p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                @if($campaign->status === 'draft')
                                <button onclick="startCampaign({{ $campaign->id }})" class="text-green-600 hover:text-green-400 text-sm" title="Start">
                                    <i class="fas fa-play"></i>
                                </button>
                                @elseif($campaign->status === 'running')
                                <button onclick="pauseCampaign({{ $campaign->id }})" class="text-yellow-600 hover:text-yellow-400 text-sm" title="Pause">
                                    <i class="fas fa-pause"></i>
                                </button>
                                @elseif($campaign->status === 'paused')
                                <button onclick="startCampaign({{ $campaign->id }})" class="text-green-600 hover:text-green-400 text-sm" title="Resume">
                                    <i class="fas fa-play"></i>
                                </button>
                                @endif
                                <button onclick="viewStats({{ $campaign->id }})" class="text-blue-600 hover:text-blue-400 text-sm" title="View Stats">
                                    <i class="fas fa-chart-bar"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                </svg>
                                <p>No campaigns found</p>
                                <button onclick="openModal('createModal')" class="mt-2 text-whatsapp-dark hover:underline">Create your first campaign</button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($campaigns->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $campaigns->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Create Campaign Modal -->
<div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800">New Campaign</h2>
                <button onclick="closeModal('createModal')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <form id="createForm" class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Campaign Name *</label>
                    <input type="text" name="name" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                    <select name="type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                        <option value="broadcast">Broadcast</option>
                        <option value="sequence">Sequence</option>
                        <option value="trigger">Trigger-based</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Message Template</label>
                <select name="template_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="">Select Template</option>
                    @foreach($templates as $template)
                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Target Segments</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($segments as $segment)
                    <label class="inline-flex items-center px-3 py-2 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer">
                        <input type="checkbox" name="target_segments[]" value="{{ $segment->id }}" class="rounded border-gray-300 text-whatsapp-light focus:ring-whatsapp-light">
                        <span class="ml-2 text-sm">{{ $segment->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Schedule (Leave empty for draft)</label>
                <input type="datetime-local" name="scheduled_at"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeModal('createModal')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                    Create Campaign
                </button>
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

document.getElementById('createForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data.target_segments = formData.getAll('target_segments[]');
    
    try {
        const response = await fetch('{{ route("crm.campaigns.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Campaign created successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message || 'Failed to create campaign', 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
});

async function startCampaign(id) {
    if (!confirm('Are you sure you want to start this campaign?')) return;
    
    try {
        const response = await fetch(`/crm/campaigns/${id}/start`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Campaign started', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message || 'Failed to start campaign', 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
}

async function pauseCampaign(id) {
    try {
        const response = await fetch(`/crm/campaigns/${id}/pause`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Campaign paused', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
}

function viewStats(id) {
    window.location.href = '/crm/campaigns/' + id + '/stats';
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} z-50`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>
@endpush
