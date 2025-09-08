{{-- resources/views/welcome.blade.php (Tailwind CDN version) --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Local Council Performance Dashboard — Decentralization Secretariat (Sierra Leone)</title>

    {{-- Tailwind CSS via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['Figtree', 'ui-sans-serif', 'system-ui'] },
            colors: {
              brand: { 900: '#1f2937', 800: '#374151' } // gray-900/800 alias for consistency
            }
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
                    <img src="{{ asset('images/logo.png') }}" alt="Decentralization Secretariat Logo" class="h-8 w-8 rounded object-cover">
                    <span class="font-semibold text-gray-900">LCPD • Decentralization Secretariat</span>
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
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-2 text-xs font-medium px-2.5 py-1 rounded-full bg-white border text-gray-700">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Live data collection
                        </span>
                        {{-- Ownership badge --}}
                        <span class="inline-flex items-center gap-2 text-xs font-medium px-2.5 py-1 rounded-full bg-gray-900 text-white">
                            Owned by the Decentralization Secretariat, Government of Sierra Leone
                        </span>
                    </div>

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
       <div class="h-56 md:h-72 w-full rounded-lg border bg-white p-4 flex flex-col">
  <!-- Header -->
  <div class="flex items-start justify-between gap-3">
    <div class="text-xs text-gray-500">
      <div class="font-semibold text-gray-900">Service coverage — Water</div>
      <div>Last 10 months</div>
    </div>
    <div class="shrink-0">
      <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg text-xs bg-gray-900 text-white">
        82.4% <span class="rounded bg-white/15 px-1.5 py-0.5">+1.8 pp</span>
      </span>
    </div>
  </div>

  <!-- Quick badges -->
  <div class="mt-2 flex flex-wrap gap-2 text-[11px] text-gray-600">
    <span class="px-2 py-0.5 rounded border">Avg: 78%</span>
    <span class="px-2 py-0.5 rounded border">Best: 85%</span>
    <span class="px-2 py-0.5 rounded border">Target: 80%</span>
  </div>

  <!-- Chart -->
  <svg viewBox="0 0 320 140" class="mt-3 w-full h-full">
    <defs>
      <!-- area gradient -->
      <linearGradient id="areaFill" x1="0" x2="0" y1="0" y2="1">
        <stop offset="0%" stop-color="#1f2937" stop-opacity="0.35"/>
        <stop offset="100%" stop-color="#1f2937" stop-opacity="0.05"/>
      </linearGradient>
      <!-- glow for current dot -->
      <filter id="glow" x="-50%" y="-50%" width="200%" height="200%">
        <feGaussianBlur stdDeviation="3" result="blur"/>
        <feMerge><feMergeNode in="blur"/><feMergeNode in="blur"/></feMerge>
      </filter>
    </defs>

    <!-- subtle grid -->
    <g stroke="#e5e7eb" stroke-width="1">
      <line x1="0" y1="30"  x2="320" y2="30"/>
      <line x1="0" y1="60"  x2="320" y2="60"/>
      <line x1="0" y1="90"  x2="320" y2="90"/>
      <line x1="0" y1="120" x2="320" y2="120"/>
    </g>

    <!-- baseline (target 80%) -->
    <line x1="0" y1="60" x2="320" y2="60" stroke="#d1d5db" stroke-width="2" stroke-dasharray="4 4"/>

    <!-- area under line (values: 68,72,74,77,79,81,82,83,84,85 => y=140-v) -->
    <path d="M10,72 L42,68 L74,66 L106,63 L138,61 L170,59 L202,58 L234,57 L266,56 L298,55 L298,140 L10,140 Z"
          fill="url(#areaFill)"/>

    <!-- line -->
    <polyline fill="none" stroke="#1f2937" stroke-width="2.25"
              points="10,72 42,68 74,66 106,63 138,61 170,59 202,58 234,57 266,56 298,55"/>

    <!-- current point + glow -->
    <circle cx="298" cy="55" r="4.5" fill="#1f2937" filter="url(#glow)"/>
    <circle cx="298" cy="55" r="2" fill="white" stroke="#1f2937" stroke-width="1.5"/>

    <!-- x ticks -->
    <g fill="#6b7280" font-size="10">
      <text x="8"   y="135">−9</text>
      <text x="72"  y="135">−6</text>
      <text x="138" y="135">−3</text>
      <text x="286" y="135">Now</text>
    </g>
  </svg>
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
                    <p class="mt-1 text-sm text-gray-600">Streamlined forms for finance, indicators, projects, and governance.</p>
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
                <div>© {{ date('Y') }} Decentralization Secretariat, Government of Sierra Leone. All rights reserved.</div>
                <div class="flex items-center gap-4">
                    <a href="/privacy-policy" class="hover:text-gray-700">Privacy Policy</a>
                    <a href="mailto:info@decsec.gov.sl" class="hover:text-gray-700">Contact</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
