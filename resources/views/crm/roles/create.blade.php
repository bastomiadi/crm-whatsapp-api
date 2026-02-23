@extends('layouts.app')

@section('title', 'Create Role')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Create Role</h1>
    <p class="text-gray-600">Add a new role with permissions</p>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form method="POST" action="{{ route('crm.roles.store') }}">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Role Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                @error('slug')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="color" class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                <input type="color" name="color" id="color" value="{{ old('color', '#6366f1') }}"
                    class="w-full h-10 border border-gray-300 rounded-lg cursor-pointer">
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_default" id="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}
                    class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                <label for="is_default" class="ml-2 text-sm text-gray-700">Set as default role for new users</label>
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-800 mb-4">Permissions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($permissions as $module => $modulePermissions)
                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium text-gray-700 mb-2 capitalize">{{ str_replace('_', ' ', $module) }}</h4>
                        <div class="space-y-2">
                            @foreach($modulePermissions as $permission)
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                        {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-600">{{ $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">No permissions available</p>
                @endforelse
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('crm.roles.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                Create Role
            </button>
        </div>
    </form>
</div>
@endsection
