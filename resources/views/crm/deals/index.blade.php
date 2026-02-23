@extends('layouts.app')

@section('title', 'Deals Pipeline')

@section('content')
@php
use App\Models\Deal;
@endphp
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Deals Pipeline</h1>
            <p class="text-gray-500 mt-1">Manage your sales opportunities</p>
        </div>
        <button onclick="openModal('create-deal-modal')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i>New Deal
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Deals</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                    <i class="fas fa-handshake text-indigo-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Value</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($stats['total_value'], 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Won Deals</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ $stats['won'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                    <i class="fas fa-trophy text-emerald-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Win Rate</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['win_rate'] }}%</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-percentage text-blue-600"></i>
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
        Showing My Deals Only
    </div>
    @endif

    <!-- Pipeline View -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 overflow-x-auto">
        @foreach($stages as $stage => $stageName)
        <div class="min-w-[250px]">
            <div class="bg-gray-100 rounded-t-lg px-3 py-2 flex items-center justify-between" style="border-top: 3px solid {{ $stageColors[$stage] }}">
                <span class="font-semibold text-gray-700">{{ $stageName }}</span>
                <span class="bg-white px-2 py-0.5 rounded-full text-xs font-medium">
                    {{ $deals->where('stage', $stage)->count() }}
                </span>
            </div>
            <div class="bg-gray-50 rounded-b-lg p-2 min-h-[400px] space-y-2" 
                 ondrop="drop(event, '{{ $stage }}')" 
                 ondragover="allowDrop(event)">
                @foreach($deals->where('stage', $stage) as $deal)
                <div class="bg-white rounded-lg shadow-sm p-3 cursor-pointer hover:shadow-md transition-shadow"
                     draggable="true"
                     ondragstart="drag(event, {{ $deal->id }})"
                     onclick="openDealDetail({{ $deal->id }})">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-medium text-gray-800">{{ $deal->title }}</h4>
                            <p class="text-sm text-gray-500">{{ $deal->contact->name ?? 'No Contact' }}</p>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-lg font-bold text-green-600">{{ number_format($deal->value, 0, ',', '.') }}</span>
                        @if($deal->probability > 0)
                        <span class="text-xs text-gray-500">{{ $deal->probability }}%</span>
                        @endif
                    </div>
                    @if($deal->expected_close_date)
                    <div class="mt-2 text-xs text-gray-500">
                        <i class="fas fa-calendar mr-1"></i>{{ \Carbon\Carbon::parse($deal->expected_close_date)->format('d M Y') }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Create Deal Modal -->
<div id="create-deal-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b flex items-center justify-between sticky top-0 bg-white">
            <h3 class="text-lg font-semibold text-gray-800">Create New Deal</h3>
            <button onclick="closeModal('create-deal-modal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="{{ route('crm.deals.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deal Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact <span class="text-red-500">*</span></label>
                <select name="contact_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select Contact</option>
                    @foreach($contacts as $contact)
                    <option value="{{ $contact->id }}">{{ $contact->name ?? 'No Name' }} - {{ $contact->phone }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Value <span class="text-red-500">*</span></label>
                    <input type="number" name="value" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Probability (%)</label>
                    <input type="number" name="probability" value="0" min="0" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stage <span class="text-red-500">*</span></label>
                    <select name="stage" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach(Deal::getStages() as $stage => $stageName)
                        <option value="{{ $stage }}">{{ $stageName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                    <select name="source" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Source</option>
                        @foreach(Deal::getSources() as $source => $sourceName)
                        <option value="{{ $source }}">{{ $sourceName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expected Close Date</label>
                <input type="date" name="expected_close_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="closeModal('create-deal-modal')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Create Deal</button>
            </div>
        </form>
    </div>
</div>

<script>
let draggedDealId = null;

function drag(event, dealId) {
    draggedDealId = dealId;
    event.dataTransfer.setData("text/plain", dealId);
}

function allowDrop(event) {
    event.preventDefault();
}

function drop(event, stage) {
    event.preventDefault();
    if (draggedDealId) {
        fetch(`/crm/deals/${draggedDealId}/stage`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ stage: stage })
        }).then(() => location.reload());
    }
}

function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function openDealDetail(dealId) {
    window.location.href = `/crm/deals/${dealId}`;
}
</script>
@endsection
