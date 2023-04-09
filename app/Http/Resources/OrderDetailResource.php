<?php

namespace App\Http\Resources;

use App\Models\Order_item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
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
