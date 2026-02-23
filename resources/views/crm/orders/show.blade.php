@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('crm.orders.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left text-gray-600"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Order #{{ $order->order_number }}</h1>
                <p class="text-gray-500 mt-1">Order Details</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <span class="px-3 py-1 rounded-full text-sm font-medium
                @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                @elseif($order->status === 'processing') bg-indigo-100 text-indigo-800
                @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                @elseif($order->status === 'delivered') bg-green-100 text-green-800
                @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                @else bg-gray-100 text-gray-800 @endif">
                {{ ucfirst($order->status) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Info -->
        <div class="lg:col-span-2">
            <!-- Order Items -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b">
                    <h3 class="font-semibold text-gray-800">Order Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($order->items ?? [] as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-gray-800">{{ $item['name'] ?? 'Product' }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $item['quantity'] ?? 1 }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-800">Rp {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    No items found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notes -->
            @if($order->notes)
            <div class="bg-white rounded-xl shadow-sm mt-6 p-6">
                <h3 class="font-semibold text-gray-800 mb-2">Notes</h3>
                <p class="text-gray-600">{{ $order->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Customer Info -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Customer</h3>
                @if($order->contact)
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-whatsapp-light rounded-full flex items-center justify-center text-white font-bold">
                        {{ $order->contact->initials ?? 'C' }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">{{ $order->contact->display_name }}</p>
                        <p class="text-sm text-gray-500">{{ $order->contact->phone }}</p>
                    </div>
                </div>
                @else
                <p class="text-gray-500">No customer info</p>
                @endif
            </div>

            <!-- Order Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Summary</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Order Date</span>
                        <span class="text-gray-800">{{ $order->ordered_at ? $order->ordered_at->format('d M Y') : '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total</span>
                        <span class="text-lg font-bold text-gray-800">Rp {{ number_format($order->total_amount ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Actions</h3>
                <div class="space-y-2">
                    <button onclick="updateStatus()" class="w-full px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors text-sm">
                        Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateStatus() {
    const status = prompt('Enter new status (pending, confirmed, processing, shipped, delivered, cancelled):');
    if (status) {
        fetch('/crm/orders/{{ $order->id }}/status', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update status');
            }
        });
    }
}
</script>
@endsection
