<?php

namespace App\Models;

use Akaunting\Money\Currency;
use App\Events\UserActivities\EmailChanged;
use App\Events\UserActivities\PhoneChanged;
use App\Events\UserPresenceChanged;
use App\Helpers\Token;
use App\Helpers\TwoFactorAuth;
use App\Helpers\UserVerification;
use App\Models\Support\Cache;
use App\Models\Support\Comparison;
use App\Models\Support\HasRatings;
use App\Models\Support\Lock;
use App\Models\Support\Rateable;
use App\Models\Support\TwoFactor;
use App\Notifications\Auth\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Musonza\Chat\Traits\Messageable;
use PHPUnit\Util\Exception;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail, Rateable
{
    use HasFactory, Notifiable, TwoFactor, SoftDeletes, HasRoles, Lock, HasRatings, Messageable, Cache, Comparison;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'deactivated_until',
        'password',
        'country',
        'currency',
        'two_factor_enable',
        'phone_verified_at',
        'email_verified_at',
        'presence',
        'notifications_read_at',
        'last_seen_at',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'email',
        'phone',
        'two_factor_secret',
        'password',
        'remember_token',
        'roles',
        'permissions',
        'activities',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'phone_verified_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'two_factor_enable' => 'boolean',
        'deactivated_until' => 'datetime',
        'notifications_read_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['profile'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'country_name',
        'currency_name',
        'rank',
        'country_operation',
        'is_super_admin',
        'all_permissions',
        'all_roles',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function ($user) {
            $user->two_factor_secret = app(TwoFactorAuth::class)->generateSecretKey();
        });

        static::updating(function (self $user) {
            if ($user->isDirty('email')) {
                event(new EmailChanged($user));
                $user->email_verified_at = null;
            }

            if ($user->isDirty('presence') && $user->presence === 'online') {
                $user->last_seen_at = $user->freshTimestamp();
            }

            if ($user->isDirty('phone')) {
                event(new PhoneChanged($user));
                $user->phone_verified_at = null;
            }
        });

        static::created(function (self $user) {
            $user->profile()->save(new UserProfile);
        });
    }

    /**
     * Get path for profile
     *
     * @return string
     */
    public function path(): string
    {
        return "profile/{$this->id}";
    }

    /**
     * Generate phone token
     *
     * @return array
     */
    public function generatePhoneToken(): array
    {
        return app(Token::class)->generate($this->phone);
    }

    /**
     * Validate phone token
     *
     * @param  string  $token
     * @return bool
     */
    public function validatePhoneToken(string $token): bool
    {
        return app(Token::class)->validate($this->phone, $token);
    }

    /**
     * Generate email token
     *
     * @return array
     */
    public function generateEmailToken(): array
    {
        return app(Token::class)->generate($this->email);
    }

    /**
     * Validate email token
     *
     * @param  string  $token
     * @return bool
     */
    public function validateEmailToken(string $token): bool
    {
        return app(Token::class)->validate($this->email, $token);
    }

    /**
     * Check if user is super_admin
     *
     * @return bool
     */
    protected function getIsSuperAdminAttribute(): bool
    {
        return $this->hasRole(Role::superAdmin());
    }

    /**
     * Get location activity
     *
     * @return Attribute
     */
    protected function location(): Attribute
    {
        return Attribute::get(function (): ?array {
            $activity = $this->activities()->latest()->first();

            return $activity?->location;
        })->shouldCache();
    }

    /**
     * Country operation status
     *
     * @return Attribute
     */
    protected function countryOperation(): Attribute
    {
        return Attribute::get(function (): bool {
            return is_string($this->country) && OperatingCountry::where('code', $this->country)->exists();
        })->shouldCache();
    }

    /**
     * Get currency
     *
     * @return Attribute
     */
    protected function currency(): Attribute
    {
        return Attribute::get(function ($value): string {
            return SupportedCurrency::findByCode($value) ? strtoupper($value) : defaultCurrency();
        })->shouldCache();
    }

    /**
     * Get currency name
     *
     * @return Attribute
     */
    protected function currencyName(): Attribute
    {
        return Attribute::get(fn () => (new Currency($this->currency))->getName())->shouldCache();
    }

    /**
     * Check if user's phone is verified
     *
     * @return bool
     */
    public function isPhoneVerified(): bool
    {
        return (bool) $this->phone_verified_at;
    }

    /**
     * Check if user's email is verified
     *
     * @return bool
     */
    public function isEmailVerified(): bool
    {
        return (bool) $this->email_verified_at;
    }

    /**
     * Get rank by role
     *
     * @return Attribute
     */
    protected function rank(): Attribute
    {
        return Attribute::get(function (): ?int {
            return $this->roles->sortBy('rank')->first()?->rank;
        })->shouldCache();
    }

    /**
     * Check if user is superior to another
     *
     * @param  User|int  $user
     * @return bool
     */
    public function superiorTo(self|int $user): bool
    {
        if (is_null($this->rank)) {
            return false;
        }

        if ($user instanceof self) {
            return is_null($user->rank) || $this->rank < $user->rank;
        } else {
            return $this->subordinates()->whereKey($user)->exists();
        }
    }

    /**
     * Query subordinates
     *
     * @return Builder
     */
    public function subordinates(): Builder
    {
        if (is_null($this->rank)) {
            throw new Exception('User does not have a rank.');
        }

        return self::whereKeyNot($this->getKey())->whereDoesntHave('roles', function (Builder $query) {
            $query->where('rank', '<=', $this->rank);
        });
    }

    /**
     * long_term attribute
     *
     * @return bool
     */
    protected function getLongTermAttribute(): bool
    {
        return $this->isLongTerm();
    }

    /**
     * Active attribute
     *
     * @return bool
     */
    protected function getActiveAttribute(): bool
    {
        return $this->isActive();
    }

    /**
     * Log user activity
     *
     * @param  string  $action
     * @param  string  $ip
     * @param  null  $source
     * @param  null  $agent
     * @return Model
     */
    public function log(string $action, string $ip = '127.0.0.1', $source = null, $agent = null): Model
    {
        return $this->activities()->create([
            'action' => $action,
            'source' => $source,
            'ip' => $ip,
            'location' => geoip($ip)->toArray(),
            'agent' => $agent,
        ]);
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail);
    }

    /**
     * Get private channel
     *
     * @return string
     */
    public function privateChannel(): string
    {
        return 'App.Models.User.' . $this->id;
    }

    /**
     * The channels the user receives notification broadcasts on.
     *
     * @return string
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return $this->privateChannel();
    }

    /**
     * Route notifications for the Vonage channel.
     *
     * @return string
     */
    public function routeNotificationForVonage(): string
    {
        return preg_replace('/\D+/', '', $this->phone);
    }

    /**
     * Route notifications for the SNS channel.
     *
     * @return string
     */
    public function routeNotificationForSns(): string
    {
        return $this->phone;
    }

    /**
     * Route notifications for the Twilio channel.
     *
     * @return string
     */
    public function routeNotificationForTwilio(): string
    {
        return $this->phone;
    }

    /**
     * Route notifications for the Africas Talking channel.
     *
     * @return string
     */
    public function routeNotificationForAfricasTalking(): string
    {
        return $this->phone;
    }

    /**
     * User profile
     *
     * @return HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }

    /**
     * Get wallet address label
     *
     * @return string
     */
    public function getWalletLabel(): string
    {
        return "$this->name [$this->email]";
    }

    /**
     * Get user roles
     *
     * @return Attribute
     */
    protected function allRoles(): Attribute
    {
        return Attribute::get(function (): array {
            return $this->roles->sortBy('rank')->pluck('name')->toArray();
        })->shouldCache();
    }

    /**
     * Get user permissions
     *
     * @return Attribute
     */
    protected function allPermissions(): Attribute
    {
        return Attribute::get(function (): array {
            return $this->getAllPermissions()->pluck('name')->toArray();
        })->shouldCache();
    }

    /**
     * Get participation details
     *
     * @return array
     */
    public function getParticipantDetails(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'presence' => $this->presence,
            'last_seen_at' => $this->last_seen_at,
            'picture' => $this->profile->picture,
        ];
    }

    /**
     * Get country name
     *
     * @return Attribute
     */
    protected function countryName(): Attribute
    {
        return Attribute::get(fn () => config("countries.$this->country"));
    }

    /**
     * Update authenticated user's presence
     *
     * @param  string  $presence
     * @return void
     */
    public function updatePresence(string $presence): void
    {
        $this->update(['presence' => $presence]);
        broadcast(new UserPresenceChanged($this));
    }

    /**
     * Check if user is deactivated
     *
     * @return bool
     */
    public function deactivated(): bool
    {
        return $this->deactivated_until && $this->deactivated_until > now();
    }

    /**
     * Check if user is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return !$this->deactivated();
    }

    /**
     * User's wallet accounts
     *
     * @return HasMany
     */
    public function walletAccounts(): HasMany
    {
        return $this->hasMany(WalletAccount::class, 'user_id', 'id');
    }

    /**
     * User's activities
     *
     * @return HasMany
     */
    public function activities(): HasMany
    {
        return $this->hasMany(UserActivity::class, 'user_id', 'id');
    }

    /**
     * User's transfer records
     *
     * @return HasManyThrough
     */
    public function transferRecords(): HasManyThrough
    {
        return $this->hasManyThrough(TransferRecord::class, WalletAccount::class, 'user_id', 'wallet_account_id');
    }

    /**
     * User's payment transactions
     *
     * @return HasManyThrough
     */
    public function paymentTransactions(): HasManyThrough
    {
        return $this->hasManyThrough(PaymentTransaction::class, PaymentAccount::class, 'user_id', 'payment_account_id');
    }

    /**
     * User's exchange trades
     *
     * @return HasManyThrough
     */
    public function exchangeTrades(): HasManyThrough
    {
        return $this->hasManyThrough(ExchangeTrade::class, WalletAccount::class, 'user_id', 'wallet_account_id');
    }

    /**
     * User's sell trades
     *
     * @return HasManyThrough
     */
    public function sellPeerTrades(): HasManyThrough
    {
        return $this->hasManyThrough(PeerTrade::class, WalletAccount::class, 'user_id', 'seller_wallet_account_id');
    }

    /**
     * User's buy trades
     *
     * @return HasManyThrough
     */
    public function buyPeerTrades(): HasManyThrough
    {
        return $this->hasManyThrough(PeerTrade::class, WalletAccount::class, 'user_id', 'buyer_wallet_account_id');
    }

    /**
     * User's stakes
     *
     * @return HasManyThrough
     */
    public function stakes(): HasManyThrough
    {
        return $this->hasManyThrough(Stake::class, WalletAccount::class, 'user_id', 'wallet_account_id');
    }

    /**
     * Get notification settings
     *
     * @return HasMany
     */
    public function notificationSettings(): HasMany
    {
        $config = config('notifications.settings');

        return $this->hasMany(UserNotificationSetting::class, 'user_id', 'id')
            ->whereIn('name', array_keys($config));
    }

    /**
     * Get notification settings
     *
     * @return Collection
     */
    public function getNotificationSettings(): Collection
    {
        return $this->remember('notification_settings', function () {
            $this->updateNotificationSettings();

            return $this->notificationSettings;
        });
    }

    /**
     * Update user settings
     *
     * @return void
     */
    protected function updateNotificationSettings(): void
    {
        $config = config('notifications.settings', []);
        $settings = $this->notificationSettings;

        collect(array_keys($config))->diff($settings->pluck('name'))->each(function ($name) use ($config) {
            $this->notificationSettings()->updateOrCreate(compact('name'), [
                'email' => (bool) data_get($config, "$name.email"),
                'database' => (bool) data_get($config, "$name.database"),
                'sms' => (bool) data_get($config, "$name.sms"),
            ]);
        });
    }

    /**
     * Get user's documents
     *
     * @return HasMany
     */
    public function documents(): HasMany
    {
        return $this->hasMany(UserDocument::class, 'user_id', 'id');
    }

    /**
     * User's address
     *
     * @return HasOne
     */
    public function address(): HasOne
    {
        return $this->hasOne(UserAddress::class, 'user_id', 'id');
    }

    /**
     * User's payment accounts
     *
     * @return HasMany
     */
    public function paymentAccounts(): HasMany
    {
        return $this->hasMany(PaymentAccount::class, 'user_id', 'id')->has('supportedCurrency');
    }

    /**
     * Get followers
     *
     * @return BelongsToMany
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'followed_id', 'follower_id')
            ->withPivot('blocked')->withTimestamps();
    }

    /**
     * Get follower
     *
     * @param  User  $user
     * @return Pivot|null
     */
    public function getFollowerPivot(self $user): ?Pivot
    {
        return $this->followers()->find($user->id)?->pivot;
    }

    /**
     * Get following
     *
     * @return BelongsToMany
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'followed_id')
            ->withPivot('blocked')->withTimestamps();
    }

    /**
     * Check if user is following another
     *
     * @param  User  $user
     * @return bool
     */
    public function isFollowing(User $user): bool
    {
        return $this->following()->whereKey($user->id)->exists();
    }

    /**
     * Get following
     *
     * @param  User  $user
     * @return Pivot|null
     */
    public function getFollowingPivot(self $user): ?Pivot
    {
        return $this->following()->find($user->id)?->pivot;
    }

    /**
     * Check if user can be followed
     *
     * @param  User  $user
     * @return bool
     */
    public function canFollow(self $user): bool
    {
        return $this->isNot($user);
    }

    /**
     * Check if user cannot be followed
     *
     * @param  User  $user
     * @return bool
     */
    public function cannotFollow(self $user): bool
    {
        return !$this->canFollow($user);
    }

    /**
     * Current payment account.
     *
     * @return PaymentAccount
     */
    public function getPaymentAccount(): PaymentAccount
    {
        return $this->getPaymentAccountByCurrency($this->currency);
    }

    /**
     * Get payment account by currency
     *
     * @param  string  $currency
     * @return PaymentAccount
     */
    public function getPaymentAccountByCurrency(string $currency): PaymentAccount
    {
        return $this->paymentAccounts()->where('currency', $currency)->firstOr(function () use ($currency) {
            return $this->paymentAccounts()->create(['currency' => $currency]);
        });
    }

    /**
     * User's bank accounts
     *
     * @return HasMany
     */
    public function activeBankAccounts(): HasMany
    {
        return $this->bankAccounts()->where('currency', $this->currency)->where(function ($query) {
            $query->whereHas('bank.operatingCountries', function (Builder $query) {
                $query->where('code', $this->country);
            })->orDoesntHave('bank');
        });
    }

    /**
     * User's BankAccounts
     *
     * @return HasMany
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class, 'user_id', 'id')->has('supportedCurrency');
    }

    /**
     * User's commerce account
     *
     * @return HasOne
     */
    public function commerceAccount(): HasOne
    {
        return $this->hasOne(CommerceAccount::class, 'user_id', 'id');
    }

    /**
     * Get operating banks
     *
     * @return Builder
     */
    public function operatingBanks(): Builder
    {
        return Bank::country($this->country);
    }

    /**
     * Get deposit bank account
     *
     * @return BankAccount|null
     */
    public function getDepositBankAccount(): ?BankAccount
    {
        return BankAccount::doesntHave('user')->has('supportedCurrency')
            ->whereHas('bank.operatingCountries', fn (Builder $query) => $query->where('code', $this->country))
            ->where('currency', $this->currency)->latest()->first();
    }

    /**
     * Get verification helper
     *
     * @return Attribute
     */
    protected function verification(): Attribute
    {
        return Attribute::get(function (): UserVerification {
            return UserVerification::make($this);
        });
    }

    /**
     * Super admin users
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeSuperAdmin(Builder $query): Builder
    {
        return $query->role(Role::superAdmin())->latest();
    }

    /**
     * Operator users
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeOperator(Builder $query): Builder
    {
        return $query->role(Role::operator())->latest();
    }

    /**
     * permission: view user
     *
     * @param  User|int  $user
     * @return bool
     */
    public function canViewUser(self|int $user): bool
    {
        return $this->isEqualTo($user) || $this->can('access_control_panel');
    }

    /**
     * permission: update user
     *
     * @param  User|int  $user
     * @return bool
     */
    public function canUpdateUser(self|int $user): bool
    {
        return $this->isNotEqualTo($user) && $this->superiorTo($user) && $this->can('manage_users');
    }

    /**
     * permission: delete user
     *
     * @param  User|int  $user
     * @return bool
     */
    public function canDeleteUser(self|int $user): bool
    {
        return $this->isNotEqualTo($user) && $this->superiorTo($user) && $this->can('delete_users');
    }

    /**
     * Get user's WalletAccount
     *
     * @param  Wallet  $wallet
     * @return WalletAccount
     */
    public function getWalletAccount(Wallet $wallet): WalletAccount
    {
        return $wallet->getAccount($this);
    }

    /**
     * Rate model
     *
     * @param  Rateable  $rateable
     * @param  int  $value
     * @param  string|null  $comment
     * @return Rating
     */
    public function rate(Rateable $rateable, int $value, string $comment = null): Rating
    {
        $rating = new Rating();

        $rating->value = min($value, 5);
        $rating->comment = $comment;
        $rating->user()->associate($this);

        $rateable->ratings()->save($rating);

        return $rating;
    }

    /**
     * Rate model once
     *
     * @param  Rateable  $rateable
     * @param  int  $value
     * @param  string|null  $comment
     * @return Rating
     */
    public function rateOnce(Rateable $rateable, int $value, string $comment = null): Rating
    {
        $query = $rateable->ratings()->where('user_id', $this->id);

        if ($rating = $query->first()) {
            $rating->value = min($value, 5);
            $rating->comment = $comment;

            return tap($rating)->save();
        } else {
            return $this->rate($rateable, $value, $comment);
        }
    }

    /**
     * Check if user is offline
     *
     * @return bool
     */
    public function isUnavailable(): bool
    {
        return !$this->last_seen_at || now()->diffInMinutes($this->last_seen_at) > 30;
    }

    /**
     * Check if this is a "long term" user
     *
     * @return bool
     */
    public function isLongTerm(): bool
    {
        return now()->diffInMonths($this->created_at) >= settings()->get('long_term_period');
    }

    /**
     * Retrieve the User for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return Model
     */
    public function resolveRouteBinding($value, $field = null): Model
    {
        try {
            return $this->resolveRouteBindingQuery($this, $value, $field)->firstOrFail();
        } catch (ModelNotFoundException) {
            abort(404, trans('user.not_found'));
        }
    }

    /**
     * Get exchange operator
     *
     * @return User|null
     */
    public static function exchangeOperator(): ?User
    {
        return Module::exchange()->operator;
    }

    /**
     * Get giftcard operator
     *
     * @return User|null
     */
    public static function giftcardOperator(): ?User
    {
        return Module::giftcard()->operator;
    }

    /**
     * Get wallet operator
     *
     * @return User|null
     */
    public static function walletOperator(): ?User
    {
        return Module::wallet()->operator;
    }

    /**
     * Get staking operator
     *
     * @return User|null
     */
    public static function stakingOperator(): ?User
    {
        return Module::staking()->operator;
    }

    /**
     * Get commerce operator
     *
     * @return User|null
     */
    public static function commerceOperator(): ?User
    {
        return Module::commerce()->operator;
    }

    /**
     * Get peer trade operator
     *
     * @return User|null
     */
    public static function peerOperator(): ?User
    {
        return Module::peer()->operator;
    }
}
