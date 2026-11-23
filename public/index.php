<?php

declare(strict_types=1);

use ConsultaCpf\Domain\ApiException;
use ConsultaCpf\Domain\CpfConsultationService;
use ConsultaCpf\Infrastructure\CurlHttpClient;
use ConsultaCpf\Infrastructure\FileCircuitBreaker;

require dirname(__DIR__) . '/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_SERVER['REQUEST_URI'] ?? '') === '/health') { http_response_code(200); echo json_encode(['status' => 'ok']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) !== '/v1/cpf/consultations') { http_response_code(404); echo json_encode(['error' => ['code' => 'not_found', 'message' => 'Rota não encontrada.']]); exit; }

try {
    $request = json_decode((string) file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($request) || !isset($request['cpf'], $request['birth_date'])) throw new ApiException(400, 'Informe cpf e birth_date.', 'invalid_request');
    $service = new CpfConsultationService(new CurlHttpClient(), new FileCircuitBreaker(dirname(__DIR__) . '/var/circuit-breaker.json'), getenv('CPF_PROVIDER_URL') ?: '', getenv('CPF_PROVIDER_API_KEY') ?: '');
    echo json_encode(['data' => $service->consult((string) $request['cpf'], (string) $request['birth_date'])], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
} catch (ApiException $exception) {
    http_response_code($exception->httpStatus); echo json_encode(['error' => ['code' => $exception->codeName, 'message' => $exception->getMessage()]]);
} catch (JsonException) {
    http_response_code(400); echo json_encode(['error' => ['code' => 'invalid_json', 'message' => 'JSON inválido.']]);
}
