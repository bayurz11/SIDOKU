<?php

namespace App\Shared\Traits;

trait WithAlerts
{
    public function showAlert($type, $title, $text = '', $options = [])
    {
        $this->dispatch('show-alert', [
            'type' => $type,
            'title' => $title,
            'text' => $text,
            'options' => $options,
        ]);
    }

    public function showToast($type, $title)
    {
        $this->dispatch('show-toast', [
            'type' => $type,
            'title' => $title,
        ]);
    }

    public function showConfirm($title, $text, $method, $params = [], $confirmText = 'Yes, proceed!', $cancelText = 'Cancel')
    {
        $this->dispatch('show-confirm', [
            'title' => $title,
            'text' => $text,
            'method' => $method,
            'params' => $params,
            'confirmText' => $confirmText,
            'cancelText' => $cancelText,
        ]);
    }

    public function showSuccess($title, $text = '')
    {
        $this->showAlert('success', $title, $text);
    }

    public function showError($title, $text = '')
    {
        $this->showAlert('error', $title, $text);
    }

    public function showWarning($title, $text = '')
    {
        $this->showAlert('warning', $title, $text);
    }

    public function showInfo($title, $text = '')
    {
        $this->showAlert('info', $title, $text);
    }

    public function showSuccessToast($title)
    {
        $this->showToast('success', $title);
    }

    public function showErrorToast($title)
    {
        $this->showToast('error', $title);
    }

    public function showWarningToast($title)
    {
        $this->showToast('warning', $title);
    }

    public function showInfoToast($title)
    {
        $this->showToast('info', $title);
    }
}