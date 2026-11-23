<?php

declare(strict_types=1);

namespace ConsultaCpf\Domain;

final class Cpf
{
    public static function normalizeAndValidate(string $value): string
    {
        $cpf = preg_replace('/\D/', '', $value) ?? '';
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            throw new ApiException(422, 'CPF inválido.', 'invalid_cpf');
        }
        for ($position = 9; $position < 11; $position++) {
            $sum = 0;
            for ($index = 0; $index < $position; $index++) {
                $sum += (int) $cpf[$index] * (($position + 1) - $index);
            }
            $digit = (($sum * 10) % 11) % 10;
            if ($digit !== (int) $cpf[$position]) {
                throw new ApiException(422, 'CPF inválido.', 'invalid_cpf');
            }
        }
        return $cpf;
    }
}
