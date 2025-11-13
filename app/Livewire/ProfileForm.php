<?php

namespace App\Livewire;

use App\Shared\Traits\WithAlerts;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProfileForm extends Component
{
    use WithAlerts;

    public $name = '';
    public $email = '';

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();
        
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->showSuccessToast('Profile updated successfully!');
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.profile-form');
    }
}