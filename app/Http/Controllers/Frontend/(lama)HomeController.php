<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\CatalogService;
use App\Services\Frontend\CartService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected CatalogService $catalogService;
    protected CartService $cartService;

    public function __construct(CatalogService $catalogService, CartService $cartService)
    {
        $this->catalogService = $catalogService;
        $this->cartService = $cartService;
    }

    /**
     * Display homepage with featured books
     */
    public function index()
    {
        $featuredBooks = $this->catalogService->getFeaturedBooks(8);
        $newestBooks = $this->catalogService->getNewestBooks(4);
        $categories = $this->catalogService->getCategories();
        
        return view('frontend.home.index', [
            'featuredBooks' => $featuredBooks,
            'newestBooks' => $newestBooks,
            'categories' => $categories,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }
}
