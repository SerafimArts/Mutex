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

/**
 * Mutual exclusion for co-processes/threads.
 *
 * Semaphore has two states: locked and unlocked. It is non-reentrant, that is
 * invoking lock even from the same thread/process that currently holds the
 * lock still suspends the invoker.
 */
interface MutexInterface
{
    /**
     * Executes a block of code exclusively.
     *
     * This method implements Java's synchronized semantic. I.e. this method
     * waits until a lock could be acquired, executes the code exclusively and
     * releases the lock.
     *
     * The code block may throw an exception. In this case the lock will be
     * released as well.
     *
     * This code can be executed in non-blocking mode if it is run inside
     * a Fiber:
     *
     * ```php
     *  // Your Critical Section
     *  $critical = function (): int {
     *      // Do Something Important
     *
     *      return 42;
     *  };
     *
     *  // Create And Run Fiber
     *  $fiber = new Fiber(fn () => $mutex->synchronized($critical));
     *  $fiber->start();
     *
     *  // Waiting For Critical Section Execution
     *  while (!$fiber->isTerminated()) {
     *      $fiber->resume();
     *  }
     *
     *  $result = $fiber->getReturn();
     *  // Expected Result:
     *  //  int(42)
     * ```
     *
     * @template TReturn of mixed
     *
     * @param callable():TReturn $context
     * @return TReturn
     * @throws LockException
     */
    public function synchronized(callable $context): mixed;

    /**
     * Returns {@see true} if and only if the **current** thread holds the
     * monitor lock on the specified object.
     */
    public function holdsLock(): bool;

    /**
     * @return bool
     */
    public function isLocked(): bool;

    /**
     * Locks this mutex, suspending caller while the mutex is locked.
     *
     * This code can be executed in non-blocking mode if it is run inside
     * a Fiber:
     *
     * ```php
     *  $fiber = new Fiber($mutex->lock(...));
     *  $fiber->start();
     *
     *  // Waiting For A Lock
     *  while (!$fiber->isTerminated()) {
     *      $fiber->resume();
     *  }
     * ```
     *
     * @throws LockException In case of internal error while mutex lock.
     */
    public function lock(): void;

    /**
     * Tries to lock this mutex, returning {@see false} if this mutex is
     * already locked.
     *
     * @throws LockException In case of internal locking error.
     */
    public function tryLock(): bool;

    /**
     * Unlocks this mutex.
     *
     * @throws LockException In case of internal error while unlocking the mutex.
     */
    public function unlock(): void;

    /**
     * Globally destroys the current state of the mutex.
     *
     * @throws LockException In case of internal error while removing the mutex.
     */
    public function remove(): void;
}