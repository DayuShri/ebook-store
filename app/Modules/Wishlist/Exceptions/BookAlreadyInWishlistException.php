<?php

namespace App\Modules\Wishlist\Exceptions;

use Exception;

class BookAlreadyInWishlistException extends Exception
{
    public function __construct(string $message = 'Book is already in wishlist', int $code = 409)
    {
        parent::__construct($message, $code);
    }
}
