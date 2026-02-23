@extends('layouts.app')

@section('title', 'Duplicate Detection')

@section('content')
@php
use App\Services\DuplicateDetectionService;
@endphp
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Duplicate Detection</h1>
            <p class="text-gray-500 mt-1">Find and merge duplicate contacts</p>
        </div>
        <button onclick="scanDuplicates()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <i class="fas fa-search mr-2"></i>Scan for Duplicates
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Phone Duplicates</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['total_phone_duplicates'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <i class="fas fa-phone text-red-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Email Duplicates</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['total_email_duplicates'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                    <i class="fas fa-envelope text-orange-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Duplicate Groups</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['duplicate_groups'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                    <i class="fas fa-user text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Badge - Duplicates show all data since they span across all users -->
    <div class="flex items-center px-3 py-2 bg-blue-100 text-blue-800 rounded-lg text-sm">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Showing all duplicates across all contacts
    </div>

    <!-- Phone Duplicates -->
    @if($phoneDuplicates->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b bg-red-50">
            <h3 class="text-lg font-semibold text-red-800">
                <i class="fas fa-phone mr-2"></i>Phone Number Duplicates
            </h3>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($phoneDuplicates as $group)
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="text-lg font-medium">{{ $group->first()->phone }}</span>
                        <span class="text-gray-500 ml-2">({{ $group->count() }} contacts)</span>
                    </div>
                    <button onclick="showMergeModal({{ $group->pluck('id') }})" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 text-sm">
                        Merge
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($group as $contact)
                    <div class="border rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium">{{ $contact->name ?? '-' }}</p>
                                <p class="text-sm text-gray-500">{{ $contact->email ?? '-' }}</p>
                                <p class="text-sm text-gray-500">{{ $contact->company ?? '-' }}</p>
                            </div>
                            <div class="text-right text-sm text-gray-500">
                                <p>Orders: {{ $contact->orders->count() }}</p>
                                <p>Tickets: {{ $contact->tickets->count() }}</p>
                                <p>Created: {{ $contact->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Email Duplicates -->
    @if($emailDuplicates->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b bg-orange-50">
            <h3 class="text-lg font-semibold text-orange-800">
                <i class="fas fa-envelope mr-2"></i>Email Duplicates
            </h3>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($emailDuplicates as $group)
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="text-lg font-medium">{{ $group->first()->email }}</span>
                        <span class="text-gray-500 ml-2">({{ $group->count() }} contacts)</span>
                    </div>
                    <button onclick="showMergeModal({{ $group->pluck('id') }})" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 text-sm">
                        Merge
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($group as $contact)
                    <div class="border rounded-lg p-4">
                        <p class="font-medium">{{ $contact->name ?? '-' }}</p>
                        <p class="text-sm text-gray-500">{{ $contact->phone ?? '-' }}</p>
                        <p class="text-sm text-gray-500">{{ $contact->company ?? '-' }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Similar Names -->
    @if($similarNames->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b bg-yellow-50">
            <h3 class="text-lg font-semibold text-yellow-800">
                <i class="fas fa-user mr-2"></i>Similar Names
            </h3>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($similarNames as $group)
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-gray-500">({{ $group->count() }} similar contacts)</span>
                    <button onclick="showMergeModal({{ $group->pluck('id') }})" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 text-sm">
                        Merge
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($group as $contact)
                    <div class="border rounded-lg p-4">
                        <p class="font-medium">{{ $contact->name }}</p>
                        <p class="text-sm text-gray-500">{{ $contact->phone }}</p>
                        <p class="text-sm text-gray-500">{{ $contact->email ?? '-' }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($phoneDuplicates->isEmpty() && $emailDuplicates->isEmpty() && $similarNames->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-check text-green-600 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-800">No Duplicates Found</h3>
        <p class="text-gray-500 mt-2">Your contact list looks clean!</p>
    </div>
    @endif
</div>

<!-- Merge Modal -->
<div id="merge-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold">Merge Contacts</h3>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Select the contact to keep. All data (orders, tickets, deals) will be merged into this contact.</p>
            <form id="merge-form" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Keep this contact:</label>
                    <select name="keep_id" id="keep-select" class="w-full px-3 py-2 border rounded-lg">
                    </select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeMergeModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Merge</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentMergeIds = [];

function scanDuplicates() {
    window.location.href = '{{ route("crm.duplicates.scan") }}';
}

function showMergeModal(ids) {
    currentMergeIds = ids;
    const select = document.getElementById('keep-select');
    select.innerHTML = '';
    
    ids.forEach(id => {
        const option = document.createElement('option');
        option.value = id;
        option.textContent = 'Contact #' + id;
        select.appendChild(option);
    });
    
    document.getElementById('merge-modal').classList.remove('hidden');
    document.getElementById('merge-form').action = '/crm/contacts/merge';
}

function closeMergeModal() {
    document.getElementById('merge-modal').classList.add('hidden');
}
</script>
@endsection
