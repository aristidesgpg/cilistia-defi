<?php

namespace App\Models\Support;

use App\Exceptions\LockException;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;

trait Lock
{
    /**
     * Get cache lock name
     *
     * @return string
     */
    protected function getLockName(): string
    {
        return 'lock.' . $this->getTable() . '.' . $this->getKey();
    }

    /**
     * Get lock exception message
     *
     * @return string
     */
    protected function getLockExceptionMessage(): string
    {
        return trans('common.model_in_use', ['name' => $this->getTable() . ':' . $this->getKey()]);
    }

    /**
     * Attempts to acquire lock
     *
     * @param  callable  $callback
     * @return mixed
     */
    protected function __acquireLock(callable $callback): mixed
    {
        try {
            $reflection = new ReflectionFunction($callback);

            return Cache::store('redis')->lock($this->getLockName())->get(function () use ($reflection) {
                return tap(match ($reflection->getNumberOfParameters()) {
                    1 => $reflection->invoke($this->fresh()),
                    0 => $reflection->invoke(),
                    default => throw new InvalidArgumentException(),
                });
            });
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * Acquire lock
     *
     * @param  callable  $callback
     * @return mixed
     */
    public function acquireLock(callable $callback): mixed
    {
        return untap($this->__acquireLock($callback));
    }

    /**
     * Acquire Lock or throw exception
     *
     * @param  callable  $callback
     * @return mixed
     *
     * @throws LockException
     */
    public function acquireLockOrThrow(callable $callback): mixed
    {
        return with($this->__acquireLock($callback), function ($result) {
            if ($result === false) {
                throw new LockException($this->getLockExceptionMessage());
            }

            return untap($result);
        });
    }

    /**
     * Acquire Lock or throw Http Exception
     *
     * @param  callable  $callback
     * @return mixed
     */
    public function acquireLockOrAbort(callable $callback): mixed
    {
        try {
            return $this->acquireLockOrThrow($callback);
        } catch (LockException $exception) {
            abort(403, $exception->getMessage());
        }
    }
}
