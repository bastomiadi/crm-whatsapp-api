@extends('layouts.app')

@section('title', 'Tasks & Follow-ups')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tasks & Follow-ups</h1>
            <p class="text-gray-600">Manage your tasks and follow-ups</p>
        </div>
        <button onclick="openModal('taskModal')" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-green-600 transition-colors flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Add Task</span>
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-sm text-gray-500">Total Tasks</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-sm text-gray-500">Overdue</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['overdue'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-sm text-gray-500">Due Today</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['due_today'] }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4 items-center">
            @if(!$canViewAll)
            <div class="flex items-center px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Showing My Tasks Only
            </div>
            @endif
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <select name="priority" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="all" {{ $priority === 'all' ? 'selected' : '' }}>All Priority</option>
                <option value="low" {{ $priority === 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ $priority === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ $priority === 'high' ? 'selected' : '' }}>High</option>
                <option value="urgent" {{ $priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
            </select>
            <select name="assigned" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="all" {{ $assigned === 'all' ? 'selected' : '' }}>All Assignments</option>
                <option value="me" {{ $assigned === 'me' ? 'selected' : '' }}>Assigned to Me</option>
                @foreach($users as $user)
                <option value="{{ $user->id }}" {{ $assigned == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Tasks List -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Task</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tasks as $task)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <p class="font-medium text-gray-800">{{ $task->title }}</p>
                        @if($task->description)
                        <p class="text-sm text-gray-500 truncate max-w-xs">{{ $task->description }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($task->contact)
                        <a href="{{ route('crm.contacts.show', $task->contact->id) }}" class="text-green-600 hover:underline">
                            {{ $task->contact->name }}
                        </a>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="{{ $task->is_overdue ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                            {{ $task->due_date->format('M d, Y') }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $task->priority_color }}-100 text-{{ $task->priority_color }}-800">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <select onchange="updateTaskStatus({{ $task->id }}, this.value)" class="text-sm border-0 bg-{{ $task->status_color }}-100 text-{{ $task->status_color }}-800 rounded-full px-2 py-1">
                            <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $task->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </td>
                    <td class="px-6 py-4">
                        {{ $task->assignedTo->name ?? 'Unassigned' }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="editTask({{ $task->id }}, '{{ $task->title }}', '{{ $task->description }}', '{{ $task->due_date->format('Y-m-d') }}', '{{ $task->priority }}', '{{ $task->contact_id }}', '{{ $task->assigned_to }}')" class="p-2 text-gray-500 hover:text-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                        <button onclick="deleteTask({{ $task->id }})" class="p-2 text-gray-500 hover:text-red-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        No tasks found. Create your first task!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($tasks->hasPages())
    <div class="flex justify-center">
        {{ $tasks->links() }}
    </div>
    @endif
</div>

<!-- Task Modal -->
<div id="taskModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
        <div class="p-6">
            <h2 id="modalTitle" class="text-xl font-semibold text-gray-800 mb-4">Add New Task</h2>
            <form id="taskForm">
                @csrf
                <input type="hidden" id="taskId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input type="text" id="taskTitle" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="taskDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date *</label>
                            <input type="date" id="taskDueDate" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority *</label>
                            <select id="taskPriority" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact</label>
                            <select id="taskContact" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                <option value="">No Contact</option>
                                @foreach($contacts as $contact)
                                <option value="{{ $contact->id }}">{{ $contact->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                            <select id="taskAssignedTo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                <option value="">Unassigned</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('taskModal')" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-green-600">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function editTask(id, title, description, dueDate, priority, contactId, assignedTo) {
    document.getElementById('modalTitle').textContent = 'Edit Task';
    document.getElementById('taskId').value = id;
    document.getElementById('taskTitle').value = title;
    document.getElementById('taskDescription').value = description || '';
    document.getElementById('taskDueDate').value = dueDate;
    document.getElementById('taskPriority').value = priority;
    document.getElementById('taskContact').value = contactId || '';
    document.getElementById('taskAssignedTo').value = assignedTo || '';
    openModal('taskModal');
}

document.getElementById('taskForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const taskId = document.getElementById('taskId').value;
    const data = {
        title: document.getElementById('taskTitle').value,
        description: document.getElementById('taskDescription').value,
        due_date: document.getElementById('taskDueDate').value,
        priority: document.getElementById('taskPriority').value,
        contact_id: document.getElementById('taskContact').value || null,
        assigned_to: document.getElementById('taskAssignedTo').value || null,
    };
    const url = taskId ? `/crm/tasks/${taskId}` : '/crm/tasks';
    const method = taskId ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) { closeModal('taskModal'); location.reload(); }
        else { alert(result.message || 'Error saving task'); }
    } catch (error) { alert('Error saving task'); }
});

async function updateTaskStatus(taskId, status) {
    try {
        const response = await fetch(`/crm/tasks/${taskId}/status`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ status })
        });
        const result = await response.json();
        if (result.success) { location.reload(); }
    } catch (error) { alert('Error updating status'); }
}

async function deleteTask(id) {
    if (!confirm('Delete this task?')) return;
    try {
        const response = await fetch(`/crm/tasks/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const result = await response.json();
        if (result.success) { location.reload(); }
    } catch (error) { alert('Error deleting task'); }
}
</script>
@endpush
@endsection
