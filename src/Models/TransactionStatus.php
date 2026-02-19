<?php

namespace Raion\Gateways\Models;

enum TransactionStatus: int
{
    case Pending = 0;
    case Confirmed = 1;
    case Failed = 2;

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isConfirmed(): bool
    {
        return
            $this === self::Confirmed;
    }

    public function isFailed(): bool
    {
        return
            $this === self::Failed;
    }
}