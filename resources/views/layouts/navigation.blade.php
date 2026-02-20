<nav class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800">
                    Smart Inventory
                </a>
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2">Dashboard</a>
                        @can('products.view')
                            <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2">Products</a>
                        @endcan
                        @can('stock.view')
                            <a href="{{ route('stock.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2">Stock</a>
                        @endcan
                        @can('suppliers.view')
                            <a href="{{ route('suppliers.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2">Suppliers</a>
                        @endcan
                        @can('purchase-orders.view')
                            <a href="{{ route('purchase-orders.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2">Purchase Orders</a>
                        @endcan
                        @can('restock.view')
                            <a href="{{ route('restock.recommendations') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2">Restock</a>
                        @endcan

                        {{-- New Menu Items --}}
                        @can('customers.view')
                            <a href="{{ route('customers.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2">Customers</a>
                        @endcan
                        @can('sales-orders.view')
                            <a href="{{ route('sales-orders.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2">Sales</a>
                        @endcan
                        @can('warehouses.view')
                            <a href="{{ route('warehouses.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2">Warehouses</a>
                        @endcan
                        @can('batches.view')
                            <a href="{{ route('batches.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2">Batches</a>
                        @endcan
                        @can('stock-opname.view')
                            <a href="{{ route('stock-opname.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2">Opname</a>
                        @endcan
                        @can('reports.view')
                            <a href="{{ route('reports.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2">Reports</a>
                        @endcan

                        {{-- Admin Menu --}}
                        @role('owner|admin')
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="text-gray-500 hover:text-gray-700 px-3 py-2 inline-flex items-center">
                                    Admin
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5" style="display: none;">
                                    <div class="py-1">
                                        <a href="{{ route('users.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Users</a>
                                        <a href="{{ route('bin-locations.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Bin Locations</a>
                                        <a href="{{ route('approvals.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Approvals</a>
                                    </div>
                                </div>
                            </div>
                        @endrole
                    @endauth
                </div>
            </div>
            <div class="flex items-center">
                @auth
                    <span class="text-gray-700 mr-4">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-700">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-700">Login</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
