@extends('layouts.app')

@section('title', 'Edit Chatbot - ' . $chatbot->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('crm.chatbots.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left text-gray-600"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Chatbot</h1>
                <p class="text-gray-500 mt-1">{{ $chatbot->name }}</p>
            </div>
        </div>
        <div>
            <span class="px-3 py-1 text-sm font-medium rounded-full 
                @if($chatbot->status === 'active') bg-green-100 text-green-800
                @elseif($chatbot->status === 'draft') bg-gray-100 text-gray-800
                @else bg-yellow-100 text-yellow-800 @endif">
                {{ ucfirst($chatbot->status) }}
            </span>
        </div>
    </div>

    <form id="editForm" method="POST" action="{{ route('crm.chatbots.update', $chatbot->id) }}">
        @csrf
        @method('PUT')

        <!-- Page Header -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" name="name" value="{{ $chatbot->name }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">{{ $chatbot->description }}</textarea>
                </div>
            </div>
        </div>

        <!-- Flows Configuration -->
        <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Flows Configuration</h2>
            <div id="flowsContainer">
                @php
                    $flows = is_array($chatbot->flows) ? $chatbot->flows : json_decode($chatbot->flows, true) ?? [];
                @endphp
                @if(count($flows) > 0)
                    @foreach($flows as $index => $flow)
                    <div class="flow-item mb-4 p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <input type="text" name="flows[{{ $index }}][name]" value="{{ $flow['name'] ?? '' }}" placeholder="Flow name" 
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent w-1/2">
                            <button type="button" onclick="removeFlow(this)" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <textarea name="flows[{{ $index }}][response]" rows="2" placeholder="Auto response message"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">{{ $flow['response'] ?? '' }}</textarea>
                    </div>
                    @endforeach
                @else
                    <div class="flow-item mb-4 p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <input type="text" name="flows[0][name]" value="" placeholder="Flow name" 
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent w-1/2">
                            <button type="button" onclick="removeFlow(this)" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <textarea name="flows[0][response]" rows="2" placeholder="Auto response message"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"></textarea>
                    </div>
                @endif
            </div>
            <button type="button" onclick="addFlow()" class="mt-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-plus mr-2"></i> Add Flow
            </button>
        </div>

        <!-- Keywords -->
        <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Keywords</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Trigger Keywords (comma separated)</label>
                <input type="text" name="keywords_text" value="{{ is_array($chatbot->keywords) ? implode(', ', $chatbot->keywords) : '' }}" 
                    placeholder="hello, help, support"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
        </div>

        <!-- Responses -->
        <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Responses</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Response</label>
                    <textarea name="default_response_text" rows="3" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">{{ is_array($chatbot->default_response) ? ($chatbot->default_response[0] ?? '') : '' }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fallback Response</label>
                    <textarea name="fallback_response_text" rows="3" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">{{ is_array($chatbot->fallback_response) ? ($chatbot->fallback_response[0] ?? '') : '' }}</textarea>
                </div>
            </div>
        </div>

        <!-- Handover Settings -->
        <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Handover Settings</h2>
            <div class="flex items-center mb-4">
                <input type="checkbox" name="handover_enabled" id="handover_enabled" value="1" 
                    {{ $chatbot->handover_enabled ? 'checked' : '' }}
                    class="rounded border-gray-300 text-whatsapp-light focus:ring-whatsapp-light">
                <label for="handover_enabled" class="ml-2 text-sm text-gray-700">Enable Handover to Human Agent</label>
            </div>
            <div id="handoverFields" class="{{ $chatbot->handover_enabled ? '' : 'hidden' }}">
                <label class="block text-sm font-medium text-gray-700 mb-1">Handover To</label>
                <select name="handover_to" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="">Select Agent</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ $chatbot->handover_to == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 mt-6">
            <a href="{{ route('crm.chatbots.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                Save Changes
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let flowCount = {{ count($flows) > 0 ? count($flows) : 1 }};

function addFlow() {
    const container = document.getElementById('flowsContainer');
    const div = document.createElement('div');
    div.className = 'flow-item mb-4 p-4 border border-gray-200 rounded-lg';
    div.innerHTML = `
        <div class="flex justify-between items-center mb-2">
            <input type="text" name="flows[${flowCount}][name]" value="" placeholder="Flow name" 
                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent w-1/2">
            <button type="button" onclick="removeFlow(this)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <textarea name="flows[${flowCount}][response]" rows="2" placeholder="Auto response message"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"></textarea>
    `;
    container.appendChild(div);
    flowCount++;
}

function removeFlow(button) {
    const container = document.getElementById('flowsContainer');
    if (container.children.length > 1) {
        button.closest('.flow-item').remove();
    }
}

document.getElementById('handover_enabled').addEventListener('change', function() {
    document.getElementById('handoverFields').classList.toggle('hidden', !this.checked);
});

document.getElementById('editForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        name: formData.get('name'),
        description: formData.get('description'),
        flows: [],
        keywords: [],
        default_response: [],
        fallback_response: [],
        handover_enabled: formData.has('handover_enabled'),
        handover_to: formData.get('handover_to'),
    };

    // Collect flows
    const flowNames = formData.getAll('flows[][name]');
    const flowResponses = formData.getAll('flows[][response]');
    flowNames.forEach((name, index) => {
        if (name || flowResponses[index]) {
            data.flows.push({ name: name, response: flowResponses[index] });
        }
    });

    // Process keywords
    const keywordsText = formData.get('keywords_text');
    if (keywordsText) {
        data.keywords = keywordsText.split(',').map(k => k.trim()).filter(k => k);
    }

    // Process default response
    const defaultResponse = formData.get('default_response_text');
    if (defaultResponse) {
        data.default_response = [defaultResponse];
    }

    // Process fallback response
    const fallbackResponse = formData.get('fallback_response_text');
    if (fallbackResponse) {
        data.fallback_response = [fallbackResponse];
    }

    try {
        const response = await fetch('{{ route('crm.chatbots.update', $chatbot->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showToast('Chatbot updated successfully', 'success');
            setTimeout(() => window.location.href = '{{ route('crm.chatbots.index') }}', 1000);
        } else {
            showToast(result.message || 'Failed to update chatbot', 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
});

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} z-50`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>
@endpush

@endsection
