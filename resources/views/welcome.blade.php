{{-- resources/views/welcome.blade.php (Tailwind CDN version) --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Local Council Performance Dashboard</title>

    {{-- Tailwind CSS via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['Figtree', 'ui-sans-serif', 'system-ui'] },
          }
        }
      }
    </script>

    {{-- Figtree font --}}
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <style>
      html { scroll-behavior: smooth; }
      body { font-family: 'Figtree', ui-sans-serif, system-ui; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    {{-- Top Navigation --}}
    <header class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b">
        <div class="max-w-7xl mx-auto px-6">
            <div class="h-16 flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <img src="{{ asset('img/switch-logo.jpg') }}" alt="Logo" class="h-8 w-8 rounded object-cover">
                    <span class="font-semibold text-gray-900">LCPD</span>
                </a>

                <nav class="hidden md:flex items-center gap-6 text-sm">
                    <a href="#features" class="hover:text-gray-900 text-gray-700">Features</a>
                    <a href="#how-it-works" class="hover:text-gray-900 text-gray-700">How it works</a>
                    <a href="#contact" class="hover:text-gray-900 text-gray-700">Contact</a>
                </nav>

                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="border border-gray-300 hover:bg-gray-100 text-gray-800 px-3 py-1.5 rounded-lg text-sm">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="border border-gray-300 hover:bg-gray-100 text-gray-800 px-3 py-1.5 rounded-lg text-sm">
                            Sign in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="bg-gray-900 hover:bg-gray-800 text-white px-3 py-1.5 rounded-lg text-sm">
                                Get started
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none"
             style="background: radial-gradient(60rem 30rem at 80% -10%, rgba(31,41,55,0.08), transparent),
                             radial-gradient(40rem 20rem at -10% 10%, rgba(31,41,55,0.06), transparent);"></div>

        <div class="max-w-7xl mx-auto px-6">
            <div class="py-16 md:py-24 grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
                <div>
                    <span class="inline-flex items-center gap-2 text-xs font-medium px-2.5 py-1 rounded-full bg-white border text-gray-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Live data collection
                    </span>

                    <h1 class="mt-4 text-3xl md:text-5xl font-bold leading-tight text-gray-900">
                        Local Council Performance, <span class="text-gray-700">made visible.</span>
                    </h1>
                    <p class="mt-4 text-gray-600 text-base md:text-lg leading-relaxed">
                        Track indicators, finance, projects, and governance issues across councils.
                        Compare trends, export reports, and make data-driven decisions with confidence.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        @auth
                            <a href="{{ route('compare.councils') }}"
                               class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">
                                Compare Councils
                            </a>
                            <a href="{{ route('data.collect') }}"
                               class="border border-gray-300 hover:bg-gray-100 text-gray-800 px-4 py-2 rounded-lg text-sm">
                                Collect Data
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">
                                Sign in to start
                            </a>
                            <a href="#features"
                               class="border border-gray-300 hover:bg-gray-100 text-gray-800 px-4 py-2 rounded-lg text-sm">
                                Learn more
                            </a>
                        @endauth
                    </div>

                    <div class="mt-8 grid grid-cols-3 gap-6 max-w-lg">
                        <div>
                            <div class="text-2xl font-semibold text-gray-900">24+</div>
                            <div class="text-xs text-gray-500">Councils</div>
                        </div>
                        <div>
                            <div class="text-2xl font-semibold text-gray-900">120+</div>
                            <div class="text-xs text-gray-500">Indicators</div>
                        </div>
                        <div>
                            <div class="text-2xl font-semibold text-gray-900">4</div>
                            <div class="text-xs text-gray-500">Core modules</div>
                        </div>
                    </div>
                </div>

                {{-- Preview card --}}
                <div class="relative">
                    <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                        <div class="border-b px-4 py-2 text-xs text-gray-500">Preview</div>
                        <div class="p-4">
                            <div class="h-56 md:h-72 w-full rounded-lg border bg-gradient-to-br from-gray-50 to-white flex items-center justify-center">
                                <div class="text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-10 w-10" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M3 3h18v2H3V3Zm0 6h18v2H3V9Zm0 6h18v2H3v-2Zm0 6h18v2H3v-2Z" opacity=".1"/>
                                        <path d="M7 12h3v7H7v-7Zm4-5h3v12h-3V7Zm4 8h3v4h-3v-4Z"/>
                                    </svg>
                                    <div class="mt-3 text-sm text-gray-600">Analytics & comparisons</div>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 text-xs text-gray-500">
                            Screenshots are illustrative. Log in to explore live data.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="py-14 md:py-20">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Everything you need in one place</h2>
                <p class="mt-3 text-gray-600">From data collection to decision-ready dashboards.</p>
            </div>

            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white border rounded-xl p-5">
                    <div class="h-9 w-9 rounded-lg bg-gray-900 text-white flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 3h18v2H3zM7 9h3v10H7zM12 5h3v14h-3zM17 13h3v6h-3z"/>
                        </svg>
                    </div>
                    <div class="font-semibold text-gray-900">Compare Councils</div>
                    <p class="mt-1 text-sm text-gray-600">Benchmark indicators, finance, projects, and issues.</p>
                </div>

                <div class="bg-white border rounded-xl p-5">
                    <div class="h-9 w-9 rounded-lg bg-gray-900 text-white flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 2h6v2h3a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h3V2z"/>
                        </svg>
                    </div>
                    <div class="font-semibold text-gray-900">Data Collection</div>
                    <p class="mt-1 text-sm text-gray-600">Livewire forms for finance, indicators, projects, and governance.</p>
                </div>

                <div class="bg-white border rounded-xl p-5">
                    <div class="h-9 w-9 rounded-lg bg-gray-900 text-white flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10 4l2 2h8a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2h6z"/>
                        </svg>
                    </div>
                    <div class="font-semibold text-gray-900">Sector Dashboards</div>
                    <p class="mt-1 text-sm text-gray-600">Quick-switch “pills” for HLTH, EDU, INFR, SANI and more.</p>
                </div>

                <div class="bg-white border rounded-xl p-5">
                    <div class="h-9 w-9 rounded-lg bg-gray-900 text-white flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 3v10m0 0l3-3m-3 3L9 10M5 20h14a2 2 0 002-2v-3H3v3a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="font-semibold text-gray-900">Exports</div>
                    <p class="mt-1 text-sm text-gray-600">Download clean XLSX snapshots of finance and indicators.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="py-14 md:py-20 bg-white border-t">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">How it works</h3>
                    <p class="mt-2 text-gray-600">Simple, guided flow from data entry to decisions.</p>
                </div>
                <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="bg-gray-50 border rounded-xl p-5">
                        <div class="text-xs text-gray-500">Step 1</div>
                        <div class="font-semibold text-gray-900 mt-1">Collect</div>
                        <p class="mt-1 text-sm text-gray-600">Submit finance, indicators, projects, and issues with validation.</p>
                    </div>
                    <div class="bg-gray-50 border rounded-xl p-5">
                        <div class="text-xs text-gray-500">Step 2</div>
                        <div class="font-semibold text-gray-900 mt-1">Compare</div>
                        <p class="mt-1 text-sm text-gray-600">Select councils and periods, then benchmark instantly.</p>
                    </div>
                    <div class="bg-gray-50 border rounded-xl p-5">
                        <div class="text-xs text-gray-500">Step 3</div>
                        <div class="font-semibold text-gray-900 mt-1">Decide</div>
                        <p class="mt-1 text-sm text-gray-600">Spot gaps and act—export and share insights.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-14 md:py-20">
        <div class="max-w-7xl mx-auto px-6">
            <div class="bg-gray-900 rounded-2xl text-white overflow-hidden">
                <div class="px-6 md:px-10 py-10 md:py-14 grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <div class="md:col-span-2">
                        <h3 class="text-2xl md:text-3xl font-bold">Start making data-driven decisions today</h3>
                        <p class="mt-2 text-gray-300">Sign in to access dashboards, comparisons, and exports.</p>
                    </div>
                    <div class="flex md:justify-end">
                        @auth
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center gap-2 bg-white text-gray-900 px-4 py-2 rounded-lg text-sm hover:bg-gray-100">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center gap-2 bg-white text-gray-900 px-4 py-2 rounded-lg text-sm hover:bg-gray-100">
                                Sign in
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer id="contact" class="border-t bg-white">
        <div class="max-w-7xl mx-auto px-6 py-8 text-sm text-gray-500">
            <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                <div>© {{ date('Y') }} SPS – CAS. All rights reserved.</div>
                <div class="flex items-center gap-4">
                    <a href="/privacy-policy" class="hover:text-gray-700">Privacy Policy</a>
                    <a href="mailto:info@example.com" class="hover:text-gray-700">Contact</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
