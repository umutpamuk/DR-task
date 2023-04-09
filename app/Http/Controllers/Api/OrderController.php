<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderDetailResource;
use App\Models\Order;
use App\Models\Order_item;
use App\Models\Product;
use App\Traits\OrderTrait;
use App\Traits\ApiResponder;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    use OrderTrait;
    use ApiResponder;

    /**
     * @param CreateOrderRequest $request
     * @return JsonResponse
     */
    public function store(CreateOrderRequest $request) : JsonResponse
    {
        $checkout = json_decode($this->checkout($request), true);

        if ($checkout['status'] == "error") {

           return $this->sendError($checkout['message']);
        }

        $applyCampaign = $this->applyCampaign($checkout);

        if ($applyCampaign['totalAmount'] > 0) {

            $shippingPrice = ($applyCampaign['totalAmount'] > 50) ? null : 10;

            if ($applyCampaign['orderItems']) {

                $orderNumber = uniqid();

                $orderId = Order::insertGetId([
                    'user_id'       => 1,
                    'order_number'  => $orderNumber,
                    'address'       => $request->checkoutDetails['address'],
                    'shipping_price'=> $shippingPrice,
                    'total_amount'  => $applyCampaign['totalAmount']+$shippingPrice,
                    'created_at'    => Carbon::now(),
                ]);

                foreach ($applyCampaign['orderItems'] as $orderItem) {

                    $product = Product::find($orderItem['product_id']);
                    $product->product_quantity -= $orderItem['quantity'];
                    $quantityDecrement = $product->save();

                    if ($quantityDecrement) {
                        Order_item::insert([
                            'order_id'    => $orderId,
                            'product_id'  => $orderItem['product_id'],
                            'quantity'    => $orderItem['quantity'],
                            'selling_price'  => $orderItem['selling_price'],
                            'discount_price' => $orderItem['discount_price'],
                            'campaign_id' => (!empty($orderItem['campaign_id'])) ? $orderItem['campaign_id'] : null,
                            'created_at'  => Carbon::now()
                        ]);
                    }
                }
                return $this->sendResponse(["orderNumber" => $orderNumber], 'Sipariş Oluşturuldu');
            }

        } else {
            return $this->sendError('Sepetene Eklenen Ürünleri Kontrol Ediniz!');
        }

    }

    /**
     * @param $orderNumber
     * @return JsonResponse
     */
    public function show($orderNumber)
    {
        $orderDetails = Order::where('order_number', $orderNumber)->firstOrFail();

        return response()->json(new OrderDetailResource($orderDetails));
    }

}
