<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Report — Maxumax POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .stock-low {
            color: #f87171;
        }

        .stock-ok {
            color: #34d399;
        }

        .stock-mid {
            color: #fbbf24;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #fff;
                color: #000;
            }
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="bg-slate-900 text-slate-100 min-h-screen" x-data="{
    search: '',
    selectedCategory: 'All',
    get filtered() {
        let items = document.querySelectorAll('[data-product]');
        return items;
    }
}">

    {{-- Header --}}
    <header class="bg-slate-800 border-b border-slate-700 sticky top-0 z-50 no-print">
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('pos.index') }}"
                    class="flex items-center gap-2 text-slate-400 hover:text-white transition text-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to POS
                </a>
                <div class="w-px h-6 bg-slate-700"></div>
                <div>
                    <h1
                        class="text-lg font-bold bg-gradient-to-r from-blue-400 to-indigo-500 bg-clip-text text-transparent">
                        Stock Report
                    </h1>
                    <p class="text-xs text-slate-500">{{ now()->format('d F Y, H:i') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="window.print()"
                    class="flex items-center gap-2 bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white border border-slate-600 font-medium py-2 px-4 rounded-full text-sm transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print / Export PDF
                </button>
            </div>
        </div>
    </header>

    <div class="max-w-screen-2xl mx-auto px-4 md:px-8 py-8">

        {{-- Summary Cards --}}
        @php
            $totalProducts = $products->count();
            $totalStock = $products->sum('total_stock');
            $lowStockCount = $products->filter(fn($p) => $p['total_stock'] > 0 && $p['total_stock'] <= 5)->count();
            $outOfStockCount = $products->filter(fn($p) => $p['total_stock'] == 0)->count();
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700">
                <p class="text-xs text-slate-500 uppercase tracking-widest mb-1">Total Produk</p>
                <p class="text-3xl font-bold text-slate-100">{{ $totalProducts }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ $categories->count() }} kategori</p>
            </div>
            <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700">
                <p class="text-xs text-slate-500 uppercase tracking-widest mb-1">Total Stok</p>
                <p class="text-3xl font-bold text-emerald-400">{{ $totalStock }}</p>
                <p class="text-xs text-slate-400 mt-1">unit tersedia</p>
            </div>
            <div class="bg-slate-800 rounded-2xl p-5 border border-amber-700/30 bg-amber-900/10">
                <p class="text-xs text-amber-500/80 uppercase tracking-widest mb-1">Stok Rendah</p>
                <p class="text-3xl font-bold text-amber-400">{{ $lowStockCount }}</p>
                <p class="text-xs text-amber-400/60 mt-1">produk (≤ 5 unit)</p>
            </div>
            <div class="bg-slate-800 rounded-2xl p-5 border border-rose-700/30 bg-rose-900/10">
                <p class="text-xs text-rose-500/80 uppercase tracking-widest mb-1">Habis</p>
                <p class="text-3xl font-bold text-rose-400">{{ $outOfStockCount }}</p>
                <p class="text-xs text-rose-400/60 mt-1">produk kehabisan stok</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex flex-col md:flex-row items-start md:items-center gap-4 mb-6 no-print">
            <div class="relative w-full md:w-80">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Cari nama produk / kode..."
                    class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-full py-2 pl-9 pr-4 text-sm focus:ring-2 focus:ring-blue-500 placeholder-slate-500 outline-none">
            </div>

            <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide w-full md:w-auto">
                <button onclick="filterCategory('All')" id="cat-All"
                    class="cat-btn active-cat px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition border border-slate-600 bg-blue-600 text-white">
                    Semua
                </button>
                @foreach($categories as $cat)
                    <button onclick="filterCategory('{{ $cat }}')" id="cat-{{ Str::slug($cat) }}"
                        class="cat-btn px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition border border-slate-700 bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white">
                        {{ $cat }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Per Category Tables --}}
        @foreach($grouped as $category => $items)
            <div class="mb-10 category-section" data-category="{{ $category }}">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-1.5 h-7 bg-blue-500 rounded-full"></span>
                    <h2 class="text-lg font-semibold text-slate-200">{{ $category }}</h2>
                    <span class="text-xs bg-slate-700 text-slate-400 px-2.5 py-1 rounded-full font-medium">
                        {{ $items->count() }} produk · {{ $items->sum('total_stock') }} unit
                    </span>
                </div>

                <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-900/50 border-b border-slate-700">
                                    <th
                                        class="text-left text-xs text-slate-400 uppercase tracking-wider px-5 py-3 font-medium">
                                        Produk</th>
                                    <th
                                        class="text-left text-xs text-slate-400 uppercase tracking-wider px-5 py-3 font-medium">
                                        Kode</th>
                                    <th
                                        class="text-left text-xs text-slate-400 uppercase tracking-wider px-5 py-3 font-medium">
                                        Harga</th>
                                    <th
                                        class="text-left text-xs text-slate-400 uppercase tracking-wider px-5 py-3 font-medium">
                                        Stok per Ukuran</th>
                                    <th
                                        class="text-right text-xs text-slate-400 uppercase tracking-wider px-5 py-3 font-medium">
                                        Total Stok</th>
                                    <th
                                        class="text-right text-xs text-slate-400 uppercase tracking-wider px-5 py-3 font-medium">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $product)
                                    @php
                                        $total = $product['total_stock'];
                                        $statusClass = $total == 0 ? 'bg-rose-500/20 text-rose-400 border-rose-500/30'
                                            : ($total <= 5 ? 'bg-amber-500/20 text-amber-400 border-amber-500/30'
                                                : 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30');
                                        $statusLabel = $total == 0 ? 'Habis' : ($total <= 5 ? 'Rendah' : 'Tersedia');
                                    @endphp
                                    <tr class="border-b border-slate-700/50 hover:bg-slate-700/30 transition product-row"
                                        data-name="{{ strtolower($product['name']) }}"
                                        data-code="{{ strtolower($product['item_code'] ?? '') }}"
                                        data-category="{{ $category }}">
                                        <td class="px-5 py-4">
                                            <span class="font-medium text-slate-200">{{ $product['name'] }}</span>
                                        </td>
                                        <td class="px-5 py-4 text-slate-400 font-mono text-xs">
                                            {{ $product['item_code'] ?? '—' }}
                                        </td>
                                        <td class="px-5 py-4 text-slate-300">
                                            BND {{ number_format($product['price'], 2) }}
                                        </td>
                                        <td class="px-5 py-4">
                                            @if(!empty($product['stock']))
                                                <div class="flex flex-wrap gap-1.5">
                                                    @foreach($product['stock'] as $s)
                                                        @php
                                                            $sv = $s['size_value'] ?? 0;
                                                            $sClass = $sv == 0 ? 'bg-rose-900/40 text-rose-400 border-rose-700/50'
                                                                : ($sv <= 3 ? 'bg-amber-900/40 text-amber-400 border-amber-700/50'
                                                                    : 'bg-slate-700 text-slate-300 border-slate-600');
                                                        @endphp
                                                        <span class="text-xs font-medium px-2 py-0.5 rounded border {{ $sClass }}">
                                                            {{ $s['size_key'] }}: {{ $sv }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-slate-600 italic text-xs">Tidak ada data ukuran</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <span
                                                class="text-lg font-bold @if($total == 0) text-rose-400 @elseif($total <= 5) text-amber-400 @else text-slate-100 @endif">
                                                {{ $total }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <span
                                                class="text-xs font-semibold px-2.5 py-1 rounded-full border {{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

    <script>
        function filterTable() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            document.querySelectorAll('.product-row').forEach(row => {
                const name = row.dataset.name || '';
                const code = row.dataset.code || '';
                row.style.display = (name.includes(query) || code.includes(query)) ? '' : 'none';
            });
        }

        function filterCategory(cat) {
            // Update active tab styling
            document.querySelectorAll('.cat-btn').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-slate-800', 'text-slate-400');
            });
            const slugId = cat === 'All' ? 'cat-All' : 'cat-' + cat.toLowerCase().replace(/\s+/g, '-');
            const active = document.getElementById(slugId);
            if (active) {
                active.classList.add('bg-blue-600', 'text-white');
                active.classList.remove('bg-slate-800', 'text-slate-400');
            }

            document.querySelectorAll('.category-section').forEach(section => {
                if (cat === 'All') {
                    section.style.display = '';
                } else {
                    section.style.display = section.dataset.category === cat ? '' : 'none';
                }
            });
        }
    </script>
</body>

</html>