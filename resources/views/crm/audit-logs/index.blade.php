@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
@php
use App\Models\AuditLog;
@endphp
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Audit Logs</h1>
            <p class="text-gray-500 mt-1">Track all system activities and changes</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="exportLogs()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                <i class="fas fa-download mr-2"></i>Export
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form action="{{ route('crm.audit-logs.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                <select name="action" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Actions</option>
                    @foreach(App\Models\AuditLog::getActions() as $action => $label)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Entity Type</label>
                <select name="entity_type" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Types</option>
                    <option value="contact" {{ request('entity_type') == 'contact' ? 'selected' : '' }}>Contacts</option>
                    <option value="order" {{ request('entity_type') == 'order' ? 'selected' : '' }}>Orders</option>
                    <option value="deal" {{ request('entity_type') == 'deal' ? 'selected' : '' }}>Deals</option>
                    <option value="ticket" {{ request('entity_type') == 'ticket' ? 'selected' : '' }}>Tickets</option>
                    <option value="user" {{ request('entity_type') == 'user' ? 'selected' : '' }}>Users</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Filter Badge -->
    @if(!$canViewAll)
    <div class="flex items-center px-3 py-2 bg-yellow-100 text-yellow-800 rounded-lg text-sm">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        Showing My Activity Only
    </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-sm text-gray-500">Total Logs</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-sm text-gray-500">Today</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['today'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-sm text-gray-500">This Week</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['week'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-sm text-gray-500">This Month</p>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['month'] }}</p>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $log->created_at->format('d M Y H:i:s') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($log->user)
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-xs text-indigo-600">
                                    {{ strtoupper(substr($log->user->name, 0, 2)) }}
                                </div>
                                <span class="ml-2 text-sm">{{ $log->user->name }}</span>
                            </div>
                            @else
                            <span class="text-sm text-gray-400">System</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full" style="background-color: {{ $log->action_color }}20; color: {{ $log->action_color }}">
                                {{ $log->action_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($log->entity_type)
                            <span class="font-medium">{{ ucfirst($log->entity_type) }}</span>
                            <span class="text-gray-400">#{{ $log->entity_id }}</span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                            @if($log->description)
                            {{ $log->description }}
                            @elseif($log->new_values)
                            @php
                            $changes = array_keys($log->new_values ?? []);
                            @endphp
                            Changed: {{ implode(', ', $changes) }}
                            @else
                            -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $log->ip_address ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No audit logs found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', '1');
    window.location.href = '{{ route("crm.audit-logs.index") }}?' + params.toString();
}
</script>
@endsection
