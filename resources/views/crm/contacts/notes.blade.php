@extends('layouts.app')

@section('title', 'Contact Notes - ' . $contact->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('crm.contacts.show', $contact->id) }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Notes for {{ $contact->name }}</h1>
                <p class="text-gray-600">{{ $contact->phone }} • {{ $contact->email }}</p>
            </div>
        </div>
    </div>

    <!-- Contact Info Card -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ ucfirst($contact->status) }}</span>
            </div>
            <div>
                <p class="text-sm text-gray-500">Segment</p>
                <p class="font-medium">{{ $contact->segment->name ?? 'No Segment' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Assigned To</p>
                <p class="font-medium">{{ $contact->assignedTo->name ?? 'Unassigned' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Orders</p>
                <p class="font-medium">{{ $contact->orders->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Add Note Form -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Add New Note</h2>
        <form id="noteForm">
            @csrf
            <div class="space-y-4">
                <div>
                    <textarea id="noteContent" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Write your note here..."></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-green-700 transition-colors">
                        Add Note
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Notes List -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Notes ({{ count($notes) }})</h2>
        
        @if(count($notes) > 0)
        <div class="space-y-4">
            @foreach(array_reverse($notes) as $note)
            <div class="border-b border-gray-100 pb-4 last:border-0" data-note-id="{{ $note['id'] }}">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                            <span class="text-sm font-medium text-green-700">{{ substr($note['created_by'] ?? 'U', 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $note['created_by'] ?? 'Unknown' }}</p>
                            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($note['created_at'])->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    <button onclick="deleteNote('{{ $note['id'] }}')" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
                <div class="mt-3 text-gray-700 whitespace-pre-wrap">{{ $note['content'] }}</div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <p class="text-gray-500">No notes yet. Add your first note above!</p>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.getElementById('noteForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const note = document.getElementById('noteContent').value;
    
    if (!note.trim()) {
        alert('Please enter a note');
        return;
    }
    
    try {
        const response = await fetch('{{ route("crm.contacts.notes.store", $contact->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ note })
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('noteContent').value = '';
            location.reload();
        } else {
            alert(data.message || 'Error adding note');
        }
    } catch (error) {
        alert('Error adding note');
    }
});

async function deleteNote(noteId) {
    if (!confirm('Are you sure you want to delete this note?')) return;
    
    try {
        const response = await fetch(`/crm/contacts/{{ $contact->id }}/notes/${noteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error deleting note');
        }
    } catch (error) {
        alert('Error deleting note');
    }
}
</script>
@endpush
@endsection
