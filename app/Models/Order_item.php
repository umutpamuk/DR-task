<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order_item extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function order(){
        return $this->belongsTo(Order::class,'order_id','id');
    }

    public function campaign() {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }

    public static function orderItemsDetail($id) {

        $orderItemDetails = Order_item::where('order_id',$id)->get();

        foreach ($orderItemDetails as $orderItem) {
            $productDetailsArray[] = [
                'productId'     => $orderItem->product->id,
                'productName'   => $orderItem->product->product_name,
                'quantity'      => $orderItem->quantity,
                'categoryId'    => $orderItem->product->category->id,
                'categoryName'  => $orderItem->product->category->category_name,
                'authorId'      => $orderItem->product->author->id,
                'authorName'    => $orderItem->product->author->author_name,
                'authorNationality'   => $orderItem->product->author->author_nationality,
                'productSellingPrice' => $orderItem->selling_price,
                'productDiscountPrice'=> $orderItem->discount_price,
                'campaignId'    => $orderItem->campaign_id,
                'campaignName'    => ($orderItem->campaign_id) ? $orderItem->campaign->campaign_name : NULL,
            ];
        }

        return $productDetailsArray;

    }
}
