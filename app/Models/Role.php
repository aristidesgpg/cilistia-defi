<?php

namespace App\Models;

use App\Models\Support\StaticCache;
use Exception;
use Spatie\Permission\Models\Role as Model;

class Role extends Model
{
    use StaticCache;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['protected'];

    /**
     * Reserved rank
     *
     * @var int
     */
    public static int $reservedRank = 10;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (self $role) {
            if (!static::isNameReserved($role->name) && self::isRankReserved($role->rank)) {
                throw new Exception('The rank is reserved');
            }
        });

        static::updating(function (self $role) {
            if ($role->isDirty('name') && $role->isProtected()) {
                throw new Exception('Unable to change protected role.');
            }

            if ($role->isDirty('rank') && self::isRankReserved($role->rank) && !$role->isProtected()) {
                throw new Exception('The rank is reserved');
            }
        });

        static::deleting(function (self $role) {
            if ($role->isProtected()) {
                throw new Exception('Unable to delete protected role.');
            }
        });
    }

    /**
     * Check if rank is reserved
     *
     * @param $rank
     * @return bool
     */
    public static function isRankReserved($rank): bool
    {
        return !is_null($rank) && $rank < static::$reservedRank;
    }

    /**
     * Check if name is reserved
     *
     * @param $name
     * @return bool
     */
    public static function isNameReserved($name): bool
    {
        return collect(config('permission.roles'))->contains($name);
    }

    /**
     * Check if array is protected
     *
     * @return bool
     */
    public function isProtected(): bool
    {
        return static::isNameReserved($this->getOriginal('name'));
    }

    /**
     * Protection status
     *
     * @return bool
     */
    protected function getProtectedAttribute(): bool
    {
        return $this->isProtected();
    }

    /**
     * Get Super Admin Role
     *
     * @return Role
     */
    public static function superAdmin(): Role
    {
        return static::staticRemember('role.superAdmin', function () {
            return self::where('name', config('permission.roles.super_admin'))->firstOrFail();
        });
    }

    /**
     * Get Operator Role
     *
     * @return Role
     */
    public static function operator(): Role
    {
        return static::staticRemember('role.operator', function () {
            return self::where('name', config('permission.roles.operator'))->firstOrFail();
        });
    }
}
