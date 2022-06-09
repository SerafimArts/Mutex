<?php

/**
 * This file is part of Mutex package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Mutex\Exception;

use Serafim\Mutex\MutexInterface;

class NotAvailableException extends MutexException
{
    final public const CODE_EXTENSION_REQUIRED = 0x01;

    /**
     * @param class-string<MutexInterface> $class
     * @param non-empty-string $name
     * @return self
     */
    public static function fromExtension(string $class, string $name): self
    {
        $message = 'Unable to create "%s" mutex because required extension "%s" is missing';
        $message = \sprintf($message, $class, $name);

        return new self($message, self::CODE_EXTENSION_REQUIRED);
    }
}