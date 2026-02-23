@extends('layouts.app')

@section('title', 'Chatbot Sessions - ' . $chatbot->name)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('crm.chatbots.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left text-gray-600"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $chatbot->name }}</h1>
                <p class="text-gray-500 mt-1">Chat Sessions</p>
            </div>
        </div>
    </div>

    <!-- Sessions List -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-800">All Sessions</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Messages</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($sessions as $session)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-900 font-mono">{{ $session->session_id ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $session->contact_name ?? 'Unknown' }}</p>
                                <p class="text-sm text-gray-500">{{ $session->contact_phone ?? '' }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'active' => 'green',
                                    'waiting' => 'yellow',
                                    'ended' => 'gray',
                                    'handover' => 'blue',
                                ];
                                $color = $statusColors[$session->status] ?? 'gray';
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                                {{ ucfirst($session->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-500">{{ $session->created_at->format('M d, Y H:i') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-500">{{ $session->message_count ?? 0 }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="viewSession('{{ $session->id }}')" class="text-blue-600 hover:text-blue-900 text-sm">
                                View
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <p>No sessions found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $sessions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
