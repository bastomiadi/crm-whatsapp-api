@extends('layouts.app')

@section('title', 'Surveys & Feedback')

@section('content')
@php
use App\Models\Survey;
@endphp
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Surveys & Feedback</h1>
            <p class="text-gray-500 mt-1">Collect feedback from your customers</p>
        </div>
        <button onclick="openModal('create-survey-modal')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i>Create Survey
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Surveys</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $surveys->total() }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                    <i class="fas fa-poll text-indigo-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active Surveys</p>
                    <p class="text-2xl font-bold text-green-600">{{ $surveys->where('status', 'active')->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-play text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Responses</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $totalResponses }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-comments text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Avg NPS Score</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $avgNps ?? '-' }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-star text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Badge -->
    @if(!$canViewAll)
    <div class="flex items-center px-3 py-2 bg-yellow-100 text-yellow-800 rounded-lg text-sm">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        Showing My Surveys Only
    </div>
    @endif

    <!-- Surveys List -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Survey</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Responses</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($surveys as $survey)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-900">{{ $survey->title }}</p>
                                <p class="text-sm text-gray-500">{{ Str::limit($survey->description, 50) }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                {{ ucfirst($survey->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($survey->status === 'active')
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                            @elseif($survey->status === 'draft')
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Draft</span>
                            @else
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Closed</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $survey->response_count }}
                        </td>
                        <td class="px-6 py-4">
                            @if($survey->type === 'nps' && $survey->nps_score !== null)
                            <span class="font-bold" style="color: {{ $survey->nps_score >= 50 ? '#22c55e' : ($survey->nps_score >= 0 ? '#f59e0b' : '#ef4444') }}">
                                {{ $survey->nps_score }}
                            </span>
                            @elseif($survey->type === 'satisfaction' && $survey->avg_satisfaction !== null)
                            <span class="font-bold text-blue-600">{{ $survey->avg_satisfaction }}/5</span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $survey->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="viewSurvey({{ $survey->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="editSurvey({{ $survey->id }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-edit"></i>
                            </button>
                            @if($survey->status === 'draft')
                            <button onclick="activateSurvey({{ $survey->id }})" class="text-green-600 hover:text-green-900 mr-3">
                                <i class="fas fa-play"></i>
                            </button>
                            @elseif($survey->status === 'active')
                            <button onclick="closeSurvey({{ $survey->id }})" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                <i class="fas fa-stop"></i>
                            </button>
                            @endif
                            <button onclick="deleteSurvey({{ $survey->id }})" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">No surveys found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($surveys->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $surveys->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Create Survey Modal -->
<div id="create-survey-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b flex items-center justify-between sticky top-0 bg-white">
            <h3 class="text-lg font-semibold">Create New Survey</h3>
            <button onclick="closeModal('create-survey-modal')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('crm.surveys.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Survey Title</label>
                <input type="text" name="title" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Survey Type</label>
                    <select name="type" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        @foreach(App\Models\Survey::getTypes() as $type => $label)
                        <option value="{{ $type }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        @foreach(App\Models\Survey::getStatuses() as $status => $label)
                        <option value="{{ $status }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="starts_at" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="ends_at" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="send_via_whatsapp" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Send via WhatsApp</span>
                </label>
            </div>
            <div class="flex justify-end space-x-2 pt-4">
                <button type="button" onclick="closeModal('create-survey-modal')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Create Survey</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function viewSurvey(id) {
    window.location.href = '/crm/surveys/' + id;
}

function editSurvey(id) {
    window.location.href = '/crm/surveys/' + id + '/edit';
}

function activateSurvey(id) {
    if (confirm('Activate this survey?')) {
        fetch('/crm/surveys/' + id + '/activate', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => location.reload());
    }
}

function closeSurvey(id) {
    if (confirm('Close this survey?')) {
        fetch('/crm/surveys/' + id + '/close', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => location.reload());
    }
}

function deleteSurvey(id) {
    if (confirm('Are you sure you want to delete this survey?')) {
        fetch('/crm/surveys/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => location.reload());
    }
}
</script>
@endsection
