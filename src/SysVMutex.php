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
use Serafim\Mutex\Exception\NotAvailableException;
use Serafim\Mutex\Internal\ErrorHandler;

/**
 * @psalm-type MutexAddress = positive-int
 */
class SysVMutex extends Mutex
{
    /**
     * @var \SysvSemaphore|null
     */
    private ?\SysvSemaphore $ptr = null;

    /**
     * @param MutexAddress $id
     * @throws NotAvailableException
     */
    public function __construct(
        public readonly int $id,
    ) {
        if (!\extension_loaded('sysvsem')) {
            throw NotAvailableException::fromExtension(static::class, 'sysvsem');
        }
    }

    /**
     * @return \SysvSemaphore
     * @throws \ErrorException
     *
     * @psalm-suppress InvalidNullableReturnType
     * @psalm-suppress NullableReturnStatement
     */
    protected function create(): \SysvSemaphore
    {
        return ErrorHandler::handle(fn (): ?\SysvSemaphore =>
            @\sem_get($this->id)
        );
    }

    /**
     * @return bool
     * @throws LockException
     * @throws \ErrorException
     */
    protected function acquire(): bool
    {
        return ErrorHandler::handle(fn(): bool =>
            @\sem_acquire($this->getMutex(), true)
        );
    }

    /**
     * @return bool
     * @throws LockException
     * @throws \ErrorException
     */
    protected function release(): bool
    {
        return ErrorHandler::handle(fn (): bool =>
            @\sem_release($this->getMutex())
        );
    }

    /**
     * @throws LockException
     */
    protected function getMutex(): \SysvSemaphore
    {
        try {
            return $this->ptr ??= $this->create();
        } catch (\Throwable $e) {
            throw LockException::fromException($e);
        }
    }

    /**
     * @return void
     * @throws \ErrorException
     */
    public function remove(): void
    {
        if ($this->ptr !== null) {
            $this->locked = false;
            /** @psalm-suppress PossiblyNullArgument */
            ErrorHandler::handle(fn () => @\sem_remove($this->ptr));
            $this->ptr = null;
        }
    }

    /**
     * @return array{
     *  id: positive-int,
     *  locked: bool,
     *  exists: bool
     * }
     */
    public function __debugInfo(): array
    {
        return \array_merge(parent::__debugInfo(), [
            'id' => $this->id,
            'exists' => $this->ptr !== null,
        ]);
    }
}