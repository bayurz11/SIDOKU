@extends('layouts.app')

@section('content')
<div class="space-y-8 animate-fadeInUp">
    <!-- Profile Header -->
    <div class="relative bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-800 rounded-2xl shadow-2xl overflow-hidden">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-sm">
            <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"></div>
        </div>
        
        <div class="relative p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2 bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                        Profile Settings
                    </h1>
                    <p class="text-xl text-blue-100">Manage your account information</p>
                </div>
                <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                    <span class="text-2xl font-bold text-white">{{ substr(auth()->user()->name, 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Settings Tabs -->
    <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-blue-50 px-6 py-4 border-b border-gray-200">
            <nav class="flex space-x-8">
                <button onclick="showTab('profile')" id="profile-tab" class="tab-button active flex items-center px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Profile Information
                </button>
                <button onclick="showTab('password')" id="password-tab" class="tab-button flex items-center px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Password & Security
                </button>
            </nav>
        </div>

        <!-- Tab Contents -->
        <div class="p-6">
            <!-- Profile Tab -->
            <div id="profile-content" class="tab-content">
                <livewire:profile-form />
            </div>

            <!-- Password Tab -->
            <div id="password-content" class="tab-content hidden">
                <livewire:password-form />
            </div>
        </div>
    </div>
</div>

<style>
    .tab-button {
        color: #6b7280;
        background: transparent;
    }
    
    .tab-button:hover {
        color: #374151;
        background: rgba(59, 130, 246, 0.1);
        transform: translateY(-1px);
    }
    
    .tab-button.active {
        color: #2563eb;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(147, 51, 234, 0.1));
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
    }
    
    .tab-content {
        animation: fadeInUp 0.4s ease-out;
    }
</style>

<script>
    function showTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Remove active class from all tab buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });
        
        // Show selected tab content
        document.getElementById(tabName + '-content').classList.remove('hidden');
        
        // Add active class to selected tab button
        document.getElementById(tabName + '-tab').classList.add('active');
    }
</script>
@endsection