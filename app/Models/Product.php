<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function author() {
        return $this->belongsTo(Author::class,'author_id', 'id');
    }

    public function category() {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public static function productQuantityCheck($orderProductItem)
    {
        $product = Product::where('id', $orderProductItem['product_id'])->first();

        if ($product->product_quantity >= $orderProductItem['product_quantity']) {
            $response = json_encode(['status'=>"success", 'productDetails' => $product, 'orderProductItem' => $orderProductItem]);
        } else {
            $response = json_encode(['status'=>"error", 'message' => "ProductId : {$product->id} tükendi"]);
        }

        return $response;
    }

}
