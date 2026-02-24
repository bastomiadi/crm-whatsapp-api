@extends('layouts.app')

@section('title', 'Contacts')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Contacts</h1>
            <p class="text-gray-500 mt-1">Manage your customer contacts</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(!Auth::user()->canViewAllData())
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full">
                    <i class="fas fa-filter mr-1"></i> Showing My Contacts Only
                </span>
            @endif
            <div class="relative">
                <button onclick="toggleDropdown('exportMenu')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-download mr-2"></i> Export <i class="fas fa-chevron-down ml-1"></i>
                </button>
                <div id="exportMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-20">
                    <a href="{{ route('crm.contacts.export', ['filter' => 'all']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">All Contacts</a>
                    <a href="{{ route('crm.contacts.export', ['filter' => 'assigned']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Assigned Only</a>
                    <a href="{{ route('crm.contacts.export', ['filter' => 'unassigned']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Unassigned Only</a>
                    <a href="{{ route('crm.contacts.export', ['filter' => 'recent']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Last 7 Days</a>
                </div>
            </div>
            <button onclick="openModal('bulkAssignModal')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-user-plus mr-2"></i> Assign
            </button>
            <button onclick="openModal('importModal')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-file-import mr-2"></i> Import
            </button>
            <button onclick="openModal('createModal')" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                <i class="fas fa-plus mr-2"></i> New Contact
            </button>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $totalContacts = $contacts->total();
            $contactQuery = \App\Models\Contact::query();
            if (!Auth::user()->canViewAllData()) {
                $contactQuery->where('created_by', Auth::id());
            }
            $activeContacts = (clone $contactQuery)->where('status', 'active')->count();
            $inactiveContacts = (clone $contactQuery)->where('status', 'inactive')->count();
            $blockedContacts = (clone $contactQuery)->where('status', 'blocked')->count();
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Contacts</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($totalContacts) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($activeContacts) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Inactive</p>
                    <p class="text-2xl font-bold text-gray-600">{{ number_format($inactiveContacts) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-pause-circle text-gray-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Blocked</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($blockedContacts) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <i class="fas fa-ban text-red-600"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search contacts..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            <div class="w-48">
                <select name="segment" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="">All Segments</option>
                    @foreach($segments as $segment)
                    <option value="{{ $segment->id }}" {{ request('segment') == $segment->id ? 'selected' : '' }}>{{ $segment->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors">
                <i class="fas fa-filter mr-2"></i> Filter
            </button>
        </form>
    </div>
    
    <!-- Contacts Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">All Contacts</h2>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <input type="checkbox" class="rounded border-gray-300 text-whatsapp-light focus:ring-whatsapp-light" id="selectAll">
                    <label for="selectAll" class="text-sm text-gray-600">Select All</label>
                </div>
                <div class="relative">
                    <button id="bulkActionsBtn" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50" disabled>
                        <i class="fas fa-tasks mr-1"></i> Bulk Actions
                        <i class="fas fa-chevron-down ml-1"></i>
                    </button>
                    <div id="bulkActionsMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                        <button onclick="bulkAction('export')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50">
                            <i class="fas fa-download mr-2 text-gray-400"></i> Export Selected
                        </button>
                        <button onclick="bulkAction('delete')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 text-red-600">
                            <i class="fas fa-trash mr-2"></i> Delete Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                            <span class="sr-only">Select</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Segment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tags</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Contacted</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($contacts as $contact)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <input type="checkbox" class="rounded border-gray-300 text-whatsapp-light focus:ring-whatsapp-light contact-checkbox" value="{{ $contact->id }}">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-whatsapp-light to-whatsapp-dark rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium text-sm">{{ $contact->initials }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $contact->display_name }}</p>
                                    <p class="text-sm text-gray-500">{{ $contact->email ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">{{ $contact->phone }}</td>
                        <td class="px-6 py-4">
                            @if($contact->segment)
                            <span class="px-2 py-1 text-xs font-medium rounded-full" style="background-color: {{ $contact->segment->color }}20; color: {{ $contact->segment->color }}">
                                {{ $contact->segment->name }}
                            </span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1 max-w-[150px]">
                                @forelse($contact->tags()->get() as $tag)
                                <span class="px-2 py-0.5 text-xs rounded-full" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                    {{ $tag->name }}
                                </span>
                                @empty
                                <span class="text-gray-400">-</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                @if($contact->status === 'active') bg-green-100 text-green-800
                                @elseif($contact->status === 'inactive') bg-gray-100 text-gray-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($contact->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                            {{ $contact->last_contacted_at ? $contact->last_contacted_at->diffForHumans() : 'Never' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center space-x-1">
                                <button onclick="openViewContactModal('{{ $contact->id }}', '{{ $contact->display_name }}', '{{ $contact->phone }}', '{{ $contact->email ?? '' }}', '{{ $contact->company ?? '' }}', '{{ $contact->status }}', '{{ $contact->segment->name ?? '' }}', '{{ $contact->created_at->format('d M Y') }}')" class="p-2 text-gray-500 hover:text-whatsapp-dark hover:bg-gray-100 rounded-lg transition-colors" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="openSendMessageModal('{{ $contact->id }}', '{{ $contact->phone }}', '{{ $contact->display_name }}')" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors" title="Send Message">
                                    <i class="fas fa-comment-dots"></i>
                                </button>
                                <button onclick="openEditContactModal('{{ $contact->id }}', '{{ $contact->display_name }}', '{{ $contact->phone }}', '{{ $contact->email ?? '' }}', '{{ $contact->company ?? '' }}', '{{ $contact->status }}', '{{ $contact->segment_id ?? '' }}')" class="p-2 text-gray-500 hover:text-yellow-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="openDeleteContactModal('{{ $contact->id }}', '{{ addslashes($contact->display_name) }}')" class="p-2 text-gray-500 hover:text-red-600 hover:bg-gray-100 rounded-lg transition-colors" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-users text-3xl text-gray-300"></i>
                                </div>
                                <p class="text-gray-600 font-medium">No contacts found</p>
                                <p class="text-sm text-gray-400 mt-1">Get started by creating your first contact</p>
                                <a href="{{ route('crm.contacts.create') }}" class="mt-4 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                    <i class="fas fa-plus mr-2"></i> Add Contact
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($contacts->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $contacts->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-file-import mr-2 text-whatsapp-light"></i> Import Contacts
                </h3>
                <button onclick="closeModal('importModal')" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <form action="{{ route('crm.contacts.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">CSV File</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-whatsapp-light transition-colors">
                        <input type="file" name="file" accept=".csv" required id="csvFile" class="hidden">
                        <label for="csvFile" class="cursor-pointer">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                            <p class="text-xs text-gray-400 mt-1">CSV files only</p>
                        </label>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">CSV Format:</p>
                    <code class="text-xs text-gray-500">phone, name, email, company</code>
                </div>
            </div>
            <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('importModal')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                    <i class="fas fa-upload mr-2"></i> Import
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Create Contact Modal -->
<div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-user-plus mr-2 text-whatsapp-light"></i> New Contact
                </h3>
                <button onclick="closeModal('createModal')" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <form id="createContactForm" class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" required placeholder="628123456789"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" placeholder="John Doe"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" placeholder="john@example.com"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                <input type="text" name="company" placeholder="Company Name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Segment</label>
                    <select name="segment_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                        <option value="">No Segment</option>
                        @foreach($segments as $segment)
                        <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="blocked">Blocked</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                <div class="flex flex-wrap gap-2 p-3 border border-gray-300 rounded-lg">
                    @foreach($tags as $tag)
                    <label class="inline-flex items-center px-3 py-1.5 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer">
                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="rounded border-gray-300 text-whatsapp-light focus:ring-whatsapp-light">
                        <span class="ml-2 text-sm">{{ $tag->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" placeholder="Additional notes..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"></textarea>
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeModal('createModal')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                    <i class="fas fa-save mr-2"></i> Save Contact
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Assign Modal -->
<div id="bulkAssignModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-user-plus mr-2 text-whatsapp-light"></i> Bulk Assign Contacts
                </h3>
                <button onclick="closeModal('bulkAssignModal')" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <form id="bulkAssignForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Selected Contacts</label>
                <div id="selectedCount" class="text-2xl font-bold text-whatsapp-light">0</div>
                <p class="text-xs text-gray-500">Select contacts using checkboxes above</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assign to User <span class="text-red-500">*</span></label>
                <select name="assigned_to" id="bulkAssignedTo" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="">Select User</option>
                    @php $users = \App\Models\User::all(); @endphp
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeModal('bulkAssignModal')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                    <i class="fas fa-user-plus mr-2"></i> Assign
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Delete Confirmation Modal -->
<div id="bulkDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-exclamation-triangle mr-2 text-red-500"></i> Confirm Bulk Delete
                </h3>
                <button onclick="closeModal('bulkDeleteModal')" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <p class="text-gray-600">Are you sure you want to delete <span id="deleteCount" class="font-bold text-red-500">0</span> contacts? This action cannot be undone.</p>
            <div class="flex space-x-3 pt-6">
                <button type="button" onclick="closeModal('bulkDeleteModal')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="confirmBulkDelete()" class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                    <i class="fas fa-trash mr-2"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Contact Modal -->
<div id="viewContactModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Contact Details</h3>
                <button onclick="closeModal('viewContactModal')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-whatsapp-light to-whatsapp-dark rounded-full flex items-center justify-center">
                    <span id="viewContactInitials" class="text-white font-bold text-xl"></span>
                </div>
                <div>
                    <h4 id="viewContactName" class="text-xl font-bold text-gray-800"></h4>
                    <span id="viewContactStatus" class="px-2 py-1 text-xs font-medium rounded-full"></span>
                </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Phone</span>
                    <span id="viewContactPhone" class="text-gray-800 font-medium"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Email</span>
                    <span id="viewContactEmail" class="text-gray-800"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Company</span>
                    <span id="viewContactCompany" class="text-gray-800"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Segment</span>
                    <span id="viewContactSegment" class="text-gray-800"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Created</span>
                    <span id="viewContactCreated" class="text-gray-800"></span>
                </div>
            </div>
            <div class="flex space-x-3 pt-4">
                <button onclick="closeModal('viewContactModal')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Close
                </button>
                <a id="viewContactLink" href="" class="flex-1 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors text-center">
                    View Full Profile
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Edit Contact Modal -->
<div id="editContactModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-edit mr-2 text-yellow-500"></i> Edit Contact
                </h3>
                <button onclick="closeModal('editContactModal')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <form id="editContactForm" class="p-6 space-y-4">
            <input type="hidden" name="contact_id" id="editContactId">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                <input type="text" name="phone" id="editPhone" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" id="editName" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="editEmail" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                <input type="text" name="company" id="editCompany" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Segment</label>
                    <select name="segment_id" id="editSegment" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                        <option value="">No Segment</option>
                        @foreach($segments as $segment)
                        <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="editStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="blocked">Blocked</option>
                    </select>
                </div>
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeModal('editContactModal')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors">
                    <i class="fas fa-save mr-2"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Contact Confirmation Modal -->
<div id="deleteContactModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-exclamation-triangle mr-2 text-red-500"></i> Confirm Delete
                </h3>
                <button onclick="closeModal('deleteContactModal')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <p class="text-gray-600">Are you sure you want to delete <span id="deleteContactName" class="font-bold text-red-500"></span>? This action cannot be undone.</p>
            <div class="flex space-x-3 pt-6">
                <button type="button" onclick="closeModal('deleteContactModal')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="executeDeleteContact()" class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                    <i class="fas fa-trash mr-2"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Send Message Modal -->
<div id="sendMessageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Send WhatsApp Message</h3>
                <button onclick="closeModal('sendMessageModal')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <form id="sendMessageForm" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="contactId" id="modalContactId" value="">
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-sm text-gray-500">Recipient</p>
                <p id="modalRecipientName" class="font-medium text-gray-800"></p>
                <p id="modalRecipientPhone" class="text-sm text-gray-600"></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Session *</label>
                <select name="sessionId" id="modalSessionId" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="">Select a session</option>
                    @if(isset($sessions['data']))
                        @foreach($sessions['data'] as $session)
                        <option value="{{ $session['sessionId'] }}" {{ $session['status'] === 'connected' ? '' : 'disabled' }}>
                            {{ $session['sessionId'] }} {{ $session['status'] === 'connected' ? '(Connected)' : '(' . $session['status'] . ')' }}
                        </option>
                        @endforeach
                    @endif
                </select>
            </div>
            
            <!-- Message Type Tabs -->
            <div class="flex space-x-2 border-b border-gray-200 pb-2">
                <button type="button" onclick="selectModalMessageType('text')" class="modal-type-btn px-3 py-1 text-sm rounded-md bg-whatsapp-light text-white" data-type="text">Text</button>
                <button type="button" onclick="selectModalMessageType('image')" class="modal-type-btn px-3 py-1 text-sm rounded-md hover:bg-gray-100" data-type="image">Image</button>
                <button type="button" onclick="selectModalMessageType('document')" class="modal-type-btn px-3 py-1 text-sm rounded-md hover:bg-gray-100" data-type="document">Document</button>
            </div>
            
            <!-- Text Message Fields -->
            <div id="modalTextFields" class="modal-message-fields space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                    <textarea name="message" id="modalMessage" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="Type your message here..."></textarea>
                </div>
            </div>
            
            <!-- Image Fields -->
            <div id="modalImageFields" class="modal-message-fields space-y-4 hidden">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image URL *</label>
                    <input type="url" name="imageUrl" id="modalImageUrl"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="https://example.com/image.jpg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                    <textarea name="caption" id="modalCaption" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="Image caption (optional)"></textarea>
                </div>
            </div>
            
            <!-- Document Fields -->
            <div id="modalDocumentFields" class="modal-message-fields space-y-4 hidden">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Document URL *</label>
                    <input type="url" name="documentUrl" id="modalDocumentUrl"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="https://example.com/document.pdf">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filename *</label>
                    <input type="text" name="filename" id="modalFilename"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                        placeholder="document.pdf">
                </div>
            </div>
            
            <div class="flex justify-end pt-4 space-x-3">
                <button type="button" onclick="closeModal('sendMessageModal')" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Send Message
                </button>
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

// Select all checkbox
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.contact-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkActionsBtn();
});

// Update bulk actions button state
function updateBulkActionsBtn() {
    const checked = document.querySelectorAll('.contact-checkbox:checked').length;
    const btn = document.getElementById('bulkActionsBtn');
    btn.disabled = checked === 0;
    btn.innerHTML = checked > 0 
        ? `<i class="fas fa-tasks mr-1"></i> ${checked} selected <i class="fas fa-chevron-down ml-1"></i>`
        : `<i class="fas fa-tasks mr-1"></i> Bulk Actions <i class="fas fa-chevron-down ml-1"></i>`;
}

document.querySelectorAll('.contact-checkbox').forEach(cb => {
    cb.addEventListener('change', updateBulkActionsBtn);
});

// Bulk actions menu toggle
document.getElementById('bulkActionsBtn')?.addEventListener('click', function() {
    const menu = document.getElementById('bulkActionsMenu');
    menu.classList.toggle('hidden');
});

// Close menu when clicking outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById('bulkActionsMenu');
    const btn = document.getElementById('bulkActionsBtn');
    if (!menu?.contains(e.target) && !btn?.contains(e.target)) {
        menu?.classList.add('hidden');
    }
});

function bulkAction(action) {
    const selected = Array.from(document.querySelectorAll('.contact-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) return;
    
    if (action === 'delete') {
        document.getElementById('deleteCount').textContent = selected.length;
        openModal('bulkDeleteModal');
    } else if (action === 'assign') {
        document.getElementById('selectedCount').textContent = selected.length;
        openModal('bulkAssignModal');
    }
    document.getElementById('bulkActionsMenu').classList.add('hidden');
}

// Bulk assign form submission
document.getElementById('bulkAssignForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const selected = Array.from(document.querySelectorAll('.contact-checkbox:checked')).map(cb => cb.value);
    const assignedTo = document.getElementById('bulkAssignedTo').value;
    
    if (!assignedTo) {
        alert('Please select a user to assign contacts to');
        return;
    }
    
    try {
        const response = await fetch('{{ route('crm.contacts.assign-bulk') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                contact_ids: selected,
                assigned_to: assignedTo
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast(result.message, 'success');
            closeModal('bulkAssignModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message || 'Failed to assign contacts', 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
});

// Bulk delete confirmation
async function confirmBulkDelete() {
    const selected = Array.from(document.querySelectorAll('.contact-checkbox:checked')).map(cb => cb.value);
    
    try {
        const response = await fetch('{{ route('crm.contacts.bulk-delete') }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                contact_ids: selected
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast(result.message, 'success');
            closeModal('bulkDeleteModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message || 'Failed to delete contacts', 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
}

// Dropdown toggle
function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    dropdown.classList.toggle('hidden');
}

// Toast notification
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} z-50 shadow-lg`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Create contact form submission
document.getElementById('createContactForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data.tags = formData.getAll('tags[]');
    
    try {
        const response = await fetch('{{ route("crm.contacts.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Contact created successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message || 'Failed to create contact', 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
});

async function deleteContact(id) {
    if (!confirm('Are you sure you want to delete this contact?')) return;
    
    try {
        const response = await fetch(`/crm/contacts/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Contact deleted successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message || 'Failed to delete contact', 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
}

function editContact(id) {
    window.location.href = `/crm/contacts/${id}/edit`;
}

function sendMessage(phone) {
    window.location.href = '{{ route("dashboard.messaging") }}?chatId=' + phone;
}

let currentModalMessageType = 'text';
let deleteContactId = null;

function selectModalMessageType(type) {
    currentModalMessageType = type;
    
    // Update button styles
    document.querySelectorAll('.modal-type-btn').forEach(btn => {
        btn.classList.remove('bg-whatsapp-light', 'text-white');
        btn.classList.add('hover:bg-gray-100');
        if (btn.dataset.type === type) {
            btn.classList.add('bg-whatsapp-light', 'text-white');
            btn.classList.remove('hover:bg-gray-100');
        }
    });
    
    // Show/hide fields
    document.querySelectorAll('.modal-message-fields').forEach(field => {
        field.classList.add('hidden');
    });
    document.getElementById('modal' + type.charAt(0).toUpperCase() + type.slice(1) + 'Fields').classList.remove('hidden');
}

function openSendMessageModal(contactId, phone, name) {
    if (!phone) {
        alert('No phone number available for this contact');
        return;
    }
    
    document.getElementById('modalContactId').value = contactId;
    document.getElementById('modalRecipientName').textContent = name;
    document.getElementById('modalRecipientPhone').textContent = phone;
    
    // Reset form
    document.getElementById('sendMessageForm').reset();
    selectModalMessageType('text');
    
    openModal('sendMessageModal');
}

function openViewContactModal(id, name, phone, email, company, status, segment, created) {
    document.getElementById('viewContactName').textContent = name;
    document.getElementById('viewContactPhone').textContent = phone;
    document.getElementById('viewContactEmail').textContent = email || '-';
    document.getElementById('viewContactCompany').textContent = company || '-';
    document.getElementById('viewContactSegment').textContent = segment || '-';
    document.getElementById('viewContactCreated').textContent = created;
    document.getElementById('viewContactInitials').textContent = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
    
    // Status badge
    const statusEl = document.getElementById('viewContactStatus');
    statusEl.textContent = status.charAt(0).toUpperCase() + status.slice(1);
    statusEl.className = 'px-2 py-1 text-xs font-medium rounded-full';
    if (status === 'active') {
        statusEl.classList.add('bg-green-100', 'text-green-800');
    } else if (status === 'inactive') {
        statusEl.classList.add('bg-gray-100', 'text-gray-800');
    } else {
        statusEl.classList.add('bg-red-100', 'text-red-800');
    }
    
    document.getElementById('viewContactLink').href = '/crm/contacts/' + id;
    
    openModal('viewContactModal');
}

function openEditContactModal(id, name, phone, email, company, status, segmentId) {
    document.getElementById('editContactId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editPhone').value = phone;
    document.getElementById('editEmail').value = email || '';
    document.getElementById('editCompany').value = company || '';
    document.getElementById('editStatus').value = status;
    document.getElementById('editSegment').value = segmentId || '';
    
    openModal('editContactModal');
}

function openDeleteContactModal(id, name) {
    document.getElementById('deleteContactName').textContent = name;
    deleteContactId = id;
    openModal('deleteContactModal');
}

async function executeDeleteContact() {
    if (!deleteContactId) return;
    
    try {
        const response = await fetch(`/crm/contacts/${deleteContactId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Contact deleted successfully', 'success');
            closeModal('deleteContactModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message || 'Failed to delete contact', 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
}

// Edit contact form submission
document.getElementById('editContactForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const contactId = document.getElementById('editContactId').value;
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch(`/crm/contacts/${contactId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Contact updated successfully', 'success');
            closeModal('editContactModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message || 'Failed to update contact', 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
});

// Send message form submission
document.getElementById('sendMessageForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const sessionId = formData.get('sessionId');
    const chatId = document.getElementById('modalRecipientPhone').textContent;
    const typingTime = 0;
    const replyTo = null;
    
    let endpoint = '';
    let data = { sessionId, chatId, typingTime, replyTo };
    
    switch (currentModalMessageType) {
        case 'text':
            endpoint = '{{ route("api.send.text") }}';
            data.message = formData.get('message');
            break;
        case 'image':
            endpoint = '{{ route("api.send.image") }}';
            data.imageUrl = formData.get('imageUrl');
            data.caption = formData.get('caption');
            break;
        case 'document':
            endpoint = '{{ route("api.send.document") }}';
            data.documentUrl = formData.get('documentUrl');
            data.filename = formData.get('filename');
            break;
    }
    
    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Message sent successfully!', 'success');
            closeModal('sendMessageModal');
        } else {
            showToast(result.message || 'Failed to send message', 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
});

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} z-50 shadow-lg`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>
@endpush
