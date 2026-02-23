@extends('layouts.app')

@section('title', 'Analytics')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Analytics</h1>
            <p class="text-gray-500 mt-1">View performance metrics and statistics</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(!Auth::user()->canViewAllData())
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full">
                    <i class="fas fa-filter mr-1"></i> Showing My Data Only
                </span>
            @endif
            <select id="dateRange" class="px-4 py-2 border border-gray-300 rounded-lg" onchange="updateCharts()">
                <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 days</option>
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 days</option>
                <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 days</option>
            </select>
            <button onclick="exportReport()" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900">
                <i class="fas fa-download mr-2"></i> Export
            </button>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Messages Sent</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($stats['messages_sent']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-paper-plane text-green-600"></i>
                </div>
            </div>
            <p class="text-xs text-green-600 mt-2"><i class="fas fa-arrow-up mr-1"></i>Last {{ $days }} days</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Messages Received</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['messages_received']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-inbox text-blue-600"></i>
                </div>
            </div>
            <p class="text-xs text-blue-600 mt-2"><i class="fas fa-arrow-up mr-1"></i>Last {{ $days }} days</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Tickets Created</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ number_format($ticketsCreated) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                    <i class="fas fa-ticket-alt text-yellow-600"></i>
                </div>
            </div>
            <p class="text-xs text-gray-600 mt-2">{{ $ticketsResolved }} resolved</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Tickets Resolved</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($ticketsResolved) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-check-circle text-purple-600"></i>
                </div>
            </div>
            <p class="text-xs text-green-600 mt-2"><i class="fas fa-arrow-up mr-1"></i>Last {{ $days }} days</p>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Message Volume Chart -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Message Volume</h3>
            <div class="h-64 flex items-end justify-between space-x-2">
                @php
                    $maxVal = max(
                        $messageVolume->max('sent') ?: 1,
                        $messageVolume->max('received') ?: 1
                    );
                @endphp
                @foreach($messageVolume as $data)
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-full flex space-x-1">
                        <div class="flex-1 bg-green-400 rounded-t" style="height: {{ ($data['sent'] / $maxVal) * 200 }}px"></div>
                        <div class="flex-1 bg-blue-400 rounded-t" style="height: {{ ($data['received'] / $maxVal) * 200 }}px"></div>
                    </div>
                    <span class="text-xs text-gray-500 mt-2">{{ $data['day'] }}</span>
                </div>
                @endforeach
            </div>
            <div class="flex justify-center space-x-6 mt-4">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-400 rounded mr-2"></div>
                    <span class="text-sm text-gray-600">Sent</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-400 rounded mr-2"></div>
                    <span class="text-sm text-gray-600">Received</span>
                </div>
            </div>
        </div>
        
        <!-- Response Time Chart -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Average Response Time</h3>
            <div class="h-64 flex items-end justify-between space-x-2">
                @php
                    $maxTime = $responseTimeData->max('minutes') ?: 1;
                    $avgResponseTime = $responseTimeData->avg('minutes');
                @endphp
                @foreach($responseTimeData as $data)
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-full bg-purple-400 rounded-t" style="height: {{ ($data['minutes'] / $maxTime) * 200 }}px"></div>
                    <span class="text-xs text-gray-500 mt-2">{{ $data['day'] }}</span>
                </div>
                @endforeach
            </div>
            <p class="text-center text-sm text-gray-600 mt-4">Average: <span class="font-bold">{{ round($avgResponseTime) }} minutes</span></p>
        </div>
    </div>
    
    <!-- Performance Metrics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Delivery Rate -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Delivery Rate</h3>
            <div class="relative w-32 h-32 mx-auto">
                <svg class="w-full h-full" viewBox="0 0 36 36">
                    <path class="text-gray-200" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    @php $deliveryRate = $totalSent > 0 ? round(($delivered / $totalSent) * 100) : 0; @endphp
                    <path class="text-green-500" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="{{ $deliveryRate }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-2xl font-bold text-gray-800">{{ $deliveryRate }}%</span>
                </div>
            </div>
            <p class="text-center text-sm text-gray-600 mt-4">{{ number_format($delivered) }} of {{ number_format($totalSent) }} messages</p>
        </div>
        
        <!-- Read Rate -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Read Rate</h3>
            <div class="relative w-32 h-32 mx-auto">
                <svg class="w-full h-full" viewBox="0 0 36 36">
                    <path class="text-gray-200" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    @php $readRate = $delivered > 0 ? round(($read / $delivered) * 100) : 0; @endphp
                    <path class="text-blue-500" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="{{ $readRate }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-2xl font-bold text-gray-800">{{ $readRate }}%</span>
                </div>
            </div>
            <p class="text-center text-sm text-gray-600 mt-4">{{ number_format($read) }} of {{ number_format($delivered) }} delivered</p>
        </div>
        
        <!-- Response Rate -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Response Rate</h3>
            <div class="relative w-32 h-32 mx-auto">
                <svg class="w-full h-full" viewBox="0 0 36 36">
                    <path class="text-gray-200" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    @php $responseRate = $read > 0 ? round(($replied / $read) * 100) : 0; @endphp
                    <path class="text-purple-500" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="{{ $responseRate }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-2xl font-bold text-gray-800">{{ $responseRate }}%</span>
                </div>
            </div>
            <p class="text-center text-sm text-gray-600 mt-4">{{ number_format($replied) }} of {{ number_format($read) }} read</p>
        </div>
    </div>
    
    <!-- Top Performers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Agents -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Top Agents</h3>
            <div class="space-y-4">
                @forelse($topAgents as $agent)
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                            <span class="font-medium text-gray-600">{{ substr($agent['name'], 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $agent['name'] }}</p>
                            <p class="text-sm text-gray-500">{{ $agent['tickets'] }} tickets resolved</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                        <span class="font-medium">{{ $agent['rating'] }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                    <p>No agent data available</p>
                </div>
                @endforelse
            </div>
        </div>
        
        <!-- Top Campaigns -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Top Campaigns</h3>
            <div class="space-y-4">
                @forelse($topCampaigns as $campaign)
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-800">{{ $campaign['name'] }}</p>
                        <p class="text-sm text-gray-500">{{ number_format($campaign['sent']) }} sent</p>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 text-sm bg-green-100 text-green-800 rounded-full">{{ $campaign['rate'] }}% response</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-bullhorn text-4xl mb-4 text-gray-300"></i>
                    <p>No campaign data available</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateCharts() {
        const range = document.getElementById('dateRange').value;
        window.location.href = '{{ route('crm.analytics') }}?range=' + range;
    }
    
    function exportReport() {
        const range = document.getElementById('dateRange').value;
        window.location.href = '{{ route('crm.analytics') }}?range=' + range + '&export=1';
    }
</script>
@endpush
