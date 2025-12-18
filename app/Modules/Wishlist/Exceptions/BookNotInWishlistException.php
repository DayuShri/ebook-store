<?php

namespace App\Modules\Wishlist\Exceptions;

use Exception;

class BookNotInWishlistException extends Exception
{
    public function __construct(string $message = 'Book not found in wishlist', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
