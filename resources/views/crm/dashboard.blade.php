@extends('layouts.app')

@section('title', 'CRM Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">CRM Dashboard</h1>
            <p class="text-gray-500 mt-1">Customer Relationship Management Overview</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('crm.contacts.index') }}" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                + New Contact
            </a>
            <a href="{{ route('crm.campaigns.index') }}" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors">
                + New Campaign
            </a>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Contacts</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($stats['total_contacts']) }}</p>
                    <p class="text-sm text-green-600 mt-1">{{ $stats['active_contacts'] }} active</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Orders</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($stats['total_orders']) }}</p>
                    <p class="text-sm text-yellow-600 mt-1">{{ $stats['pending_orders'] }} pending</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Open Tickets</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['open_tickets'] }}</p>
                    <p class="text-sm text-red-600 mt-1">Needs attention</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active Campaigns</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['active_campaigns'] }}</p>
                    <p class="text-sm text-purple-600 mt-1">{{ $stats['active_automations'] }} automations</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <a href="{{ route('crm.contacts.index') }}" class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow text-center">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-700">Contacts</p>
        </a>
        
        <a href="{{ route('crm.orders.index') }}" class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow text-center">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-700">Orders</p>
        </a>
        
        <a href="{{ route('crm.tickets.index') }}" class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow text-center">
            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-700">Tickets</p>
        </a>
        
        <a href="{{ route('crm.campaigns.index') }}" class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow text-center">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-700">Campaigns</p>
        </a>
        
        <a href="{{ route('crm.automations.index') }}" class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow text-center">
            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-700">Automations</p>
        </a>
        
        <a href="{{ route('crm.chatbots.index') }}" class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow text-center">
            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-700">Chatbots</p>
        </a>
    </div>
    
    <!-- Recent Items -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Contacts -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">Recent Contacts</h2>
                <a href="{{ route('crm.contacts.index') }}" class="text-sm text-whatsapp-dark hover:underline">View All</a>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($recentContacts as $contact)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                            <span class="text-gray-600 font-medium">{{ $contact->initials }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $contact->display_name }}</p>
                            <p class="text-sm text-gray-500">{{ $contact->phone }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">Recent Orders</h2>
                <a href="{{ route('crm.orders.index') }}" class="text-sm text-whatsapp-dark hover:underline">View All</a>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($recentOrders as $order)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-800">{{ $order->order_number }}</p>
                            <p class="text-sm text-gray-500">{{ $order->contact->display_name ?? 'Unknown' }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $order->status_color }}-100 text-{{ $order->status_color }}-800">
                            {{ $order->status_label }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Recent Tickets -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">Recent Tickets</h2>
                <a href="{{ route('crm.tickets.index') }}" class="text-sm text-whatsapp-dark hover:underline">View All</a>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($recentTickets as $ticket)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-800">{{ $ticket->subject }}</p>
                            <p class="text-sm text-gray-500">{{ $ticket->contact->display_name ?? 'Unknown' }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $ticket->status_color }}-100 text-{{ $ticket->status_color }}-800">
                            {{ $ticket->status }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
