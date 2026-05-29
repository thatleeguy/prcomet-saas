<?php

namespace App\Services\Llm\Prompts;

use App\Models\LlmUsageEvent;
use App\Models\MatchRecord;
use App\Models\PitchDraft;
use App\Services\Llm\LlmClient;

/**
 * Draft a cold-outreach pitch email for one match in a chosen tone.
 *
 * Reuses every piece of LLM context the platform already pays for:
 *   - The journalist's profile (name, publication, recent topics
 *     extracted from their corpus)
 *   - The press release being pitched
 *   - The pre-computed match rationale + suggested angle
 *   - The company brand voice + press contact identity
 *   - The published one-pager URL when one exists
 *
 * Uses Sonnet — drafting is judgement-but-not-life-and-death and the
 * shorter system prompt keeps Opus from being justified.
 */
class PitchDraftPrompt
{
    public const VERSION = 'v1.0';

    /**
     * @return array{subject:string, body:string}|null
     */
    public static function run(LlmClient $llm, MatchRecord $match, string $tone, ?string $onePagerUrl = null): ?array
    {
        $match->loadMissing([
            'company',
            'pressRelease',
            'publicationItem.source',
            'author',
        ]);

        $system = self::systemPrompt($tone);
        $user = self::userMessage($match, $onePagerUrl);

        $raw = $llm->complete($system, $user, [
            'model' => config('services.anthropic.default_model', 'claude-sonnet-4-6'),
            'max_tokens' => 800,
            'temperature' => 0.4,
            '_context' => [
                'feature' => LlmUsageEvent::FEATURE_MATCH_BRIEF, // close enough — same model + scope
                'team_id' => $match->company?->team_id,
            ],
        ]);

        return self::parse($raw);
    }

    private static function systemPrompt(string $tone): string
    {
        $toneRules = match ($tone) {
            PitchDraft::TONE_FORMAL => "Tone: formal. Neutral verbs, no contractions, address by surname.",
            PitchDraft::TONE_DIRECT => "Tone: direct. Sentences under 15 words. Punchy. No softeners. First-name address.",
            PitchDraft::TONE_CASUAL => "Tone: casual. First-name, contractions OK, conversational but professional.",
            default => "Tone: direct. Sentences under 15 words. Punchy. First-name address.",
        };

        return <<<PROMPT
        You draft cold-outreach pitch emails from a company's PR/IR
        contact to a specific journalist who covers their sector.

        You will be given the journalist's profile, the press release
        the team wants to pitch, the pre-computed rationale + angle
        for why this journalist is a fit, the company's identity, and
        a one-pager URL when one is available.

        Respond ONLY with a single JSON object on one line:
          {"subject": "...", "body": "..."}

        Subject rules:
        - 60 characters or fewer.
        - Specific. Intercept-summary-first when the release is a drill
          result; otherwise lead with the substance.
        - Never start with "Pitch from", "Story idea", or anything
          obviously promotional.

        Body rules:
        - 3–5 short paragraphs.
        - Open with the news, not introductions. Journalists know how
          to read.
        - One line that references the journalist's recent work
          specifically.
        - State the offer concretely (CEO call, embargo, exclusivity,
          materials).
        - Close with the one-pager URL on its own line if one was
          provided. Sign off with the press contact name from the
          company brief.
        - Never include: "I hope this email finds you well", "I'm
          reaching out", "synergies", "leveraging", "circle back",
          "low hanging fruit".

        {$toneRules}
        PROMPT;
    }

    private static function userMessage(MatchRecord $match, ?string $onePagerUrl): string
    {
        $journalistName = $match->author?->name ?? 'the journalist';
        $publication = $match->publicationItem?->source?->name ?? 'an outlet we follow';
        $journalistTopics = collect($match->author?->tags ?? [])->take(8)->implode(', ');
        $recentPiece = $match->publicationItem?->title;
        $journalistBio = mb_substr((string) ($match->author?->bio ?? ''), 0, 240);

        $releaseTitle = $match->pressRelease?->title ?? '(untitled release)';
        $releaseExcerpt = mb_substr((string) $match->pressRelease?->body_text, 0, 800);

        $rationale = $match->rationale ?? '(no rationale available)';
        $angle = $match->suggested_angle ?? '(no angle available)';

        $company = $match->company;
        $brandDescription = mb_substr((string) ($company?->description_md ?? ''), 0, 400);
        $pressContactName = $company?->ir_contact_name ?? 'the team';
        $pressContactEmail = $company?->press_contact_email
            ?? $company?->ir_contact_email;

        $onePagerLine = $onePagerUrl
            ? "One-pager URL (include verbatim in body): {$onePagerUrl}"
            : 'No one-pager URL — omit the one-pager line from the body.';

        return <<<MSG
        JOURNALIST
        Name: {$journalistName}
        Publication: {$publication}
        Topics they cover (from their corpus): {$journalistTopics}
        Bio: {$journalistBio}
        Recent piece referenced in match: {$recentPiece}

        PRESS RELEASE
        Title: {$releaseTitle}
        Excerpt:
        {$releaseExcerpt}

        WHY THIS JOURNALIST (pre-computed)
        Rationale: {$rationale}
        Suggested angle: {$angle}

        COMPANY
        Name: {$company?->name}
        Description: {$brandDescription}
        Press contact name: {$pressContactName}
        Press contact email: {$pressContactEmail}

        {$onePagerLine}
        MSG;
    }

    private static function parse(string $raw): ?array
    {
        $trimmed = trim($raw);
        // Tolerate code fences even though we asked for none.
        $trimmed = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $trimmed) ?? $trimmed;

        $decoded = json_decode($trimmed, true);
        if (! is_array($decoded) || ! isset($decoded['subject'], $decoded['body'])) {
            return null;
        }

        return [
            'subject' => mb_substr(trim((string) $decoded['subject']), 0, 200),
            'body' => trim((string) $decoded['body']),
        ];
    }
}
