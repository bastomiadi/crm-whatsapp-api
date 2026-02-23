@extends('layouts.app')

@section('title', 'Groups')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Groups</h1>
            <p class="text-gray-500 mt-1">Manage WhatsApp groups</p>
        </div>
        <button onclick="openCreateGroupModal()" class="flex items-center space-x-2 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Create Group</span>
        </button>
    </div>
    
    <!-- Session Selection -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-end space-x-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Session</label>
                <select id="groupSessionId" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="">Select a session</option>
                    @if(isset($sessions['data']))
                        @foreach($sessions['data'] as $session)
                        <option value="{{ $session['sessionId'] }}">{{ $session['sessionId'] }} {{ $session['status'] === 'connected' ? '(Connected)' : '(' . $session['status'] . ')' }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <button onclick="loadGroups()" class="px-6 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                Load Groups
            </button>
        </div>
    </div>
    
    <!-- Groups List -->
    <div id="groupsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-full bg-white rounded-xl shadow-sm p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">No Groups Loaded</h3>
            <p class="text-gray-500">Select a session to view and manage groups</p>
        </div>
    </div>
</div>

<!-- Create Group Modal -->
<div id="createGroupModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800">Create New Group</h2>
                <button onclick="closeCreateGroupModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <form id="createGroupForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Group Name *</label>
                <input type="text" name="name" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                    placeholder="My New Group">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Participants (one per line) *</label>
                <textarea name="participants" rows="4" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent font-mono text-sm"
                    placeholder="628123456789&#10;628987654321"></textarea>
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeCreateGroupModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark">
                    Create Group
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Group Details Modal -->
<div id="groupDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 sticky top-0 bg-white">
            <div class="flex items-center justify-between">
                <h2 id="groupDetailsTitle" class="text-xl font-bold text-gray-800">Group Details</h2>
                <button onclick="closeGroupDetailsModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div id="groupDetailsContent" class="p-6">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentSessionId = null;
    let currentGroupId = null;
    let groups = [];
    
    function openCreateGroupModal() {
        if (!document.getElementById('groupSessionId').value) {
            showToast('Please select a session first', 'error');
            return;
        }
        document.getElementById('createGroupModal').classList.remove('hidden');
    }
    
    function closeCreateGroupModal() {
        document.getElementById('createGroupModal').classList.add('hidden');
        document.getElementById('createGroupForm').reset();
    }
    
    function closeGroupDetailsModal() {
        document.getElementById('groupDetailsModal').classList.add('hidden');
    }
    
    async function loadGroups() {
        const sessionId = document.getElementById('groupSessionId').value;
        
        if (!sessionId) {
            showToast('Please select a session', 'error');
            return;
        }
        
        currentSessionId = sessionId;
        
        try {
            const response = await fetch('{{ route("api.groups") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sessionId })
            });
            
            const result = await response.json();
            
            if (result.success && result.data) {
                groups = result.data;
                renderGroups(groups);
            } else {
                document.getElementById('groupsContainer').innerHTML = '<div class="col-span-full text-center text-gray-500 py-8">No groups found</div>';
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    }
    
    function renderGroups(groupList) {
        let html = '';
        
        groupList.forEach(group => {
            const id = group.id || '';
            const subject = group.subject || 'Unknown Group';
            const participants = group.participants || [];
            const creation = group.creation ? new Date(group.creation * 1000).toLocaleDateString() : 'Unknown';
            
            html += `
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">${subject}</h3>
                                <p class="text-sm text-gray-500">${participants.length} participants</p>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500">
                            <p>Created: ${creation}</p>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <button onclick="viewGroupDetails('${id}')" class="text-whatsapp-dark hover:underline text-sm">
                            View Details
                        </button>
                        <button onclick="leaveGroup('${id}')" class="text-red-600 hover:underline text-sm">
                            Leave Group
                        </button>
                    </div>
                </div>
            `;
        });
        
        document.getElementById('groupsContainer').innerHTML = html || '<div class="col-span-full text-center text-gray-500 py-8">No groups found</div>';
    }
    
    document.getElementById('createGroupForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const name = formData.get('name');
        const participantsText = formData.get('participants');
        const participants = participantsText.split('\n').map(p => p.trim()).filter(p => p);
        
        try {
            const response = await fetch('{{ route("api.groups.create") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    sessionId: currentSessionId,
                    name,
                    participants
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Group created successfully', 'success');
                closeCreateGroupModal();
                loadGroups();
            } else {
                showToast(result.message || 'Failed to create group', 'error');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    });
    
    async function viewGroupDetails(groupId) {
        currentGroupId = groupId;
        
        try {
            const response = await fetch('{{ route("api.groups.metadata") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sessionId: currentSessionId, groupId })
            });
            
            const result = await response.json();
            
            if (result.success && result.data) {
                renderGroupDetails(result.data);
                document.getElementById('groupDetailsModal').classList.remove('hidden');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    }
    
    function renderGroupDetails(group) {
        const subject = group.subject || 'Unknown';
        const description = group.desc || 'No description';
        const participants = group.participants || [];
        
        let html = `
            <div class="space-y-6">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">${subject}</h3>
                        <p class="text-sm text-gray-500">${participants.length} participants</p>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Description</h4>
                    <p class="text-gray-600 bg-gray-50 rounded-lg p-3">${description}</p>
                </div>
                
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Participants</h4>
                    <div class="max-h-48 overflow-y-auto space-y-2">
                        ${participants.map(p => {
                            const id = p.id || '';
                            const isAdmin = p.admin;
                            return `
                                <div class="flex items-center justify-between bg-gray-50 rounded-lg p-2">
                                    <span class="text-sm">${id.split('@')[0]}</span>
                                    ${isAdmin ? '<span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded">' + isAdmin + '</span>' : ''}
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-gray-200">
                    <button onclick="showAddParticipantsForm()" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark text-sm">
                        Add Participants
                    </button>
                    <button onclick="getInviteCode()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                        Get Invite Link
                    </button>
                    <button onclick="showUpdateSubjectForm()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                        Change Name
                    </button>
                    <button onclick="showUpdateDescForm()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                        Change Description
                    </button>
                </div>
            </div>
        `;
        
        document.getElementById('groupDetailsTitle').textContent = subject;
        document.getElementById('groupDetailsContent').innerHTML = html;
    }
    
    async function leaveGroup(groupId) {
        if (!confirm('Are you sure you want to leave this group?')) return;
        
        try {
            const response = await fetch('{{ route("api.groups.leave") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sessionId: currentSessionId, groupId })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Left group successfully', 'success');
                loadGroups();
            } else {
                showToast(result.message || 'Failed to leave group', 'error');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    }
    
    async function getInviteCode() {
        try {
            const response = await fetch('{{ route("api.groups.invite-code") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sessionId: currentSessionId, groupId: currentGroupId })
            });
            
            const result = await response.json();
            
            if (result.success && result.data) {
                alert('Invite Link: ' + result.data.inviteLink);
            } else {
                showToast(result.message || 'Failed to get invite code', 'error');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    }
    
    function showAddParticipantsForm() {
        const phones = prompt('Enter phone numbers (comma separated):');
        if (!phones) return;
        
        const participants = phones.split(',').map(p => p.trim()).filter(p => p);
        addParticipants(participants);
    }
    
    async function addParticipants(participants) {
        try {
            const response = await fetch('{{ route("api.groups.participants.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sessionId: currentSessionId, groupId: currentGroupId, participants })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Participants added successfully', 'success');
                viewGroupDetails(currentGroupId);
            } else {
                showToast(result.message || 'Failed to add participants', 'error');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    }
    
    function showUpdateSubjectForm() {
        const subject = prompt('Enter new group name:');
        if (!subject) return;
        
        updateGroupSubject(subject);
    }
    
    async function updateGroupSubject(subject) {
        try {
            const response = await fetch('{{ route("api.groups.subject") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sessionId: currentSessionId, groupId: currentGroupId, subject })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Group name updated', 'success');
                viewGroupDetails(currentGroupId);
            } else {
                showToast(result.message || 'Failed to update group name', 'error');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    }
    
    function showUpdateDescForm() {
        const description = prompt('Enter new group description:');
        if (!description) return;
        
        updateGroupDescription(description);
    }
    
    async function updateGroupDescription(description) {
        try {
            const response = await fetch('{{ route("api.groups.description") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sessionId: currentSessionId, groupId: currentGroupId, description })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Group description updated', 'success');
                viewGroupDetails(currentGroupId);
            } else {
                showToast(result.message || 'Failed to update description', 'error');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    }
</script>
@endpush
@endsection
