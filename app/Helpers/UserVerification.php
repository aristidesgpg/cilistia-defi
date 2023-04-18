<?php

namespace App\Helpers;

use App\Models\RequiredDocument;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UserVerification
{
    /**
     * Context user
     *
     * @var User
     */
    protected User $user;

    /**
     * Cache store
     *
     * @var array
     */
    protected array $cache = [];

    /**
     * Basic verifications
     *
     * @var array|string[]
     */
    protected array $basic = [
        'verified_phone',
        'verified_email',
        'complete_profile',
    ];

    /**
     * Advanced verifications
     *
     * @var array|string[]
     */
    protected array $advanced = [
        'verified_address',
        'verified_documents',
    ];

    /**
     * Construct verification
     *
     * @param  User  $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Initialize
     *
     * @param  User  $user
     * @return UserVerification
     */
    public static function make(User $user): UserVerification
    {
        return new static($user);
    }

    /**
     * Get basic data
     *
     * @return Collection
     */
    public function getBasic(): Collection
    {
        if (!array_key_exists('basic', $this->cache)) {
            $this->cache['basic'] = $this->parse($this->basic);
        }

        return $this->cache['basic'];
    }

    /**
     * Get advanced data
     *
     * @return Collection
     */
    public function getAdvanced(): Collection
    {
        if (!array_key_exists('advanced', $this->cache)) {
            $this->cache['advanced'] = $this->parse($this->advanced);
        }

        return $this->cache['advanced'];
    }

    /**
     * Check if verification is complete
     *
     * @return bool
     */
    public function isComplete(): bool
    {
        return $this->isCompleteAdvanced();
    }

    /**
     * Check 'advanced' verification
     *
     * @return bool
     */
    protected function isCompleteAdvanced(): bool
    {
        return $this->isCompleteBasic() && $this->getStatus($this->getAdvanced());
    }

    /**
     * Check 'basic' verification
     *
     * @return bool
     */
    protected function isCompleteBasic(): bool
    {
        return $this->getStatus($this->getBasic());
    }

    /**
     * Verification status
     *
     * @return string
     */
    public function getLevel(): string
    {
        return $this->isCompleteAdvanced() ? 'advanced' : ($this->isCompleteBasic() ? 'basic' : 'unverified');
    }

    /**
     * Parse verification
     *
     * @param  array  $names
     * @return Collection
     */
    protected function parse(array $names): Collection
    {
        return collect($names)
            ->filter(function ($name) {
                return settings()->verification->get($name);
            })
            ->map(function ($name) {
                $status = $this->{'check' . Str::studly($name)}();

                return collect(['name' => $name])
                    ->put('title', trans("verification.$name"))
                    ->put('status', $status);
            })
            ->values();
    }

    /**
     * Parse status
     *
     * @param  Collection  $data
     * @return bool
     */
    protected function getStatus(Collection $data): bool
    {
        return $data->reduce(function ($status, $record) {
            return data_get($record, 'status') && $status;
        }, true);
    }

    /**
     * Verified Phone
     *
     * @return bool
     */
    protected function checkVerifiedPhone(): bool
    {
        return $this->user->isPhoneVerified();
    }

    /**
     * Verified Email
     *
     * @return bool
     */
    protected function checkVerifiedEmail(): bool
    {
        return $this->user->isEmailVerified();
    }

    /**
     * Complete Profile
     *
     * @return bool
     */
    protected function checkCompleteProfile(): bool
    {
        return (bool) $this->user->profile?->is_complete;
    }

    /**
     * Verified Documents
     *
     * @return bool
     */
    protected function checkVerifiedDocuments(): bool
    {
        return RequiredDocument::enabled()->get()->reduce(function ($verified, $requirement) {
            return $requirement->getDocument($this->user)?->status === 'approved' && $verified;
        }, true);
    }

    /**
     * Verified Address
     *
     * @return bool
     */
    protected function checkVerifiedAddress(): bool
    {
        return $this->user->address?->status === 'approved';
    }
}
