@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Profile Settings</h1>
        <p class="text-gray-600">Manage your account information</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Avatar Section -->
            <div class="mb-6 flex items-center space-x-4">
                <div class="relative">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-24 h-24 rounded-full object-cover border-4 border-primary-100">
                    @else
                        <div class="w-24 h-24 rounded-full bg-primary-100 flex items-center justify-center border-4 border-primary-200">
                            <span class="text-3xl font-bold text-primary-600">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </div>
                    @endif
                    <label for="avatar" class="absolute bottom-0 right-0 bg-primary-600 text-white p-2 rounded-full cursor-pointer hover:bg-primary-700 transition-colors shadow-lg">
                        <i class="fas fa-camera text-sm"></i>
                        <input type="file" name="avatar" id="avatar" class="hidden" accept="image/*">
                    </label>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-800">Profile Photo</h3>
                    <p class="text-sm text-gray-500">Click the camera icon to upload a new photo</p>
                    <p class="text-xs text-gray-400 mt-1">Max size: 2MB. Formats: JPG, PNG, GIF, WebP</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Change Password (Optional)</h3>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <input type="password" name="password" id="password" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <div class="mt-6 bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-800 mb-4">Your Roles</h3>
        <div class="flex flex-wrap gap-2">
            @forelse($user->roles as $role)
                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">
                    {{ $role->name }}
                </span>
            @empty
                <p class="text-gray-500">No roles assigned</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
