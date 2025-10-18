<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductsController extends Controller
{
    public function index()
    {
        $products = Product::with('media')->paginate(10);
        return ProductResource::collection($products);
    }

    public function show(Product $product)
    {
        $product->load('media');
        return new ProductResource($product);
    }
}
