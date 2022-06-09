<?php

/**
 * This file is part of Mutex package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Mutex;

use Serafim\Mutex\Exception\LockException;

abstract class Mutex implements MutexInterface
{
    /**
     * Contains information about **local** blocking.
     *
     * Note: It is worth noting that this value has nothing to do with whether
     * the Semaphore is actually locked and is just a switch that contains
     * information about the state of the mutex in the current thread/process.
     */
    protected bool $locked = false;

    /**
     * @throws \Throwable
     */
    abstract protected function create(): mixed;

    /**
     * @throws \Throwable
     */
    abstract protected function acquire(): bool;

    /**
     * @throws \Throwable
     */
    abstract protected function release(): bool;

    /**
     * {@inheritDoc}
     */
    public function holdsLock(): bool
    {
        return $this->locked;
    }

    /**
     * {@inheritDoc}
     */
    public function lock(): void
    {
        if ($this->locked === false) {
            try {
                while (!$this->acquire()) {
                    \Fiber::getCurrent() and \Fiber::suspend();
                }

                $this->locked = true;
            } catch (\Throwable $e) {
                $this->locked = false;
                throw LockException::fromException($e);
            }
        }
    }

    /**
     * @return bool
     * @throws LockException
     */
    public function isLocked(): bool
    {
        if ($this->tryLock()) {
            $this->unlock();

            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function tryLock(): bool
    {
        if ($this->locked === false) {
            try {
                return $this->locked = $this->acquire();
            } catch (\Throwable $e) {
                $this->locked = false;
                throw LockException::fromException($e);
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function unlock(): void
    {
        if ($this->locked === true) {
            try {
                $this->locked = !$this->release();
            } catch (\Throwable $e) {
                $this->locked = true;
                throw LockException::fromException($e);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function synchronized(callable $context): mixed
    {
        $this->lock();

        try {
            return $context();
        } finally {
            $this->unlock();
        }
    }

    /**
     * @return array{locked: bool}
     */
    public function __debugInfo(): array
    {
        return ['locked' => $this->locked];
    }

    /**
     * Force unlock the mutex in case of developer forget it.
     */
    public function __destruct()
    {
        if ($this->locked) {
            $this->unlock();
        }
    }
}