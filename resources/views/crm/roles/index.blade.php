@extends('layouts.app')

@section('title', 'Roles Management')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Roles Management</h1>
        <p class="text-gray-600">Manage user roles and permissions</p>
    </div>
    <a href="{{ route('crm.roles.create') }}" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
        <i class="fas fa-plus mr-2"></i> Add Role
    </a>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($roles as $role)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <span class="px-2 py-1 text-xs rounded-full" style="background-color: {{ $role->color }}20; color: {{ $role->color }}">
                                {{ $role->name }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $role->slug }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $role->users()->count() }} users
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $role->permissions()->count() }} permissions
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($role->is_default)
                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Default</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('crm.roles.edit', $role) }}" class="text-primary-600 hover:text-primary-900 mr-3">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @if(!$role->is_default)
                            <button type="button" class="text-red-600 hover:text-red-900" onclick="deleteRole({{ $role->id }})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No roles found. Create your first role.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $roles->links() }}
</div>

@push('scripts')
<script>
function deleteRole(id) {
    if(confirm('Are you sure you want to delete this role?')) {
        fetch(`/crm/roles/${id}`, {
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
