@extends('layouts.app')

@section('title', 'User Management')

@section('content')
@php
use App\Models\Role;
use App\Models\User;
@endphp
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
            <p class="text-gray-500 mt-1">Manage team members and their roles</p>
        </div>
        <button onclick="openModal('create-user-modal')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i>Add User
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Users</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $users->total() }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                    <i class="fas fa-users text-indigo-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active Users</p>
                    <p class="text-2xl font-bold text-green-600">{{ $users->where('is_active', true)->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-user-check text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Roles</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $roles->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-user-tag text-blue-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold">All Users</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Roles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-medium">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($user->roles as $role)
                                <span class="px-2 py-1 text-xs rounded-full" style="background-color: {{ $role->color ?? '#6b7280' }}20; color: {{ $role->color ?? '#6b7280' }}">
                                    {{ $role->name }}
                                </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->is_active)
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="editUser({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteUser({{ $user->id }})" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No users found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <!-- Roles Section -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold">Roles & Permissions</h3>
            <div class="flex space-x-2">
                <button onclick="openModal('create-role-modal')" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                    <i class="fas fa-plus mr-1"></i>Add Role
                </button>
                <button onclick="openModal('create-permission-modal')" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                    <i class="fas fa-plus mr-1"></i>Add Permission
                </button>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6">
            @foreach($roles as $role)
            <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-semibold text-gray-800">{{ $role->name }}</h4>
                    <span class="text-xs text-gray-500">{{ $role->users->count() }} users</span>
                </div>
                <p class="text-sm text-gray-500 mb-3">{{ $role->description }}</p>
                <div class="flex flex-wrap gap-1 mb-3">
                    @foreach($role->permissions->take(5) as $permission)
                    <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">{{ $permission->name }}</span>
                    @endforeach
                    @if($role->permissions->count() > 5)
                    <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">+{{ $role->permissions->count() - 5 }} more</span>
                    @endif
                </div>
                <div class="flex space-x-2">
                    <button onclick="editRole({{ $role->id }})" class="text-sm text-indigo-600 hover:text-indigo-800">Edit</button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Create Role Modal -->
<div id="create-role-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold">Add New Role</h3>
            <button onclick="closeModal('create-role-modal')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('crm.roles.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Permissions</label>
                <select name="permissions[]" multiple class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    @foreach(App\Models\Permission::all() as $permission)
                    <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex space-x-3 pt-2">
                <button type="button" onclick="closeModal('create-role-modal')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Create Role
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Role Modal -->
<div id="edit-role-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold">Edit Role</h3>
            <button onclick="closeModal('edit-role-modal')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="edit-role-form" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" id="edit-role-name" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" id="edit-role-slug" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500" readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" id="edit-role-description" rows="2" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Permissions</label>
                <select name="permissions[]" id="edit-role-permissions" multiple class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    @foreach(App\Models\Permission::all() as $permission)
                    <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex space-x-3 pt-2">
                <button type="button" onclick="closeModal('edit-role-modal')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Update Role
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Create User Modal -->
<div id="create-user-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold">Add New User</h3>
            <button onclick="closeModal('create-user-modal')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('crm.users.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Roles</label>
                <select name="roles[]" multiple class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('create-user-modal')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Create Permission Modal -->
<div id="create-permission-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold">Add New Permission</h3>
            <button onclick="closeModal('create-permission-modal')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('crm.permissions.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Module</label>
                <input type="text" name="module" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div class="flex space-x-3 pt-2">
                <button type="button" onclick="closeModal('create-permission-modal')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Create Permission
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Permission Modal -->
<div id="edit-permission-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold">Edit Permission</h3>
            <button onclick="closeModal('edit-permission-modal')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="edit-permission-form" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" id="edit-permission-name" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" id="edit-permission-slug" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Module</label>
                <input type="text" name="module" id="edit-permission-module" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" id="edit-permission-description" rows="2" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div class="flex space-x-3 pt-2">
                <button type="button" onclick="closeModal('edit-permission-modal')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Update Permission
                </button>
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

function editUser(userId) {
    window.location.href = `/crm/users/${userId}/edit`;
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch(`/crm/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => location.reload());
    }
}

function editRole(roleId) {
    fetch("/crm/roles/" + roleId + "/data", {
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById("edit-role-name").value = data.name;
        document.getElementById("edit-role-slug").value = data.slug;
        document.getElementById("edit-role-description").value = data.description || "";
        
        const permissionsSelect = document.getElementById("edit-role-permissions");
        const rolePermissions = data.permissions || [];
        Array.from(permissionsSelect.options).forEach(option => {
            option.selected = rolePermissions.includes(parseInt(option.value));
        });
        
        document.getElementById("edit-role-form").action = "/crm/roles/" + roleId;
        
        openModal("edit-role-modal");
    });
}

function editPermission(permissionId) {
    fetch("/crm/permissions/" + permissionId + "/data", {
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById("edit-permission-name").value = data.name;
        document.getElementById("edit-permission-slug").value = data.slug;
        document.getElementById("edit-permission-description").value = data.description || "";
        document.getElementById("edit-permission-module").value = data.module || "";
        
        document.getElementById("edit-permission-form").action = "/crm/permissions/" + permissionId;
        
        openModal("edit-permission-modal");
    });
}
</script>
@endsection
