<?php

namespace App\Modules\Wishlist\Exceptions;

use Exception;

class WishlistException extends Exception
{
    public function __construct(string $message = 'An error occurred with the wishlist', int $code = 500)
    {
        parent::__construct($message, $code);
    }
}
