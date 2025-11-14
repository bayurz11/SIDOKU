<div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
    <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 px-6 py-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <div
                    class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                        </path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Users Management</h2>
                    <p class="text-sm text-gray-600 mt-1">Manage system users and their access</p>
                </div>
            </div>
            @permission('users.create')
                <button wire:click="$dispatch('openUserCreateForm')"
                    class="group bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-3 rounded-xl text-sm font-semibold flex items-center shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform duration-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add User
                </button>
            @endpermission

        </div>

        <div
            class="mt-6 flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0 lg:space-x-6">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input wire:model.live="search" type="text" placeholder="Search users by name, email..."
                        class="block w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                </div>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <label
                    class="flex items-center px-4 py-3 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                    <input wire:model.live="showInactive" type="checkbox"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                    <span class="ml-3 text-sm font-medium text-gray-700">Show Inactive</span>
                </label>
                <select wire:model.live="perPage"
                    class="px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-sm font-medium transition-all duration-200">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                    <th wire:click="sortBy('name')"
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200 transition-colors duration-200 rounded-tl-xl">
                        <div class="flex items-center space-x-2">
                            <span>Name</span>
                            @if ($sortField === 'name')
                                <div class="w-4 h-4 bg-blue-100 rounded flex items-center justify-center">
                                    <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </th>

                    <th wire:click="sortBy('email')"
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200 transition-colors duration-200">
                        <div class="flex items-center space-x-2">
                            <span>Email</span>
                            @if ($sortField === 'email')
                                <div class="w-4 h-4 bg-blue-100 rounded flex items-center justify-center">
                                    <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </th>

                    {{-- 🔹 NEW: Department --}}
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Department
                    </th>

                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Roles
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Created
                    </th>
                    <th
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tr-xl">
                        Actions
                    </th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($users as $user)
                    <tr
                        class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 transition-all duration-300 group">

                        {{-- NAME --}}
                        <td class="px-6 py-5 whitespace-nowrap">
                            <div class="flex items-center">
                                <div
                                    class="w-12 h-12 bg-gradient-to-br from-blue-400 to-purple-500 rounded-xl flex items-center justify-center mr-4 shadow-md group-hover:shadow-lg transition-shadow duration-300">
                                    <span class="text-sm font-bold text-white">{{ substr($user->name, 0, 2) }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">System user</div>
                                </div>
                            </div>
                        </td>

                        {{-- EMAIL --}}
                        <td class="px-6 py-5 whitespace-nowrap">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207">
                                    </path>
                                </svg>
                                <div class="text-sm font-medium text-gray-900">{{ $user->email }}</div>
                            </div>
                        </td>

                        {{-- 🔹 NEW: DEPARTMENT --}}
                        <td class="px-6 py-5 whitespace-nowrap">
                            @if ($user->department)
                                <span
                                    class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-gradient-to-r from-sky-100 to-blue-100 text-sky-800 shadow-sm">
                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253l-6 3.25V18l6 3.75 6-3.75v-8.497l-6-3.25z" />
                                    </svg>
                                    {{ $user->department->name }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400 italic">No department</span>
                            @endif
                        </td>

                        {{-- ROLES --}}
                        <td class="px-6 py-5 whitespace-nowrap">
                            <div class="flex flex-wrap gap-2">
                                @forelse($user->roles as $role)
                                    <span
                                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-gradient-to-r from-blue-100 to-indigo-100 text-blue-800 shadow-sm">
                                        <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                            </path>
                                        </svg>
                                        {{ $role->display_name }}
                                    </span>
                                @empty
                                    <span
                                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-gray-100 text-gray-600">
                                        No roles assigned
                                    </span>
                                @endforelse
                            </div>
                        </td>

                        {{-- STATUS, CREATED, ACTIONS tetap sama --}}
                        {{-- ... (biarkan seperti kode kamu tadi) ... --}}
                    </tr>
                @empty
                    <tr>
                        {{-- 🔹 Ubah colspan dari 6 -> 7 karena sekarang ada 7 kolom --}}
                        <td colspan="7" class="px-6 py-16 text-center">
                            {{-- empty state content tetap sama --}}
                            ...
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>


    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-blue-50 border-t border-gray-200 rounded-b-2xl">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $users->firstItem() ?? 0 }}</span> to <span
                    class="font-medium">{{ $users->lastItem() ?? 0 }}</span> of <span
                    class="font-medium">{{ $users->total() }}</span> users
            </div>
            <div class="flex-1 flex justify-center">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
