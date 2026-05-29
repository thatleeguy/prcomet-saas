<?php

namespace App\Mail\Concerns;

use App\Models\Company;
use Illuminate\Mail\Mailables\Address;

/**
 * Helpers for the co-branding pattern across every newsroom email.
 *
 * From line: "{Company Name} via PrComet <subscriptions@…>" — the
 * sending domain stays PrComet's so SPF/DKIM stays aligned and
 * deliverability doesn't suffer from impersonation heuristics; the
 * display name signals to the subscriber whose news they're reading.
 *
 * Reply-To: the company's press_contact_email when set, so replies
 * land where they should rather than disappearing into our shared
 * inbox.
 */
trait CoBrandedMail
{
    protected function coBrandedFrom(Company $company): Address
    {
        $address = (string) (config('mail.from.address') ?: 'hello@prcomet.com');
        $name = "{$company->name} via PrComet";

        return new Address($address, $name);
    }

    protected function coBrandedReplyTo(Company $company): ?Address
    {
        $email = $company->press_contact_email ?: $company->ir_contact_email;
        if (! $email) {
            return null;
        }

        return new Address($email, $company->name);
    }
}
