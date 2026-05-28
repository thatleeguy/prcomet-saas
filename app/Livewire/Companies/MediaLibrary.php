<?php

namespace App\Livewire\Companies;

use App\Models\Company;
use App\Models\MediaAsset;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Per-company Media Library — the substance pool that one-pagers draw from.
 *
 * Browse + filter view. Add and edit happen on a dedicated page
 * ({@see MediaAssetEdit}) so we have room for credit attribution, media
 * release toggles, and file revision history. The library is intentionally
 * narrow — listing, filtering, quick activate/delete.
 */
#[Layout('layouts.app')]
#[Title('Media library')]
class MediaLibrary extends Component
{
    public Company $company;

    #[Url(as: 'type', except: 'all')]
    public string $typeFilter = 'all';

    #[Url(as: 'q')]
    public string $search = '';

    public function mount(Company $company): void
    {
        abort_unless($company->team_id === auth()->user()->currentTeam?->id, 403);
        $this->company = $company;

        // Navigating directly to a company-scoped page implicitly switches
        // focus — keeps the nav chip and global lists honest about which
        // company you're actually working in.
        if (auth()->user()->current_company_id !== $company->id) {
            auth()->user()->switchCompany($company);
        }
    }

    #[Computed]
    public function assets()
    {
        return MediaAsset::query()
            ->where('company_id', $this->company->id)
            ->when($this->typeFilter !== 'all', fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(fn ($inner) => $inner
                    ->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('quote_text', 'like', $term)
                    ->orWhere('quote_attribution', 'like', $term));
            })
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function counts(): array
    {
        return MediaAsset::query()
            ->where('company_id', $this->company->id)
            ->selectRaw('type, COUNT(*) as c')
            ->groupBy('type')
            ->pluck('c', 'type')
            ->toArray();
    }

    public function setType(string $type): void
    {
        $this->typeFilter = $type;
    }

    public function delete(int $assetId): void
    {
        $asset = MediaAsset::where('company_id', $this->company->id)->findOrFail($assetId);
        $asset->deleteFile();
        $asset->delete();
        session()->flash('status', 'Asset removed.');
    }

    public function toggleActive(int $assetId): void
    {
        $asset = MediaAsset::where('company_id', $this->company->id)->findOrFail($assetId);
        $asset->update(['is_active' => ! $asset->is_active]);
    }

    public function render()
    {
        return view('livewire.companies.media-library');
    }
}
