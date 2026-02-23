@extends('layouts.app')

@section('title', 'Create Permission')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Create Permission</h1>
    <p class="text-gray-600">Add a new permission</p>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form method="POST" action="{{ route('crm.permissions.store') }}">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Permission Name</label>
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
                <label for="module" class="block text-sm font-medium text-gray-700 mb-2">Module</label>
                <select name="module" id="module" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Select Module</option>
                    <option value="contacts">Contacts</option>
                    <option value="products">Products</option>
                    <option value="orders">Orders</option>
                    <option value="deals">Deals</option>
                    <option value="campaigns">Campaigns</option>
                    <option value="chat">Chat</option>
                    <option value="tickets">Tickets</option>
                    <option value="automations">Automations</option>
                    <option value="reports">Reports</option>
                    <option value="users">Users</option>
                    <option value="settings">Settings</option>
                    <option value="surveys">Surveys</option>
                </select>
                @error('module')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('crm.permissions.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                Create Permission
            </button>
        </div>
    </form>
</div>
@endsection
