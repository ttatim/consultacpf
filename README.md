# API de consulta de CPF

API PHP 8.3, sem Composer e sem framework. Ela **não automatiza a página pública da Receita nem tenta resolver CAPTCHA**. O portal público exige interação humana e pode mudar ou limitar acessos; portanto, não há como garantir disponibilidade ou impedir bloqueios usando aquele scraping legado.

Para operação de produção, configure um provedor de dados oficialmente contratado/autorizado. A aplicação oferece validação local de CPF, TLS obrigatório, timeout, circuit breaker e respostas estáveis quando o provedor estiver indisponível.

## Configuração

```bash
export CPF_PROVIDER_URL='https://seu-provedor.example/v1/cpf'
export CPF_PROVIDER_API_KEY='sua-chave'
php -S 127.0.0.1:8080 -t public
```

O provedor deve aceitar `POST` JSON com `cpf` e `birth_date` (`DD/MM/AAAA`) e devolver JSON. A API repassa o conteúdo do provedor dentro de `data`.

## Uso

```bash
curl -X POST http://127.0.0.1:8080/v1/cpf/consultations \
  -H 'Content-Type: application/json' \
  -d '{"cpf":"529.982.247-25","birth_date":"01/01/1990"}'
```

`GET /health` confirma que o processo está disponível. Execute os testes com `php tests/run.php`.

## Limites operacionais

Nenhuma integração externa pode garantir disponibilidade contínua. Para SLA, contrato, base legal/LGPD, credenciais e suporte devem ser providos pelo fornecedor autorizado. Não registre CPF, nascimento ou chave de API em logs.
