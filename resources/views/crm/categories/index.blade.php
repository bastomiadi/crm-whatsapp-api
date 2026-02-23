@extends('layouts.app')

@section('title', 'Categories Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Categories Management</h1>
            <p class="text-gray-600">Manage product categories</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(!Auth::user()->canViewAllData())
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full">
                    <i class="fas fa-filter mr-1"></i> Showing My Categories Only
                </span>
            @endif
            <button onclick="openModal('categoryModal')" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Add Category</span>
            </button>
        </div>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($categories as $category)
        <div class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow" data-category-id="{{ $category->id }}">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $category->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $category->products_count }} products</p>
                    </div>
                </div>
                <div class="flex items-center space-x-1">
                    <button onclick="editCategory({{ $category->id }}, '{{ $category->name }}', '{{ $category->description }}', {{ $category->is_active ? 'true' : 'false' }})" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </button>
                    <button onclick="toggleCategory({{ $category->id }})" class="p-2 text-gray-500 hover:text-{{ $category->is_active ? 'red' : 'green' }}-600 hover:bg-{{ $category->is_active ? 'red' : 'green' }}-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($category->is_active)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            @endif
                        </svg>
                    </button>
                    <button onclick="deleteCategory({{ $category->id }})" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
            @if($category->description)
            <p class="mt-3 text-sm text-gray-600">{{ $category->description }}</p>
            @endif
            <div class="mt-3 flex items-center justify-between text-xs">
                <span class="text-gray-500">Created: {{ $category->created_at->format('M d, Y') }}</span>
                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <p class="text-gray-500">No categories yet. Create your first category!</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($categories->hasPages())
    <div class="flex justify-center">
        {{ $categories->links() }}
    </div>
    @endif
</div>

<!-- Category Modal -->
<div id="categoryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
        <div class="p-6">
            <h2 id="modalTitle" class="text-xl font-semibold text-gray-800 mb-4">Add New Category</h2>
            <form id="categoryForm">
                @csrf
                <input type="hidden" id="categoryId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
                        <input type="text" id="categoryName" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Enter category name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="categoryDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Optional description"></textarea>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="categoryActive" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500" checked>
                        <label for="categoryActive" class="ml-2 text-sm text-gray-700">Active</label>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('categoryModal')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
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

function editCategory(id, name, description, isActive) {
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('categoryId').value = id;
    document.getElementById('categoryName').value = name;
    document.getElementById('categoryDescription').value = description || '';
    document.getElementById('categoryActive').checked = isActive;
    openModal('categoryModal');
}

document.getElementById('categoryForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const categoryId = document.getElementById('categoryId').value;
    const name = document.getElementById('categoryName').value;
    const description = document.getElementById('categoryDescription').value;
    const is_active = document.getElementById('categoryActive').checked;
    
    const url = categoryId ? `/crm/categories/${categoryId}` : '/crm/categories';
    const method = categoryId ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name, description, is_active })
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeModal('categoryModal');
            location.reload();
        } else {
            alert(data.message || 'Error saving category');
        }
    } catch (error) {
        alert('Error saving category');
    }
});

async function toggleCategory(id) {
    try {
        // First get the current category data
        const response = await fetch(`/crm/categories/${id}`);
        const data = await response.json();
        
        if (!data.category) {
            alert('Error: Category not found');
            return;
        }
        
        // Toggle is_active
        const newStatus = !data.category.is_active;
        
        // Update with the new status
        const updateResponse = await fetch(`/crm/categories/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                name: data.category.name, 
                description: data.category.description,
                is_active: newStatus 
            })
        });
        
        const updateData = await updateResponse.json();
        
        if (updateData.success) {
            location.reload();
        } else {
            alert(updateData.message || 'Error toggling category');
        }
    } catch (error) {
        alert('Error toggling category');
    }
}

async function deleteCategory(id) {
    if (!confirm('Are you sure you want to delete this category?')) return;
    
    try {
        const response = await fetch(`/crm/categories/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error deleting category');
        }
    } catch (error) {
        alert('Error deleting category');
    }
}
</script>
@endpush
@endsection
