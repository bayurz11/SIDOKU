<?php

namespace App\Livewire;

use App\Shared\Traits\WithAlerts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class PasswordForm extends Component
{
    use WithAlerts;

    public $current_password = '';
    public $password = '';
    public $password_confirmation = '';
    public $show_current_password = false;
    public $show_new_password = false;
    public $show_confirm_password = false;

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $user = Auth::user();
        
        // Check current password
        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'The current password is incorrect.');
            return;
        }

        // Update password
        $user->update([
            'password' => Hash::make($this->password),
        ]);

        // Clear form fields
        $this->reset(['current_password', 'password', 'password_confirmation']);
        
        // Reset visibility toggles
        $this->show_current_password = false;
        $this->show_new_password = false;
        $this->show_confirm_password = false;

        $this->showSuccessToast('Password updated successfully!');
        $this->dispatch('$refresh');
    }

    public function toggleCurrentPasswordVisibility()
    {
        $this->show_current_password = !$this->show_current_password;
    }

    public function toggleNewPasswordVisibility()
    {
        $this->show_new_password = !$this->show_new_password;
    }

    public function toggleConfirmPasswordVisibility()
    {
        $this->show_confirm_password = !$this->show_confirm_password;
    }

    public function getPasswordStrength()
    {
        if (empty($this->password)) {
            return ['strength' => 0, 'text' => '', 'color' => 'gray'];
        }

        $score = 0;
        $password = $this->password;

        // Length check
        if (strlen($password) >= 8) $score++;
        
        // Character variety checks
        if (preg_match('/[a-z]/', $password)) $score++;
        if (preg_match('/[A-Z]/', $password)) $score++;
        if (preg_match('/[0-9]/', $password)) $score++;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $score++;

        // Determine strength
        if ($score <= 2) {
            return ['strength' => 25, 'text' => 'Weak', 'color' => 'red'];
        } elseif ($score <= 3) {
            return ['strength' => 50, 'text' => 'Fair', 'color' => 'yellow'];
        } elseif ($score <= 4) {
            return ['strength' => 75, 'text' => 'Good', 'color' => 'blue'];
        } else {
            return ['strength' => 100, 'text' => 'Strong', 'color' => 'green'];
        }
    }

    public function render()
    {
        return view('livewire.password-form', [
            'passwordStrength' => $this->getPasswordStrength()
        ]);
    }
}