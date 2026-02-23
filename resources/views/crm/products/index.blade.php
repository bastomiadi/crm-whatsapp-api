@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Products</h1>
            <p class="text-gray-500 mt-1">Manage product catalog</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(!Auth::user()->canViewAllData())
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full">
                    <i class="fas fa-filter mr-1"></i> Showing My Products Only
                </span>
            @endif
            <a href="{{ route('crm.products.export') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-download mr-2"></i> Export
            </a>
            <button onclick="openModal('createProductModal')" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                <i class="fas fa-plus mr-2"></i> New Product
            </button>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $productStatsQuery = \App\Models\Product::query();
            if (!Auth::user()->canViewAllData()) {
                $productStatsQuery->where('created_by', Auth::id());
            }
            $totalProducts = (clone $productStatsQuery)->count();
            $active = (clone $productStatsQuery)->where('is_active', true)->count();
            $inactive = (clone $productStatsQuery)->where('is_active', false)->count();
            $categories = (clone $productStatsQuery)->distinct()->pluck('category')->filter()->count();
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active Products</p>
                    <p class="text-2xl font-bold text-green-600">{{ $active }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-box text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Inactive</p>
                    <p class="text-2xl font-bold text-gray-600">{{ $inactive }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-box text-gray-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Categories</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $categories }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-tags text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Value</p>
                    <p class="text-2xl font-bold text-purple-600">Rp {{ number_format(collect($products)->sum('price'), 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-money-bill text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex flex-wrap gap-4">
            <select id="categoryFilter" class="px-4 py-2 border border-gray-300 rounded-lg" onchange="filterProducts()">
                <option value="">All Categories</option>
                @php
                    $categories = $products->pluck('category')->filter()->unique()->sort();
                @endphp
                @foreach($categories as $category)
                <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>
            <input type="text" id="searchFilter" placeholder="Search products..." class="px-4 py-2 border border-gray-300 rounded-lg flex-1" onkeyup="filterProducts()">
        </div>
    </div>
    
    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" id="productsGrid">
        @forelse($products as $product)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden" data-category="{{ $product->category }}" data-name="{{ strtolower($product->name) }}">
            @if($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-40 object-cover">
            @else
            <div class="w-full h-40 bg-gray-100 flex items-center justify-center">
                <i class="fas fa-box text-4xl text-gray-300"></i>
            </div>
            @endif
            <div class="p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $product->name }}</h3>
                        <p class="text-xs text-gray-500">{{ $product->sku }}</p>
                    </div>
                    @if($product->is_active)
                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                    @else
                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">Inactive</span>
                    @endif
                </div>
                <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $product->description }}</p>
                <div class="mt-3 flex items-center justify-between">
                    <span class="text-lg font-bold text-whatsapp-dark">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                    <span class="text-sm text-gray-500">Stock: {{ $product->stock }}</span>
                </div>
                @if($product->category)
                <span class="inline-block mt-2 px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">{{ $product->category }}</span>
                @endif
            </div>
            <div class="p-4 border-t border-gray-100 flex justify-between">
                <button onclick="editProduct('{{ $product->id }}')" class="text-blue-600 hover:text-blue-900 text-sm">Edit</button>
                <button onclick="createOrderWithProduct('{{ $product->id }}')" class="text-whatsapp-dark hover:text-whatsapp-light text-sm">Create Order</button>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-gray-500">
            <i class="fas fa-box text-4xl mb-4 text-gray-300"></i>
            <p>No products found. Add your first product!</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Create Product Modal -->
<div id="createProductModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Add New Product</h3>
        </div>
        <form id="createProductForm" onsubmit="submitProductForm(event)">
            @csrf
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                        <input type="text" name="sku" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                        <input type="number" name="price" step="0.01" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                        <input type="number" name="stock" value="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <input type="text" name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="e.g., Internet, Game, PLN">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                    <input type="url" name="image_url" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="https://example.com/image.jpg">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="productActive" checked class="h-4 w-4 text-whatsapp-light focus:ring-whatsapp-light border-gray-300 rounded">
                    <label for="productActive" class="ml-2 text-sm text-gray-700">Active</label>
                </div>
            </div>
            <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('createProductModal')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark">Add Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Edit Product</h3>
        </div>
        <form id="editProductForm" onsubmit="submitEditProductForm(event)">
            @csrf
            <input type="hidden" id="editProductId" name="id">
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                        <input type="text" id="editProductSku" name="sku" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" id="editProductName" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="editProductDescription" name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                        <input type="number" id="editProductPrice" name="price" step="0.01" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                        <input type="number" id="editProductStock" name="stock" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <input type="text" id="editProductCategory" name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                    <input type="url" id="editProductImage" name="image_url" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="editProductActive" name="is_active" class="h-4 w-4 text-whatsapp-light focus:ring-whatsapp-light border-gray-300 rounded">
                    <label for="editProductActive" class="ml-2 text-sm text-gray-700">Active</label>
                </div>
            </div>
            <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('editProductModal')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark">Update Product</button>
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
    
    function filterProducts() {
        const category = document.getElementById('categoryFilter').value;
        const search = document.getElementById('searchFilter').value.toLowerCase();
        const cards = document.querySelectorAll('#productsGrid > div');
        
        cards.forEach(card => {
            const cardCategory = card.dataset.category || '';
            const cardName = card.dataset.name || '';
            const matchCategory = !category || cardCategory === category;
            const matchSearch = !search || cardName.includes(search);
            card.style.display = matchCategory && matchSearch ? '' : 'none';
        });
    }
    
    async function submitProductForm(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
        const params = new URLSearchParams();
        for (const [key, value] of formData) {
            if (key === 'is_active') {
                params.append(key, value === 'on' ? '1' : '0');
            } else {
                params.append(key, value);
            }
        }
        
        try {
            const response = await fetch('{{ route('crm.products.store') }}', {
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
                closeModal('createProductModal');
                form.reset();
                window.location.reload();
            } else {
                alert('Error creating product: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error creating product');
        }
    }
    
    async function editProduct(id) {
        try {
            const response = await fetch('/crm/products/' + id + '/edit');
            const data = await response.json();
            
            if (data.product) {
                document.getElementById('editProductId').value = data.product.id;
                document.getElementById('editProductSku').value = data.product.sku;
                document.getElementById('editProductName').value = data.product.name;
                document.getElementById('editProductDescription').value = data.product.description || '';
                document.getElementById('editProductPrice').value = data.product.price;
                document.getElementById('editProductStock').value = data.product.stock;
                document.getElementById('editProductCategory').value = data.product.category || '';
                document.getElementById('editProductImage').value = data.product.image_url || '';
                document.getElementById('editProductActive').checked = data.product.is_active;
                openModal('editProductModal');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error loading product');
        }
    }
    
    async function submitEditProductForm(event) {
        event.preventDefault();
        const form = event.target;
        const productId = document.getElementById('editProductId').value;
        const formData = new FormData(form);
        
        const params = new URLSearchParams();
        for (const [key, value] of formData) {
            if (key === 'is_active') {
                params.append(key, value === 'on' ? '1' : '0');
            } else {
                params.append(key, value);
            }
        }
        
        params.append('_method', 'PUT');
        
        try {
            const response = await fetch('/crm/products/' + productId, {
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
                closeModal('editProductModal');
                window.location.reload();
            } else {
                alert('Error updating product: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error updating product');
        }
    }
    
    function createOrderWithProduct(id) {
        window.location.href = '/crm/orders/create?product=' + id;
    }
</script>
@endpush
