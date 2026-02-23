@extends('layouts.app')

@section('title', 'Message Templates')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Message Templates</h1>
            <p class="text-gray-500 mt-1">Manage reusable message templates</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(!Auth::user()->canViewAllData())
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full">
                    <i class="fas fa-filter mr-1"></i> Showing My Templates Only
                </span>
            @endif
            <button onclick="openModal('createTemplateModal')" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                <i class="fas fa-plus mr-2"></i> New Template
            </button>
        </div>
    </div>
    
    <!-- Template Categories -->
    <div class="flex flex-wrap gap-2">
        <button onclick="filterByCategory('')" class="px-4 py-2 bg-whatsapp-light text-white rounded-full text-sm">All</button>
        <button onclick="filterByCategory('order_confirmation')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-full text-sm hover:bg-gray-300">Order Confirmation</button>
        <button onclick="filterByCategory('payment_reminder')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-full text-sm hover:bg-gray-300">Payment Reminder</button>
        <button onclick="filterByCategory('shipping_notification')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-full text-sm hover:bg-gray-300">Shipping</button>
        <button onclick="filterByCategory('greeting')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-full text-sm hover:bg-gray-300">Greeting</button>
        <button onclick="filterByCategory('marketing')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-full text-sm hover:bg-gray-300">Marketing</button>
        <button onclick="filterByCategory('support')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-full text-sm hover:bg-gray-300">Support</button>
    </div>
    
    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="templatesGrid">
        @forelse($templates ?? [] as $template)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden" data-category="{{ $template->category }}" data-id="{{ $template->id }}" data-name="{{ $template->name }}" data-content="{{ $template->content }}">
            <div class="p-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-8 h-8 rounded-full bg-whatsapp-light/20 flex items-center justify-center">
                            <i class="fas fa-file-alt text-whatsapp-dark text-sm"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-800">{{ $template->name }}</h3>
                            <p class="text-xs text-gray-500">{{ $template->category }}</p>
                        </div>
                    </div>
                    @if($template->is_approved)
                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Approved</span>
                    @else
                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                    @endif
                </div>
            </div>
            <div class="p-4">
                <p class="text-sm text-gray-600 line-clamp-3">{{ $template->content }}</p>
                @php $variables = is_array($template->variables) ? $template->variables : []; @endphp
                @if(count($variables) > 0)
                <div class="mt-3 flex flex-wrap gap-1">
                    @foreach($variables as $var)
                    <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded">@php echo '{{' . $var . '}}'; @endphp</span>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="p-4 border-t border-gray-100 flex justify-between items-center">
                <span class="text-xs text-gray-500">{{ $template->type }}</span>
                <div class="flex space-x-2">
                    @if(!$template->is_approved)
                    <button type="button" onclick="approveTemplate('{{ $template->id }}')" class="text-green-600 hover:text-green-900 text-sm">Approve</button>
                    @endif
                    <button onclick="previewTemplate('{{ $template->id }}')" class="text-blue-600 hover:text-blue-900 text-sm">Preview</button>
                    <button onclick="useTemplate('{{ $template->id }}')" class="text-whatsapp-dark hover:text-whatsapp-light text-sm">Use</button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-gray-500">
            <i class="fas fa-file-alt text-4xl mb-4 text-gray-300"></i>
            <p>No templates found. Create your first template!</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Create Template Modal -->
<div id="createTemplateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Create New Template</h3>
        </div>
        <form id="createTemplateForm" onsubmit="submitTemplateForm(event)">
            @csrf
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text" name="slug" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="text">Text</option>
                            <option value="image">Image</option>
                            <option value="document">Document</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="general">General</option>
                            <option value="order_confirmation">Order Confirmation</option>
                            <option value="payment_reminder">Payment Reminder</option>
                            <option value="shipping_notification">Shipping Notification</option>
                            <option value="greeting">Greeting</option>
                            <option value="marketing">Marketing</option>
                            <option value="support">Support</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea name="content" rows="6" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Hello @{{name}}, your order #@{{order_number}} has been confirmed."></textarea>
                    <p class="text-xs text-gray-500 mt-1">Use @{{variable}} for dynamic content</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Variables (comma separated)</label>
                    <input type="text" name="variables_string" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="name, order_number, total">
                </div>
            </div>
            <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('createTemplateModal')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark">Create Template</button>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewTemplateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Template Preview</h3>
            <button onclick="closeModal('previewTemplateModal')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <h4 id="previewTemplateName" class="text-xl font-bold text-gray-800 mb-4"></h4>
            <div class="bg-gray-100 p-4 rounded-lg">
                <p id="previewTemplateContent" class="text-gray-800 whitespace-pre-wrap"></p>
            </div>
        </div>
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
    
    function filterByCategory(category) {
        const cards = document.querySelectorAll('#templatesGrid > div');
        cards.forEach(card => {
            if (!category || card.dataset.category === category) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    async function submitTemplateForm(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
        // Convert formData to URLSearchParams for proper Laravel validation
        const params = new URLSearchParams();
        for (const [key, value] of formData) {
            if (key === 'variables_string' && value) {
                // Keep variables_string as is, controller will handle conversion
                params.append(key, value);
            } else if (key !== 'variables') {
                params.append(key, value);
            }
        }
        
        try {
            const response = await fetch('{{ route('crm.templates.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json'
                },
                body: params.toString()
            });
            
            const data = await response.json();
            
            if (data.success) {
                closeModal('createTemplateModal');
                form.reset();
                // Reload page to show new template
                window.location.reload();
            } else {
                alert('Error creating template: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error creating template');
        }
    }
    
    async function approveTemplate(id) {
        if (!confirm('Are you sure you want to approve this template?')) return;
        
        try {
            const response = await fetch('/crm/templates/' + id + '/approve', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error approving template: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error approving template');
        }
    }
    
    function previewTemplate(id) {
        const card = document.querySelector('#templatesGrid > div[data-id="' + id + '"]');
        if (card) {
            const name = card.dataset.name;
            const content = card.dataset.content;
            document.getElementById('previewTemplateName').textContent = name;
            document.getElementById('previewTemplateContent').textContent = content;
            openModal('previewTemplateModal');
        }
    }
    
    function useTemplate(id) {
        // Redirect to messaging with template
        window.location.href = '/messaging?template=' + id;
    }
</script>
@endpush
