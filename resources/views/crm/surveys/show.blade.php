@extends('layouts.app')

@section('title', $survey->title)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('crm.surveys.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $survey->title }}</h1>
                <p class="text-gray-500">{{ $survey->description }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            @if($survey->status === 'draft')
            <a href="{{ route('crm.surveys.edit', $survey->id) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Edit Survey
            </a>
            <form action="{{ route('crm.surveys.activate', $survey->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Activate
                </button>
            </form>
            @elseif($survey->status === 'active')
            <form action="{{ route('crm.surveys.close', $survey->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    Close Survey
                </button>
            </form>
            @endif
            <form action="{{ route('crm.surveys.destroy', $survey->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this survey?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Survey Info -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-500">Status</p>
                @if($survey->status === 'active')
                <span class="px-2 py-1 text-sm rounded-full bg-green-100 text-green-800">Active</span>
                @elseif($survey->status === 'draft')
                <span class="px-2 py-1 text-sm rounded-full bg-yellow-100 text-yellow-800">Draft</span>
                @else
                <span class="px-2 py-1 text-sm rounded-full bg-gray-100 text-gray-800">Closed</span>
                @endif
            </div>
            <div>
                <p class="text-sm text-gray-500">Type</p>
                <p class="font-medium">{{ ucfirst($survey->type) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Start Date</p>
                <p class="font-medium">{{ $survey->starts_at ? $survey->starts_at->format('d M Y') : 'Not set' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">End Date</p>
                <p class="font-medium">{{ $survey->ends_at ? $survey->ends_at->format('d M Y') : 'Not set' }}</p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500">Total Responses</p>
            <p class="text-3xl font-bold text-gray-800">{{ $survey->response_count }}</p>
        </div>
        @if($survey->type === 'nps')
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500">NPS Score</p>
            <p class="text-3xl font-bold" style="color: {{ $survey->nps_score >= 50 ? '#22c55e' : ($survey->nps_score >= 0 ? '#f59e0b' : '#ef4444') }}">
                {{ $survey->nps_score ?? '-' }}
            </p>
        </div>
        @elseif($survey->type === 'satisfaction')
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500">Avg. Satisfaction</p>
            <p class="text-3xl font-bold text-blue-600">{{ $survey->avg_satisfaction ? $survey->avg_satisfaction . '/5' : '-' }}</p>
        </div>
        @endif
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500">Created</p>
            <p class="text-3xl font-bold text-gray-800">{{ $survey->created_at->format('d M Y') }}</p>
        </div>
    </div>

    <!-- NPS Distribution (for NPS surveys) -->
    @if($survey->type === 'nps' && $survey->response_count > 0)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">NPS Distribution</h3>
        <div class="space-y-3">
            @php
            $responses = $survey->responses;
            $promoters = $responses->where('nps_score', '>=', 9)->count();
            $passives = $responses->whereBetween('nps_score', [7, 8])->count();
            $detractors = $responses->where('nps_score', '<=', 6)->count();
            $total = $responses->count();
            @endphp
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-green-600">Promoters (9-10)</span>
                    <span>{{ $promoters }} ({{ $total > 0 ? round(($promoters / $total) * 100) : 0 }}%)</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $total > 0 ? ($promoters / $total) * 100 : 0 }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-yellow-600">Passives (7-8)</span>
                    <span>{{ $passives }} ({{ $total > 0 ? round(($passives / $total) * 100) : 0 }}%)</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $total > 0 ? ($passives / $total) * 100 : 0 }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-red-600">Detractors (0-6)</span>
                    <span>{{ $detractors }} ({{ $total > 0 ? round(($detractors / $total) * 100) : 0 }}%)</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-red-500 h-2 rounded-full" style="width: {{ $total > 0 ? ($detractors / $total) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Responses List -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Responses ({{ $survey->response_count }})</h3>
        </div>
        @if($survey->responses->count() > 0)
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Feedback</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($survey->responses as $response)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        @if($response->contact)
                        <a href="{{ route('crm.contacts.show', $response->contact->id) }}" class="text-indigo-600 hover:underline">
                            {{ $response->contact->name }}
                        </a>
                        @else
                        <span class="text-gray-400">Unknown</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($response->nps_score !== null)
                        <span class="font-medium" style="color: {{ $response->nps_score >= 9 ? '#22c55e' : ($response->nps_score >= 7 ? '#f59e0b' : '#ef4444') }}">
                            {{ $response->nps_score }} ({{ $response->nps_label }})
                        </span>
                        @elseif($response->satisfaction_score !== null)
                        <span class="font-medium text-blue-600">{{ $response->satisfaction_score }}/5</span>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        {{ $response->feedback ?: '-' }}
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        {{ $response->completed_at ? $response->completed_at->format('d M Y H:i') : $response->created_at->format('d M Y H:i') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="px-6 py-12 text-center text-gray-500">
            No responses yet. This survey has not received any responses.
        </div>
        @endif
    </div>
</div>
@endsection
