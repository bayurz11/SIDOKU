<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('public/doc.png') }}" type="image/png">

    <title>{{ config('app.name', 'SIDOKU') }}</title>

    <link href="{{ asset('public/css/app.css') }}" rel="stylesheet">

    <script>
        document.addEventListener('livewire:navigated', () => {
            // Reset modal scroll position jika diperlukan
            document.body.style.overflow = 'auto';
        });
    </script>

    @livewireStyles
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    <style>
        .sidebar-gradient {
            background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%);
        }

        .navbar-gradient {
            background: linear-gradient(90deg, #ffffff 0%, #f8fafc 100%);
        }

        .sidebar-toggle {
            transition: all 0.3s ease;
        }

        .sidebar-hidden {
            transform: translateX(-100%);
        }

        .main-content-expanded {
            margin-left: 0;
        }

        @media (min-width: 768px) {
            .sidebar-toggle {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 16rem;
            }
        }

        /* Modern Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-slideInRight {
            animation: slideInRight 0.4s ease-out;
        }

        /* Glassmorphism Effects */
        .glass {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        /* Modern Shadow Effects */
        .shadow-modern {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .shadow-modern-lg {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Hover Glow Effects */
        .hover-glow {
            transition: all 0.3s ease;
        }

        .hover-glow:hover {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
        }

        /* Modern Loading Spinner */
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(45deg, #5a6fd8, #6a4190);
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-50">

    @auth
        <div class="min-h-screen">
            <!-- Sidebar -->

            <!-- Sidebar Navigation -->
            @include('layouts.sidebar')

            <!-- Main Content -->
            <div id="main-content" class="main-content transition-all duration-300 ease-in-out">
                <!-- Top Navigation Bar -->
                <nav class="navbar-gradient shadow-sm border-b border-gray-200">
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <!-- Mobile menu button -->
                                <button id="sidebar-toggle" type="button"
                                    class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16"></path>
                                    </svg>
                                </button>

                                <!-- Breadcrumb -->
                                <nav class="ml-4 md:ml-0">
                                    <ol class="flex items-center space-x-2 text-sm">
                                        <li>
                                            <div class="flex items-center">
                                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />

                                                </svg>
                                                <a href="{{ route('dashboard') }}"
                                                    class="ml-2 text-gray-500 hover:text-gray-700">Dashboard</a>
                                            </div>
                                        </li>
                                        @if (!request()->routeIs('dashboard'))
                                            <li>
                                                <div class="flex items-center">
                                                    <svg class="h-4 w-4 text-gray-400" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                            clip-rule="evenodd"></path>
                                                    </svg>
                                                    <span class="ml-2 text-gray-700 font-medium">
                                                        @if (request()->routeIs('users.*'))
                                                            Users Management
                                                        @elseif(request()->routeIs('roles.*'))
                                                            Roles & Permissions
                                                        @elseif(request()->routeIs('profile.*'))
                                                            Profile Settings
                                                        @elseif(request()->routeIs('department.*'))
                                                            Department Management
                                                        @elseif(request()->routeIs('document_types.*'))
                                                            Document Type Management
                                                        @elseif(request()->routeIs('document_categories.*'))
                                                            Document Category Management
                                                        @elseif(request()->routeIs('document_prefix_settings.*'))
                                                            Document Prefix Settings
                                                        @elseif(request()->routeIs('ipc.product-checks.*'))
                                                            Kadar Air Produk
                                                        @elseif(request()->routeIs('ipc.tiup-botol.*'))
                                                            Tiup Botol
                                                        @elseif(request()->routeIs('documents.*'))
                                                            Document
                                                        @else
                                                            {{ ucwords(str_replace(['-', '_'], ' ', request()->route()->getName())) }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </li>
                                        @endif
                                    </ol>
                                </nav>
                            </div>

                            {{-- RIGHT: Bell + Search + Avatar --}}
                            <div class="flex items-center gap-3">

                                {{-- Notifications --}}
                                @php
                                    $notifications = auth()->user()?->unreadNotifications()->latest()->take(5)->get();
                                @endphp

                                <div class="relative">
                                    <button type="button" x-on:click="openNotif = !openNotif"
                                        class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                        </svg>
                                        <span class="absolute top-1 right-1 block h-2 w-2 rounded-full bg-red-500"></span>
                                        @if ($notifications && $notifications->count())
                                            <span
                                                class="absolute top-1 right-1 block h-2 w-2 rounded-full bg-red-500"></span>
                                        @endif
                                    </button>

                                    <div x-show="openNotif"
                                        class="absolute right-0 mt-2 w-80 bg-white shadow-lg rounded-xl border border-gray-200 z-50"
                                        x-cloak>
                                        <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                                            <span class="text-sm font-semibold text-gray-800">Notifications</span>
                                            <a href="#" class="text-xs text-blue-600 hover:underline">View all</a>
                                        </div>

                                        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                                            @forelse($notifications as $notif)
                                                @php $data = $notif->data; @endphp
                                                <a href="{{ $data['url'] ?? '#' }}"
                                                    class="block px-4 py-3 hover:bg-gray-50 text-sm text-gray-700">
                                                    <div class="font-semibold">{{ $data['title'] ?? '-' }}</div>
                                                    <div class="text-xs text-gray-500 mt-0.5">
                                                        {{ $data['message'] ?? '' }}
                                                    </div>
                                                    <div class="text-[10px] text-gray-400 mt-1">
                                                        {{ $notif->created_at->diffForHumans() }}
                                                    </div>
                                                </a>
                                            @empty
                                                <div class="px-4 py-3 text-xs text-gray-500">
                                                    Tidak ada notifikasi baru.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>


                                {{-- Avatar + Dropdown (compact, no focus ring) --}}
                                @php
                                    $name = Auth::user()->name ?? 'User';
                                    $email = Auth::user()->email ?? '';

                                    // Ambil dua huruf pertama dari nama (tanpa spasi)
                                    $nameClean = preg_replace('/\s+/', '', $name);
                                    $initials = strtoupper(substr($nameClean, 0, 2));
                                @endphp

                                <div x-data="{ open: false }" @keydown.escape.window="open=false" class="relative">
                                    <!-- Trigger: avatar + chevron badge -->
                                    <button @click="open=!open" @click.outside="open=false" type="button"
                                        class="relative h-10 w-10 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center shadow hover:brightness-95 select-none outline-none ring-0 focus:outline-none focus:ring-0 focus-visible:outline-none active:outline-none"
                                        aria-haspopup="menu" :aria-expanded="open.toString()"
                                        style="-webkit-tap-highlight-color: transparent;">
                                        <span class="text-sm">{{ $initials }}</span>

                                        <span
                                            class="absolute -bottom-0.5 -right-0.5 h-4 w-4 rounded-full bg-white ring-1 ring-black/10 flex items-center justify-center">
                                            <svg class="h-3 w-3 text-gray-500 transition-transform duration-200 transform-gpu"
                                                :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </span>

                                        <span class="sr-only">Open user menu</span>
                                    </button>

                                    <!-- Menu -->
                                    <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-150"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-1"
                                        class="absolute right-0 mt-2 w-56 z-50 rounded-xl bg-white shadow-lg ring-1 ring-black/5 overflow-hidden"
                                        role="menu" aria-label="User menu">
                                        <div class="px-4 py-3 bg-gray-50">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $name }}
                                            </p>
                                            @if ($email)
                                                <p class="text-xs text-gray-600 truncate">{{ $email }}</p>
                                            @endif
                                        </div>
                                        <div class="my-1 border-t border-gray-100"></div>
                                        <p class="px-4 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Account</p>

                                        <div class="py-1">
                                            <a href="{{ route('profile.index') }}"
                                                class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                role="menuitem">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 7a3 3 0 11-6 0 3 3 0 016 0zM4 21a8 8 0 1116 0H4z" />
                                                </svg>
                                                Profile Settings
                                            </a>
                                        </div>
                                        <div class="my-1 border-t border-gray-100"></div>
                                        <form method="POST" action="{{ route('logout') }}" role="none">
                                            @csrf
                                            <button type="submit"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                                                role="menuitem">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" />
                                                </svg>
                                                Sign Out
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Page Content -->
                <main class="p-6 min-h-screen bg-gradient-to-br from-gray-50 via-blue-50/30 to-purple-50/30">
                    <!-- Modern Flash Messages -->
                    @if (session('message'))
                        <div class="mb-6 bg-gradient-to-r from-blue-50 to-emerald-50 border border-blue-200 text-blue-700 px-6 py-4 rounded-2xl shadow-lg backdrop-blur-sm"
                            role="alert">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">{{ session('message') }}</span>
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl shadow-lg backdrop-blur-sm"
                            role="alert">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </main>

                <!-- Modern Footer -->
                <footer class="bg-gradient-to-r from-white via-gray-50 to-white border-t border-gray-200 shadow-lg">
                    <div class="px-6 py-6">
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center space-x-3 text-gray-600">
                                <div
                                    class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">
                                        {{ config('app.name', 'SIDOKU') }}</div>
                                    <div class="text-xs text-gray-500">© {{ date('Y') }} All rights reserved.</div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-6 text-gray-500">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                    <span class="text-xs font-medium">Version 1.0.0</span>
                                </div>
                                <div class="hidden md:flex items-center space-x-4">
                                    <a href="#"
                                        class="hover:text-blue-600 transition-colors duration-200 flex items-center space-x-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        <span>Documentation</span>
                                    </a>
                                    <a href="#"
                                        class="hover:text-blue-600 transition-colors duration-200 flex items-center space-x-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z">
                                            </path>
                                        </svg>
                                        <span>Support</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <!-- Mobile sidebar overlay -->
        <div id="sidebar-overlay"
            class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 md:hidden transform opacity-0 pointer-events-none transition-opacity duration-300 ease-linear">
        </div>

        <script>
            // Sidebar toggle functionality
            document.addEventListener('DOMContentLoaded', function() {
                const sidebarToggle = document.getElementById('sidebar-toggle');
                const sidebar = document.getElementById('sidebar');
                const sidebarOverlay = document.getElementById('sidebar-overlay');
                const mainContent = document.getElementById('main-content');

                function toggleSidebar() {
                    sidebar.classList.toggle('-translate-x-full');
                    sidebarOverlay.classList.toggle('opacity-0');
                    sidebarOverlay.classList.toggle('pointer-events-none');
                }

                if (sidebarToggle) {
                    sidebarToggle.addEventListener('click', toggleSidebar);
                }

                if (sidebarOverlay) {
                    sidebarOverlay.addEventListener('click', toggleSidebar);
                }
            });
        </script>
    @else
        <!-- Not authenticated content -->
        <div class="min-h-screen flex items-center justify-center">
            <div class="text-center">
                <p class="text-gray-500">Please log in to access the application.</p>
                <a href="{{ route('login') }}"
                    class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Login</a>
            </div>
        </div>
    @endauth

    @livewireScripts

    <!-- SweetAlert2 for modern alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
    <script>
        // Modern Alert System
        window.showAlert = function(type, title, text, options = {}) {
            const config = {
                title: title,
                text: text,
                icon: type,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'OK',
                ...options
            };

            return Swal.fire(config);
        };

        window.showConfirm = function(title, text, confirmText = 'Yes, proceed!', cancelText = 'Cancel') {
            return Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#ef4444',
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                reverseButtons: true
            });
        };

        window.showSuccess = function(title, text = '') {
            return showAlert('success', title, text);
        };

        window.showError = function(title, text = '') {
            return showAlert('error', title, text);
        };

        window.showWarning = function(title, text = '') {
            return showAlert('warning', title, text);
        };

        window.showInfo = function(title, text = '') {
            return showAlert('info', title, text);
        };

        // Toast notifications
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        window.showToast = function(type, title) {
            Toast.fire({
                icon: type,
                title: title
            });
        };

        // Handle Laravel flash messages with modern alerts
        @if (session('message'))
            showToast('success', '{{ session('message') }}');
        @endif

        @if (session('error'))
            showToast('error', '{{ session('error') }}');
        @endif

        @if (session('warning'))
            showToast('warning', '{{ session('warning') }}');
        @endif

        @if (session('info'))
            showToast('info', '{{ session('info') }}');
        @endif

        // Livewire integration
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-alert', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                showAlert(data.type, data.title, data.text, data.options || {});
            });

            Livewire.on('show-toast', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                showToast(data.type, data.title);
            });

            Livewire.on('show-confirm', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                showConfirm(data.title, data.text, data.confirmText, data.cancelText)
                    .then((result) => {
                        if (result.isConfirmed && data.method) {
                            Livewire.dispatch(data.method, data.params || {});
                        }
                    });
            });
        });

        // Logout confirmation
        function confirmLogout() {
            showConfirm(
                'Sign Out',
                'Are you sure you want to sign out?',
                'Yes, sign out',
                'Cancel'
            ).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }
    </script>

</body>

</html>
