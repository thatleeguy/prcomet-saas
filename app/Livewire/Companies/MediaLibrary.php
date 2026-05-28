<?php

namespace App\Livewire\Companies;

use App\Models\Company;
use App\Models\MediaAsset;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Per-company Media Library — the substance pool that one-pagers draw from.
 *
 * Combines a filterable browse view with a multi-modal "add asset" dialog
 * that supports four kinds of payloads (file uploads, external links, pull
 * quotes, and editable existing rows). Auto-extracted quotes from press
 * releases show up here too with a small "from PR" attribution chip.
 */
#[Layout('layouts.app')]
#[Title('Media library')]
class MediaLibrary extends Component
{
    use WithFileUploads;

    public Company $company;

    #[Url(as: 'type', except: 'all')]
    public string $typeFilter = 'all';

    #[Url(as: 'q')]
    public string $search = '';

    // Add/edit modal state.
    public bool $editorOpen = false;
    public ?int $editingId = null;
    public string $editingMode = 'image'; // image|pdf|quote|link

    #[Validate('nullable|string|max:160')]
    public string $name = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('nullable|file|max:10240')] // 10 MB; tweakable
    public $file;

    #[Validate('nullable|url|max:1000')]
    public string $url = '';

    #[Validate('nullable|string|max:2000')]
    public string $quoteText = '';

    #[Validate('nullable|string|max:160')]
    public string $quoteAttribution = '';

    /** Comma-separated tags, normalized on save. */
    #[Validate('nullable|string|max:255')]
    public string $tagsCsv = '';

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

    public function openEditor(string $mode, ?int $assetId = null): void
    {
        $this->resetEditor();
        $this->editingMode = $mode;
        $this->editorOpen = true;

        if ($assetId !== null) {
            $asset = MediaAsset::where('company_id', $this->company->id)->findOrFail($assetId);
            $this->editingId = $asset->id;
            $this->editingMode = match ($asset->type) {
                MediaAsset::TYPE_PDF => 'pdf',
                MediaAsset::TYPE_QUOTE => 'quote',
                MediaAsset::TYPE_LINK => 'link',
                default => 'image',
            };
            $this->name = $asset->name;
            $this->description = $asset->description ?? '';
            $this->url = $asset->url ?? '';
            $this->quoteText = $asset->quote_text ?? '';
            $this->quoteAttribution = $asset->quote_attribution ?? '';
            $this->tagsCsv = implode(', ', $asset->tags ?? []);
        }
    }

    public function closeEditor(): void
    {
        $this->editorOpen = false;
        $this->resetEditor();
    }

    private function resetEditor(): void
    {
        $this->reset(['editingId', 'name', 'description', 'file', 'url', 'quoteText', 'quoteAttribution', 'tagsCsv']);
    }

    public function save(): void
    {
        $payload = match ($this->editingMode) {
            'image', 'pdf' => $this->saveFileBacked(),
            'quote' => $this->saveQuote(),
            'link' => $this->saveLink(),
            default => throw new \InvalidArgumentException("Unknown editor mode: {$this->editingMode}"),
        };

        $payload['tags'] = $this->parseTags();

        if ($this->editingId) {
            $asset = MediaAsset::where('company_id', $this->company->id)->findOrFail($this->editingId);

            // For file-backed updates, only swap the file if a new one was uploaded.
            if (! ($this->file ?? null) && isset($payload['file_path'])) {
                unset($payload['file_path'], $payload['mime_type'], $payload['size_bytes']);
            }

            $asset->update($payload);
        } else {
            $this->company->mediaAssets()->create($payload);
        }

        session()->flash('status', $this->editingId ? 'Asset updated.' : 'Asset added.');
        $this->closeEditor();
    }

    private function saveFileBacked(): array
    {
        $this->validate([
            'name' => 'required|string|max:160',
            'file' => $this->editingId ? 'nullable|file|max:10240' : 'required|file|max:10240',
        ]);

        $type = $this->editingMode === 'pdf' ? MediaAsset::TYPE_PDF : MediaAsset::TYPE_IMAGE;

        $payload = [
            'type' => $type,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'source' => MediaAsset::SOURCE_MANUAL,
        ];

        if ($this->file instanceof TemporaryUploadedFile) {
            $path = $this->file->store('media/'.$this->company->id, config('filesystems.default'));
            $payload['file_path'] = $path;
            $payload['mime_type'] = $this->file->getMimeType();
            $payload['size_bytes'] = $this->file->getSize();
        }

        return $payload;
    }

    private function saveQuote(): array
    {
        $this->validate([
            'quoteText' => 'required|string|max:2000',
            'quoteAttribution' => 'required|string|max:160',
        ]);

        return [
            'type' => MediaAsset::TYPE_QUOTE,
            'name' => $this->name ?: \Illuminate\Support\Str::limit($this->quoteText, 60),
            'quote_text' => $this->quoteText,
            'quote_attribution' => $this->quoteAttribution,
            'source' => MediaAsset::SOURCE_MANUAL,
        ];
    }

    private function saveLink(): array
    {
        $this->validate([
            'name' => 'required|string|max:160',
            'url' => 'required|url|max:1000',
        ]);

        return [
            'type' => MediaAsset::TYPE_LINK,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'url' => $this->url,
            'source' => MediaAsset::SOURCE_MANUAL,
        ];
    }

    /** @return array<int,string> */
    private function parseTags(): array
    {
        return collect(explode(',', $this->tagsCsv))
            ->map(fn ($t) => strtolower(trim($t)))
            ->filter()
            ->unique()
            ->values()
            ->all();
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
