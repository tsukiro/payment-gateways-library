<?php

namespace Raion\Gateways\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use Raion\Gateways\Models\TransactionStatus;

class TransactionStatusTest extends TestCase
{
    public function test_pending_status_value(): void
    {
        $this->assertEquals(0, TransactionStatus::Pending->value);
    }

    public function test_confirmed_status_value(): void
    {
        $this->assertEquals(1, TransactionStatus::Confirmed->value);
    }

    public function test_failed_status_value(): void
    {
        $this->assertEquals(2, TransactionStatus::Failed->value);
    }

    public function test_is_pending_returns_true_for_pending(): void
    {
        $this->assertTrue(TransactionStatus::Pending->isPending());
        $this->assertFalse(TransactionStatus::Confirmed->isPending());
        $this->assertFalse(TransactionStatus::Failed->isPending());
    }

    public function test_is_confirmed_returns_true_for_confirmed(): void
    {
        $this->assertTrue(TransactionStatus::Confirmed->isConfirmed());
        $this->assertFalse(TransactionStatus::Pending->isConfirmed());
        $this->assertFalse(TransactionStatus::Failed->isConfirmed());
    }

    public function test_is_failed_returns_true_for_failed(): void
    {
        $this->assertTrue(TransactionStatus::Failed->isFailed());
        $this->assertFalse(TransactionStatus::Pending->isFailed());
        $this->assertFalse(TransactionStatus::Confirmed->isFailed());
    }

    public function test_can_get_all_status_cases(): void
    {
        $cases = TransactionStatus::cases();
        
        $this->assertCount(3, $cases);
        $this->assertContains(TransactionStatus::Pending, $cases);
        $this->assertContains(TransactionStatus::Confirmed, $cases);
        $this->assertContains(TransactionStatus::Failed, $cases);
    }
}
