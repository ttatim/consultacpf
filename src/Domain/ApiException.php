<?php

declare(strict_types=1);

namespace ConsultaCpf\Domain;

use RuntimeException;

final class ApiException extends RuntimeException
{
    public function __construct(public readonly int $httpStatus, string $message, public readonly string $codeName)
    {
        parent::__construct($message);
    }
}
