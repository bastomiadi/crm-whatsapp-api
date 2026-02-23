@extends('layouts.app')

@section('title', 'Edit Permission')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Edit Permission</h1>
    <p class="text-gray-600">Update permission details</p>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form method="POST" action="{{ route('crm.permissions.update', $permission) }}">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Permission Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $permission->name) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $permission->slug) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                @error('slug')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="module" class="block text-sm font-medium text-gray-700 mb-2">Module</label>
                <select name="module" id="module" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Select Module</option>
                    <option value="contacts" {{ old('module', $permission->module) == 'contacts' ? 'selected' : '' }}>Contacts</option>
                    <option value="products" {{ old('module', $permission->module) == 'products' ? 'selected' : '' }}>Products</option>
                    <option value="orders" {{ old('module', $permission->module) == 'orders' ? 'selected' : '' }}>Orders</option>
                    <option value="deals" {{ old('module', $permission->module) == 'deals' ? 'selected' : '' }}>Deals</option>
                    <option value="campaigns" {{ old('module', $permission->module) == 'campaigns' ? 'selected' : '' }}>Campaigns</option>
                    <option value="chat" {{ old('module', $permission->module) == 'chat' ? 'selected' : '' }}>Chat</option>
                    <option value="tickets" {{ old('module', $permission->module) == 'tickets' ? 'selected' : '' }}>Tickets</option>
                    <option value="automations" {{ old('module', $permission->module) == 'automations' ? 'selected' : '' }}>Automations</option>
                    <option value="reports" {{ old('module', $permission->module) == 'reports' ? 'selected' : '' }}>Reports</option>
                    <option value="users" {{ old('module', $permission->module) == 'users' ? 'selected' : '' }}>Users</option>
                    <option value="settings" {{ old('module', $permission->module) == 'settings' ? 'selected' : '' }}>Settings</option>
                    <option value="surveys" {{ old('module', $permission->module) == 'surveys' ? 'selected' : '' }}>Surveys</option>
                </select>
                @error('module')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('description', $permission->description) }}</textarea>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('crm.permissions.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                Update Permission
            </button>
        </div>
    </form>
</div>
@endsection
