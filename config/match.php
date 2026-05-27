<?php

return [
    // Minimum LLM confidence score (0..1) for a match to be persisted.
    // Below this, the model is filtering out — quality over coverage.
    'min_confidence' => env('MATCH_MIN_CONFIDENCE', 0.5),

    // Candidate corpus filter knobs.
    'recency_days' => env('MATCH_RECENCY_DAYS', 90),
    'candidate_limit' => env('MATCH_CANDIDATE_LIMIT', 20),

    // How many top matches to ask the LLM to produce briefs for, per release.
    'briefs_per_release' => env('MATCH_BRIEFS_PER_RELEASE', 5),
];
