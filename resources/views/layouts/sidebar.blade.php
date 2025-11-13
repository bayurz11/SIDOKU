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

             <div x-data="{ open: {{ request()->routeIs('department.*', 'document_types.*', 'document_prefix_settings.*') ? 'true' : 'false' }} }" class="relative pt-4 mt-4 border-t border-blue-400 border-opacity-30">
                 <button @click="open = !open" aria-controls="menu-master" :aria-expanded="open.toString()"
                     class="group relative flex w-full items-center rounded-lg px-4 py-3 text-sm font-medium transition-colors duration-200 {{ request()->routeIs('department.*', 'document_types.*', 'document_prefix_settings.*') ? 'text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}">
                     @if (request()->routeIs('department.*', 'document_types.*', 'document_prefix_settings.*'))
                         <span class="absolute left-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r bg-emerald-400"></span>
                     @endif

                     <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round"
                             d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                     </svg>

                     <span>Master Data</span>

                     <svg :class="{ 'rotate-180': open }"
                         class="ml-auto h-4 w-4 transform transition-transform duration-200" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                     </svg>
                 </button>

                 <div id="menu-master" x-show="open" x-collapse class="mt-1 pl-10 space-y-1 overflow-hidden">
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

             {{-- MENU: DOKUMEN SAJA --}}
             <div x-data="{ open: {{ request()->routeIs('documents.*', 'document_types.*', 'document_prefix_settings.*') ? 'true' : 'false' }} }" class="relative pt-4 mt-4 border-t border-blue-400 border-opacity-30">
                 <button @click="open = !open" aria-controls="menu-master" :aria-expanded="open.toString()"
                     class="group relative flex w-full items-center rounded-lg px-4 py-3 text-sm font-medium transition-colors duration-200 {{ request()->routeIs('department.*', 'document_types.*', 'document_prefix_settings.*') ? 'text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}">
                     @if (request()->routeIs('department.*', 'document_types.*', 'document_prefix_settings.*'))
                         <span class="absolute left-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r bg-emerald-400"></span>
                     @endif

                     <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round"
                             d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                     </svg>

                     <span>Master Data</span>

                     <svg :class="{ 'rotate-180': open }"
                         class="ml-auto h-4 w-4 transform transition-transform duration-200" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                     </svg>
                 </button>

                 <div id="menu-master" x-show="open" x-collapse class="mt-1 pl-10 space-y-1 overflow-hidden">
                     @permission('documents.view')
                         <a href="{{ route('documents.index') }}"
                             class="block rounded-md px-4 py-2 text-sm {{ request()->routeIs('documents.*') ? 'bg-white bg-opacity-20 text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }}">
                             Documents
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


             <!-- Account Settings -->
             <div class="pt-4 mt-4 border-t border-blue-400 border-opacity-30">
                 <p class="px-4 text-xs font-semibold text-blue-200 uppercase tracking-wider">Account</p>
                 <div class="mt-2 space-y-2">
                     <a href="{{ route('profile.index') }}"
                         class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('profile.*') ? 'bg-white bg-opacity-20 text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }} transition-colors duration-200">
                         <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                             </path>
                         </svg>
                         Profile Settings
                     </a>


                     <!-- User Management -->
                     @permission('users.view')
                         <a href="{{ route('users.index') }}"
                             class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('users.*') ? 'bg-white bg-opacity-20 text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }} transition-colors duration-200">
                             <svg xmlns="http://www.w3.org/2000/svg" class="mr-3 h-5 w-5" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                     d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                             </svg>
                             Users
                         </a>
                     @endpermission


                     <!-- Role Management -->
                     @permission('roles.view')
                         <a href="{{ route('roles.index') }}"
                             class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('roles.*') ? 'bg-white bg-opacity-20 text-white' : 'text-blue-100 hover:bg-white hover:bg-opacity-10 hover:text-white' }} transition-colors duration-200">
                             <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                     d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                 </path>
                             </svg>
                             Roles & Permissions
                         </a>
                     @endpermission

                 </div>
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
