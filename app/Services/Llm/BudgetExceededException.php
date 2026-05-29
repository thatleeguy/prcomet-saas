<?php

namespace App\Services\Llm;

use RuntimeException;

/**
 * Thrown by {@see LlmUsageTracker::guardSystemBudget()} when the
 * system-wide daily or weekly cap has been hit. Callers catch this and
 * soft-fail their LLM-augmented feature (the literal half always
 * remains visible to the user). Never propagates to the operator
 * surface as an error — it's the expected steady state when we're
 * burning through budget.
 */
class BudgetExceededException extends RuntimeException
{
    public function __construct(public readonly string $window, string $message)
    {
        parent::__construct($message);
    }
}
