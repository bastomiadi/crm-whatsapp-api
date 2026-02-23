@extends('layouts.app')

@section('title', 'Segments')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Segments</h1>
            <p class="text-gray-500 mt-1">Manage customer segments for targeting</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(!Auth::user()->canViewAllData())
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full">
                    <i class="fas fa-filter mr-1"></i> Showing My Segments Only
                </span>
            @endif
            <button onclick="openModal('createSegmentModal')" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                <i class="fas fa-plus mr-2"></i> New Segment
            </button>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $segments = $segments ?? [];
            $totalContacts = \App\Models\Contact::count();
        @endphp
        @foreach($segments as $segment)
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">{{ $segment->name }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $segment->contacts()->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: {{ $segment->color }}20">
                    <i class="fas fa-users" style="color: {{ $segment->color }}"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Segments Table -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-800">All Segments</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Segment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacts</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($segments as $segment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $segment->color }}"></div>
                                <span class="font-medium text-gray-800">{{ $segment->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $segment->description ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-sm bg-gray-100 rounded-full">{{ $segment->contacts()->count() }} contacts</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($segment->is_dynamic)
                            <span class="px-2 py-1 text-sm bg-blue-100 text-blue-800 rounded-full">Dynamic</span>
                            @else
                            <span class="px-2 py-1 text-sm bg-gray-100 text-gray-800 rounded-full">Static</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button onclick="editSegment({{ $segment->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                            <button onclick="deleteSegment({{ $segment->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">No segments found. Create your first segment!</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Segment Modal -->
<div id="createSegmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Create New Segment</h3>
        </div>
        <form id="createSegmentForm" onsubmit="submitSegmentForm(event)">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" name="slug" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                    <input type="color" name="color" value="#22c55e" class="w-full h-10 border border-gray-300 rounded-lg">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_dynamic" id="is_dynamic" class="h-4 w-4 text-whatsapp-light focus:ring-whatsapp-light border-gray-300 rounded">
                    <label for="is_dynamic" class="ml-2 text-sm text-gray-700">Dynamic Segment (auto-update based on criteria)</label>
                </div>
            </div>
            <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('createSegmentModal')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark">Create Segment</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Segment Modal -->
<div id="editSegmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Edit Segment</h3>
        </div>
        <form id="editSegmentForm" onsubmit="submitEditSegmentForm(event)">
            @csrf
            <input type="hidden" id="editSegmentId" name="id">
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" id="editSegmentName" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" id="editSegmentSlug" name="slug" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="editSegmentDescription" name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                    <input type="color" id="editSegmentColor" name="color" value="#22c55e" class="w-full h-10 border border-gray-300 rounded-lg">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="editSegmentDynamic" name="is_dynamic" class="h-4 w-4 text-whatsapp-light focus:ring-whatsapp-light border-gray-300 rounded">
                    <label for="editSegmentDynamic" class="ml-2 text-sm text-gray-700">Dynamic Segment (auto-update based on criteria)</label>
                </div>
            </div>
            <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('editSegmentModal')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark">Update Segment</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.getElementById(id).classList.add('flex');
    }
    
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }
    
    function editSegment(id) {
        window.location.href = '/crm/segments/' + id + '/edit';
    }
    
    async function submitSegmentForm(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
        // Convert formData to URLSearchParams
        const params = new URLSearchParams();
        for (const [key, value] of formData) {
            params.append(key, value);
        }
        
        // Handle checkbox
        if (form.querySelector('input[name="is_dynamic"]').checked) {
            params.append('is_dynamic', 'on');
        }
        
        try {
            const response = await fetch('{{ route('crm.segments.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json'
                },
                body: params.toString()
            });
            
            const data = await response.json();
            
            if (data.success) {
                closeModal('createSegmentModal');
                form.reset();
                window.location.reload();
            } else {
                alert('Error creating segment: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error creating segment');
        }
    }
    
    async function editSegment(id) {
        // Fetch segment data
        try {
            const response = await fetch('/crm/segments/' + id + '/edit');
            const data = await response.json();
            
            if (data.segment) {
                document.getElementById('editSegmentId').value = data.segment.id;
                document.getElementById('editSegmentName').value = data.segment.name;
                document.getElementById('editSegmentSlug').value = data.segment.slug;
                document.getElementById('editSegmentDescription').value = data.segment.description || '';
                document.getElementById('editSegmentColor').value = data.segment.color || '#22c55e';
                document.getElementById('editSegmentDynamic').checked = data.segment.is_dynamic;
                openModal('editSegmentModal');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error loading segment');
        }
    }
    
    async function submitEditSegmentForm(event) {
        event.preventDefault();
        const form = event.target;
        const segmentId = document.getElementById('editSegmentId').value;
        const formData = new FormData(form);
        
        // Convert formData to URLSearchParams
        const params = new URLSearchParams();
        for (const [key, value] of formData) {
            params.append(key, value);
        }
        
        // Handle checkbox
        if (form.querySelector('input[name="is_dynamic"]').checked) {
            params.append('is_dynamic', 'on');
        }
        
        // Add _method for PUT request
        params.append('_method', 'PUT');
        
        try {
            const response = await fetch('/crm/segments/' + segmentId, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json'
                },
                body: params.toString()
            });
            
            const data = await response.json();
            
            if (data.success) {
                closeModal('editSegmentModal');
                window.location.reload();
            } else {
                alert('Error updating segment: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error updating segment');
        }
    }
    
    async function deleteSegment(id) {
        if (!confirm('Delete this segment?')) return;
        
        try {
            const response = await fetch('/crm/segments/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error deleting segment: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error deleting segment');
        }
    }
</script>
@endpush
