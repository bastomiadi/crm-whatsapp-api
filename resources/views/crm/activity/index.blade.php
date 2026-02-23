@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Activity Log</h1>
            <p class="text-gray-500 mt-1">Track all CRM activities and interactions</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(!Auth::user()->canViewAllData())
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full">
                    <i class="fas fa-filter mr-1"></i> Showing My Activities Only
                </span>
            @endif
            <select id="filterType" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                <option value="">All Types</option>
                <option value="contact">Contacts</option>
                <option value="interaction">Interactions</option>
                <option value="order">Orders</option>
                <option value="ticket">Tickets</option>
                <option value="campaign">Campaigns</option>
            </select>
            <button onclick="exportLog()" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                <i class="fas fa-download mr-2"></i> Export
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $contactQuery = \App\Models\Contact::query();
            $interactionQuery = \App\Models\Interaction::query();
            $orderQuery = \App\Models\Order::query();
            if (!Auth::user()->canViewAllData()) {
                $userId = Auth::id();
                $contactQuery->where('created_by', $userId);
                $interactionQuery->where('user_id', $userId);
                $orderQuery->where('created_by', $userId);
            }
            $contactCount = $contactQuery->count();
            $interactionCount = $interactionQuery->count();
            $orderCount = $orderQuery->count();
            $todayActivities = $interactionQuery->whereDate('created_at', today())->count();
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Contacts</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($contactCount) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Interactions</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($interactionCount) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-comments text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Orders</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($orderCount) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-purple-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Today</p>
                    <p class="text-2xl font-bold text-orange-600">{{ number_format($todayActivities) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                    <i class="fas fa-calendar-day text-orange-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Recent Activities</h2>
        </div>
        <div class="p-6">
            <div class="space-y-6" id="activityTimeline">
                @forelse($activities as $activity)
                <div class="flex space-x-4 relative" data-type="{{ $activity['type'] }}">
                    <!-- Timeline Line -->
                    <div class="absolute left-5 top-12 bottom-0 w-0.5 bg-gray-200"></div>
                    
                    <!-- Icon -->
                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 z-10
                        @if($activity['type'] === 'contact') bg-blue-100 text-blue-600
                        @elseif($activity['type'] === 'interaction') bg-green-100 text-green-600
                        @elseif($activity['type'] === 'order') bg-purple-100 text-purple-600
                        @else bg-gray-100 text-gray-600 @endif">
                        @if($activity['type'] === 'contact')
                        <i class="fas fa-user"></i>
                        @elseif($activity['type'] === 'interaction')
                        <i class="fas fa-comment"></i>
                        @elseif($activity['type'] === 'order')
                        <i class="fas fa-shopping-bag"></i>
                        @else
                        <i class="fas fa-circle"></i>
                        @endif
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 pb-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="font-medium text-gray-800">{{ $activity['title'] }}</h4>
                                <p class="text-sm text-gray-600 mt-1">{{ $activity['description'] }}</p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($activity['type'] === 'contact') bg-blue-100 text-blue-700
                                    @elseif($activity['type'] === 'interaction') bg-green-100 text-green-700
                                    @elseif($activity['type'] === 'order') bg-purple-100 text-purple-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ ucfirst($activity['type']) }}
                                </span>
                                <p class="text-xs text-gray-500 mt-1">{{ $activity['created_at']->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex items-center mt-2 text-xs text-gray-500">
                            <i class="fas fa-user-circle mr-1"></i>
                            {{ $activity['user'] }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-history text-5xl mb-4 text-gray-300"></i>
                    <p class="text-lg font-medium">No activities yet</p>
                    <p class="text-sm text-gray-400 mt-1">Activities will appear here as they happen</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('filterType')?.addEventListener('change', function() {
    const filter = this.value;
    document.querySelectorAll('#activityTimeline > div').forEach(item => {
        if (!filter || item.dataset.type === filter) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
});

function exportLog() {
    window.location.href = '{{ route("crm.contacts.export") }}';
}
</script>
@endpush
@endsection
