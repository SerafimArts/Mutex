<?php

/**
 * This file is part of Mutex package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Mutex\Internal;

/**
 * @psalm-type ErrorArray = array{
 *  type: positive-int,
 *  message: string,
 *  file: non-empty-string,
 *  line: positive-int
 * }
 *
 * @internal ErrorHandler is an internal library class, please do not use
 *           it in your code.
 * @psalm-internal \Serafim\Mutex
 */
final class ErrorHandler
{
    /**
     * This class is "static" and cannot be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * @template TReturn of mixed
     *
     * @param callable():TReturn $context
     * @return TReturn
     * @throws \ErrorException
     */
    public static function handle(callable $context): mixed
    {
        \error_clear_last();

        $result = $context();

        /** @psalm-var ErrorArray|null $error */
        $error = \error_get_last();
        if ($error !== null) {
            throw self::errorArrayToException($error);
        }

        /** @psalm-var TReturn $result */
        return $result;
    }

    /**
     * @param ErrorArray $error
     * @param int $code
     * @return \ErrorException
     */
    private static function errorArrayToException(array $error, int $code = 0): \ErrorException
    {
        $message = $error['message'];

        if (\str_contains($message, '():')) {
            $parts = \explode('():', $message);
            $message = \trim(\implode('():', \array_slice($parts, 1)));
        }

        return new \ErrorException(
            $message,
            $code,
            $error['type'],
            $error['file'],
            $error['line'],
        );
    }
}