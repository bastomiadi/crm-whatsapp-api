@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
            <p class="text-gray-500 mt-1">Monitor your WhatsApp API status and sessions</p>
        </div>
        <button onclick="refreshData()" class="flex items-center space-x-2 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span>Refresh</span>
        </button>
    </div>
    
    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- API Status -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">API Status</p>
                    <p class="text-xl font-bold mt-1 {{ isset($health['success']) && $health['success'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ isset($health['success']) && $health['success'] ? 'Online' : 'Offline' }}
                    </p>
                </div>
                <div class="w-12 h-12 {{ isset($health['success']) && $health['success'] ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 {{ isset($health['success']) && $health['success'] ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            @if(isset($health['timestamp']))
            <p class="text-xs text-gray-400 mt-2">Last check: {{ $health['timestamp'] }}</p>
            @endif
        </div>
        
        <!-- Active Sessions -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active Sessions</p>
                    <p class="text-xl font-bold mt-1 text-gray-800">
                        {{ isset($sessions['data']) ? count(array_filter($sessions['data'], fn($s) => $s['status'] === 'connected')) : 0 }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Total: {{ isset($sessions['data']) ? count($sessions['data']) : 0 }} sessions</p>
        </div>
        
        <!-- WebSocket Connections -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">WebSocket Connections</p>
                    <p class="text-xl font-bold mt-1 text-gray-800">
                        {{ $wsStats['data']['totalConnections'] ?? 0 }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Real-time connections</p>
        </div>
        
        <!-- Messages Today -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Messages</p>
                    <p class="text-xl font-bold mt-1 text-gray-800">-</p>
                </div>
                <div class="w-12 h-12 bg-whatsapp-light/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-whatsapp-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Track via webhooks</p>
        </div>
    </div>
    
    <!-- Sessions Table -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Sessions</h2>
                <a href="{{ route('dashboard.sessions') }}" class="text-sm text-whatsapp-dark hover:underline">View All</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @if(isset($sessions['data']) && count($sessions['data']) > 0)
                        @foreach($sessions['data'] as $session)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">{{ $session['sessionId'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($session['status'] === 'connected') bg-green-100 text-green-800
                                    @elseif($session['status'] === 'connecting') bg-yellow-100 text-yellow-800
                                    @elseif($session['status'] === 'qr_ready') bg-blue-100 text-blue-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ $session['status'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $session['phoneNumber'] ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $session['name'] ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('dashboard.sessions') }}" class="text-whatsapp-dark hover:underline">Manage</a>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No sessions found. <a href="{{ route('dashboard.sessions') }}" class="text-whatsapp-dark hover:underline">Create one</a>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('dashboard.messaging') }}" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-whatsapp-light/20 rounded-lg flex items-center justify-center group-hover:bg-whatsapp-light/30 transition-colors">
                    <svg class="w-6 h-6 text-whatsapp-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Send Message</h3>
                    <p class="text-sm text-gray-500">Text, image, document, etc.</p>
                </div>
            </div>
        </a>
        
        <a href="{{ route('dashboard.bulk-messaging') }}" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Bulk Messaging</h3>
                    <p class="text-sm text-gray-500">Send to multiple recipients</p>
                </div>
            </div>
        </a>
        
        <a href="{{ route('dashboard.groups') }}" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Group Management</h3>
                    <p class="text-sm text-gray-500">Create and manage groups</p>
                </div>
            </div>
        </a>
    </div>
    
    <!-- API Info -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">API Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">API URL</p>
                <p class="font-mono text-sm text-gray-800">{{ config('chatery.api_url') }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">API Key</p>
                <p class="font-mono text-sm text-gray-800">{{ config('chatery.api_key') ? '••••••••' . substr(config('chatery.api_key'), -4) : 'Not configured' }}</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function refreshData() {
        location.reload();
    }
    
    // Auto refresh every 30 seconds
    // setInterval(refreshData, 30000);
</script>
@endpush
@endsection
