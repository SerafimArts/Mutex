<?php

/**
 * This file is part of Mutex package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Mutex\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Serafim\Mutex\Exception\NotAvailableException;
use Serafim\Mutex\MutexInterface;
use Serafim\Mutex\SysVMutex;
use Serafim\Mutex\Tests\Stub\UnavailableMutex;

abstract class TestCase extends BaseTestCase
{
    private static int $mutexId = 0;

    /**
     * @return array<non-empty-string, array{MutexInterface}>
     */
    public function mutexDataProvider(): array
    {
        $result = [];

        foreach ($this->mutexLazyDataProvider() as $name => [$instantiator]) {
            try {
                $result[$name] = [$instantiator()];
            } catch (NotAvailableException $e) {
                $result[$name] = [new UnavailableMutex($e, $this)];
            }
        }

        return $result;
    }

    /**
     * @return array<non-empty-string, array{callable():MutexInterface}>
     */
    public function mutexLazyDataProvider(): array
    {
        return [
            SysVMutex::class => [$this->pureMutexInitialization(function (): MutexInterface {
                static $id;
                $id ??= $this->createMutexId();

                return new SysVMutex($id);
            })],
        ];
    }

    private function pureMutexInitialization(\Closure $instantiator): \Closure
    {
        return function () use ($instantiator): MutexInterface {
            try {
                return $instantiator();
            } catch (NotAvailableException $e) {
                return new UnavailableMutex($e, $this);
            }
        };
    }

    /**
     * @return positive-int
     */
    protected function createMutexId(): int
    {
        return ++self::$mutexId;
    }
}