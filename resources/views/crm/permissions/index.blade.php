@extends('layouts.app')

@section('title', 'Permissions Management')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Permissions Management</h1>
        <p class="text-gray-600">Manage system permissions</p>
    </div>
    <a href="{{ route('crm.permissions.create') }}" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
        <i class="fas fa-plus mr-2"></i> Add Permission
    </a>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

@forelse($permissions as $module => $modulePermissions)
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', $module) }}</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($modulePermissions as $permission)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-800">{{ $permission->name }}</p>
                            <p class="text-sm text-gray-500">{{ $permission->slug }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('crm.permissions.edit', $permission) }}" class="text-primary-600 hover:text-primary-900">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="text-red-600 hover:text-red-900" onclick="deletePermission({{ $permission->id }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@empty
    <div class="bg-white rounded-lg shadow p-6 text-center">
        <p class="text-gray-500">No permissions found. Create your first permission.</p>
    </div>
@endforelse

@push('scripts')
<script>
function deletePermission(id) {
    if(confirm('Are you sure you want to delete this permission?')) {
        fetch(`/crm/permissions/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}
</script>
@endpush
@endsection
