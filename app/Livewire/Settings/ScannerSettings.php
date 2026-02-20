<?php

namespace App\Livewire\Settings;

use Livewire\Component;

use App\Models\Setting;
use Livewire\Attributes\Title;

#[Title('Scanner Settings - Smart Inventory')]
class ScannerSettings extends Component
{
    public bool $scanner_enabled = true;
    public int $scanner_timeout = 50;
    public string $scanner_suffix = 'Enter';

    public function mount()
    {
        $this->scanner_enabled = Setting::get('scanner_enabled', '1') === '1';
        $this->scanner_timeout = (int) Setting::get('scanner_timeout', '50');
        $this->scanner_suffix = Setting::get('scanner_suffix', 'Enter');
    }

    public function save()
    {
        $this->validate([
            'scanner_timeout' => 'required|integer|min:10|max:1000',
            'scanner_suffix' => 'required|string|max:20',
        ]);

        Setting::set('scanner_enabled', $this->scanner_enabled ? '1' : '0');
        Setting::set('scanner_timeout', (string) $this->scanner_timeout);
        Setting::set('scanner_suffix', $this->scanner_suffix);

        $this->dispatch('toast', message: 'Scanner settings saved successfully!', type: 'success');
    }

    public function render()
    {
        return view('livewire.settings.scanner-settings');
    }
}
