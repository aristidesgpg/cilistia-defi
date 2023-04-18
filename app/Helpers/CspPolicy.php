<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policies\Policy;
use Symfony\Component\HttpFoundation\Response;

class CspPolicy extends Policy
{
    /**
     * Disable CSP for client error.
     *
     * @param  Request  $request
     * @param  Response  $response
     * @return bool
     */
    public function shouldBeApplied(Request $request, Response $response): bool
    {
        if ($this->shouldIgnoreErrorDebugResponse($response)) {
            return false;
        }

        return parent::shouldBeApplied($request, $response);
    }

    /**
     * Should ignore debug response
     *
     * @param  Response  $response
     * @return bool
     */
    protected function shouldIgnoreErrorDebugResponse(Response $response): bool
    {
        return config('app.debug') && ($response->isClientError() || $response->isServerError());
    }

    /**
     * Configure Policies
     *
     * @return void
     */
    public function configure(): void
    {
        $whitelisted = $this->whitelistedHost();

        $this
            ->addDirective(Directive::DEFAULT, $whitelisted)
            ->addDirective(Directive::STYLE, [...$whitelisted, Keyword::UNSAFE_INLINE, 'fonts.googleapis.com'])
            ->addDirective(Directive::SCRIPT, [...$whitelisted, Keyword::REPORT_SAMPLE, 'hcaptcha.com', 'newassets.hcaptcha.com', 'polyfill.io'])
            ->addDirective(Directive::BASE, [...$whitelisted])
            ->addDirective(Directive::CONNECT, [...$whitelisted])
            ->addDirective(Directive::FONT, [...$whitelisted, 'fonts.gstatic.com'])
            ->addDirective(Directive::FRAME, [...$whitelisted, 'hcaptcha.com', 'newassets.hcaptcha.com'])
            ->addDirective(Directive::IMG, [...$whitelisted, 'data:', 'unpkg.com'])
            ->addDirective(Directive::FORM_ACTION, [...$whitelisted])
            ->addDirective(Directive::OBJECT, Keyword::NONE)
            ->addNonceForDirective(Directive::SCRIPT);
    }

    /**
     * Get whitelisted patterns
     *
     * @return string[]
     */
    public function whitelistedHost(): array
    {
        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';

        return array_merge([
            Keyword::SELF, "$domain", "www.$domain",
            "ws://$domain:2095", "ws://www.$domain:2095",
            "wss://$domain:2096", "wss://www.$domain:2096",
            "app.$domain:8080", "ws://app.$domain:8080",
        ], config('csp.whitelist_host'));
    }
}
