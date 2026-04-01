<?php

namespace App\Actions\Support;

class ActionResult
{
    private const TYPE_VALIDATION = 'validation';

    private const TYPE_DOMAIN = 'domain';

    private const TYPE_AUTHORIZATION = 'authorization';

    private const TYPE_INFRASTRUCTURE = 'infrastructure';

    public function __construct(
        private bool $success,
        private int $status,
        private ?string $message = null,
        private array $data = [],
        private ?string $errorType = null,
        private array $errors = [],
    ) {}

    public static function success(array $data = [], ?string $message = null, int $status = 200): self
    {
        return new self(true, $status, $message, $data);
    }

    public static function validationError(string $message, array $errors = [], int $status = 422): self
    {
        return new self(false, $status, $message, [], self::TYPE_VALIDATION, $errors);
    }

    public static function domainError(string $message, array $errors = [], int $status = 422): self
    {
        return new self(false, $status, $message, [], self::TYPE_DOMAIN, $errors);
    }

    public static function authorizationFailure(string $message = 'Unauthorized action.', int $status = 403): self
    {
        return new self(false, $status, $message, [], self::TYPE_AUTHORIZATION);
    }

    public static function infrastructureFailure(string $message = 'Unexpected infrastructure failure.', int $status = 500): self
    {
        return new self(false, $status, $message, [], self::TYPE_INFRASTRUCTURE);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function message(): ?string
    {
        return $this->message;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function errorType(): ?string
    {
        return $this->errorType;
    }

    public function toPayload(): array
    {
        return array_merge(
            [
                'success' => $this->success,
                'message' => $this->message,
            ],
            $this->data,
            $this->errors === [] ? [] : ['errors' => $this->errors],
            $this->errorType === null ? [] : ['error_type' => $this->errorType],
        );
    }
}
