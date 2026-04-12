<?php

namespace App\Livewire\Admin;

use App\Livewire\Actions\Logout;
use Livewire\Component;

class LogoutButton extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect(route('login'), navigate: true);
    }

    public function render()
    {
        return <<<'BLADE'
        <button
            wire:click="logout"
            class="flex items-center gap-3 w-full px-3 py-2 rounded-lg text-sm font-medium
                   text-slate-500 hover:bg-red-50 hover:text-red-600 transition-colors duration-150">
            <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Cerrar sesión
        </button>
        BLADE;
    }
}
