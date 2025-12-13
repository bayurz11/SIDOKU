 <div id="sidebar"
     class="sidebar-toggle fixed inset-y-0 left-0 z-50 w-64 sidebar-gradient shadow-xl transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
     <!-- Sidebar Header -->
     <div class="flex items-center justify-center h-16 px-4 bg-black bg-opacity-20">
         <div class="flex items-center">
             <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                 <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                     </path>
                 </svg>
             </div>
             <span class="ml-3 text-white font-bold text-lg">{{ config('app.name', 'SIDOKU') }}</span>
         </div>
     </div>

     <!-- Sidebar Navigation -->
     <nav class="mt-8 px-4">
         <div class="space-y-2">
             <!-- Dashboard -->
             <a href="{{ route('dashboard') }}"
                 class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'bg-white bg-opacity-20 text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }} transition-colors duration-200">
                 <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                 </svg>
                 Dashboard
             </a>

             @php
                 // ===== ACTIVE ROUTE (UNTUK HIGHLIGHT & AUTO-OPEN) =====
                 $masterActive = request()->routeIs('department.*', 'document_types.*', 'document_prefix_settings.*');
                 $documentActive = request()->routeIs(
                     'documents.*',
                     'documents.approval-queue',
                     'document_revisions.*',
                 );
                 $ipcActive = request()->routeIs('ipc.product-checks.*', 'ipc.tiup-botol.*', 'ipc.product.*');
                 $accountActive = request()->routeIs('users.*', 'roles.*');

                 // ===== VISIBILITY (MINIMAL 1 PERMISSION DI GROUP) =====
                 $canSeeMasterMenu = auth()
                     ->user()
                     ->hasAnyPermission(['departments.view', 'document_types.view', 'document_prefix_settings.view']);

                 $canSeeDocumentMenu = auth()
                     ->user()
                     ->hasAnyPermission([
                         'documents.view',
                         'documents.review',
                         'documents.approve',
                         'documents.revision',
                     ]);

                 $canSeeIpcMenu = auth()
                     ->user()
                     ->hasAnyPermission(['ipc_product_checks.view']);

                 $canSeeAccountMenu = auth()
                     ->user()
                     ->hasAnyPermission(['users.view', 'roles.view']);

                 // ===== ACTIVE ACCORDION (HANYA JIKA MENU TAMPIL) =====
                 $activeMenuInitial =
                     $masterActive && $canSeeMasterMenu
                         ? 'master'
                         : ($documentActive && $canSeeDocumentMenu
                             ? 'document'
                             : ($ipcActive && $canSeeIpcMenu
                                 ? 'ipc'
                                 : ($accountActive && $canSeeAccountMenu
                                     ? 'account'
                                     : '')));
             @endphp


             {{-- WRAPPER ACCORDION --}}
             <div x-data="{ activeMenu: @js($activeMenuInitial) }" class="space-y-2">

                 {{-- ============== MASTER DATA ============== --}}
                 @if ($canSeeMasterMenu)
                     <div class="relative pt-4 mt-4 border-t border-blue-400 border-opacity-30">
                         <button @click="activeMenu = (activeMenu === 'master' ? '' : 'master')"
                             aria-controls="menu-master" :aria-expanded="(activeMenu === 'master').toString()"
                             class="group relative flex w-full items-center rounded-lg px-4 py-3 text-sm font-medium transition-colors duration-200 {{ $masterActive ? 'text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}"
                             type="button">
                             @if ($masterActive)
                                 <span
                                     class="absolute left-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r bg-emerald-400"></span>
                             @endif

                             <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round"
                                     d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                             </svg>

                             <span>Master Data</span>

                             <svg :class="{ 'rotate-180': activeMenu === 'master' }"
                                 class="ml-auto h-4 w-4 transform transition-transform duration-200" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                     d="M19 9l-7 7-7-7" />
                             </svg>
                         </button>

                         <div id="menu-master" x-show="activeMenu === 'master'" x-collapse
                             class="mt-1 pl-10 space-y-1 overflow-hidden">
                             @permission('departments.view')
                                 <a href="{{ route('department.index') }}"
                                     class="block rounded-md px-4 py-2 text-sm {{ request()->routeIs('department.*') ? 'bg-white bg-opacity-20 text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}">
                                     Department
                                 </a>
                             @endpermission

                             @permission('document_types.view')
                                 <a href="{{ route('document_types.index') }}"
                                     class="block rounded-md px-4 py-2 text-sm {{ request()->routeIs('document_types.*') ? 'bg-white bg-opacity-20 text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}">
                                     Document Type
                                 </a>
                             @endpermission

                             @permission('document_prefix_settings.view')
                                 <a href="{{ route('document_prefix_settings.index') }}"
                                     class="block rounded-md px-4 py-2 text-sm {{ request()->routeIs('document_prefix_settings.*') ? 'bg-white bg-opacity-20 text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}">
                                     Dokumen Prefix
                                 </a>
                             @endpermission
                         </div>
                     </div>
                 @endif

                 {{-- ============== DOKUMEN ============== --}}
                 @if ($canSeeDocumentMenu)
                     <div class="relative pt-4 mt-4 border-t border-blue-400 border-opacity-30">
                         <button @click="activeMenu = (activeMenu === 'document' ? '' : 'document')"
                             aria-controls="menu-document" :aria-expanded="(activeMenu === 'document').toString()"
                             class="group relative flex w-full items-center rounded-lg px-4 py-3 text-sm font-medium transition-colors duration-200 {{ $documentActive ? 'text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}"
                             type="button">
                             @if ($documentActive)
                                 <span
                                     class="absolute left-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r bg-emerald-400"></span>
                             @endif

                             <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round"
                                     d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                             </svg>

                             <span>Dokumen</span>

                             <svg :class="{ 'rotate-180': activeMenu === 'document' }"
                                 class="ml-auto h-4 w-4 transform transition-transform duration-200" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                     d="M19 9l-7 7-7-7" />
                             </svg>
                         </button>

                         <div id="menu-document" x-show="activeMenu === 'document'" x-collapse
                             class="mt-1 pl-10 space-y-1 overflow-hidden">
                             @permission('documents.view')
                                 <a href="{{ route('documents.index') }}"
                                     class="block rounded-md px-4 py-2 text-sm {{ request()->routeIs('documents.*') ? 'bg-white bg-opacity-20 text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}">
                                     Document List
                                 </a>
                             @endpermission

                             @if (auth()->user()->hasAnyPermission(['documents.review', 'documents.approve']))
                                 <a href="{{ route('documents.approval-queue') }}"
                                     class="block rounded-md px-4 py-2 text-sm {{ request()->routeIs('documents.approval-queue') ? 'bg-white bg-opacity-20 text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}">
                                     Approval Queue
                                 </a>
                             @endif


                             @permission('documents.revision')
                                 <a href="#"
                                     class="block rounded-md px-4 py-2 text-sm {{ request()->routeIs('document_revisions.*') ? 'bg-white bg-opacity-20 text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}">
                                     Document Revisions
                                 </a>
                             @endpermission
                         </div>
                     </div>
                 @endif

                 {{-- ============== IPC ============== --}}
                 @if ($canSeeIpcMenu)
                     <div class="relative pt-4 mt-4 border-t border-blue-400 border-opacity-30">
                         <button @click="activeMenu = (activeMenu === 'ipc' ? '' : 'ipc')" aria-controls="menu-ipc"
                             :aria-expanded="(activeMenu === 'ipc').toString()"
                             class="group relative flex w-full items-center rounded-lg px-4 py-3 text-sm font-medium transition-colors duration-200 {{ $ipcActive ? 'text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}"
                             type="button">
                             @if ($ipcActive)
                                 <span
                                     class="absolute left-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r bg-emerald-400"></span>
                             @endif

                             <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round"
                                     d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                             </svg>

                             <span>IPC</span>

                             <svg :class="{ 'rotate-180': activeMenu === 'ipc' }"
                                 class="ml-auto h-4 w-4 transform transition-transform duration-200" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                     d="M19 9l-7 7-7-7" />
                             </svg>
                         </button>

                         <div id="menu-ipc" x-show="activeMenu === 'ipc'" x-collapse
                             class="mt-1 pl-10 space-y-1 overflow-hidden">

                             @permission('ipc_product_checks.view')
                                 <a href="{{ route('ipc.product-checks.index') }}"
                                     class="block rounded-md px-4 py-2 text-sm {{ request()->routeIs('ipc.product-checks.*') ? 'bg-white bg-opacity-20 text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}">
                                     Kadar Air Produk
                                 </a>
                             @endpermission

                             @permission('ipc_product_checks.view')
                                 <a href="{{ route('ipc.product.index') }}"
                                     class="block rounded-md px-4 py-2 text-sm {{ request()->routeIs('ipc.product.*') ? 'bg-white bg-opacity-20 text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}">
                                     Produk
                                 </a>
                             @endpermission

                             @permission('ipc_product_checks.view')
                                 <a href="{{ route('ipc.tiup-botol.index') }}"
                                     class="block rounded-md px-4 py-2 text-sm {{ request()->routeIs('ipc.tiup-botol.*') ? 'bg-white bg-opacity-20 text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}">
                                     Tiup Botol
                                 </a>
                             @endpermission

                         </div>
                     </div>
                 @endif

                 {{-- ACCOUNT --}}
                 @if ($canSeeAccountMenu)
                     <div class="relative pt-4 mt-4 border-t border-blue-400 border-opacity-30">
                         <button @click="activeMenu = (activeMenu === 'account' ? '' : 'account')"
                             aria-controls="menu-account" :aria-expanded="(activeMenu === 'account').toString()"
                             class="group relative flex w-full items-center rounded-lg px-4 py-3 text-sm font-medium transition-colors duration-200 {{ $accountActive ? 'text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}"
                             type="button">
                             @if ($accountActive)
                                 <span
                                     class="absolute left-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r bg-emerald-400">
                                 </span>
                             @endif

                             {{-- ICON ACCOUNT (bisa diganti sesuai selera) --}}
                             <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                     d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                 <path stroke-linecap="round" stroke-linejoin="round"
                                     d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                             </svg>

                             <span>Users & Roles</span>

                             {{-- CHEVRON --}}
                             <svg :class="{ 'rotate-180': activeMenu === 'account' }"
                                 class="ml-auto h-4 w-4 transform transition-transform duration-200" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                     d="M19 9l-7 7-7-7" />
                             </svg>
                         </button>

                         <div id="menu-account" x-show="activeMenu === 'account'" x-collapse
                             class="mt-1 pl-10 space-y-1 overflow-hidden">
                             @permission('users.view')
                                 <a href="{{ route('users.index') }}"
                                     class="block rounded-md px-4 py-2 text-sm {{ request()->routeIs('users.*') ? 'bg-white bg-opacity-20 text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}">
                                     Users
                                 </a>
                             @endpermission

                             @permission('roles.view')
                                 <a href="{{ route('roles.index') }}"
                                     class="block rounded-md px-4 py-2 text-sm {{ request()->routeIs('roles.*') ? 'bg-white bg-opacity-20 text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}">
                                     Roles & Permissions
                                 </a>
                             @endpermission
                         </div>
                     </div>
                 @endif

             </div>
         </div>
     </nav>

     <!-- User Profile Section -->
     <div class="absolute bottom-0 w-full p-4">
         <div class="bg-white bg-opacity-10 rounded-lg p-3">
             <div class="flex items-center">
                 <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center">
                     <span class="text-xs font-bold text-blue-600">{{ substr(auth()->user()->name, 0, 2) }}</span>
                 </div>
                 <div class="ml-3 flex-1">
                     <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                     <p class="text-xs text-blue-200 truncate">{{ auth()->user()->email }}</p>
                 </div>
             </div>
             <form method="POST" action="{{ route('logout') }}" class="mt-3" id="logout-form">
                 @csrf
                 <button type="button" onclick="confirmLogout()"
                     class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-100 bg-white bg-opacity-10 rounded-md hover:bg-opacity-20 transition-colors duration-200">
                     <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                             d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                         </path>
                     </svg>
                     Sign Out
                 </button>
             </form>
         </div>
     </div>
 </div>
