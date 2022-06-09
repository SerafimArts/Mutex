<?php

/**
 * This file is part of Mutex package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Mutex\Exception;

class LockException extends MutexException
{
    /**
     * @param \Throwable $e
     * @return self
     */
    public static function fromException(\Throwable $e): self
    {
        $exception = new self($e->getMessage(), (int) $e->getCode());
        $exception->file = $e->getFile();
        $exception->line = $e->getLine();

        return $exception;
    }
}