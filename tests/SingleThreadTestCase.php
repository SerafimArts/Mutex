<?php

/**
 * This file is part of Mutex package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Mutex\Tests;

use Serafim\Mutex\MutexInterface;

class SingleThreadTestCase extends TestCase
{
    /**
     * @dataProvider mutexDataProvider
     */
    public function testNonLockedByDefault(MutexInterface $mutex): void
    {
        $this->assertFalse($mutex->isLocked());
    }

    /**
     * @dataProvider mutexDataProvider
     */
    public function testLockable(MutexInterface $mutex): void
    {
        $mutex->lock();

        try {
            $this->assertTrue($mutex->isLocked());
        } finally {
            $mutex->unlock();
        }
    }

    /**
     * @dataProvider mutexLazyDataProvider
     * @testdox two different mutexes refer to the same state
     */
    public function testDifferentMutexesState(callable $instantiator): void
    {
        /**
         * @var MutexInterface $a
         * @var MutexInterface $b
         */
        [$a, $b] = [$instantiator(), $instantiator()];

        // Data-provider validation
        $this->assertNotSame($a, $b);
        $this->assertFalse($a->isLocked());
        $this->assertFalse($b->isLocked());

        // Lock "A"
        $a->lock();

        // Assert that "A" and "B" is locked.
        $this->assertTrue($a->isLocked());
        $this->assertTrue($b->isLocked());

        // Assert that only "A" holds lock.
        $this->assertTrue($a->holdsLock());
        $this->assertFalse($b->holdsLock());
    }

    /**
     * @dataProvider mutexDataProvider
     */
    public function testRemovable(MutexInterface $mutex): void
    {
        $mutex->lock();
        $this->assertTrue($mutex->isLocked());

        $mutex->remove();
        $this->assertFalse($mutex->isLocked());

        $mutex->lock();
        $this->assertTrue($mutex->isLocked());
    }

    /**
     * @dataProvider mutexLazyDataProvider
     */
    public function testDifferentMutexesStateRemove(callable $instantiator): void
    {
        $this->markTestIncomplete('TODO: Undefined behavior');

        /**
         * @var MutexInterface $a
         * @var MutexInterface $b
         */
        [$a, $b] = [$instantiator(), $instantiator()];

        $a->lock();
        $this->assertTrue($a->isLocked());
        $this->assertTrue($b->isLocked());

        $a->remove();
        $this->assertFalse($a->isLocked());
        $this->assertFalse($b->isLocked());
    }
}
