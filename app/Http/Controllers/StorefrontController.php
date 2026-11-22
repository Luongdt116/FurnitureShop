<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    /**
     * Display the storefront home page.
     */
    public function home(): View
    {
        // Load active categories
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        // Best sellers (first 6 active products)
        $bestSellers = Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true)
            ->orderByDesc('discount_percent')
            ->take(6)
            ->get();

        // Flash sale products (with discount_percent > 0)
        $flashSales = Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true)
            ->where('discount_percent', '>', 0)
            ->orderByDesc('discount_percent')
            ->take(4)
            ->get();

        return view('storefront.home', compact('categories', 'bestSellers', 'flashSales'));
    }

    /**
     * Display the product catalog with filtering, sorting and pagination.
     */
    public function catalog(Request $request): View
    {
        $query = Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true);

        // Filter by category slug or id
        if ($request->filled('category')) {
            $catSlug = $request->query('category');
            $query->whereHas('category', function ($q) use ($catSlug) {
                $q->where('slug', $catSlug)->orWhere('id', $catSlug);
            });
        }

        // Filter by room
        if ($request->filled('room') && $request->query('room') !== 'all') {
            $query->where('dimensions', 'like', '%' . $request->query('room') . '%')
                  ->orWhere('description', 'like', '%' . $request->query('room') . '%');
        }

        // Filter by material
        if ($request->filled('material') && $request->query('material') !== 'all') {
            $query->where('material', 'like', '%' . $request->query('material') . '%');
        }

        // Filter by price range
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->query('max_price'));
        }

        // Search query
        if ($request->filled('q')) {
            $searchTerm = '%' . $request->query('q') . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('sku', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('material', 'like', $searchTerm);
            });
        }

        // Sorting
        switch ($request->query('sort', 'newest')) {
            case 'price-asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price-desc':
                $query->orderBy('price', 'desc');
                break;
            case 'bestseller':
                $query->orderByDesc('discount_percent');
                break;
            case 'newest':
            default:
                $query->orderByDesc('id');
                break;
        }

        $products = $query->paginate(9)->withQueryString();
        $categories = Category::query()->where('is_active', true)->orderBy('sort_order')->get();

        return view('storefront.catalog', compact('products', 'categories'));
    }

    /**
     * Display product detail page.
     */
    public function productDetail(string $slug): View
    {
        $product = Product::query()
            ->with(['category', 'images', 'reviews.user'])
            ->where('slug', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        $relatedProducts = Product::query()
            ->with(['category', 'images'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(4)
            ->get();

        return view('storefront.product-detail', compact('product', 'relatedProducts'));
    }
}
