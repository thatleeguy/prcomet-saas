<?php

/**
 * LLM cost guardrails.
 *
 * All values are operator-only — none of this is ever surfaced to end
 * users. Budgets are denominated in CENTS so they fit in an unsigned
 * integer column without floating-point drift.
 *
 * Two layers of protection:
 *
 *   - System hard caps (daily, weekly): when crossed, EVERY further
 *     LLM call throws BudgetExceededException. Callers handle this as
 *     a soft-fail — match briefs get skipped, watch hit confirmations
 *     stay "pending". The literal half of every feature keeps working.
 *
 *   - Per-team soft daily cap: when a team crosses it, operators
 *     receive a single notification per (team, day). The user sees
 *     nothing change; this is a heads-up for us, not a throttle on
 *     them. Re-armed at midnight.
 *
 * Pricing is per 1M tokens, copied from Anthropic's published rates.
 * Override per-model via env if rates change before we redeploy.
 */
return [
    'budgets' => [
        // Hard system-wide caps. Once exceeded, the AnthropicLlmClient
        // refuses to make further calls until the window rolls.
        'system_daily_cents' => env('LLM_SYSTEM_DAILY_CAP_CENTS', 5000),  // $50/day default
        'system_weekly_cents' => env('LLM_SYSTEM_WEEKLY_CAP_CENTS', 20000), // $200/week default

        // Heads-up threshold per team per day — emails operators on the
        // first call that pushes a team over this number. The team is
        // never throttled by this; it's purely informational.
        'team_daily_soft_cents' => env('LLM_TEAM_DAILY_SOFT_CENTS', 500), // $5/day default

        // Approaching-system-cap percentage. When daily spend hits this
        // share of system_daily_cents, fire the "approaching cap" alert.
        'system_approaching_pct' => env('LLM_SYSTEM_APPROACHING_PCT', 80),
    ],

    // Per-model pricing in cents per 1M tokens. Defaults track
    // Anthropic's published rates as of late 2026; override via env
    // when prices change so we don't need to redeploy.
    'pricing' => [
        'claude-sonnet-4-6' => [
            'input_cents_per_million' => env('LLM_SONNET_INPUT_C', 300),   // $3.00 / 1M
            'output_cents_per_million' => env('LLM_SONNET_OUTPUT_C', 1500), // $15.00 / 1M
        ],
        'claude-opus-4-7' => [
            'input_cents_per_million' => env('LLM_OPUS_INPUT_C', 1500),    // $15.00 / 1M
            'output_cents_per_million' => env('LLM_OPUS_OUTPUT_C', 7500),  // $75.00 / 1M
        ],
        // Fallback used when a request specifies an unknown model. Set
        // high so we never accidentally under-price a new model.
        'default' => [
            'input_cents_per_million' => env('LLM_DEFAULT_INPUT_C', 1500),
            'output_cents_per_million' => env('LLM_DEFAULT_OUTPUT_C', 7500),
        ],
    ],

    // Who gets the budget alerts. Comma-separated list of emails — if
    // empty, falls back to every User with is_admin = true.
    'alert_recipients' => array_filter(array_map('trim', explode(',', (string) env('LLM_ALERT_RECIPIENTS', '')))),
];
