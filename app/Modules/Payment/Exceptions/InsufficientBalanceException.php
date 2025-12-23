<?php

namespace App\Modules\Payment\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    public function __construct(string $message = 'Insufficient wallet balance', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}