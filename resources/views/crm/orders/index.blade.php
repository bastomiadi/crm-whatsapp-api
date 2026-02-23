@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Orders</h1>
            <p class="text-gray-500 mt-1">Manage customer orders</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('crm.orders.export') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-download mr-2"></i> Export
            </a>
            <a href="{{ route('crm.orders.create') }}" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                <i class="fas fa-plus mr-2"></i> New Order
            </a>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Processing</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['processing'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-cog text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Shipped</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['shipped'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-truck text-purple-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Delivered</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['delivered'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-check text-green-600"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter Badge -->
    @if(!$canViewAll)
    <div class="flex items-center px-3 py-2 bg-yellow-100 text-yellow-800 rounded-lg text-sm">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        Showing My Orders Only
    </div>
    @endif
    
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex flex-wrap gap-4">
            <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg" onchange="filterOrders()">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <input type="text" id="searchFilter" placeholder="Search orders..." class="px-4 py-2 border border-gray-300 rounded-lg flex-1" onkeyup="filterOrders()">
        </div>
    </div>
    
    <!-- Orders Table -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="ordersTable">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50" data-status="{{ $order->status }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-medium text-gray-800">{{ $order->order_number }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <p class="font-medium text-gray-800">{{ $order->contact->name ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-500">{{ $order->contact->phone ?? '' }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ is_array($order->items) ? count($order->items) : 0 }} items</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full {{ [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'confirmed' => 'bg-blue-100 text-blue-800',
                                'processing' => 'bg-indigo-100 text-indigo-800',
                                'shipped' => 'bg-purple-100 text-purple-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                            ][$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button onclick="viewOrder('{{ $order->id }}')" class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                            <button onclick="updateStatus('{{ $order->id }}')" class="text-green-600 hover:text-green-900">Update Status</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">No orders found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Order Modal -->
<div id="createOrderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Create New Order</h3>
        </div>
        <form action="{{ route('crm.orders.store') }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                    <select name="contact_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">Select Customer</option>
                        @foreach(\App\Models\Contact::all() as $contact)
                        <option value="{{ $contact->id }}">{{ $contact->name }} ({{ $contact->phone }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount</label>
                    <input type="number" name="total_amount" step="0.01" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shipping Address</label>
                    <textarea name="shipping_address" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>
            <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('createOrderModal')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-whatsapp-light text-white rounded-lg hover:bg-whatsapp-dark">Create Order</button>
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
    
    function filterOrders() {
        const status = document.getElementById('statusFilter').value;
        const search = document.getElementById('searchFilter').value.toLowerCase();
        const rows = document.querySelectorAll('#ordersTable tr');
        
        rows.forEach(row => {
            const rowStatus = row.dataset.status || '';
            const text = row.textContent.toLowerCase();
            const matchStatus = !status || rowStatus === status;
            const matchSearch = !search || text.includes(search);
            row.style.display = matchStatus && matchSearch ? '' : 'none';
        });
    }
    
    function viewOrder(id) {
        // Open order detail view
        window.location.href = '/crm/orders/' + id;
    }
    
    function updateStatus(id) {
        const status = prompt('Enter new status (pending, confirmed, processing, shipped, delivered, cancelled):');
        if (status) {
            fetch('/crm/orders/' + id + '/status', {
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
@endpush
