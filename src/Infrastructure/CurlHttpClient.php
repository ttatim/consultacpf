<?php

declare(strict_types=1);

namespace ConsultaCpf\Infrastructure;

use ConsultaCpf\Contracts\HttpClient;
use RuntimeException;

final class CurlHttpClient implements HttpClient
{
    public function postJson(string $url, array $payload, array $headers, int $timeoutSeconds): array
    {
        $handle = curl_init($url);
        if ($handle === false) {
            throw new RuntimeException('Não foi possível inicializar o cliente HTTP.');
        }

        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Accept: application/json';
        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => min(5, $timeoutSeconds),
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $body = curl_exec($handle);
        $error = curl_error($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        if ($body === false) {
            throw new RuntimeException('Falha de transporte com o provedor: ' . $error);
        }
        return ['status' => $status, 'body' => $body];
    }
}
