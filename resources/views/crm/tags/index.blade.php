@extends('layouts.app')

@section('title', 'Tags Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tags Management</h1>
            <p class="text-gray-600">Manage contact tags for segmentation</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(!Auth::user()->canViewAllData())
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full">
                    <i class="fas fa-filter mr-1"></i> Showing My Tags Only
                </span>
            @endif
            <button onclick="openModal('tagModal')" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Add Tag</span>
            </button>
        </div>
    </div>

    <!-- Tags Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($tags as $tag)
        <div class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow" data-tag-id="{{ $tag->id }}">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: {{ $tag->color }}20;">
                        <span class="font-semibold" style="color: {{ $tag->color }};">{{ substr($tag->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $tag->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $tag->contacts_count }} contacts</p>
                    </div>
                </div>
                <div class="flex items-center space-x-1">
                    <button onclick="editTag({{ $tag->id }}, '{{ $tag->name }}', '{{ $tag->color }}', '{{ $tag->description }}')" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </button>
                    <button onclick="deleteTag({{ $tag->id }})" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
            @if($tag->description)
            <p class="mt-3 text-sm text-gray-600">{{ $tag->description }}</p>
            @endif
            <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                <span>Created: {{ $tag->created_at->format('M d, Y') }}</span>
                <span class="w-3 h-3 rounded-full" style="background-color: {{ $tag->color }};"></span>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <p class="text-gray-500">No tags yet. Create your first tag!</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($tags->hasPages())
    <div class="flex justify-center">
        {{ $tags->links() }}
    </div>
    @endif
</div>

<!-- Tag Modal -->
<div id="tagModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
        <div class="p-6">
            <h2 id="modalTitle" class="text-xl font-semibold text-gray-800 mb-4">Add New Tag</h2>
            <form id="tagForm">
                @csrf
                <input type="hidden" id="tagId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tag Name</label>
                        <input type="text" id="tagName" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Enter tag name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                        <div class="flex items-center space-x-3">
                            <input type="color" id="tagColor" value="#6366f1" class="w-12 h-10 rounded cursor-pointer border-0">
                            <div class="flex space-x-2">
                                @foreach(['#ef4444', '#f97316', '#eab308', '#22c55e', '#06b6d4', '#3b82f6', '#8b5cf6', '#ec4899'] as $color)
                                <button type="button" onclick="setColor('{{ $color }}')" class="w-6 h-6 rounded-full border-2 border-white shadow-sm hover:scale-110 transition-transform" style="background-color: {{ $color }};"></button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="tagDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Optional description"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('tagModal')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function setColor(color) {
    document.getElementById('tagColor').value = color;
}

function editTag(id, name, color, description) {
    document.getElementById('modalTitle').textContent = 'Edit Tag';
    document.getElementById('tagId').value = id;
    document.getElementById('tagName').value = name;
    document.getElementById('tagColor').value = color;
    document.getElementById('tagDescription').value = description || '';
    openModal('tagModal');
}

document.getElementById('tagForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const tagId = document.getElementById('tagId').value;
    const name = document.getElementById('tagName').value;
    const color = document.getElementById('tagColor').value;
    const description = document.getElementById('tagDescription').value;
    
    const url = tagId ? `/crm/tags/${tagId}` : '/crm/tags';
    const method = tagId ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name, color, description })
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeModal('tagModal');
            location.reload();
        } else {
            alert(data.message || 'Error saving tag');
        }
    } catch (error) {
        alert('Error saving tag');
    }
});

async function deleteTag(id) {
    if (!confirm('Are you sure you want to delete this tag?')) return;
    
    try {
        const response = await fetch(`/crm/tags/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error deleting tag');
        }
    } catch (error) {
        alert('Error deleting tag');
    }
}
</script>
@endpush
@endsection
