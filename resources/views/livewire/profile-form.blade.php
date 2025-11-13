<div class="space-y-6">
    <!-- Profile Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-gray-900">Profile Information</h3>
            <p class="text-gray-600 mt-1">Update your account's profile information and email address.</p>
        </div>
        <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg">
            <span class="text-lg font-bold text-white">{{ substr($name ?? 'U', 0, 2) }}</span>
        </div>
    </div>

    <!-- Profile Form -->
    <form wire:submit.prevent="updateProfile" class="space-y-6">
        <!-- Form Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name Field -->
            <div class="space-y-2">
                <label for="profile_name" class="block text-sm font-semibold text-gray-700">
                    Full Name
                </label>
                <input 
                    type="text" 
                    id="profile_name" 
                    wire:model="name" 
                    class="block w-full px-3 py-3 border border-gray-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter your full name"
                >
                @error('name')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Field -->
            <div class="space-y-2">
                <label for="profile_email" class="block text-sm font-semibold text-gray-700">
                    Email Address
                </label>
                <input 
                    type="email" 
                    id="profile_email" 
                    wire:model="email" 
                    class="block w-full px-3 py-3 border border-gray-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter your email address"
                >
                @error('email')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end pt-6 border-t border-gray-200">
            <div class="flex space-x-3">
                <button 
                    type="button" 
                    wire:click="mount"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                >
                    Reset
                </button>
                <button 
                    type="submit" 
                    class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>Update Profile</span>
                    <span wire:loading>Updating...</span>
                </button>
            </div>
        </div>
    </form>
</div>