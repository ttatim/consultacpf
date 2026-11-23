<?php

declare(strict_types=1);

namespace ConsultaCpf\Infrastructure;

use ConsultaCpf\Domain\ApiException;

final class FileCircuitBreaker
{
    public function __construct(private readonly string $path, private readonly int $failureThreshold = 3, private readonly int $cooldownSeconds = 60) {}

    public function assertAvailable(): void
    {
        $state = $this->read();
        if (($state['openUntil'] ?? 0) > time()) {
            throw new ApiException(503, 'Provedor temporariamente indisponível. Tente novamente mais tarde.', 'provider_unavailable');
        }
    }

    public function success(): void { $this->write(['failures' => 0, 'openUntil' => 0]); }

    public function failure(): void
    {
        $state = $this->read();
        $failures = ($state['failures'] ?? 0) + 1;
        $this->write(['failures' => $failures, 'openUntil' => $failures >= $this->failureThreshold ? time() + $this->cooldownSeconds : 0]);
    }

    private function read(): array
    {
        if (!is_file($this->path)) return [];
        return json_decode((string) file_get_contents($this->path), true) ?: [];
    }
    private function write(array $state): void
    {
        $directory = dirname($this->path);
        if (!is_dir($directory)) mkdir($directory, 0700, true);
        file_put_contents($this->path, json_encode($state, JSON_THROW_ON_ERROR), LOCK_EX);
        @chmod($this->path, 0600);
    }
}
