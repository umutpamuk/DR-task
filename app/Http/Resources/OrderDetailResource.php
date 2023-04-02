<?php

namespace App\Http\Resources;

use App\Models\Order_item;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        return [
            'user' => [
                'userName' => $this['user']['name'],
                'userEmail' => $this['user']['email'],
            ],
            'order' => [
                'orderNumber'   => $this->order_number,
                'orderItems'    => Order_item::orderItemsDetail($this->id),
                'address'       => $this->address,
                'shippingPrice' => $this->shipping_price,
                'totalAmount'   => $this->total_amount,
                'createdAt'     => $this->created_at,
            ]
        ];
    }
}
