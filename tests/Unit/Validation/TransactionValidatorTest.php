<?php

namespace Tests\Unit\Validation;

use PHPUnit\Framework\TestCase;
use Raion\Gateways\Validation\TransactionValidator;
use Raion\Gateways\Exceptions\ValidationException;

class TransactionValidatorTest extends TestCase
{
    private TransactionValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new TransactionValidator();
    }

    public function testValidateValidTransaction(): void
    {
        $this->expectNotToPerformAssertions();
        
        $this->validator->validateTransaction(
            'flow',
            'ORDER-123',
            10000,
            'CLP',
            'Test product',
            'user@example.com'
        );
    }

    public function testValidateIdTooShort(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Transaction ID cannot be empty');
        
        $this->validator->validateTransaction(
            'flow',
            '',
            10000,
            'CLP',
            'Test product',
            'user@example.com'
        );
    }

    public function testValidateIdTooLong(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Transaction ID cannot exceed 255 characters');
        
        $this->validator->validateTransaction(
            'flow',
            str_repeat('A', 256),
            10000,
            'CLP',
            'Test product',
            'user@example.com'
        );
    }

    public function testValidateIdInvalidCharacters(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Transaction ID can only contain alphanumeric characters, hyphens and underscores');
        
        $this->validator->validateTransaction(
            'flow',
            'ORDER@123!',
            10000,
            'CLP',
            'Test product',
            'user@example.com'
        );
    }

    public function testValidateAmountBelowMinimum(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Amount must be at least 50 for flow');
        
        $this->validator->validateTransaction(
            'flow',
            'ORDER-123',
            49,
            'CLP',
            'Test product',
            'user@example.com'
        );
    }

    public function testValidateAmountAboveMaximum(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Amount cannot exceed');
        
        $this->validator->validateTransaction(
            'flow',
            'ORDER-123',
            1000000000,
            'CLP',
            'Test product',
            'user@example.com'
        );
    }

    public function testValidateUnsupportedCurrency(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Currency 'USD' is not supported by flow");
        
        $this->validator->validateTransaction(
            'flow',
            'ORDER-123',
            10000,
            'USD',
            'Test product',
            'user@example.com'
        );
    }

    public function testValidateSupportedCurrenciesForMercadoPago(): void
    {
        $this->expectNotToPerformAssertions();
        
        // MercadoPago soporta múltiples monedas
        $currencies = ['CLP', 'ARS', 'BRL', 'MXN', 'USD'];
        
        foreach ($currencies as $currency) {
            $this->validator->validateTransaction(
                'mercadopago',
                'ORDER-123',
                1000,
                $currency,
                'Test product',
                'user@example.com'
            );
        }
    }

    public function testValidateDescriptionTooShort(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Description must be at least 3 characters');
        
        $this->validator->validateTransaction(
            'flow',
            'ORDER-123',
            10000,
            'CLP',
            'AB',
            'user@example.com'
        );
    }

    public function testValidateDescriptionTooLong(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Description cannot exceed 500 characters');
        
        $this->validator->validateTransaction(
            'flow',
            'ORDER-123',
            10000,
            'CLP',
            str_repeat('A', 501),
            'user@example.com'
        );
    }

    public function testValidateInvalidEmail(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid email format');
        
        $this->validator->validateTransaction(
            'flow',
            'ORDER-123',
            10000,
            'CLP',
            'Test product',
            'invalid-email'
        );
    }

    public function testValidateEmailWithSpecialCharacters(): void
    {
        $this->expectNotToPerformAssertions();
        
        $this->validator->validateTransaction(
            'flow',
            'ORDER-123',
            10000,
            'CLP',
            'Test product',
            'user+tag@example.co.uk'
        );
    }

    public function testValidateWebpayTransaction(): void
    {
        $this->expectNotToPerformAssertions();
        
        $this->validator->validateTransaction(
            'webpay',
            'ORDER-123',
            1000,
            'CLP',
            'Test product',
            'user@example.com'
        );
    }

    public function testWebpayMinimumAmount(): void
    {
        $this->expectException(ValidationException::class);
        
        $this->validator->validateTransaction(
            'webpay',
            'ORDER-123',
            49,
            'CLP',
            'Test product',
            'user@example.com'
        );
    }

    public function testFlowSupportsUF(): void
    {
        $this->expectNotToPerformAssertions();
        
        $this->validator->validateTransaction(
            'flow',
            'ORDER-123',
            50,
            'UF',
            'Test product',
            'user@example.com'
        );
    }
}
