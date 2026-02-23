@extends('layouts.app')

@section('title', 'Campaign Stats - ' . ($campaign->name ?? 'Campaign'))

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('crm.campaigns.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left text-gray-600"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $campaign->name ?? 'Unknown' }}</h1>
                <p class="text-gray-500 mt-1">Campaign Statistics</p>
            </div>
        </div>
        <div>
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
            <span class="px-3 py-1 text-sm font-medium rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                {{ ucfirst($campaign->status ?? 'unknown') }}
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Recipients</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($campaign->total_recipients ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Sent</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($campaign->sent_count ?? 0) }}</p>
                    <p class="text-xs text-gray-400">{{ $campaign->progress_percentage ?? 0 }}%</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-paper-plane text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Delivered</p>
                    <p class="text-2xl font-bold text-teal-600">{{ number_format($campaign->delivered_count ?? 0) }}</p>
                    <p class="text-xs text-gray-400">{{ $campaign->delivery_rate ?? 0 }}%</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center">
                    <i class="fas fa-check-circle text-teal-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Failed</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($campaign->failed_count ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Engagement Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Read Rate</p>
                <span class="text-lg font-bold text-indigo-600">{{ $campaign->read_rate ?? 0 }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ min($campaign->read_rate ?? 0, 100) }}%"></div>
            </div>
            <p class="text-sm text-gray-600 mt-2">{{ number_format($campaign->read_count ?? 0) }} messages read</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Reply Rate</p>
                <span class="text-lg font-bold text-purple-600">{{ $campaign->reply_rate ?? 0 }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-purple-500 h-2 rounded-full" style="width: {{ min($campaign->reply_rate ?? 0, 100) }}%"></div>
            </div>
            <p class="text-sm text-gray-600 mt-2">{{ number_format($campaign->replied_count ?? 0) }} replies</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Pending</p>
                <span class="text-lg font-bold text-yellow-600">
                    {{ $campaign->total_recipients > 0 ? round((($campaign->total_recipients - ($campaign->sent_count ?? 0)) / $campaign->total_recipients) * 100, 1) : 0 }}%
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $campaign->total_recipients > 0 ? min((($campaign->total_recipients - ($campaign->sent_count ?? 0)) / $campaign->total_recipients) * 100, 100) : 0 }}%"></div>
            </div>
            <p class="text-sm text-gray-600 mt-2">{{ number_format(($campaign->total_recipients ?? 0) - ($campaign->sent_count ?? 0)) }} pending</p>
        </div>
    </div>

    <!-- Campaign Details -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-800">Campaign Details</h2>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Type</p>
                    <p class="text-sm font-medium text-gray-800 capitalize">{{ $campaign->type ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Created By</p>
                    <p class="text-sm font-medium text-gray-800">{{ $campaign->creator->name ?? 'Unknown' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Created At</p>
                    <p class="text-sm font-medium text-gray-800">{{ $campaign->created_at ? $campaign->created_at->format('M d, Y') : 'N/A' }}</p>
                </div>
                @if($campaign->started_at)
                <div>
                    <p class="text-xs text-gray-500">Started</p>
                    <p class="text-sm font-medium text-gray-800">{{ $campaign->started_at->format('M d, Y H:i') }}</p>
                </div>
                @endif
                @if($campaign->completed_at)
                <div>
                    <p class="text-xs text-gray-500">Completed</p>
                    <p class="text-sm font-medium text-gray-800">{{ $campaign->completed_at->format('M d, Y H:i') }}</p>
                </div>
                @endif
                @if($campaign->scheduled_at)
                <div>
                    <p class="text-xs text-gray-500">Scheduled</p>
                    <p class="text-sm font-medium text-gray-800">{{ $campaign->scheduled_at->format('M d, Y H:i') }}</p>
                </div>
                @endif
                @if($campaign->template)
                <div>
                    <p class="text-xs text-gray-500">Template</p>
                    <p class="text-sm font-medium text-gray-800">{{ $campaign->template->name }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Progress -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex justify-between text-sm mb-2">
            <span class="text-gray-600">Campaign Progress</span>
            <span class="font-medium">{{ $campaign->progress_percentage ?? 0 }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="bg-whatsapp-light h-3 rounded-full" style="width: {{ min($campaign->progress_percentage ?? 0, 100) }}%"></div>
        </div>
        <p class="text-sm text-gray-500 mt-2">{{ number_format($campaign->sent_count ?? 0) }} of {{ number_format($campaign->total_recipients ?? 0) }} messages sent</p>
    </div>

    <!-- Actions -->
    <div class="flex justify-between">
        <a href="{{ route('crm.campaigns.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Campaigns
        </a>
        <div class="flex space-x-2">
            @if($campaign->status === 'running')
            <button onclick="pauseCampaign({{ $campaign->id }})" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors">
                <i class="fas fa-pause mr-2"></i> Pause
            </button>
            @elseif($campaign->status === 'paused')
            <button onclick="resumeCampaign({{ $campaign->id }})" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                <i class="fas fa-play mr-2"></i> Resume
            </button>
            @elseif(in_array($campaign->status, ['draft', 'paused']))
            <button onclick="startCampaign({{ $campaign->id }})" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                <i class="fas fa-play mr-2"></i> Start
            </button>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
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
            alert('Campaign started');
            location.reload();
        } else {
            alert(result.message || 'Failed to start campaign');
        }
    } catch (error) {
        alert('Error: ' + error.message);
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
            alert('Campaign paused');
            location.reload();
        } else {
            alert(result.message || 'Failed to pause campaign');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

async function resumeCampaign(id) {
    if (!confirm('Are you sure you want to resume this campaign?')) return;

    try {
        const response = await fetch(`/crm/campaigns/${id}/start`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const result = await response.json();

        if (result.success) {
            alert('Campaign resumed');
            location.reload();
        } else {
            alert(result.message || 'Failed to resume campaign');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}
</script>
@endpush
@endsection
