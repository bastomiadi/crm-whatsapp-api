@extends('layouts.app')

@section('title', 'Quick Replies')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Quick Replies</h1>
            <p class="text-gray-500 mt-1">Manage canned responses for faster customer communication</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(!Auth::user()->canViewAllData())
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full">
                    <i class="fas fa-filter mr-1"></i> Showing My Quick Replies Only
                </span>
            @endif
            <button onclick="openModal('createModal')" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                <i class="fas fa-plus mr-2"></i> New Quick Reply
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $quickReplyQuery = \App\Models\QuickReply::query();
            if (!Auth::user()->canViewAllData() && \Schema::hasColumn('quick_replies', 'created_by')) {
                $quickReplyQuery->where('created_by', Auth::id());
            }
            $totalReplies = (clone $quickReplyQuery)->count();
            $todayReplies = (clone $quickReplyQuery)->whereDate('created_at', today())->count();
            $categories = (clone $quickReplyQuery)->distinct()->pluck('category')->filter()->count();
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Quick Replies</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($totalReplies) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-reply text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Added Today</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($todayReplies) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-plus text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Categories</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($categories) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-folder text-purple-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">This Week</p>
                    @php
                        $weekQuery = \App\Models\QuickReply::where('created_at', '>=', now()->subDays(7));
                        if (!Auth::user()->canViewAllData() && \Schema::hasColumn('quick_replies', 'created_by')) {
                            $weekQuery->where('created_by', Auth::id());
                        }
                        $weekReplies = $weekQuery->count();
                    @endphp
                    <p class="text-2xl font-bold text-orange-600">{{ number_format($weekReplies) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                    <i class="fas fa-calendar-week text-orange-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" id="searchInput" placeholder="Search quick replies..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            <div class="w-48">
                <select id="categoryFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                    <option value="">All Categories</option>
                    @php $categories = \App\Models\QuickReply::distinct()->pluck('category')->filter(); @endphp
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Quick Replies Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="quickRepliesGrid">
        @forelse($replies as $reply)
        <div class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow cursor-pointer" 
             onclick="useQuickReply({{ $reply->id }})"
             data-category="{{ $reply->category }}"
             data-search="{{ strtolower($reply->name . ' ' . strip_tags($reply->content)) }}"
             id="quickReply_{{ $reply->id }}">
            
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-800">{{ $reply->name }}</h3>
                    @if($reply->category)
                    <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full">{{ $reply->category }}</span>
                    @endif
                </div>
                <div class="flex items-center space-x-1">
                    <button onclick="event.stopPropagation(); copyQuickReply({{ $reply->id }})" 
                            class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg" title="Copy">
                        <i class="fas fa-copy text-sm"></i>
                    </button>
                    <button onclick="event.stopPropagation(); editReply({{ $reply->id }})" 
                            class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-gray-100 rounded-lg" title="Edit">
                        <i class="fas fa-edit text-sm"></i>
                    </button>
                    <button onclick="event.stopPropagation(); deleteReply({{ $reply->id }})" 
                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-gray-100 rounded-lg" title="Delete">
                        <i class="fas fa-trash text-sm"></i>
                    </button>
                </div>
            </div>
            
            <p class="text-sm text-gray-600 line-clamp-3 mb-3 quick-reply-content">{{ $reply->content }}</p>
            
            <div class="flex items-center justify-between text-xs text-gray-500 pt-2 border-t">
                <span><i class="fas fa-tag mr-1"></i>{{ $reply->category ?? 'Uncategorized' }}</span>
                <span><i class="fas fa-clock mr-1"></i>{{ $reply->created_at->diffForHumans() }}</span>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-gray-500">
            <i class="fas fa-reply text-5xl mb-4 text-gray-300"></i>
            <p class="text-lg font-medium">No quick replies found</p>
            <p class="text-sm text-gray-400 mt-1">Create your first quick reply to get started</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($replies->hasPages())
    <div class="bg-white rounded-xl shadow-sm px-6 py-4">
        {{ $replies->links() }}
    </div>
    @endif
</div>

<!-- Create/Edit Modal -->
<div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-plus mr-2 text-whatsapp-light"></i> New Quick Reply
                </h3>
                <button onclick="closeModal('createModal')" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <form id="quickReplyForm" class="p-6 space-y-4">
            <input type="hidden" name="id" id="replyId">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="replyName" required placeholder="e.g., Greeting Message"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <input type="text" name="category" id="replyCategory" placeholder="e.g., Greetings, Support, Sales"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"
                    list="categoryList">
                <datalist id="categoryList">
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}">
                    @endforeach
                </datalist>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Content <span class="text-red-500">*</span></label>
                <textarea name="content" id="replyContent" rows="4" required placeholder="Type your quick reply message..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent"></textarea>
            </div>
            
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeModal('createModal')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                    <i class="fas fa-save mr-2"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Quick Reply Preview</h3>
                <button onclick="closeModal('previewModal')" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <h4 id="previewTitle" class="font-semibold text-gray-800 mb-2"></h4>
            <div class="bg-gray-50 rounded-lg p-4">
                <p id="previewContent" class="text-gray-700 whitespace-pre-wrap"></p>
            </div>
            <div class="mt-4 text-sm text-gray-500">
                <span id="previewCategory"></span>
            </div>
        </div>
        <div class="p-6 border-t border-gray-200 flex space-x-3">
            <button onclick="copyToClipboard(document.getElementById('previewContent').textContent)" 
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-copy mr-2"></i> Copy
            </button>
            <button onclick="closeModal('previewModal'); openModal('createModal')" 
                    class="flex-1 px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                <i class="fas fa-edit mr-2"></i> Edit
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Modal functions
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.getElementById(id).classList.add('flex');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).classList.remove('flex');
    resetForm();
}

function resetForm() {
    document.getElementById('quickReplyForm').reset();
    document.getElementById('replyId').value = '';
    document.querySelector('#createModal h3').innerHTML = '<i class="fas fa-plus mr-2 text-whatsapp-light"></i> New Quick Reply';
}

// Search and filter
document.getElementById('searchInput')?.addEventListener('input', filterReplies);
document.getElementById('categoryFilter')?.addEventListener('change', filterReplies);

function filterReplies() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    
    document.querySelectorAll('#quickRepliesGrid > div').forEach(card => {
        const cardSearch = card.dataset.search;
        const cardCategory = card.dataset.category;
        
        const matchSearch = !search || cardSearch.includes(search);
        const matchCategory = !category || cardCategory === category;
        
        card.style.display = matchSearch && matchCategory ? '' : 'none';
    });
}

// Quick reply actions
async function useQuickReply(id) {
    try {
        const response = await fetch(`/crm/quick-replies/${id}`);
        const data = await response.json();
        
        if (data.success) {
            // Show preview
            document.getElementById('previewTitle').textContent = data.data.name;
            document.getElementById('previewContent').textContent = data.data.content;
            document.getElementById('previewCategory').textContent = data.data.category || 'Uncategorized';
            
            // Store ID for editing
            document.getElementById('previewModal').dataset.editId = id;
            
            openModal('previewModal');
        }
    } catch (error) {
        showToast('Error loading quick reply', 'error');
    }
}

function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Copied to clipboard!', 'success');
        }).catch(() => {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    // Fallback for browsers without clipboard API
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand('copy');
        showToast('Copied to clipboard!', 'success');
    } catch (err) {
        showToast('Failed to copy', 'error');
    }
    document.body.removeChild(textarea);
}

async function copyQuickReply(id) {
    const card = document.getElementById('quickReply_' + id);
    if (card) {
        const contentElement = card.querySelector('.quick-reply-content');
        if (contentElement) {
            copyToClipboard(contentElement.textContent);
        } else {
            // Fallback: try to get from preview content
            const previewContent = document.getElementById('previewContent');
            if (previewContent && previewContent.textContent) {
                copyToClipboard(previewContent.textContent);
            }
        }
    }
}

function editReply(id) {
    fetch(`/crm/quick-replies/${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const reply = data.data;
                document.getElementById('replyId').value = reply.id;
                document.getElementById('replyName').value = reply.name;
                document.getElementById('replyCategory').value = reply.category || '';
                document.getElementById('replyContent').value = reply.content;
                
                document.querySelector('#createModal h3').innerHTML = '<i class="fas fa-edit mr-2 text-whatsapp-light"></i> Edit Quick Reply';
                openModal('createModal');
            }
        });
}

async function deleteReply(id) {
    if (!confirm('Are you sure you want to delete this quick reply?')) return;
    
    try {
        const response = await fetch(`/crm/quick-replies/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Quick reply deleted', 'success');
            location.reload();
        } else {
            showToast(result.message || 'Failed to delete', 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
}

// Form submission
document.getElementById('quickReplyForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const id = formData.get('id');
    const url = id ? `/crm/quick-replies/${id}` : '{{ route('crm.quick-replies.store') }}';
    const method = id ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(id ? 'Quick reply updated' : 'Quick reply created', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message || 'Failed to save', 'error');
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
