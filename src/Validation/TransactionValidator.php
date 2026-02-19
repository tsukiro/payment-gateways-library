<?php

namespace Raion\Gateways\Validation;

use Raion\Gateways\Exceptions\ValidationException;

/**
 * Validador de datos de transacciones
 * Valida parámetros antes de enviarlos a los gateways
 */
class TransactionValidator
{
    /**
     * Montos mínimos por gateway (en la moneda base)
     */
    private const MIN_AMOUNTS = [
        'flow' => 50,
        'webpay' => 50,
        'mercadopago' => 1,
    ];

    /**
     * Montos máximos por gateway
     */
    private const MAX_AMOUNTS = [
        'flow' => 999999999,
        'webpay' => 999999999,
        'mercadopago' => 999999999,
    ];

    /**
     * Monedas soportadas por gateway
     */
    private const SUPPORTED_CURRENCIES = [
        'flow' => ['CLP', 'UF'],
        'webpay' => ['CLP'],
        'mercadopago' => ['CLP', 'ARS', 'BRL', 'MXN', 'USD'],
    ];

    /**
     * Valida todos los parámetros de una transacción
     *
     * @throws ValidationException
     */
    public function validateTransaction(
        string $gateway,
        string $id,
        int $amount,
        string $currency,
        string $description,
        string $email
    ): void {
        $this->validateId($id);
        $this->validateAmount($amount, $gateway);
        $this->validateCurrency($currency, $gateway);
        $this->validateDescription($description);
        $this->validateEmail($email);
    }

    /**
     * Valida el ID de la transacción
     *
     * @throws ValidationException
     */
    public function validateId(string $id): void
    {
        if (empty($id)) {
            throw ValidationException::invalidValue('id', 'Transaction ID cannot be empty');
        }

        if (strlen($id) > 255) {
            throw ValidationException::invalidValue('id', 'Transaction ID cannot exceed 255 characters');
        }

        // Validar caracteres permitidos (alfanuméricos, guiones y guiones bajos)
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
            throw ValidationException::invalidValue(
                'id',
                'Transaction ID can only contain alphanumeric characters, hyphens and underscores'
            );
        }
    }

    /**
     * Valida el monto de la transacción
     *
     * @throws ValidationException
     */
    public function validateAmount(int $amount, string $gateway): void
    {
        if ($amount <= 0) {
            throw ValidationException::invalidValue('amount', 'Amount must be greater than 0');
        }

        $minAmount = self::MIN_AMOUNTS[$gateway] ?? 1;
        if ($amount < $minAmount) {
            throw ValidationException::invalidValue(
                'amount',
                "Amount must be at least {$minAmount} for {$gateway} gateway"
            );
        }

        $maxAmount = self::MAX_AMOUNTS[$gateway] ?? PHP_INT_MAX;
        if ($amount > $maxAmount) {
            throw ValidationException::invalidValue(
                'amount',
                "Amount cannot exceed {$maxAmount} for {$gateway} gateway"
            );
        }
    }

    /**
     * Valida la moneda
     *
     * @throws ValidationException
     */
    public function validateCurrency(string $currency, string $gateway): void
    {
        if (empty($currency)) {
            throw ValidationException::invalidValue('currency', 'Currency cannot be empty');
        }

        $currency = strtoupper($currency);
        $supportedCurrencies = self::SUPPORTED_CURRENCIES[$gateway] ?? [];

        if (!in_array($currency, $supportedCurrencies)) {
            $supported = implode(', ', $supportedCurrencies);
            throw ValidationException::invalidValue(
                'currency',
                "Currency '{$currency}' is not supported by {$gateway}. Supported: {$supported}"
            );
        }
    }

    /**
     * Valida la descripción
     *
     * @throws ValidationException
     */
    public function validateDescription(string $description): void
    {
        if (empty($description)) {
            throw ValidationException::invalidValue('description', 'Description cannot be empty');
        }

        if (strlen($description) < 3) {
            throw ValidationException::invalidValue('description', 'Description must be at least 3 characters');
        }

        if (strlen($description) > 500) {
            throw ValidationException::invalidValue('description', 'Description cannot exceed 500 characters');
        }
    }

    /**
     * Valida el email
     *
     * @throws ValidationException
     */
    public function validateEmail(string $email): void
    {
        if (empty($email)) {
            throw ValidationException::invalidValue('email', 'Email cannot be empty');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::invalidValue('email', "Invalid email format: {$email}");
        }

        // Validar longitud
        if (strlen($email) > 254) {
            throw ValidationException::invalidValue('email', 'Email cannot exceed 254 characters');
        }
    }

    /**
     * Obtiene las monedas soportadas por un gateway
     *
     * @return string[]
     */
    public function getSupportedCurrencies(string $gateway): array
    {
        return self::SUPPORTED_CURRENCIES[$gateway] ?? [];
    }

    /**
     * Obtiene el monto mínimo para un gateway
     */
    public function getMinAmount(string $gateway): int
    {
        return self::MIN_AMOUNTS[$gateway] ?? 1;
    }

    /**
     * Obtiene el monto máximo para un gateway
     */
    public function getMaxAmount(string $gateway): int
    {
        return self::MAX_AMOUNTS[$gateway] ?? PHP_INT_MAX;
    }
}
