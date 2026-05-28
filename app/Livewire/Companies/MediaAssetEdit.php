<?php

namespace App\Livewire\Companies;

use App\Models\Company;
use App\Models\MediaAsset;
use App\Models\MediaAssetRevision;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Full-page asset editor.
 *
 * Splits the library's old modal into a dedicated screen so we have room
 * for the things modals can't easily host:
 *  - Photo credit + credit URL
 *  - Media-release toggle (blanket vs custom) with per-asset overrides
 *  - File revisions panel with "Make current" revert
 *
 * Mounts in either "create" mode (no asset) or "edit" mode (with one). Edit
 * mode keeps you on the same component after a save, so a long fine-tuning
 * session — credit, tags, replace file, swap release — doesn't bounce you
 * back to the library between every change.
 */
#[Layout('layouts.app')]
#[Title('Media asset')]
class MediaAssetEdit extends Component
{
    use WithFileUploads;

    public Company $company;

    public ?MediaAsset $asset = null;

    /** image|pdf|quote|link — only mutable in create mode. */
    public string $type = MediaAsset::TYPE_IMAGE;

    #[Validate('required|string|max:160')]
    public string $name = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('nullable|string|max:160')]
    public string $credit = '';

    #[Validate('nullable|url|max:255')]
    public string $creditUrl = '';

    #[Validate('nullable|file|max:10240')]
    public $file;

    /** Optional one-liner attached to the revision row when replacing a file. */
    #[Validate('nullable|string|max:200')]
    public string $revisionNotes = '';

    #[Validate('nullable|url|max:1000')]
    public string $url = '';

    #[Validate('nullable|string|max:2000')]
    public string $quoteText = '';

    #[Validate('nullable|string|max:160')]
    public string $quoteAttribution = '';

    /** Comma-separated tags; normalised on save. */
    #[Validate('nullable|string|max:255')]
    public string $tagsCsv = '';

    public bool $isActive = true;

    // ── Media release ────────────────────────────────────────────────
    public bool $usesBlanketRelease = true;

    #[Validate('nullable|string|max:5000')]
    public string $mediaReleaseText = '';

    #[Validate('nullable|file|mimes:pdf|max:5120')]
    public $mediaReleaseFile;

    public function mount(Company $company, ?MediaAsset $asset = null): void
    {
        abort_unless($company->team_id === auth()->user()->currentTeam?->id, 403);
        $this->company = $company;

        // Implicit focus switch — consistent with other company-scoped pages.
        if (auth()->user()->current_company_id !== $company->id) {
            auth()->user()->switchCompany($company);
        }

        // Read the asset from the route argument with an explicit company
        // scope so a tampered id 404s rather than leaking an asset from
        // another company.
        if ($asset && $asset->exists) {
            abort_unless($asset->company_id === $company->id, 404);
            $this->asset = $asset;

            $this->type = $asset->type;
            $this->name = $asset->name;
            $this->description = $asset->description ?? '';
            $this->credit = $asset->credit ?? '';
            $this->creditUrl = $asset->credit_url ?? '';
            $this->url = $asset->url ?? '';
            $this->quoteText = $asset->quote_text ?? '';
            $this->quoteAttribution = $asset->quote_attribution ?? '';
            $this->tagsCsv = implode(', ', $asset->tags ?? []);
            $this->isActive = (bool) $asset->is_active;
            // Coerce defensively — pre-migration rows may have NULL here even
            // though new columns default to true.
            $this->usesBlanketRelease = (bool) ($asset->uses_blanket_release ?? true);
            $this->mediaReleaseText = $asset->media_release_text ?? '';
        } else {
            // Create mode: respect ?type= so the library's "Add photo" etc.
            // shortcuts pre-select the right form layout.
            $requested = request()->query('type');
            if (in_array($requested, [MediaAsset::TYPE_IMAGE, MediaAsset::TYPE_PDF, MediaAsset::TYPE_QUOTE, MediaAsset::TYPE_LINK], true)) {
                $this->type = $requested;
            }
        }
    }

    #[Computed]
    public function isEditing(): bool
    {
        return $this->asset !== null;
    }

    /**
     * Pretty type metadata — keeps the view free of repeated icon paths.
     *
     * @return array<string, array{label:string, blurb:string}>
     */
    #[Computed]
    public function typeMeta(): array
    {
        return [
            MediaAsset::TYPE_IMAGE => ['label' => 'Image', 'blurb' => 'Photos, charts, infographics.'],
            MediaAsset::TYPE_PDF => ['label' => 'PDF', 'blurb' => 'NI 43-101s, decks, fact sheets.'],
            MediaAsset::TYPE_QUOTE => ['label' => 'Pull quote', 'blurb' => 'A line worth featuring on a one-pager.'],
            MediaAsset::TYPE_LINK => ['label' => 'External link', 'blurb' => 'Anything that lives elsewhere.'],
        ];
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:160',
            'description' => 'nullable|string|max:1000',
            'credit' => 'nullable|string|max:160',
            'creditUrl' => 'nullable|url|max:255',
            'tagsCsv' => 'nullable|string|max:255',
        ];

        // Per-type validation. Files are required only on create.
        match ($this->type) {
            MediaAsset::TYPE_IMAGE, MediaAsset::TYPE_PDF => $rules['file'] = $this->isEditing
                ? 'nullable|file|max:10240'
                : 'required|file|max:10240',
            MediaAsset::TYPE_QUOTE => [
                $rules['quoteText'] = 'required|string|max:2000',
                $rules['quoteAttribution'] = 'required|string|max:160',
            ],
            MediaAsset::TYPE_LINK => $rules['url'] = 'required|url|max:1000',
        };

        $this->validate($rules);

        // Build the payload up front. credit/credit_url are common across
        // file-backed and external types; release fields are common too.
        $payload = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'credit' => $this->credit ?: null,
            'credit_url' => $this->creditUrl ?: null,
            'tags' => $this->parseTags(),
            'is_active' => $this->isActive,
            'uses_blanket_release' => $this->usesBlanketRelease,
            'media_release_text' => $this->usesBlanketRelease ? null : ($this->mediaReleaseText ?: null),
        ];

        // Type-specific fields.
        match ($this->type) {
            MediaAsset::TYPE_QUOTE => $payload = array_merge($payload, [
                'type' => MediaAsset::TYPE_QUOTE,
                'quote_text' => $this->quoteText,
                'quote_attribution' => $this->quoteAttribution,
            ]),
            MediaAsset::TYPE_LINK => $payload = array_merge($payload, [
                'type' => MediaAsset::TYPE_LINK,
                'url' => $this->url,
            ]),
            default => $payload['type'] = $this->type,
        };

        // Persist the asset row first so revision snapshots have an FK target.
        if ($this->isEditing) {
            // Replacement file → snapshot the existing one before overwrite.
            if ($this->file instanceof TemporaryUploadedFile && $this->asset->file_path) {
                $this->asset->snapshotCurrentAsRevision(
                    auth()->id(),
                    $this->revisionNotes ?: null,
                );
            }

            if ($this->file instanceof TemporaryUploadedFile) {
                $payload = array_merge($payload, $this->storeUpload($this->file));
            }

            if ($this->mediaReleaseFile instanceof TemporaryUploadedFile) {
                $payload['media_release_file_path'] = $this->storeReleaseFile($this->mediaReleaseFile);
            }

            $this->asset->update($payload);
        } else {
            $payload['source'] = MediaAsset::SOURCE_MANUAL;

            if ($this->file instanceof TemporaryUploadedFile) {
                $payload = array_merge($payload, $this->storeUpload($this->file));
            }

            if ($this->mediaReleaseFile instanceof TemporaryUploadedFile) {
                $payload['media_release_file_path'] = $this->storeReleaseFile($this->mediaReleaseFile);
            }

            $this->asset = $this->company->mediaAssets()->create($payload);
        }

        // Reset upload-only fields so the form is ready for the next change
        // without holding the previous file in memory or in the UI.
        $this->reset(['file', 'mediaReleaseFile', 'revisionNotes']);
        $this->asset->refresh();

        session()->flash('status', $this->isEditing ? 'Asset updated.' : 'Asset created.');

        // Drop the user on the edit page for the new row so they can keep
        // refining without bouncing between create and edit URLs.
        if (! $this->isEditing) {
            return $this->redirectRoute('companies.library.edit', [
                'company' => $this->company,
                'asset' => $this->asset,
            ], navigate: true);
        }

        return null;
    }

    /**
     * Revert the current file to a previous revision. Cheap because the
     * historical file is still on disk — we just swap the path back and
     * snapshot the now-displaced "current" file as a new revision.
     */
    public function revertTo(int $revisionId): void
    {
        abort_unless($this->isEditing, 404);

        $revision = MediaAssetRevision::where('media_asset_id', $this->asset->id)
            ->findOrFail($revisionId);

        // Snapshot the current state so the revert itself is reversible.
        $this->asset->snapshotCurrentAsRevision(
            auth()->id(),
            'Snapshot before revert',
        );

        $this->asset->update([
            'file_path' => $revision->file_path,
            'mime_type' => $revision->mime_type,
            'size_bytes' => $revision->size_bytes,
            'width_px' => $revision->width_px,
            'height_px' => $revision->height_px,
        ]);

        // Remove the revision we just restored — it's the current row now,
        // not history. Anything older stays put.
        $revision->delete();

        $this->asset->refresh();
        session()->flash('status', 'Reverted to earlier file.');
    }

    /**
     * Delete the asset and any historical files attached to it. Used
     * from the danger zone at the bottom of the form.
     */
    public function delete()
    {
        abort_unless($this->isEditing, 404);

        foreach ($this->asset->revisions as $rev) {
            if ($rev->file_path) {
                Storage::disk(config('filesystems.default'))->delete($rev->file_path);
            }
        }
        $this->asset->deleteFile();
        $this->asset->delete();

        session()->flash('status', 'Asset deleted.');
        return $this->redirectRoute('companies.library', $this->company, navigate: true);
    }

    private function storeUpload(TemporaryUploadedFile $upload): array
    {
        $path = $upload->store('media/'.$this->company->id, config('filesystems.default'));

        return [
            'file_path' => $path,
            'mime_type' => $upload->getMimeType(),
            'size_bytes' => $upload->getSize(),
        ];
    }

    private function storeReleaseFile(TemporaryUploadedFile $upload): string
    {
        return $upload->store('media-releases/'.$this->company->id, config('filesystems.default'));
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

    public function render()
    {
        return view('livewire.companies.media-asset-edit');
    }
}
