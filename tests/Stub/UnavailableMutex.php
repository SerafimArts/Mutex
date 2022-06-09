<?php

/**
 * This file is part of Mutex package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Mutex\Tests\Stub;

use PHPUnit\Framework\TestCase;
use Serafim\Mutex\Exception\NotAvailableException;
use Serafim\Mutex\MutexInterface;

class UnavailableMutex implements MutexInterface
{
    /**
     * @param NotAvailableException $error
     * @param TestCase $context
     */
    public function __construct(
        private readonly NotAvailableException $error,
        private readonly TestCase $context,
    ) {
    }

    public function synchronized(callable $context): mixed
    {
        $this->context->markTestIncomplete($this->error->getMessage());
    }

    public function holdsLock(): bool
    {
        $this->context->markTestIncomplete($this->error->getMessage());
    }

    public function lock(): void
    {
        $this->context->markTestIncomplete($this->error->getMessage());
    }

    public function tryLock(): bool
    {
        $this->context->markTestIncomplete($this->error->getMessage());
    }

    public function unlock(): void
    {
        $this->context->markTestIncomplete($this->error->getMessage());
    }

    public function remove(): void
    {
        $this->context->markTestIncomplete($this->error->getMessage());
    }
}