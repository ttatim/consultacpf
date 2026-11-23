<?php

declare(strict_types=1);

namespace ConsultaCpf\Contracts;

interface HttpClient
{
    /** @return array{status:int,body:string} */
    public function postJson(string $url, array $payload, array $headers, int $timeoutSeconds): array;
}
