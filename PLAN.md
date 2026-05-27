# PrComet — Build Plan

> **Vision.** PrComet is a discovery tool for PR/IR teams. A customer connects their company's RSS (press releases, results, updates). Each new item is ingested and analyzed. We continuously analyze a corpus of mining-vertical publications, podcasts, substacks, and journalists. For every new press release we surface **named outreach opportunities** — a podcast that covered the relevant commodity last week, a journalist whose recent contrarian piece is well-suited to your counter-stance — each as a brief with rationale, citations, and contact info. **We do not send. The CEO/team writes and sends.** PrComet is a research-grade telescope, not an outreach automation tool.

> **Wedge.** Junior Mining Companies (JMCs). Constant news flow, identifiable audience, capital-markets-driven attention, real budget for IR/PR. Generalizes to cannabis micro-caps, biotech, crypto, defense tech — each its own corpus.

---

## Stack

- **Laravel 13.12** + **Jetstream 5.5 (Teams + Livewire)** + **Livewire 3** + **AlpineJS** + **Tailwind**
- **PostgreSQL** (with `pgvector` for embeddings) — _**SQLite for v0**; migrate to Postgres before Phase 4_
- **Redis** + **Laravel Horizon** for queues
- **Filament v4** for the super-admin surface
- **Pest** for tests
- **Anthropic Claude** — Sonnet 4.6 default, Opus 4.7 for high-stakes match briefs; provider abstraction so we can A/B later
- **Hosting**: Forge or Laravel Cloud (decide before first deploy)

---

## Domain model

```
Team (extends Jetstream)
  + is_active, max_companies, activated_at, activated_by_id
  + billing_notes, paid_through_at
Company (a monitored JMC; team_id)
  name, ticker, exchange, website, ir_contact_*, rss_feed_url,
  secondary_feeds_json, sector_tags, is_active
PressRelease (company_id)
  external_guid, source_url, title, body_html, body_text, published_at,
  analysis_status, extracted_entities_json, stance, key_claims_json
Source (scope=global|team, team_id nullable)
  type (publication|podcast|substack|x|youtube), name, base_url,
  feed_url, ingest_strategy, ingest_config_json, is_active
PublicationItem (source_id)
  external_id, url, title, body_text, transcript, published_at,
  author_id, analysis_status, topics_json, entities_json, stance
Author
  name, bio, primary_source_id, x_handle, email,
  body_of_work_summary  // rolling LLM summary
ExtractedClaim (publication_item_id, author_id)
  claim_text, topic, stance, predicted_outcome, timeframe,
  verified_outcome, verified_at, verification_evidence_json
Match (company_id, press_release_id, publication_item_id, author_id)
  score, rationale_md, suggested_angle_md, citations_json,
  status (new|saved|contacted|dismissed|placed)
MatchEvent (match_id, user_id, event_type, notes_md)
```

The **hybrid corpus** model: `Source.scope = global` is admin-curated in Filament; `Source.scope = team` is team-managed in-app.

---

## Phased build

### Phase 0 — Foundation ✅
- [x] Laravel 13.12 scaffolded
- [x] Jetstream installed with Teams + Livewire
- [x] Filament v4 with `/admin` panel
- [x] Horizon installed
- [x] Pest initialized
- [x] First admin user via `app:create-admin` console command
- [x] Filament authorization gate (super-admin `is_admin` flag + `canAccessPanel`)
- [ ] CI scaffold (GitHub Actions running Pest)
- [ ] Initial `git init` + first commit (deferred to user)

### Phase 1 — Activation gate + seat limits ✅
- [x] Migration: `is_active`, `max_companies`, `activated_at`, `activated_by_id`, `billing_notes`, `paid_through_at` on `teams`
- [x] `Team` model casts/fillable updated, plus `activate()`/`suspend()` methods
- [x] Middleware `EnsureTeamActive` blocks workspace until team is activated
- [x] `/pending-activation` page (post-signup landing)
- [x] Filament `TeamResource` — list, activate (one-click action), suspend, edit billing notes
- [x] Mailable: `TeamSignedUp` — notification to super-admins via `TeamCreated` listener
- [x] Mailable: `TeamActivated` — confirmation to team owner on first activation
- [x] Feature tests for the full activation flow (gate, model, command, panel access, emails)

### Phase 2 — Companies CRUD ✅
- [x] `Company` model + migration + factory
- [x] Team-scoping via team relation
- [x] Livewire CRUD scoped to current team (Index / Edit / Show)
- [x] Seat enforcement via `Team::canAddCompany()` + form-level guard
- [x] Empty-state UI for `/companies` and per-company dashboard
- [x] Tests: seat-limit enforcement, team-scoping isolation, foreign-team forbid
- [ ] _Deferred:_ Filament read-only `CompanyResource` for admin (low value pre-PMF)

### Phase 3 — Press release ingestion ✅
- [x] `PressRelease` model + migration (with analysis-status state)
- [x] RSS + Atom parsing via custom `FeedParser` (SimpleXML — no extra deps)
- [x] `IngestCompanyRssJob` (queueable, dispatched hourly via scheduler)
- [x] Dedupe by `(company_id, external_guid)` unique index
- [x] Body extraction (HTML → text)
- [x] Per-company press-releases panel in `companies.show`
- [x] Manual "Ingest now" button (synchronous dispatch for instant feedback)
- [x] Tests covering parser, ingest job, dedupe, inactive-company skip

### Phase 4 — Source corpus + ingestion ✅
- [x] `Source` model with `scope` (global|team) + `Source::visibleTo(team)` scope
- [x] RSS / Atom ingest strategy implemented; podcast/X/YouTube types stored but ingestion stubbed (typed sources still appear in corpus metadata)
- [x] Filament `SourceResource` for global catalog management (scope/type filters, "Ingest now" action)
- [x] `PublicationItem` model + `IngestSourceJob`
- [x] `MiningSourceSeeder` with 14 representative mining sources (publications, podcasts, substacks)
- [x] Scheduler entry (every 30 minutes)
- [x] Tests: ingestion, deduplication, visibility scope, skipped strategies
- [ ] _Deferred to "Phase 4b":_ Postgres + pgvector migration (SQLite + topic-overlap candidate filter sufficient for v0 corpus size; LLM ranks the rest)
- [ ] _Deferred:_ Podcast transcription pipeline (Whisper/Deepgram)
- [ ] _Deferred:_ In-app Livewire UI for team-scoped custom sources (admins can add via Filament for now)

### Phase 5 — Analysis pipeline ✅
- [x] `LlmClient` provider-agnostic contract + `AnthropicLlmClient` (Messages API) + `FakeLlmClient` (tests/dev)
- [x] Container binding: real client when `ANTHROPIC_API_KEY` is set, fake otherwise
- [x] `PressReleaseAnalysisPrompt` v1 — entities, topics, stance, key claims (versioned in code)
- [x] `PublicationItemAnalysisPrompt` v1 — same plus author identification and claims with timeframes
- [x] `AuthorProfilePrompt` — rolling body-of-work summary
- [x] `AnalyzePressReleaseJob` + `AnalyzePublicationItemJob` + `BuildAuthorProfileJob`
- [x] `Author` model with slug-dedup on save; per-author profile refresh debounced via `WithoutOverlapping`
- [x] `ExtractedClaim` model + `VerifyClaimsJob`
- [x] `PriceProvider` contract + `StubPriceProvider` (production wires a real source)
- [x] Tests: extraction, JSON parse tolerance (fenced output), idempotent re-analysis, author dedup, claim verification (correct/incorrect/unverifiable)
- [ ] _Deferred:_ Real embedding generation (Phase 6 uses topic-overlap candidate filter; embeddings re-enable when corpus scales past LLM context comfortably)
- [ ] _Deferred:_ Formal eval harness against a golden set (single biggest pre-launch investment)

### Phase 6 — Matching engine + briefs ⭐ _The Product_ ✅
- [x] `MatchRecord` model (table `matches`) + `MatchEvent` model + migrations
- [x] `GenerateMatchesForReleaseJob` auto-dispatched from `AnalyzePressReleaseJob`
- [x] **Stage 1**: `MatchCandidateFinder` — recency + visibility scope + topic/commodity/jurisdiction overlap, PHP-side scoring
- [x] **Stage 2**: `MatchBriefPrompt` runs on the high-stakes model (Opus by default), returns ranked picks with rationale + suggested angle + citations
- [x] Minimum confidence threshold (`config/match.php` → `min_confidence`)
- [x] Hard rules: every brief tied to a real candidate; hallucinated IDs filtered out; idempotent on re-run
- [x] Tests: candidate filter, recency cut-off, team-isolation, threshold drops, hallucination filter, status-flow

### Phase 7 — Brief UI + workflow ✅
- [x] Team dashboard (`/dashboard`) — stats + recent matches feed
- [x] Aggregate matches index (`/matches`) and per-company variant (`/companies/{c}/matches`) with status filter chips
- [x] Match detail page (`/matches/{m}`) — rationale, suggested angle, citations, source context, author profile snippet
- [x] Workflow actions: save / mark-contacted / dismiss / mark-placed (logged as `MatchEvent`s)
- [x] Free-form notes per match
- [x] Activity feed on the match detail
- [x] Tests: list scoping, status filtering, status transitions, note-add, foreign-team forbid, invalid-status no-op
- [ ] _Deferred to Phase 10:_ Onboarding tour, in-app announcements

### Phase 8 — Email digest ✅
- [x] Per-user `digest_frequency` (`daily` | `weekly` | `off`) + `digest_sent_at` columns
- [x] `MatchDigest` mailable + markdown view
- [x] `SendMatchDigestsJob` per user — uses last `digest_sent_at` as the window
- [x] Scheduler dispatches eligible users daily at 13:00 UTC
- [x] `Settings/DigestPreferences` Livewire page at `/settings/digest`
- [x] Tests: cadence window, off-cadence skip, inactive-team skip, no-matches doesn't email but advances window, preference save

### Phase 9 — Outcome tracking + wins ✅
- [x] Placement fields on matches: `placement_url` / `_title` / `_description` / `_published_at` / `_fetched_at`
- [x] Match detail page: attach placement URL → flips to `placed` status, logs event, dispatches metadata fetch
- [x] `FetchPlacementMetadataJob` — OpenGraph/Twitter/standard meta + `<title>` fallback, robust against apostrophes in content
- [x] `/wins` Livewire page — placed matches with stats (total, contacted, placed)
- [x] Tests: attach + flip, invalid-URL validation, metadata extraction (og + fallback), failed-fetch silence, team-scoped wins list, inactive-team gate

### Phase 10 — Polish
- [ ] Post-activation onboarding flow
- [ ] In-app announcements
- [ ] Team permission tuning (Jetstream roles)
- [ ] Audit log surface
- [ ] Performance pass (queue tuning, DB indexes, eager loading)

---

## Cross-cutting

- **Team-scoping.** Global Eloquent scope on every team-owned model. Feature-tested.
- **LLM cost guardrails.** Per-team monthly cap in config, surfaced in admin.
- **Testing.** Pest feature tests for every team-scoped path. LLM calls mocked everywhere except a dedicated eval suite that runs against the golden set.
- **Security.** Standard Jetstream auth, Sanctum for any API, rate limits on ingestion endpoints.
- **Disclosure / securities-law constraint.** Briefs and rationales must only reference public, on-the-wire information from the press release and the public source items. Never forward-looking inference, never material non-public info. Enforced via prompt design and eval cases.
- **Anti-spam stance.** Friction stays at the human. We surface, we don't send. The product is judged by brief quality, not volume.

---

## Deferred decisions

- Hosting target (Forge / Laravel Cloud / Vapor)
- Podcast transcription provider (Whisper API vs Deepgram)
- Commodity-price data source for claim verification (LBMA, Kitco, paid API)
- X ingestion path (paid API tier vs RSSHub-style bridge)
- LLM cost ceiling per team for v1

---

## Status

**Phases 0 – 9 complete.** Full end-to-end pipeline lands: signup → activation → companies → press-release ingest → analysis → corpus matching → brief generation → workflow → email digest → placement attribution → wins dashboard. **116 Pest tests passing**, 7 Jetstream defaults skipped.

**Pre-launch must-do before exposing real customers:**
1. Set `ANTHROPIC_API_KEY` and verify the prompts on a real release (no eval yet — eyeball + golden-set v1).
2. Replace `StubPriceProvider` with a real commodity-price source for `VerifyClaimsJob`.
3. Pick + wire a mail driver (Mailgun/Postmark/SES) — currently `log` from `.env.example`.
4. Verify the seeded source feed URLs actually resolve (best-effort; some may have moved).
5. `git init` + initial commit + GitHub Actions CI running Pest.

**Phase 10 (polish) is next** — onboarding tour, audit log surface, queue tuning, in-app team-source UI, formal eval harness for prompts and matches.
