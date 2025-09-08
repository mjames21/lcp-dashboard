{{-- resources/views/layouts/app.blade.php --}}
@php
    use Illuminate\Support\Facades\Route as RouteFacade;

    $ui = [
        'title'     => 'LCPD – Local Council Performance Dashboard',
        'logo_path' => 'images/logo.png',
        'logo_class'=> 'h-8',
        'logo_alt'  => 'Logo',
        'footer'    => [
            'org_name' => 'LPC Tracker',
            'org_url'  => '#',
            'extra_links' => [
                ['label' => 'Privacy Policy', 'url' => '/privacy-policy'],
            ],
        ],
        'nav' => [
            [
                'type'   => 'link',
                'label'  => 'Dashboard',
                'icon'   => 'fa-gauge-high',
                'route'  => 'dashboard',
                'active' => ['dashboard'],
            ],
            [
                'type'   => 'link',
                'label'  => 'Compare Councils',
                'icon'   => 'fa-arrows-left-right-to-line',
                'route'  => 'compare.councils',
                'active' => ['compare.councils'],
            ],
            [
                'type'  => 'group',
                'label' => 'Sectors',
                'icon'  => 'fa-layer-group',
                'children' => [
                    [ 'type'=>'link','label'=>'Health','route'=>'sector.dashboard','params'=>['code'=>'Health'], 'active'=>['sector/Health','sector.dashboard'] ],
                    [ 'type'=>'link','label'=>'Education','route'=>'sector.dashboard','params'=>['code'=>'Education'], 'active'=>['sector/Education','sector.dashboard'] ],
                    [ 'type'=>'link','label'=>'Agriculture','route'=>'sector.dashboard','params'=>['code'=>'Agriculture'], 'active'=>['sector/Agriculture','sector.dashboard'] ],
                    [ 'type'=>'link','label'=>'Finance','route'=>'sector.dashboard','params'=>['code'=>'Finance'], 'active'=>['sector/Finance','sector.dashboard'] ],
                ],
            ],
            [
                'type'   => 'link',
                'label'  => 'Map',
                'icon'   => 'fa-map-location-dot',
                'route'  => 'map.view',
                'active' => ['map.view'],
            ],
            [
                'type'   => 'link',
                'label'  => 'Data Collection',
                'icon'   => 'fa-table',
                'route'  => 'data.collect',
                'active' => ['data.collect'],
            ],
            [
                'type' => 'group',
                'label' => 'Exports',
                'icon'  => 'fa-file-export',
                'children' => [
                    [ 'type' => 'link', 'label' => 'Finance CSV',    'route' => 'export.finance',    'active' => [] ],
                    [ 'type' => 'link', 'label' => 'Indicator CSV',  'route' => 'export.indicators', 'active' => [] ],
                    [ 'type' => 'link', 'label' => 'Projects CSV',   'route' => 'export.projects',   'active' => [] ],
                    [ 'type' => 'link', 'label' => 'Issues CSV',     'route' => 'export.issues',     'active' => [] ],
                ],
             ],
        ],
    ];

    // helpers ---------------------------------------------------------------
    $hrefFor = function(array $item): string {
        if (!empty($item['url'])) return (string) $item['url'];
        if (!empty($item['route'])) {
            $name   = $item['route'];
            $params = $item['params'] ?? [];
            return RouteFacade::has($name) ? route($name, $params) : '#';
        }
        return '#';
    };

    $isActive = function(array $item): bool {
        $patterns = $item['active'] ?? [];
        foreach ($patterns as $p) {
            if (str_contains($p, '/') || str_contains($p, '*')) {
                if (request()->is($p)) return true;
                continue;
            }
            if (request()->routeIs($p)) {
                $params = $item['params'] ?? [];
                $ok = true;
                foreach ($params as $k => $v) {
                    if ((string) request()->route($k) !== (string) $v) { $ok = false; break; }
                }
                if ($ok) return true;
            }
        }
        return false;
    };

    $anyActive = function(array $children) use (&$anyActive, $isActive): bool {
        foreach ($children as $c) {
            if ($isActive($c)) return true;
            if (!empty($c['children']) && $anyActive($c['children'])) return true;
        }
        return false;
    };

    // Renders links & groups. Active highlighting now applies to top-level too.
    $renderNav = function(array $items, int $depth = 0) use (&$renderNav, $hrefFor, $isActive, $anyActive): string {
        $out = '';
        foreach ($items as $it) {
            $type     = $it['type'] ?? 'link';
            $label    = $it['label'] ?? 'Item';
            $icon     = $it['icon']  ?? null;
            $title    = $it['title'] ?? $label;
            $external = !empty($it['external']);

            if ($type === 'group') {
                $open = $anyActive($it['children'] ?? []) ? 'true' : 'false';
                $out .= '<div class="mt-3" x-data="{ open: '.$open.' }">';
                $out .= '<button class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100" @click="open = !open" :title="sidebarMini ? $el.dataset.tt : \'\'" data-tt="'.e($label).'">';
                $out .= '<i class="fa-solid '.e($icon ?? 'fa-layer-group').' w-5 text-gray-900"></i>';
                $out .= '<span class="flex-1 text-left" x-show="!sidebarMini">'.e($label).'</span>';
                $out .= '<i class="fa-solid fa-chevron-down text-xs ml-auto" x-show="!sidebarMini" :class="open ? \'rotate-180\' : \'\'"></i>';
                $out .= '</button>';

                // NOTE: removed x-collapse to avoid Alpine plugin requirement
                $pad = $depth === 0 ? 'pl-10' : 'pl-6';
                $out .= '<div x-show="open && !sidebarMini" class="'.$pad.' pr-2 space-y-1">';
                $out .= $renderNav($it['children'] ?? [], $depth + 1);
                $out .= '</div>';
                $out .= '</div>';
                continue;
            }

            $active = $isActive($it);
            $href   = $hrefFor($it);
            $target = $external ? ' target="_blank" rel="noopener noreferrer"' : '';
            $state  = $active
                ? 'bg-gray-200 text-gray-900 font-semibold'   // highlight ANY active item (top or sub)
                : 'text-gray-700 hover:bg-gray-100';

            if ($depth > 0 && empty($icon)) {
                $out .= '<a href="'.e($href).'" class="block px-3 py-2 rounded-md '.$state.'" aria-current="'.($active?'page':'false').'" :title="sidebarMini ? $el.dataset.tt : \'\'" data-tt="'.e($title).'"'.$target.'>'.e($label);
                if ($external) $out .= ' <i class="fa-solid fa-arrow-up-right-from-square text-xs ml-1"></i>';
                $out .= '</a>';
            } else {
                $out .= '<a href="'.e($href).'" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors '.$state.'" aria-current="'.($active?'page':'false').'" :title="sidebarMini ? $el.dataset.tt : \'\'" data-tt="'.e($title).'"'.$target.'>';
                $out .= '<i class="fa-solid '.e($icon ?? 'fa-circle-dot').' w-5 text-gray-900"></i>';
                $out .= '<span x-show="!sidebarMini">'.e($label).'</span>';
                if ($external) $out .= '<i x-show="!sidebarMini" class="fa-solid fa-arrow-up-right-from-square text-xs ml-1"></i>';
                $out .= '</a>';
            }
        }
        return $out;
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ sidebarOpen: false, sidebarMini: false }"
      x-init="sidebarMini = JSON.parse(localStorage.getItem('lcpd.sidebarMini') ?? 'false')"
      @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
      @toggle-sidebar-mini.window="sidebarMini = !sidebarMini; localStorage.setItem('lcpd.sidebarMini', JSON.stringify(sidebarMini))">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? ($ui['title'] ?? 'LCPD – Local Council Performance Dashboard') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
    <style>
        .scroll-thin { scrollbar-width: thin; }
        .scroll-thin::-webkit-scrollbar { width: 8px; }
        .scroll-thin::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.1); border-radius: 8px; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100 text-sm text-gray-800">
<div class="min-h-screen flex">

    <aside class="w-64 bg-white text-black h-screen flex flex-col border-r shadow-sm"
           :class="[ sidebarMini ? 'w-20' : 'w-72', sidebarOpen ? 'translate-x-0' : '-translate-x-full', 'lg:translate-x-0' ]"
           @keydown.window.ctrl.b.prevent="$dispatch('toggle-sidebar-mini')"
           aria-label="Sidebar Navigation">

        {{-- Logo --}}
        <div class="h-14 flex items-center justify-center border-b">
            <img src="{{ asset($ui['logo_path'] ?? 'img/switch-logo.jpg') }}"
                 class="{{ $ui['logo_class'] ?? 'h-8' }}"
                 alt="{{ $ui['logo_alt'] ?? 'Logo' }}">
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-4 py-6 space-y-2 text-sm font-medium">
            {!! $renderNav($ui['nav']) !!}
        </nav>

        {{-- Logout --}}
        <div class="px-4 py-3 border-t text-sm">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-2 text-red-600 hover:underline">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col bg-white">
        @livewire('navigation-menu')

        <main class="flex-1 bg-gray-50 px-6 py-4">
            {{ $slot }}
        </main>

        <footer class="bg-white border-t px-6 py-4 text-sm text-gray-500">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <span class="mb-2 md:mb-0">
                    © {{ date('Y') }} <a href="{{ $ui['footer']['org_url'] ?? '#' }}" class="hover:underline text-gray-600">{{ $ui['footer']['org_name'] ?? 'SPS - CAS' }}</a>. All Rights Reserved.
                </span>
                <ul class="flex space-x-4">
                    @foreach(($ui['footer']['extra_links'] ?? []) as $link)
                        <li><a href="{{ $link['url'] ?? '#' }}" class="hover:underline text-gray-500">{{ $link['label'] ?? '' }}</a></li>
                    @endforeach
                </ul>
            </div>
        </footer>
    </div>
</div>

@livewireScripts
@stack('modals')
</body>
</html>
