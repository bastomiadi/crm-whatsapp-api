<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Chatery WhatsApp Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        whatsapp: {
                            light: '#25D366',
                            dark: '#128C7E',
                            teal: '#075E54',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar-link.active {
            background-color: #f0fdf4;
            color: #16a34a;
            border-left: 3px solid #16a34a;
        }
        .sidebar-link:hover {
            background-color: #f0fdf4;
        }
        .status-connected { color: #22c55e; }
        .status-disconnected { color: #ef4444; }
        .status-connecting { color: #f59e0b; }
        .status-qr_ready { color: #3b82f6; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg flex-shrink-0 hidden md:flex flex-col h-screen">
            <div class="p-4 border-b border-gray-200 flex-shrink-0">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-whatsapp-light rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-800">CRM</h1>
                        <p class="text-xs text-gray-500">WhatsApp API Panel</p>
                    </div>
                </div>
            </div>
            
            <nav class="flex-1 overflow-y-auto p-4 space-y-1">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-4">WhatsApp</div>
                <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('dashboard') || request()->routeIs('dashboard.index')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                
                <a href="{{ route('dashboard.sessions') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('dashboard.sessions')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>Sessions</span>
                </a>
                
                <a href="{{ route('dashboard.messaging') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('dashboard.messaging')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span>Messaging</span>
                </a>
                
                <a href="{{ route('dashboard.bulk-messaging') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('dashboard.bulk-messaging')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Bulk Messaging</span>
                </a>
                
                <a href="{{ route('dashboard.chat-history') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('dashboard.chat-history')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span>Chat History</span>
                </a>
                
                <a href="{{ route('dashboard.groups') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('dashboard.groups')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Groups</span>
                </a>
                
<a href="{{ route('dashboard.websocket') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('dashboard.websocket')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>WebSocket</span>
                </a>

                <a href="{{ route('dashboard.api-docs') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('dashboard.api-docs')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>RESTful API</span>
                </a>
                
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-2 px-4">CRM</div>
                
                <a href="{{ route('crm.dashboard') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.dashboard')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>CRM Dashboard</span>
                </a>
                
                <a href="{{ route('crm.contacts.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.contacts.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>Contacts</span>
                </a>
                
                <a href="{{ route('crm.orders.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.orders.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span>Orders</span>
                </a>
                
                <a href="{{ route('crm.tickets.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.tickets.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    <span>Tickets</span>
                </a>
                
                <a href="{{ route('crm.campaigns.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.campaigns.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    <span>Campaigns</span>
                </a>
                
                <a href="{{ route('crm.automations.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.automations.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>Automations</span>
                </a>
                
                <a href="{{ route('crm.chatbots.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.chatbots.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>Chatbots</span>
                </a>
                
                <a href="{{ route('crm.templates.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.templates.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    <span>Templates</span>
                </a>
                
                <a href="{{ route('crm.analytics') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.analytics')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Analytics</span>
                </a>
                
                <a href="{{ route('crm.segments.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.segments.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Segments</span>
                </a>
                
                <a href="{{ route('crm.products.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.products.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span>Products</span>
                </a>

                <a href="{{ route('crm.categories.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.categories.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span>Categories</span>
                </a>
                
                <a href="{{ route('crm.quick-replies.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.quick-replies.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <span>Quick Replies</span>
                </a>

                <a href="{{ route('crm.tags.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.tags.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span>Tags</span>
                </a>

                <a href="{{ route('crm.activity.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.activity.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Activity Log</span>
                </a>

                <a href="{{ route('crm.tasks.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.tasks.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span>Tasks</span>
                </a>

                <!-- NEW FEATURES MENU -->
                <a href="{{ route('crm.deals.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.deals.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Deals Pipeline</span>
                </a>

                <a href="{{ route('crm.surveys.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.surveys.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Surveys</span>
                </a>

                <a href="{{ route('crm.audit-logs.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.audit-logs.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Audit Logs</span>
                </a>

                <a href="{{ route('crm.duplicates.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.duplicates.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span>Duplicates</span>
                </a>

                <a href="{{ route('crm.users.index') }}" @if(!Auth::user()->canViewAllData()) style="display:none" @endif class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.users.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>Users</span>
                </a>

                <a href="{{ route('crm.roles.index') }}" @if(!Auth::user()->canViewAllData()) style="display:none" @endif class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.roles.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Roles</span>
                </a>

                <a href="{{ route('crm.permissions.index') }}" @if(!Auth::user()->canViewAllData()) style="display:none" @endif class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.permissions.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    <span>Permissions</span>
                </a>

                <a href="{{ route('crm.chat.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 transition-colors @if(request()->routeIs('crm.chat.*')) active @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <span>Chat</span>
                </a>
            </nav>
            
            <div class="flex-shrink-0 p-4 border-t border-gray-200 bg-white">
                @auth
                    <div id="userSidebar" class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center overflow-hidden">
                                @if(Auth::user()->avatar && file_exists(public_path('storage/' . Auth::user()->avatar)))
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ Auth::user()->roles->first()->name ?? 'User' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('profile') }}" class="p-2 text-gray-500 hover:text-gray-700" title="Profile">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="p-2 text-gray-500 hover:text-red-600" title="Logout">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="flex items-center justify-between">
                        <a href="{{ route('login') }}" class="flex items-center space-x-3 text-gray-700 hover:text-primary-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            <span class="text-sm font-medium">Login</span>
                        </a>
                        <a href="{{ route('register') }}" class="px-3 py-1 text-sm bg-primary-600 text-white rounded hover:bg-primary-700">
                            Register
                        </a>
                    </div>
                @endauth
            </div>
        </aside>
        
        <!-- Mobile Header -->
        <div class="md:hidden fixed top-0 left-0 right-0 bg-white shadow z-50">
            <div class="flex items-center justify-between p-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-whatsapp-light rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </div>
                    <h1 class="text-lg font-bold text-gray-800">Chatery</h1>
                </div>
                <button id="mobile-menu-btn" class="p-2 rounded-lg hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
            <div class="bg-white w-80 h-full overflow-y-auto">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="font-bold text-gray-800">Menu</h2>
                    <button id="close-menu-btn" class="p-2 rounded-lg hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <nav class="p-4 space-y-1 overflow-y-auto">
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-4">WhatsApp</div>
                    
                    <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('dashboard') || request()->routeIs('dashboard.index')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="{{ route('dashboard.sessions') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('dashboard.sessions')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Sessions</span>
                    </a>
                    
                    <a href="{{ route('crm.chat.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.chat.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Messaging</span>
                    </a>
                    
                    <a href="{{ route('dashboard.bulk-messaging') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('dashboard.bulk-messaging')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Bulk Messaging</span>
                    </a>
                    
                    <a href="{{ route('dashboard.chat-history') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('dashboard.chat-history')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Chat History</span>
                    </a>
                    
                    <a href="{{ route('dashboard.groups') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('dashboard.groups')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Groups</span>
                    </a>
                    
                    <a href="{{ route('dashboard.websocket') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('dashboard.websocket')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>WebSocket</span>
                    </a>
                    
                    <a href="{{ route('dashboard.api-docs') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('dashboard.api-docs')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>RESTful API</span>
                    </a>
                    
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-2 px-4">CRM</div>
                    
                    <a href="{{ route('crm.dashboard') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.dashboard')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>CRM Dashboard</span>
                    </a>
                    
                    <a href="{{ route('crm.contacts.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.contacts.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Contacts</span>
                    </a>
                    
                    <a href="{{ route('crm.orders.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.orders.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>Orders</span>
                    </a>
                    
                    <a href="{{ route('crm.tickets.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.tickets.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        <span>Tickets</span>
                    </a>
                    
                    <a href="{{ route('crm.campaigns.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.campaigns.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        <span>Campaigns</span>
                    </a>
                    
                    <a href="{{ route('crm.automations.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.automations.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Automations</span>
                    </a>
                    
                    <a href="{{ route('crm.chatbots.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.chatbots.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Chatbots</span>
                    </a>
                    
                    <a href="{{ route('crm.templates.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.templates.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                        <span>Templates</span>
                    </a>
                    
                    <a href="{{ route('crm.analytics') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.analytics')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Analytics</span>
                    </a>
                    
                    <a href="{{ route('crm.segments.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.segments.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Segments</span>
                    </a>
                    
                    <a href="{{ route('crm.products.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.products.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Products</span>
                    </a>

                    <a href="{{ route('crm.categories.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.categories.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        <span>Categories</span>
                    </a>
                    
                    <a href="{{ route('crm.quick-replies.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.quick-replies.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <span>Quick Replies</span>
                    </a>

                    <a href="{{ route('crm.tags.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.tags.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span>Tags</span>
                    </a>

                    <a href="{{ route('crm.activity.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.activity.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Activity Log</span>
                    </a>

                    <a href="{{ route('crm.tasks.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.tasks.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span>Tasks</span>
                    </a>

                    <a href="{{ route('crm.deals.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.deals.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Deals Pipeline</span>
                    </a>

                    <a href="{{ route('crm.surveys.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.surveys.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Surveys</span>
                    </a>

                    <a href="{{ route('crm.audit-logs.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.audit-logs.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Audit Logs</span>
                    </a>

                    <a href="{{ route('crm.duplicates.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.duplicates.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span>Duplicates</span>
                    </a>

                    @if(Auth::user()->canViewAllData())
                    <a href="{{ route('crm.users.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.users.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Users</span>
                    </a>

                    <a href="{{ route('crm.roles.index') }}" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 @if(request()->routeIs('crm.roles.*')) active @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Roles & Permissions</span>
                    </a>
                    @endif
                    
                    <!-- User Section for Mobile -->
                    <div class="flex-shrink-0 p-4 border-t border-gray-200 bg-gray-50 mt-4">
                        @auth
                            <div id="userSidebarMobile" class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center overflow-hidden">
                                        @if(Auth::user()->avatar && file_exists(public_path('storage/' . Auth::user()->avatar)))
                                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-gray-500">{{ Auth::user()->roles->first()->name ?? 'User' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <a href="{{ route('profile') }}" class="p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100" title="Profile">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="p-2 text-gray-500 hover:text-red-600 rounded-lg hover:bg-gray-100" title="Logout">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center justify-between">
                                <a href="{{ route('login') }}" class="flex items-center space-x-3 text-gray-700 hover:text-primary-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    <span class="text-sm font-medium">Login</span>
                                </a>
                                <a href="{{ route('register') }}" class="px-3 py-1.5 text-sm bg-primary-600 text-white rounded hover:bg-primary-700">
                                    Register
                                </a>
                            </div>
                        @endauth
                    </div>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <div class="p-6">
                @yield('content')
            </div>
        </main>
    </div>
    
    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-4 right-4 z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-4 flex items-center space-x-3">
            <div id="toast-icon" class="w-6 h-6"></div>
            <p id="toast-message" class="text-sm font-medium"></p>
        </div>
    </div>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn')?.addEventListener('click', () => {
            document.getElementById('mobile-menu').classList.remove('hidden');
        });
        
        document.getElementById('close-menu-btn')?.addEventListener('click', () => {
            document.getElementById('mobile-menu').classList.add('hidden');
        });
        
        document.getElementById('mobile-menu')?.addEventListener('click', (e) => {
            if (e.target.id === 'mobile-menu') {
                document.getElementById('mobile-menu').classList.add('hidden');
            }
        });
        
        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toast-icon');
            const msg = document.getElementById('toast-message');
            
            msg.textContent = message;
            
            if (type === 'success') {
                icon.innerHTML = '<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
            } else if (type === 'error') {
                icon.innerHTML = '<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
            } else {
                icon.innerHTML = '<svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
            }
            
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }
        
        // CSRF Token for all AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    </script>
    @stack('scripts')
</body>
</html>
