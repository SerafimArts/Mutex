<?php

/**
 * This file is part of Mutex package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Mutex\Tests\Support;

use Symfony\Component\Process\Process as SymfonyProcess;

final class Process
{
    /**
     * @psalm-taint-sink file $file
     * @param non-empty-string $file
     * @param array $args
     * @return string
     * @throws \JsonException
     */
    public function run(string $file, array $args): string
    {
        $process = $this->create($file, $args);
        $process->mustRun();

        return \trim($process->getOutput());
    }

    /**
     * @psalm-taint-sink file $file
     * @param non-empty-string $file
     * @param array $args
     * @return array
     * @throws \JsonException
     */
    public function runGetJson(string $file, array $args): array
    {
        return (array)\json_decode(
            $this->run($file, $args),
            true,
            flags: \JSON_THROW_ON_ERROR,
        );
    }

    /**
     * @psalm-taint-sink file $file
     * @param non-empty-string $file
     * @param array $args
     * @return SymfonyProcess
     * @throws \JsonException
     */
    private function create(string $file, array $args): SymfonyProcess
    {
        return new SymfonyProcess([
            ...$this->getBinary(),
            $file,
            \json_encode($args, \JSON_THROW_ON_ERROR),
        ], \dirname($file));
    }

    /**
     * @return array<non-empty-string>
     */
    private function getBinary(): array
    {
        return match (true) {
            // phpdbg
            \str_contains(\PHP_BINARY, '/phpdbg'),
            \str_ends_with(\PHP_BINARY, 'phpdbg.exe') => [\PHP_BINARY, '-qrr'],
            // php
            \str_contains(\PHP_BINARY, '/php'),
            \str_ends_with(\PHP_BINARY, 'php.exe') => [\PHP_BINARY],
            // otherwise
            default => ['php'],
        };
    }
}