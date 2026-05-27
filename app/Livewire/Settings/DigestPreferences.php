<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Digest preferences')]
class DigestPreferences extends Component
{
    public string $frequency = User::DIGEST_DAILY;

    public function mount(): void
    {
        $this->frequency = auth()->user()->digest_frequency;
    }

    public function save(): void
    {
        $allowed = [User::DIGEST_DAILY, User::DIGEST_WEEKLY, User::DIGEST_OFF];
        if (! in_array($this->frequency, $allowed, true)) {
            return;
        }

        auth()->user()->forceFill(['digest_frequency' => $this->frequency])->save();
        session()->flash('status', 'Digest preferences updated.');
    }

    public function render()
    {
        return view('livewire.settings.digest-preferences');
    }
}
