@extends('layouts.app')

@section('title', 'Create Contact')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-sm text-gray-500">
        <a href="{{ route('crm.contacts.index') }}" class="hover:text-gray-700">Contacts</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-900">Create Contact</span>
    </nav>

    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h1 class="text-xl font-bold text-gray-800">Create New Contact</h1>
            <p class="text-gray-500 mt-1">Add a new contact to your CRM</p>
        </div>

        <form id="createForm" class="p-6 space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Phone (Required) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Phone Number <span class="text-red-500">*</span>
                    </label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                            +62
                        </span>
                        <input type="text" name="phone" id="phone" required
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                            placeholder="81234567890"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Enter phone number without country code</p>
                </div>

                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" id="name"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="John Doe">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="john@example.com">
                </div>

                <!-- Company -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                    <input type="text" name="company" id="company"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="PT Example">
                </div>

                <!-- Segment -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Segment</label>
                    <select name="segment_id" id="segment_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                        <option value="">No Segment</option>
                        @foreach($segments as $segment)
                        <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="blocked">Blocked</option>
                    </select>
                </div>
            </div>

            <!-- Address -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea name="address" id="address" rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                    placeholder="Full address..."></textarea>
            </div>

            <!-- Tags -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                <div class="flex flex-wrap gap-3">
                    @foreach($tags as $tag)
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                            class="rounded border-gray-300 text-whatsapp-light focus:ring-whatsapp-light">
                        <span class="ml-2 px-2 py-1 text-xs rounded-full" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                            {{ $tag->name }}
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" id="notes" rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                    placeholder="Additional notes about this contact..."></textarea>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                <a href="{{ route('crm.contacts.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                    Create Contact
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('createForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    
    // Convert FormData to object
    for (let [key, value] of formData.entries()) {
        if (key === 'tags[]') {
            if (!data.tags) data.tags = [];
            data.tags.push(value);
        } else {
            data[key] = value;
        }
    }
    
    // Format phone number
    if (data.phone) {
        data.phone = '62' + data.phone;
    }
    
    try {
        const response = await fetch('{{ route("crm.contacts.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Contact created successfully!');
            window.location.href = '{{ route("crm.contacts.index") }}';
        } else {
            alert('Failed to create contact: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        alert('Error creating contact: ' + error.message);
    }
});
</script>
