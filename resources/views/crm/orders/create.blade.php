@extends('layouts.app')

@section('title', 'Create Order')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-sm text-gray-500">
        <a href="{{ route('crm.orders.index') }}" class="hover:text-gray-700">Orders</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-900">Create Order</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b">
                    <h1 class="text-xl font-bold text-gray-800">Create New Order</h1>
                    <p class="text-gray-500 mt-1">Create a new order for a customer</p>
                </div>

                <form id="orderForm" action="{{ route('crm.orders.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    <!-- Customer Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer <span class="text-red-500">*</span></label>
                        <select name="contact_id" id="contact_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                            <option value="">Select Customer</option>
                            @foreach($contacts as $contact)
                            <option value="{{ $contact->id }}" {{ request('contact') == $contact->id ? 'selected' : '' }}>
                                {{ $contact->display_name }} ({{ $contact->phone }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Products Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Products</label>
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-24">Qty</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200" id="productsList">
                                    @foreach($products as $product)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <label class="flex items-center cursor-pointer">
                                                <input type="checkbox" name="products[{{ $product->id }}][selected]" value="1"
                                                    data-price="{{ $product->price }}"
                                                    data-id="{{ $product->id }}"
                                                    class="product-checkbox rounded border-gray-300 text-whatsapp-light focus:ring-whatsapp-light"
                                                    onchange="updateTotals()">
                                                <span class="ml-3">
                                                    <span class="block text-sm font-medium text-gray-800">{{ $product->name }}</span>
                                                    <span class="block text-xs text-gray-500">{{ $product->sku }}</span>
                                                </span>
                                            </label>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3">
                                            <input type="number" name="products[{{ $product->id }}][quantity]" value="1" min="1"
                                                data-id="{{ $product->id }}"
                                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm quantity-input"
                                                onchange="updateTotals()" disabled>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-800 subtotal-cell" data-id="{{ $product->id }}">Rp 0</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent" placeholder="Order notes..."></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('crm.orders.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                            Create Order
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm sticky top-6">
                <div class="p-6 border-b">
                    <h3 class="font-semibold text-gray-800">Order Summary</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="text-gray-800" id="subtotalDisplay">Rp 0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Discount</span>
                            <input type="number" name="discount" value="0" min="0" step="1000"
                                class="w-24 px-2 py-1 border border-gray-300 rounded text-sm text-right"
                                onchange="updateTotals()">
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Shipping</span>
                            <input type="number" name="shipping_cost" value="0" min="0" step="1000"
                                class="w-24 px-2 py-1 border border-gray-300 rounded text-sm text-right"
                                onchange="updateTotals()">
                        </div>
                        <hr>
                        <div class="flex justify-between text-lg font-bold">
                            <span class="text-gray-800">Total</span>
                            <span class="text-whatsapp-dark" id="totalDisplay">Rp 0</span>
                        </div>
                    </div>

                    <input type="hidden" name="total_amount" id="totalAmount" value="0">
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm mt-6 p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Quick Actions</h3>
                
                <!-- Session Selection -->
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Session</label>
                    <select id="session_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-light focus:border-transparent">
                        <option value="">Select Session</option>
                        @if(isset($sessions['data']) && count($sessions['data']) > 0)
                            @foreach($sessions['data'] as $session)
                            @php
                                $sessionName = $session['sessionId'] ?? $session['session_id'] ?? $session['name'] ?? $session['id'] ?? 'Unknown-' . $loop->index;
                                $sessionStatus = $session['status'] ?? $session['state'] ?? 'unknown';
                            @endphp
                            <option value="{{ $sessionName }}" {{ $sessionStatus !== 'connected' ? 'disabled' : '' }}>
                                {{ $sessionName }} ({{ $sessionStatus }})
                            </option>
                            @endforeach
                        @elseif(isset($sessions) && count($sessions) > 0)
                            @foreach($sessions as $session)
                            @php
                                $sessionName = $session['sessionId'] ?? $session['session_id'] ?? $session['name'] ?? $session['id'] ?? 'Unknown-' . $loop->index;
                                $sessionStatus = $session['status'] ?? $session['state'] ?? 'unknown';
                            @endphp
                            <option value="{{ $sessionName }}" {{ $sessionStatus !== 'connected' ? 'disabled' : '' }}>
                                {{ $sessionName }} ({{ $sessionStatus }})
                            </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <div class="space-y-2">
                    <button type="button" onclick="sendOrderConfirmation()" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        Send Order Confirmation via WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const products = @json($products->mapWithKeys(function($p) {
    return [$p->id => ['name' => $p->name, 'price' => $p->price]];
}));

// Auto-select product from URL parameter
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('product');
    
    if (productId) {
        const checkbox = document.querySelector(`.product-checkbox[data-id="${productId}"]`);
        if (checkbox) {
            checkbox.checked = true;
            updateTotals();
        }
    }
});

function updateTotals() {
    let subtotal = 0;
    
    document.querySelectorAll('.product-checkbox').forEach(checkbox => {
        const id = checkbox.dataset.id;
        const price = parseFloat(checkbox.dataset.price);
        const qtyInput = document.querySelector(`.quantity-input[data-id="${id}"]`);
        const subtotalCell = document.querySelector(`.subtotal-cell[data-id="${id}"]`);
        
        if (checkbox.checked) {
            qtyInput.disabled = false;
            const qty = parseInt(qtyInput.value) || 1;
            const lineTotal = price * qty;
            subtotal += lineTotal;
            subtotalCell.textContent = 'Rp ' + lineTotal.toLocaleString('id-ID');
        } else {
            qtyInput.disabled = true;
            qtyInput.value = 1;
            subtotalCell.textContent = 'Rp 0';
        }
    });
    
    const discount = parseFloat(document.querySelector('input[name="discount"]').value) || 0;
    const shipping = parseFloat(document.querySelector('input[name="shipping_cost"]').value) || 0;
    const total = subtotal - discount + shipping;
    
    document.getElementById('subtotalDisplay').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    document.getElementById('totalDisplay').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('totalAmount').value = total;
}

async function sendOrderConfirmation() {
    const contactId = document.getElementById('contact_id').value;
    if (!contactId) {
        alert('Please select a customer first');
        return;
    }
    
    const total = document.getElementById('totalAmount').value;
    if (total == 0) {
        alert('Please select at least one product');
        return;
    }
    
    // Get selected products
    let productNames = [];
    document.querySelectorAll('.product-checkbox:checked').forEach(checkbox => {
        const id = checkbox.dataset.id;
        const qty = document.querySelector(`.quantity-input[data-id="${id}"]`).value;
        productNames.push(`${products[id].name} x${qty}`);
    });
    
    const message = `*Order Confirmation*\n\nThank you for your order!\n\nProducts:\n${productNames.join('\n')}\n\nTotal: Rp ${parseInt(total).toLocaleString('id-ID')}\n\nWe will process your order shortly.`;
    
    // Get customer phone
    const contactSelect = document.getElementById('contact_id');
    const selectedOption = contactSelect.options[contactSelect.selectedIndex];
    const phoneMatch = selectedOption.text.match(/\((\d+)\)/);
    
    if (!phoneMatch) {
        alert('Could not get customer phone number');
        return;
    }
    
    const phone = phoneMatch[1];
    
    // Get selected session
    const sessionSelect = document.getElementById('session_id');
    const sessionId = sessionSelect.value;
    
    if (!sessionId) {
        alert('Please select a WhatsApp session first');
        return;
    }
    
    try {
        const response = await fetch('{{ route("api.send.text") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                sessionId: sessionId,
                chatId: phone,
                message: message
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Order confirmation sent via WhatsApp!');
        } else {
            alert('Failed to send: ' + result.message);
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// Initialize
updateTotals();

// Handle form submission
document.getElementById('orderForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const contactId = formData.get('contact_id');
    
    if (!contactId) {
        alert('Please select a customer');
        return;
    }
    
    // Build items array from selected products
    const items = [];
    document.querySelectorAll('.product-checkbox:checked').forEach(checkbox => {
        const id = checkbox.dataset.id;
        const qtyInput = document.querySelector(`.quantity-input[data-id="${id}"]`);
        const quantity = parseInt(qtyInput.value) || 1;
        const price = parseFloat(checkbox.dataset.price);
        
        items.push({
            product_id: id,
            quantity: quantity,
            price: price
        });
    });
    
    if (items.length === 0) {
        alert('Please select at least one product');
        return;
    }
    
    const data = {
        contact_id: contactId,
        items: items,
        shipping_address: formData.get('shipping_address'),
        shipping_method: formData.get('shipping_method'),
        notes: formData.get('notes'),
    };
    
    try {
        const response = await fetch('{{ route('crm.orders.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Order created successfully!');
            window.location.href = '{{ route('crm.orders.index') }}';
        } else {
            alert(result.message || 'Failed to create order');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});
</script>
@endpush
@endsection
