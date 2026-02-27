<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maxumax POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-900 text-slate-100 h-screen flex overflow-hidden" x-data="posApp()">

    <!-- Main Sidebar / Cart -->
    <div class="w-full sm:w-2/5 md:w-1/3 xl:w-1/4 bg-slate-800 border-r border-slate-700 flex-col h-full shadow-2xl z-30 relative shrink-0 transition-all duration-300"
        :class="activeTab === 'cart' ? 'flex fixed inset-0' : 'hidden sm:flex'">
        <div class="p-6 border-b border-slate-700 bg-slate-800/50 backdrop-blur">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-indigo-500 bg-clip-text text-transparent">
                MAXUMAX</h1>
            <p class="text-slate-400 text-sm mt-1">Point of Sales</p>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 pb-20 sm:pb-4">
            <template x-if="cart.length === 0">
                <div class="h-full flex flex-col items-center justify-center text-slate-500">
                    <svg class="w-16 h-16 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <p>Cart is empty</p>
                </div>
            </template>

            <template x-for="(item, index) in cart" :key="index">
                <div
                    class="bg-slate-700/50 p-4 rounded-xl border border-slate-600 shadow-sm flex flex-col gap-2 relative group">
                    <button @click="removeFromCart(index)"
                        class="absolute top-2 right-2 text-slate-400 hover:text-red-400 opacity-0 group-hover:opacity-100 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <div class="flex justify-between items-start pr-6">
                        <div>
                            <h3 class="font-semibold text-slate-200" x-text="item.product_name"></h3>
                            <p class="text-xs text-slate-400 mt-1" x-text="item.item_code ? item.item_code : 'Package'">
                            </p>
                        </div>
                        <div class="text-right font-medium text-blue-400" x-text="formatCurrency(item.subtotal)"></div>
                    </div>

                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-600/50">
                        <!-- Size Dropdown -->
                        <div class="relative w-full">
                            <select x-model="item.size"
                                class="w-full bg-slate-800 border border-slate-600 text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-1.5 appearance-none">
                                <option value="">Select Size</option>
                                <template x-for="sizeOpt in getSizesForProduct(item)" :key="sizeOpt.value">
                                    <option :value="sizeOpt.value"
                                        x-text="sizeOpt.label + (sizeOpt.stock !== null ? ' (' + sizeOpt.stock + ')' : '')"
                                        :selected="item.size === sizeOpt.value"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Cart Summary -->
        <div class="p-6 bg-slate-800 border-t border-slate-700 shadow-[0_-10px_40px_rgba(0,0,0,0.2)] pb-24 sm:pb-6">
            <div class="space-y-3 mb-6">
                <div class="flex justify-between text-slate-400">
                    <span>Subtotal</span>
                    <span class="font-medium text-slate-300" x-text="formatCurrency(subtotal)"></span>
                </div>
                <div class="flex justify-between items-center group">
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400">Discount</span>
                        <span x-show="autoDiscountApplied"
                            class="text-xs bg-amber-500/20 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded-full font-semibold"
                            style="display:none" x-text="proJerseyQuantity + 'pcs Promo'">
                        </span>
                    </div>
                    <div class="relative w-32">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">BND</span>
                        <input type="number" x-model.number="discount" @input="autoDiscountApplied = false"
                            :disabled="proJerseyQuantity === 0"
                            class="bg-slate-900 border border-slate-600 text-slate-200 text-right text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            placeholder="0.00">
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-600 flex justify-between items-center">
                    <span class="text-lg font-medium text-slate-200">Total</span>
                    <span class="text-3xl font-bold text-white tracking-tight" x-text="formatCurrency(total)"></span>
                </div>
            </div>
            <button @click="checkout()" :disabled="cart.length === 0"
                class="w-full bg-blue-600 hover:bg-blue-500 disabled:bg-slate-700 disabled:text-slate-500 disabled:cursor-not-allowed text-white font-semibold py-4 px-4 rounded-xl shadow-lg shadow-blue-900/20 transition duration-200 transform active:scale-[0.98] flex justify-center items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                </svg>
                Complete Payment
            </button>
        </div>
    </div>

    <!-- Main ContentArea (Products) -->
    <div class="flex-1 flex flex-col h-full bg-slate-900 relative min-w-0"
        :class="activeTab === 'products' ? 'flex' : 'hidden sm:flex'">
        <!-- Header -->
        <header
            class="min-h-[5rem] border-b border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between px-4 md:px-8 py-4 gap-4">
            <div class="relative w-full md:w-64 lg:w-96 shrink-0">
                <svg class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-500" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" x-model="search" placeholder="Search products..."
                    class="w-full bg-slate-800 border-none text-slate-200 rounded-full py-2.5 pl-10 pr-4 focus:ring-2 focus:ring-blue-500 placeholder-slate-500 outline-none shadow-inner">
            </div>
            <div class="flex flex-wrap items-center gap-2 md:gap-3 w-full md:w-auto justify-end">
                @if($authUser->role === 'superadmin')
                    <div
                        class="flex items-center gap-2 bg-rose-500/10 border border-rose-500/30 text-rose-400 px-3 py-1.5 rounded-full text-xs font-bold animate-pulse">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        SUPERADMIN
                    </div>
                @endif

                <!-- Printer Status Indicator -->
                <div class="flex items-center gap-2">
                    <button @click="printerStatus === 'connected' ? disconnectPrinter() : connectPrinter()"
                        class="bg-slate-800 hover:bg-slate-700 font-medium py-2 px-3 md:py-2.5 md:px-4 rounded-full border border-slate-700 transition flex items-center gap-2"
                        :class="printerStatus === 'connected' ? 'text-emerald-400 border-emerald-500/30 bg-emerald-500/10' : 'text-rose-400 border-rose-500/30 bg-rose-500/10'"
                        title="Toggle Printer Status">
                        <!-- Printer SVG Icon -->
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span class="relative flex h-3 w-3">
                            <span x-show="printerStatus === 'connected'"
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3"
                                :class="printerStatus === 'connected' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                        </span>
                        <span class="text-sm"
                            x-text="printerStatus === 'connected' ? 'Printer OK' : 'No Printer'"></span>
                    </button>

                    <!-- Simulation Toggle -->
                    <button @click="isPrinterSimulation = !isPrinterSimulation"
                        class="bg-slate-800 hover:bg-slate-700 font-medium py-2 px-3 md:py-2.5 md:px-4 rounded-full border border-slate-700 transition flex items-center gap-2"
                        :class="isPrinterSimulation ? 'text-amber-400 border-amber-500/30 bg-amber-500/10' : 'text-slate-400 border-slate-700'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="text-xs" x-text="isPrinterSimulation ? 'Sim Mode: ON' : 'Sim Mode: OFF'"></span>
                    </button>
                </div>

                <a href="{{ route('pos.stockReport') }}"
                    class="bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-medium py-2 px-3 md:py-2.5 md:px-5 rounded-full border border-slate-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="hidden sm:inline">Stock Report</span>
                </a>

                <button @click="openHistory()"
                    class="bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-medium py-2 px-3 md:py-2.5 md:px-5 rounded-full border border-slate-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="hidden sm:inline">History</span>
                </button>
                <button @click="showAddModal = true"
                    class="bg-slate-800 hover:bg-slate-700 text-white font-medium py-2 px-3 md:py-2.5 md:px-5 rounded-full border border-slate-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span class="hidden sm:inline">New Product</span>
                </button>
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <button type="submit"
                        class="bg-red-500/10 hover:bg-red-500/20 text-red-500 font-medium py-2 px-3 md:py-2.5 md:px-5 rounded-full border border-red-500/20 transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Product Grid -->
        <div class="flex-1 p-4 md:p-8 overflow-y-auto pb-24 sm:pb-8">
            <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
                <span class="w-2 h-6 bg-blue-500 rounded-full inline-block"></span>
                Available Products
            </h2>

            <!-- Category Tabs -->
            <div class="flex gap-2 mb-6 overflow-x-auto pb-2 scrollbar-hide">
                <button @click="selectedCategory = 'All'"
                    :class="selectedCategory === 'All' ? 'bg-blue-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-slate-200'"
                    class="px-5 py-2.5 rounded-full font-medium whitespace-nowrap transition border border-slate-700">
                    All
                </button>
                <template x-for="cat in categories" :key="cat">
                    <button @click="selectedCategory = cat"
                        :class="selectedCategory === cat ? 'bg-blue-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-slate-200'"
                        class="px-5 py-2.5 rounded-full font-medium whitespace-nowrap transition border border-slate-700"
                        x-text="cat">
                    </button>
                </template>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div
                        class="bg-slate-800 rounded-2xl p-3 sm:p-5 border border-slate-700 hover:border-blue-500/50 hover:bg-slate-700/80 transition duration-200 flex flex-col justify-between group shadow-sm overflow-hidden relative min-h-[140px] sm:min-h-[160px]">

                        <!-- Overlay Actions (Edit / Delete) -->
                        <div
                            class="absolute top-2 right-2 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity z-20">
                            <button @click.stop="editProduct(product)"
                                class="bg-blue-600/80 hover:bg-blue-500 text-white p-1.5 rounded-md backdrop-blur shadow-lg transition"
                                title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                    </path>
                                </svg>
                            </button>
                            @if($authUser->role === 'superadmin')
                                <button @click.stop="deleteProduct(product.id)"
                                    class="bg-red-600/80 hover:bg-red-500 text-white p-1.5 rounded-md backdrop-blur shadow-lg transition"
                                    title="Delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            @endif
                        </div>

                        <!-- Clickable Area for Adding to Cart -->
                        <div @click="addToCart(product)" class="absolute inset-0 z-10 cursor-pointer"></div>

                        <!-- Image Background / Thumbnail -->
                        <div
                            class="absolute inset-x-0 top-0 h-20 sm:h-24 bg-slate-900 border-b border-slate-700 overflow-hidden">
                            <template x-if="product.image">
                                <img :src="'/storage/' + product.image" alt="Product Image"
                                    class="w-full h-full object-contain opacity-90 group-hover:opacity-100 transition duration-300 p-1">
                            </template>
                            <template x-if="!product.image">
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-slate-600" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                            </template>
                        </div>

                        <div class="z-10 mt-16 sm:mt-20 pointer-events-none">
                            <p class="text-[10px] sm:text-xs font-medium text-slate-400 mb-0.5 sm:mb-1"
                                x-text="product.item_code" style="text-shadow: 0px 1px 3px rgba(0,0,0,0.8);"></p>
                            <h3 class="font-semibold text-slate-100 line-clamp-2 leading-tight text-xs sm:text-base"
                                x-text="product.name" style="text-shadow: 0px 1px 3px rgba(0,0,0,0.8);">
                            </h3>
                        </div>
                        <div class="flex justify-between items-end mt-2 sm:mt-4 z-10 relative pointer-events-none">
                            <span
                                class="text-[9px] sm:text-[10px] bg-slate-900/80 backdrop-blur border border-slate-700 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded text-slate-300 capitalize"
                                x-text="product.type"></span>
                            <span
                                class="font-bold text-sm sm:text-lg text-blue-400 group-hover:text-blue-300 transition backdrop-blur bg-slate-900/30 px-1.5 sm:px-2 rounded -mr-1 sm:-mr-2"
                                x-text="formatCurrency(product.price)"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div x-cloak x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Backdrop -->
        <div x-show="showEditModal" x-transition.opacity class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"
            @click="showEditModal = false"></div>

        <!-- Modal Content -->
        <div x-show="showEditModal" x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 scale-95"
            class="bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 w-[92%] sm:w-full max-w-md max-h-[90vh] flex flex-col relative z-10 overflow-hidden">

            <div
                class="px-6 py-4 border-b border-slate-700 flex justify-between items-center bg-slate-800/80 backdrop-blur">
                <h3 class="text-lg font-semibold text-white">Edit Product</h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-white transition"><svg
                        class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg></button>
            </div>

            <div class="p-6 space-y-4 overflow-y-auto flex-1">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Item Code (Auto-generated)</label>
                    <input type="text" x-model="editingProduct.item_code" readonly
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-slate-500 cursor-not-allowed outline-none transition"
                        placeholder="e.g. 10014233">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Name</label>
                    <input type="text" x-model="editingProduct.name"
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg p-2.5 text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                        placeholder="e.g. MX LIFESTYLE GKA...">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-400 mb-2">Category</label>
                        <select x-model="editingProduct.category"
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:ring-2 focus:ring-blue-500/50 outline-none transition">
                            <option value="">Select Category</option>
                            <template x-for="cat in categories" :key="cat">
                                <option :value="cat" x-text="cat"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-400 mb-2">Price (BND)</label>
                        <input type="number" x-model="editingProduct.price" step="0.01"
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:ring-2 focus:ring-blue-500/50 outline-none transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Type</label>
                    <select x-model="editingProduct.type"
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg p-2.5 text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        <option value="standard">Standard</option>
                        <option value="package">Package</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Update Image (Optional)</label>
                    <input type="file" accept="image/*" @change="handleEditFileChange($event)"
                        class="block w-full text-sm text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 transition border border-slate-600 rounded-lg bg-slate-800">
                </div>

                <!-- Stock Management -->
                <div class="border-t border-slate-700 pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-semibold text-slate-200">Stock per Size</label>
                        <button type="button" @click="editingProduct.stock.push({size_key: '', size_value: '0'})"
                            class="text-xs bg-blue-600/20 text-blue-400 hover:bg-blue-600/40 border border-blue-500/30 px-3 py-1 rounded-lg transition">
                            + Add Size
                        </button>
                    </div>
                    <template x-if="editingProduct.stock.length === 0">
                        <p class="text-xs text-slate-500 italic">No stock defined. Click "+ Add Size" to add stock
                            entries.</p>
                    </template>
                    <div class="space-y-2 overflow-x-hidden max-h-60 overflow-y-auto pr-2">
                        <template x-for="(row, idx) in editingProduct.stock" :key="idx">
                            <div class="flex items-center gap-2">
                                <template
                                    x-if="editingProduct.category && categorySizes[editingProduct.category] && categorySizes[editingProduct.category].length > 0">
                                    <select x-model="row.size_key"
                                        class="flex-1 min-w-0 bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                                        <option value="">Select Size</option>
                                        <template x-for="s in categorySizes[editingProduct.category]" :key="s">
                                            <option :value="s" x-text="s"></option>
                                        </template>
                                    </select>
                                </template>
                                <template
                                    x-if="!editingProduct.category || !categorySizes[editingProduct.category] || categorySizes[editingProduct.category].length === 0">
                                    <input type="text" x-model="row.size_key" placeholder="Size (e.g. S, M, XL)"
                                        class="flex-1 min-w-0 bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                                </template>
                                <input type="number" x-model="row.size_value" min="0" placeholder="Stock"
                                    class="w-24 shrink-0 bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                                <button type="button" @click="editingProduct.stock.splice(idx, 1)"
                                    class="shrink-0 text-red-400 hover:text-red-300 bg-red-500/10 hover:bg-red-500/20 rounded-lg p-2 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-slate-800/50 border-t border-slate-700 flex justify-end gap-3">
                <button @click="showEditModal = false"
                    class="px-4 py-2 font-medium text-slate-300 hover:text-white transition">Cancel</button>
                <button @click="submitEditProduct"
                    class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-lg font-medium shadow-lg shadow-blue-500/20 transition transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="isUpdatingProduct">
                    <span x-show="!isUpdatingProduct">Update</span>
                    <span x-show="isUpdatingProduct">Updating...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div x-cloak x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Backdrop -->
        <div x-show="showAddModal" x-transition.opacity class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"
            @click="showAddModal = false"></div>

        <!-- Modal Content -->
        <div x-show="showAddModal" x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 scale-95"
            class="bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 w-[92%] sm:w-full max-w-2xl max-h-[90vh] flex flex-col relative z-10 overflow-hidden">

            <div
                class="px-6 py-4 border-b border-slate-700 flex justify-between items-center bg-slate-800/80 backdrop-blur shrink-0">
                <h3 class="text-lg font-semibold text-white">Add New Products</h3>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-white transition"><svg
                        class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg></button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-6">
                <template x-for="(product, index) in newProducts" :key="index">
                    <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-700 relative group">
                        <button x-show="newProducts.length > 1" @click="removeProductForm(index)"
                            class="absolute top-2 right-2 text-slate-500 hover:text-red-400 transition" title="Remove">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                        </button>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-400 mb-1">Product Name</label>
                                    <input type="text" x-model="product.name" placeholder="Product name"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:ring-2 focus:ring-blue-500/50 outline-none transition">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-400 mb-1">Category</label>
                                    <select x-model="product.category"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:ring-2 focus:ring-blue-500/50 outline-none transition">
                                        <option value="">Select Category</option>
                                        <template x-for="cat in categories" :key="cat">
                                            <option :value="cat" x-text="cat"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-400 mb-1">Price (BND)</label>
                                    <input type="number" x-model="product.price" step="0.01" placeholder="0.00"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:ring-2 focus:ring-blue-500/50 outline-none transition">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-400 mb-1">Type</label>
                                    <select x-model="product.type"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:ring-2 focus:ring-blue-500/50 outline-none transition">
                                        <option value="product">Product</option>
                                        <option value="package">Package</option>
                                    </select>
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-300 mb-1">Image (Optional)</label>
                                <input type="file" accept="image/*" @change="handleFileChange($event, index)"
                                    class="block w-full text-sm text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 transition border border-slate-600 rounded-lg bg-slate-800">
                            </div>

                            <!-- Stock Management for Add Product -->
                            <div class="md:col-span-2 border-t border-slate-700/50 pt-4 mt-2">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="block text-sm font-semibold text-slate-200">Stock per Size</label>
                                    <button type="button" @click="product.stock.push({size_key: '', size_value: '0'})"
                                        class="text-xs bg-blue-600/20 text-blue-400 hover:bg-blue-600/40 border border-blue-500/30 px-3 py-1 rounded-lg transition">
                                        + Add Size
                                    </button>
                                </div>
                                <template x-if="product.stock.length === 0">
                                    <p class="text-xs text-slate-500 italic">No stock defined. Click "+ Add Size" to add
                                        stock
                                        entries.</p>
                                </template>
                                <div class="space-y-2 overflow-x-hidden max-h-60 overflow-y-auto pr-2">
                                    <template x-for="(row, idx) in product.stock" :key="idx">
                                        <div class="flex items-center gap-2">
                                            <template
                                                x-if="product.category && categorySizes[product.category] && categorySizes[product.category].length > 0">
                                                <select x-model="row.size_key"
                                                    class="flex-1 min-w-0 bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                                                    <option value="">Select Size</option>
                                                    <template x-for="s in categorySizes[product.category]" :key="s">
                                                        <option :value="s" x-text="s"></option>
                                                    </template>
                                                </select>
                                            </template>
                                            <template
                                                x-if="!product.category || !categorySizes[product.category] || categorySizes[product.category].length === 0">
                                                <input type="text" x-model="row.size_key"
                                                    placeholder="Size (e.g. S, M, XL)"
                                                    class="flex-1 min-w-0 bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                                            </template>
                                            <input type="number" x-model="row.size_value" min="0" placeholder="Stock"
                                                class="w-24 shrink-0 bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                                            <button type="button" @click="product.stock.splice(idx, 1)"
                                                class="shrink-0 text-red-400 hover:text-red-300 bg-red-500/10 hover:bg-red-500/20 rounded-lg p-2 transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <button @click="addProductForm"
                    class="w-full py-3 border-2 border-dashed border-slate-600 rounded-xl text-slate-400 hover:text-white hover:border-slate-500 hover:bg-slate-800/50 transition font-medium flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Another Product Row
                </button>
            </div>

            <div class="px-6 py-4 bg-slate-800/80 border-t border-slate-700 flex justify-end gap-3 shrink-0">
                <button @click="showAddModal = false"
                    class="px-4 py-2 font-medium text-slate-300 hover:text-white transition">Cancel</button>
                <button @click="submitNewProducts"
                    class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-2.5 rounded-lg font-medium shadow-lg shadow-blue-500/20 transition transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    :disabled="isSavingProduct">
                    <span x-show="!isSavingProduct">Save All Products</span>
                    <span x-show="isSavingProduct">Saving...</span>
                </button>
            </div>
        </div>
    </div>
    </button>
    </div>
    </div>
    </div>

    <!-- Transaction History Modal -->
    <div x-cloak x-show="showHistoryModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Backdrop -->
        <div x-show="showHistoryModal" x-transition.opacity class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"
            @click="showHistoryModal = false"></div>

        <!-- Modal Content -->
        <div x-show="showHistoryModal" x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 scale-95"
            class="bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 w-full max-w-4xl max-h-[90vh] flex flex-col relative z-10 overflow-hidden">

            <div
                class="px-6 py-4 border-b border-slate-700 flex justify-between items-center bg-slate-800/80 backdrop-blur shrink-0">
                <h3 class="text-lg font-semibold text-white">Transaction History</h3>
                <button @click="showHistoryModal = false" class="text-slate-400 hover:text-white transition"><svg
                        class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg></button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-4">
                <template x-if="isLoadingHistory">
                    <div class="flex justify-center py-10">
                        <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                </template>

                <template x-if="!isLoadingHistory && transactions.length === 0">
                    <div class="text-center py-10 text-slate-500">
                        <p>No transactions found.</p>
                    </div>
                </template>

                <template x-for="txn in transactions" :key="txn.id">
                    <div class="bg-slate-900/50 rounded-xl border border-slate-700 p-5">
                        <div class="flex justify-between items-center border-b border-slate-700/50 pb-3 mb-3">
                            <div>
                                <span class="font-bold text-slate-200" x-text="txn.transaction_number"></span>
                                <span class="text-xs text-slate-400 ml-2"
                                    x-text="new Date(txn.created_at).toLocaleString()"></span>
                            </div>
                            <div class="text-right flex items-center gap-3">
                                <span class="font-bold text-blue-400" x-text="formatCurrency(txn.total_amount)"></span>
                                @if($authUser->role === 'superadmin')
                                    <button @click="deleteTransaction(txn.id)"
                                        class="text-slate-500 hover:text-red-400 p-1 rounded-md transition"
                                        title="Delete Transaction">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="space-y-2">
                            <template x-for="item in txn.items" :key="item.id">
                                <div class="flex justify-between text-sm">
                                    <div class="text-slate-300">
                                        <span x-text="item.quantity + 'x'"></span>
                                        <span class="ml-2" x-text="item.product_name"></span>
                                        <span class="text-slate-500 text-xs ml-1"
                                            x-text="item.size ? '(' + item.size + ')' : ''"></span>
                                    </div>
                                    <div class="text-slate-400" x-text="formatCurrency(item.subtotal)"></div>
                                </div>
                            </template>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-700/50 flex justify-end">
                            <button @click="window.open('/receipt/' + txn.id, '_blank', 'width=400,height=600')"
                                class="text-sm bg-slate-800 hover:bg-slate-700 text-slate-300 px-3 py-1.5 rounded transition flex items-center gap-2 border border-slate-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                    </path>
                                </svg>
                                Reprint Receipt
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="px-6 py-4 bg-slate-800/80 border-t border-slate-700 flex justify-end shrink-0">
                <button @click="showHistoryModal = false"
                    class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-2 rounded-lg font-medium transition">Close</button>
            </div>
        </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <div
        class="sm:hidden fixed bottom-0 left-0 right-0 bg-slate-800 border-t border-slate-700 flex justify-around p-2 z-40">
        <button @click="activeTab = 'products'" class="flex flex-col items-center gap-1 px-4 py-2 rounded-xl transition"
            :class="activeTab === 'products' ? 'text-blue-400 bg-blue-500/10' : 'text-slate-400'">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span class="text-[10px] font-medium">Products</span>
        </button>
        <button @click="activeTab = 'cart'"
            class="flex flex-col items-center gap-1 px-4 py-2 rounded-xl transition relative"
            :class="activeTab === 'cart' ? 'text-blue-400 bg-blue-500/10' : 'text-slate-400'">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span x-show="cart.length > 0"
                class="absolute top-1 right-3 bg-red-500 text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold"
                x-text="cart.length"></span>
            <span class="text-[10px] font-medium">Cart</span>
        </button>
    </div>

    <script>
        function posApp() {
            return {
                products: @json($products),
                filteredProducts: [], // Used to be initialized here, now it's a getter
                categories: ['FABD Jersey', 'Outdoor Shirt', 'Pro Jersey', 'Accessories'],
                selectedCategory: 'All',
                activeTab: 'products',

                // Bluetooth Printer Variables
                printerStatus: 'disconnected',
                printerDevice: null,
                printerServer: null,
                printerCharacteristic: null,

                categorySizes: {
                    'Pro Jersey': ['1/2 yrs', '3/4 yrs', '5/6 yrs', '7/8 yrs', '9/11 yrs', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'],
                    'FABD Jersey': ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL', '6XL', '7XL', '8XL'],
                    'Outdoor Shirt': ['S', 'M', 'L', 'XL', '2XL', '3XL'],
                    'Accessories': []
                },
                search: '',
                cart: [],
                discount: 0,
                autoDiscountApplied: false,
                manualDiscountUsed: false,
                showAddModal: false,
                isSavingProduct: false,
                showEditModal: false,
                isUpdatingProduct: false,
                editingProduct: { id: null, item_code: '', name: '', price: '', type: 'standard', category: '', imageFile: null, stock: [] },
                isPrinterSimulation: false,
                newProducts: [
                    { item_code: '', name: '', price: '', type: 'standard', category: '', imageFile: null, stock: [] }
                ],
                showHistoryModal: false,
                isLoadingHistory: false,
                transactions: [],
                sizes: [
                    '1/2 yrs', '3/4 yrs', '5/6 yrs', '7/8 yrs', '9/11 yrs',
                    'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'
                ],

                get filteredProducts() {
                    let productsInfo = this.products;

                    // 1. Filter by category
                    if (this.selectedCategory !== 'All') {
                        productsInfo = productsInfo.filter(p => p.category === this.selectedCategory);
                    }

                    // 2. Filter by search text
                    if (this.search !== '') {
                        productsInfo = productsInfo.filter(p =>
                            p.name.toLowerCase().includes(this.search.toLowerCase()) ||
                            p.item_code.toLowerCase().includes(this.search.toLowerCase())
                        );
                    }

                    return productsInfo;
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + item.subtotal, 0);
                },

                get totalQuantity() {
                    return this.cart.reduce((sum, item) => sum + item.quantity, 0);
                },
                get proJerseyQuantity() {
                    return this.cart.reduce((sum, item) => {
                        const product = this.products.find(p => p.id === item.product_id);
                        return (product && product.category === 'Pro Jersey') ? sum + item.quantity : sum;
                    }, 0);
                },

                get proJerseySubtotal() {
                    return this.cart.reduce((sum, item) => {
                        const product = this.products.find(p => p.id === item.product_id);
                        return (product && product.category === 'Pro Jersey') ? sum + item.subtotal : sum;
                    }, 0);
                },

                get calculatedAutoDiscount() {
                    let q = this.proJerseyQuantity;

                    // STRICT RULE: Only apply discount if EXACTLY 3 or EXACTLY 7 items.
                    if (q === 7) {
                        let targetCost = 100;
                        let actualCost = this.proJerseySubtotal;
                        let discount = actualCost - targetCost;
                        return discount > 0 ? discount : 0;
                    }
                    else if (q === 3) {
                        let targetCost = 50;
                        let actualCost = this.proJerseySubtotal;
                        let discount = actualCost - targetCost;
                        return discount > 0 ? discount : 0;
                    }

                    // ANY OTHER QUANTITY (1, 2, 4, 5, 8, etc.) gets 0 discount
                    return 0;
                },

                get total() {
                    return Math.max(0, this.subtotal - (this.discount || 0));
                },

                init() {
                    this.$watch('cart', () => {
                        if (!this.manualDiscountUsed) {
                            let expectedDiscount = this.calculatedAutoDiscount;
                            if (expectedDiscount > 0) {
                                this.discount = expectedDiscount;
                                this.autoDiscountApplied = true;
                            } else if (this.autoDiscountApplied || this.proJerseyQuantity === 0) {
                                this.discount = 0;
                                this.autoDiscountApplied = false;
                            }
                        }
                    }, { deep: true });

                    this.$watch('discount', (newVal, oldVal) => {
                        let expectedDiscount = this.calculatedAutoDiscount;
                        if (newVal !== oldVal) {
                            if (this.autoDiscountApplied && newVal !== expectedDiscount) {
                                this.manualDiscountUsed = true;
                                this.autoDiscountApplied = false;
                            } else if (newVal === 0 && this.manualDiscountUsed) {
                                this.manualDiscountUsed = false;
                            }
                        }
                    });
                },

                formatCurrency(val) {
                    return Number(val).toFixed(2) + ' BND';
                },

                getSizesForProduct(item) {
                    const product = this.products.find(p => p.id === item.product_id);
                    if (product && product.stock && product.stock.length > 0) {
                        return product.stock.map(s => ({
                            value: s.size_key,
                            label: s.size_key,
                            stock: s.size_value
                        }));
                    }
                    // Fallback to general sizes if no specific stock or category sizes
                    if (product && product.category && this.categorySizes[product.category]) {
                        return this.categorySizes[product.category].map(s => ({ value: s, label: s, stock: null }));
                    }
                    return this.sizes.map(s => ({ value: s, label: s, stock: null }));
                },

                addToCart(product) {
                    this.cart.push({
                        product_id: product.id,
                        item_code: product.item_code,
                        product_name: product.name,
                        size: '', // Default empty size
                        unit_price: Number(product.price),
                        quantity: 1,
                        subtotal: Number(product.price)
                    });
                },

                addPackage(name, price) {
                    this.cart.push({
                        product_id: null,
                        item_code: '',
                        product_name: name,
                        size: '',
                        unit_price: Number(price),
                        quantity: 1,
                        subtotal: Number(price)
                    });
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },

                updateQuantity(index, change) {
                    const item = this.cart[index];
                    const newQty = item.quantity + change;
                    if (newQty > 0) {
                        item.quantity = newQty;
                        item.subtotal = item.quantity * item.unit_price;
                    } else if (newQty === 0) {
                        this.removeFromCart(index);
                    }
                },

                addProductForm() {
                    this.newProducts.push({ item_code: '', name: '', price: '', type: 'standard', category: '', imageFile: null, stock: [] });
                },

                removeProductForm(index) {
                    this.newProducts.splice(index, 1);
                },

                handleFileChange(event, index) {
                    const file = event.target.files[0];
                    if (file) {
                        this.newProducts[index].imageFile = file;
                    } else {
                        this.newProducts[index].imageFile = null;
                    }
                },

                handleEditFileChange(event) {
                    const file = event.target.files[0];
                    this.editingProduct.imageFile = file || null;
                },

                editProduct(product) {
                    this.editingProduct = {
                        id: product.id,
                        item_code: product.item_code,
                        name: product.name,
                        price: product.price,
                        type: product.type || 'standard',
                        category: product.category || '',
                        imageFile: null,
                        stock: product.stock ? JSON.parse(JSON.stringify(product.stock)) : []
                    };
                    this.showEditModal = true;
                },

                async submitEditProduct() {
                    if (!this.editingProduct.item_code || !this.editingProduct.name || !this.editingProduct.price) {
                        alert('Please fill in all required fields.');
                        return;
                    }

                    this.isUpdatingProduct = true;
                    try {
                        const formData = new FormData();
                        formData.append('_method', 'PUT'); // Spoof PUT request
                        formData.append('item_code', this.editingProduct.item_code);
                        formData.append('name', this.editingProduct.name);
                        formData.append('price', this.editingProduct.price);
                        formData.append('type', this.editingProduct.type);
                        formData.append('category', this.editingProduct.category || '');
                        formData.append('stock', JSON.stringify(this.editingProduct.stock));
                        if (this.editingProduct.imageFile) {
                            formData.append('image', this.editingProduct.imageFile);
                        }

                        const res = await axios.post(`/api/products/${this.editingProduct.id}`, formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        });

                        // Update product in local state
                        const index = this.products.findIndex(p => p.id === this.editingProduct.id);
                        if (index !== -1) {
                            this.products[index] = res.data;
                        }

                        this.showEditModal = false;
                    } catch (e) {
                        alert('Error updating product: ' + (e.response?.data?.message || e.message));
                    } finally {
                        this.isUpdatingProduct = false;
                    }
                },

                async deleteProduct(id) {
                    if (!confirm('Are you sure you want to delete this product?')) {
                        return;
                    }

                    try {
                        await axios.delete(`/api/products/${id}`);
                        this.products = this.products.filter(p => p.id !== id);
                        await Swal.fire({
                            title: 'Deleted!',
                            text: 'Product has been deleted.',
                            icon: 'success',
                            background: '#1e293b',
                            color: '#f8fafc'
                        });
                    } catch (e) {
                        alert('Error deleting product: ' + (e.response?.data?.message || e.message));
                    }
                },

                async deleteTransaction(id) {
                    const result = await Swal.fire({
                        title: 'Hapus Transaksi?',
                        text: "Data transaksi ini akan dihapus permanen!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, Hapus!',
                        background: '#1e293b',
                        color: '#f8fafc'
                    });

                    if (result.isConfirmed) {
                        try {
                            await axios.delete(`/api/transactions/${id}`);
                            this.transactions = this.transactions.filter(t => t.id !== id);
                            Swal.fire({
                                title: 'Terhapus!',
                                text: 'Transaksi berhasil dihapus.',
                                icon: 'success',
                                background: '#1e293b',
                                color: '#f8fafc'
                            });
                        } catch (e) {
                            Swal.fire({
                                title: 'Gagal!',
                                text: 'Gagal menghapus transaksi: ' + (e.response?.data?.message || e.message),
                                icon: 'error',
                                background: '#1e293b',
                                color: '#f8fafc'
                            });
                        }
                    }
                },

                async submitNewProducts() {
                    // Validation
                    for (let i = 0; i < this.newProducts.length; i++) {
                        const p = this.newProducts[i];
                        if (!p.name || !p.price) {
                            alert(`Please fill in all required fields for Product at row ${i + 1}`);
                            return;
                        }
                    }

                    this.isSavingProduct = true;
                    try {
                        let formData = new FormData();

                        this.newProducts.forEach((p, i) => {
                            formData.append(`products[${i}][item_code]`, p.item_code);
                            formData.append(`products[${i}][name]`, p.name);
                            formData.append(`products[${i}][price]`, p.price);
                            formData.append(`products[${i}][type]`, p.type);
                            formData.append(`products[${i}][category]`, p.category || '');
                            formData.append(`products[${i}][stock]`, JSON.stringify(p.stock));
                            if (p.imageFile) {
                                formData.append(`products[${i}][image]`, p.imageFile);
                            }
                        });

                        const res = await axios.post('/api/products', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        });

                        this.products.push(...res.data);
                        this.showAddModal = false;
                        this.newProducts = [{ item_code: '', name: '', price: '', type: 'standard', category: '', imageFile: null, stock: [] }];

                        // Reset file input fields if needed (optional since modal closes and reinitializes)
                    } catch (e) {
                        alert('Error saving products: ' + (e.response?.data?.message || e.message));
                    } finally {
                        this.isSavingProduct = false;
                    }
                },

                async openHistory() {
                    this.showHistoryModal = true;
                    this.isLoadingHistory = true;
                    try {
                        const res = await axios.get('/api/transactions');
                        this.transactions = res.data;
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load transaction history: ' + (e.response?.data?.message || e.message),
                            background: '#1e293b',
                            color: '#f8fafc'
                        });
                    } finally {
                        this.isLoadingHistory = false;
                    }
                },

                async checkout() {
                    // REQUIRED: All items must have a size selected before proceeding
                    const invalidItems = this.cart.filter(i => !i.size || i.size === '');
                    if (invalidItems.length > 0) {
                        const names = invalidItems.map(i => `"${i.product_name}"`).join(', ');
                        await Swal.fire({
                            title: 'Size Wajib Dipilih!',
                            html: `Item berikut belum dipilih ukurannya:<br><br><strong>${names}</strong><br><br>Mohon pilih ukuran terlebih dahulu sebelum melanjutkan pembayaran.`,
                            icon: 'error',
                            confirmButtonColor: '#3085d6',
                            background: '#1e293b',
                            color: '#f8fafc'
                        });
                        return; // Hard block — cannot proceed without sizes
                    }

                    try {
                        const payload = {
                            subtotal: this.subtotal,
                            discount: this.discount || 0,
                            total_amount: this.total,
                            items: this.cart
                        };
                        const res = await axios.post('/api/transactions', payload);
                        const transactionId = res.data.id;
                        // Clear cart
                        this.cart = [];
                        this.discount = 0;

                        // Show SweetAlert Success then open window
                        Swal.fire({
                            title: 'Transaction Complete!',
                            text: 'Receipt is generating...',
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500,
                            background: '#1e293b',
                            color: '#f8fafc'
                        }).then(() => {
                            window.open('/receipt/' + transactionId, '_blank', 'width=400,height=600');
                            // Auto-print if connected OR simulation is ON
                            if (this.printerStatus === 'connected' || this.isPrinterSimulation) {
                                this.printReceipt(transactionId, payload);
                            }
                        });
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Payment Failed',
                            text: e.response?.data?.message || e.message,
                            background: '#1e293b',
                            color: '#f8fafc'
                        });
                    }
                },

                // --- Web Bluetooth Printer Logic ---

                async connectPrinter() {
                    try {
                        if (!navigator.bluetooth) {
                            throw new Error("Web Bluetooth API is not supported in this browser. Please use Chrome/Edge on Desktop or Android.");
                        }

                        // We request a device that offers generic Bluetooth Serial Port Profile (SPP) 
                        // or generic BLE services common to thermal printers.
                        const device = await navigator.bluetooth.requestDevice({
                            filters: [
                                { services: ['000018f0-0000-1000-8000-00805f9b34fb'] }, // Standard ESC/POS UUID
                                { services: ['e7810a71-73ae-499d-8c15-faa9aef0c3f2'] } // Sometimes used by generic mini printers
                            ],
                            optionalServices: [
                                '00001800-0000-1000-8000-00805f9b34fb',
                                '00001801-0000-1000-8000-00805f9b34fb'
                            ],
                            acceptAllDevices: false
                            // *Note*: To allow ALL devices, swap to acceptAllDevices: true and remove filters. 
                            // But optionalServices must explicitly list the UUIDs you want to write to.
                        });

                        this.printerDevice = device;
                        device.addEventListener('gattserverdisconnected', this.handleDisconnection.bind(this));

                        const server = await device.gatt.connect();
                        this.printerServer = server;

                        // Discover the writeable service and characteristic
                        // Typically for ESC/POS BLE, the write characteristic UUID ends in ...9b34fb and starts around 0x2AF1
                        const services = await server.getPrimaryServices();
                        for (let service of services) {
                            const characteristics = await service.getCharacteristics();
                            for (let characteristic of characteristics) {
                                if (characteristic.properties.write || characteristic.properties.writeWithoutResponse) {
                                    this.printerCharacteristic = characteristic;
                                    break;
                                }
                            }
                            if (this.printerCharacteristic) break;
                        }

                        if (!this.printerCharacteristic) {
                            throw new Error("No readable/writable characteristics found for this printer.");
                        }

                        this.printerStatus = 'connected';
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Printer Connected',
                            showConfirmButton: false,
                            timer: 2000,
                            background: '#1e293b', color: '#f8fafc'
                        });

                    } catch (error) {
                        if (error.name === 'NotFoundError' || error.name === 'AbortError') {
                            // User cancelled the chooser or it was aborted (standard behavior)
                            console.info('Bluetooth connection cancelled by user.');
                            return;
                        }
                        console.error('Bluetooth Connection Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Connection Failed',
                            text: error.message,
                            background: '#1e293b', color: '#f8fafc'
                        });
                    }
                },

                disconnectPrinter() {
                    if (this.printerDevice && this.printerDevice.gatt.connected) {
                        this.printerDevice.gatt.disconnect();
                    }
                    this.handleDisconnection();
                },

                handleDisconnection() {
                    this.printerStatus = 'disconnected';
                    this.printerDevice = null;
                    this.printerServer = null;
                    this.printerCharacteristic = null;
                },

                async printReceipt(transactionId, payload) {
                    if (!this.isPrinterSimulation && (this.printerStatus !== 'connected' || !this.printerCharacteristic)) {
                        console.warn("Cannot print, printer not connected.");
                        return;
                    }

                    try {
                        // Very simplified ESC/POS Payload format (Raw Text)
                        // Production apps often use `escpos-encoder` or similar JS libraries to build bytes
                        const encoder = new TextEncoder();

                        let text = "=== MAXUMAX POS ===\n";
                        text += `Txn: #${transactionId}\n`;
                        text += "--------------------------------\n";

                        payload.items.forEach(item => {
                            text += `${item.product_name} x${item.quantity}\n`;
                            text += `  Size: ${item.size} - BND ${item.subtotal}\n`;
                        });

                        text += "--------------------------------\n";
                        text += `Subtotal: BND ${payload.subtotal}\n`;
                        if (payload.discount > 0) text += `Discount: BND -${payload.discount}\n`;
                        text += `Total: BND ${payload.total_amount}\n`;
                        text += "\nThank you for shopping!\n\n\n";

                        // If Simulation mode is on, show a virtual receipt instead of sending to Bluetooth
                        if (this.isPrinterSimulation) {
                            await Swal.fire({
                                title: 'Virtual Receipt (Simulation)',
                                html: `<pre class="text-left bg-slate-900 text-emerald-400 p-4 rounded-lg font-mono text-xs overflow-x-auto">${text}</pre>`,
                                icon: 'info',
                                background: '#1e293b',
                                color: '#f8fafc',
                                confirmButtonText: 'Great!'
                            });
                            return;
                        }

                        // ESC/POS Commands (Init printer, feed lines, cut)
                        // [27, 64] = Initialize
                        // [10] = Line feed
                        const initBytes = new Uint8Array([27, 64]);
                        const textBytes = encoder.encode(text);
                        const endBytes = new Uint8Array([10, 10, 10, 10, 29, 86, 66, 0]); // Feed 4 lines and Cut

                        // Concatenate Uint8Arrays
                        let totalLength = initBytes.byteLength + textBytes.byteLength + endBytes.byteLength;
                        let fullPayload = new Uint8Array(totalLength);
                        fullPayload.set(initBytes, 0);
                        fullPayload.set(textBytes, initBytes.byteLength);
                        fullPayload.set(endBytes, initBytes.byteLength + textBytes.byteLength);

                        // Chunk the payload (BLE characteristics typically have a ~20 to 512 byte limit per write)
                        const CHUNK_SIZE = 50;
                        for (let i = 0; i < fullPayload.length; i += CHUNK_SIZE) {
                            const chunk = fullPayload.slice(i, i + CHUNK_SIZE);
                            // Some printers require writeWithoutResponse
                            if (this.printerCharacteristic.properties.writeWithoutResponse) {
                                await this.printerCharacteristic.writeValueWithoutResponse(chunk);
                            } else {
                                await this.printerCharacteristic.writeValue(chunk);
                            }
                            // Small delay to prevent overwhelming the device buffer
                            await new Promise(res => setTimeout(res, 20));
                        }

                    } catch (err) {
                        console.error("Print Failed", err);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'Printing Failed: ' + err.message,
                            showConfirmButton: false,
                            timer: 3000,
                            background: '#1e293b', color: '#f8fafc'
                        });
                    }
                }
            }
        }
    </script>
</body>

</html>