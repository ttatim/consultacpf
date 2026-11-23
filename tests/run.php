<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use ConsultaCpf\Contracts\HttpClient;
use ConsultaCpf\Domain\Cpf;
use ConsultaCpf\Domain\CpfConsultationService;
use ConsultaCpf\Infrastructure\FileCircuitBreaker;

final class FakeHttpClient implements HttpClient { public function postJson(string $url, array $payload, array $headers, int $timeoutSeconds): array { return ['status' => 200, 'body' => '{"name":"Pessoa Teste","status":"REGULAR"}']; } }
function assertSame(mixed $expected, mixed $actual): void { if ($expected !== $actual) throw new RuntimeException('Assertion failed.'); }
assertSame('52998224725', Cpf::normalizeAndValidate('529.982.247-25'));
$state = sys_get_temp_dir() . '/cpf-breaker-' . bin2hex(random_bytes(4)) . '.json';
$service = new CpfConsultationService(new FakeHttpClient(), new FileCircuitBreaker($state), 'https://provider.example/v1/cpf', 'test');
assertSame('REGULAR', $service->consult('52998224725', '01/01/1990')['status']);
@unlink($state);
echo "OK\n";
