<?php

declare(strict_types=1);

namespace ConsultaCpf\Domain;

use ConsultaCpf\Contracts\HttpClient;
use ConsultaCpf\Infrastructure\FileCircuitBreaker;
use JsonException;
use Throwable;

final class CpfConsultationService
{
    public function __construct(private readonly HttpClient $http, private readonly FileCircuitBreaker $breaker, private readonly string $providerUrl, private readonly string $apiKey) {}

    public function consult(string $cpf, string $birthDate): array
    {
        $cpf = Cpf::normalizeAndValidate($cpf);
        $date = \DateTimeImmutable::createFromFormat('!d/m/Y', $birthDate);
        if (!$date || $date->format('d/m/Y') !== $birthDate) throw new ApiException(422, 'Data de nascimento deve usar DD/MM/AAAA.', 'invalid_birth_date');
        if ($this->providerUrl === '' || $this->apiKey === '') throw new ApiException(503, 'Provedor de consulta não configurado.', 'provider_not_configured');
        $this->breaker->assertAvailable();
        try {
            $response = $this->http->postJson($this->providerUrl, ['cpf' => $cpf, 'birth_date' => $birthDate], ['Authorization: Bearer ' . $this->apiKey], 10);
            if ($response['status'] === 429 || $response['status'] >= 500) throw new ApiException(503, 'Provedor temporariamente indisponível.', 'provider_unavailable');
            if ($response['status'] >= 400) throw new ApiException(422, 'Dados não aceitos pelo provedor.', 'provider_rejected_request');
            $data = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($data)) throw new JsonException('Resposta inválida.');
            $this->breaker->success();
            return $data;
        } catch (ApiException $exception) {
            $this->breaker->failure();
            throw $exception;
        } catch (Throwable) {
            $this->breaker->failure();
            throw new ApiException(503, 'Falha ao comunicar com o provedor.', 'provider_unavailable');
        }
    }
}
