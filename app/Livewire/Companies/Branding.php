<?php

namespace App\Livewire\Companies;

use App\Models\Company;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Per-company branding editor. Drives the visual chrome of every one-pager
 * the company publishes — logo, header image, accent color, tagline, prose
 * description, contact email, social handles.
 */
#[Layout('layouts.app')]
#[Title('Branding')]
class Branding extends Component
{
    use WithFileUploads;

    public Company $company;

    #[Validate('nullable|image|max:5120')]
    public $logo;

    #[Validate('nullable|image|max:10240')]
    public $headerImage;

    #[Validate('nullable|regex:/^#[0-9A-Fa-f]{6}$/')]
    public string $accentColor = '';

    #[Validate('nullable|string|max:160')]
    public string $tagline = '';

    #[Validate('nullable|string|max:5000')]
    public string $description = '';

    #[Validate('nullable|email|max:255')]
    public string $pressContactEmail = '';

    #[Validate('nullable|url|max:255')]
    public string $twitter = '';

    #[Validate('nullable|url|max:255')]
    public string $linkedin = '';

    // Blanket media release inherited by library assets.
    #[Validate('nullable|string|max:5000')]
    public string $blanketReleaseText = '';

    #[Validate('nullable|file|mimes:pdf|max:5120')]
    public $blanketReleaseFile;

    public function mount(Company $company): void
    {
        abort_unless($company->team_id === auth()->user()->currentTeam?->id, 403);
        $this->company = $company;

        // Implicit focus-switch — see MediaLibrary::mount() for rationale.
        if (auth()->user()->current_company_id !== $company->id) {
            auth()->user()->switchCompany($company);
        }

        $this->accentColor = $company->accent_color ?? '';
        $this->tagline = $company->tagline ?? '';
        $this->description = $company->description_md ?? '';
        $this->pressContactEmail = $company->press_contact_email ?? '';
        $this->twitter = $company->social_links['twitter'] ?? '';
        $this->linkedin = $company->social_links['linkedin'] ?? '';
        $this->blanketReleaseText = $company->blanket_media_release_text ?? '';
    }

    public function save(): void
    {
        $this->validate();

        $payload = [
            'accent_color' => $this->accentColor ?: null,
            'tagline' => $this->tagline ?: null,
            'description_md' => $this->description ?: null,
            'press_contact_email' => $this->pressContactEmail ?: null,
            'social_links' => array_filter([
                'twitter' => $this->twitter ?: null,
                'linkedin' => $this->linkedin ?: null,
            ]),
            'blanket_media_release_text' => $this->blanketReleaseText ?: null,
        ];

        if ($this->logo instanceof TemporaryUploadedFile) {
            $this->deletePrevious($this->company->logo_path);
            $payload['logo_path'] = $this->logo->store('branding/'.$this->company->id, config('filesystems.default'));
        }

        if ($this->headerImage instanceof TemporaryUploadedFile) {
            $this->deletePrevious($this->company->header_image_path);
            $payload['header_image_path'] = $this->headerImage->store('branding/'.$this->company->id, config('filesystems.default'));
        }

        if ($this->blanketReleaseFile instanceof TemporaryUploadedFile) {
            $this->deletePrevious($this->company->blanket_media_release_file_path);
            $payload['blanket_media_release_file_path'] = $this->blanketReleaseFile->store('media-releases/'.$this->company->id, config('filesystems.default'));
        }

        $this->company->update($payload);
        $this->logo = null;
        $this->headerImage = null;
        $this->blanketReleaseFile = null;

        session()->flash('status', 'Branding updated.');
    }

    public function removeBlanketReleaseFile(): void
    {
        $this->deletePrevious($this->company->blanket_media_release_file_path);
        $this->company->update(['blanket_media_release_file_path' => null]);
        session()->flash('status', 'Release file removed.');
    }

    public function removeLogo(): void
    {
        $this->deletePrevious($this->company->logo_path);
        $this->company->update(['logo_path' => null]);
        session()->flash('status', 'Logo removed.');
    }

    public function removeHeader(): void
    {
        $this->deletePrevious($this->company->header_image_path);
        $this->company->update(['header_image_path' => null]);
        session()->flash('status', 'Header removed.');
    }

    private function deletePrevious(?string $path): void
    {
        if ($path) {
            Storage::disk(config('filesystems.default'))->delete($path);
        }
    }

    public function render()
    {
        return view('livewire.companies.branding');
    }
}
