<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\Order_item;
use App\Models\Product;
use Carbon\Carbon;

class OrderController extends Controller
{

    public function createOrder(CreateOrderRequest $request) {
        $checkout = json_decode($this->checkout($request),true);

        if ($checkout['status'] == "error") {

            return $checkout;
        }

        $applyCampaign = $this->applyCampaign($checkout);

        if ($applyCampaign['totalAmount'] > 0) {

            $shippingPrice = ($applyCampaign['totalAmount'] > 50) ? NULL : 10;

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
                            'campaign_id' => (!empty($orderItem['campaign_id'])) ? $orderItem['campaign_id'] : NULL,
                            'created_at'  => Carbon::now()
                        ]);
                    }
                }
                $response = json_encode(['status'=>"ok", 'message' => "Sipariş Oluşturuldu", "orderNumber" => $orderNumber]);
            }

        } else {
            $response = json_encode(['status'=>"error", 'message' => "Sepetene Eklenen Ürünleri Kontrol Ediniz!"]);
        }

        return $response;

    }

    private function checkout($request) {
        $totalAmount = NULL;
        $orderItems  = [];
        foreach ($request->productItems as $orderProductItem) {

            $productQuantity =  json_decode(Product::productQuantityCheck($orderProductItem));

            if ($productQuantity->status == "success") {

                $orderItems[] = [
                    'product_id'     => $productQuantity->productDetails->id,
                    'quantity'       => $productQuantity->orderProductItem->product_quantity,
                    'selling_price'  => $productQuantity->productDetails->selling_price,
                    'discount_price'  => $productQuantity->productDetails->selling_price,
                ];
                $totalAmount += ($productQuantity->orderProductItem->product_quantity * $productQuantity->productDetails->selling_price);

            } else {
                return json_encode(['status'=>"error", 'message' => "Ürün stok sayısı yeterli değil."]);
            }
        }

        return json_encode(['status'=>'success', 'orderItems' => $orderItems, 'totalAmount' => $totalAmount]);
    }

    private function applyCampaign($orderItems) {
        $conditionAuthorAmount = 0;
        $conditionAmount = 0;
        $conditionAuthorNationalityAmount = 0;
        $responseOrderItems = [];
        $campaignAuthorSellingPriceList = [];
        $campaignAuthorSellingCampaignId = NULL;

        $activeCampaign = Campaign::activeCampaigns();

        if (COUNT($activeCampaign) > 0) {
            foreach ($activeCampaign as $campaign) {

                foreach ($orderItems['orderItems'] as $orderItem) {

                    $productDetails = Product::find($orderItem['product_id']);

                    // 1. Kampanya
                    if ($campaign['campaignDetail']->condition_author AND $campaign['campaignDetail']->condition_category_id) {

                        $campaignId = NULL;

                        // Kampanya Koşulları Kontrol
                        if ($campaign['campaignDetail']->condition_author == $productDetails->author_id AND $campaign['campaignDetail']->condition_category_id == $productDetails->category_id) {

                            $campaignId = $campaign->id;
                            $campaignAuthorSellingCampaignId = $campaign->id;
                            $campaignAuthorSellingPriceList[$orderItem['product_id']][] = $productDetails['selling_price'];

                        }

                        $conditionAuthorAmount += $orderItem['quantity'] * $productDetails['selling_price'];

                        $responseOrderItems[$campaign->id][] = [
                            'product_id'     => $orderItem['product_id'],
                            'quantity'       => $orderItem['quantity'],
                            'selling_price'  => $productDetails['selling_price'],
                            'discount_price' => $productDetails['selling_price'],
                            'campaign_id'    => $campaignId
                        ];

                    }

                    // 2. Kampanya
                    if ($campaign['campaignDetail']->condition_author_nationality) {

                        $campaignId    = NULL;
                        $discountPrice = $productDetails['selling_price'];

                        if ($campaign['campaignDetail']->condition_author_nationality == $productDetails->author['author_nationality']) {

                            $campaignId    = $campaign->id;
                            $discountPrice = ($campaign['campaign_discount'] * $productDetails['selling_price']);

                        }

                        $conditionAuthorNationalityAmount += $orderItem['quantity'] * $discountPrice;

                        $responseOrderItems[$campaign->id][] = [
                            'product_id'     => $orderItem['product_id'],
                            'quantity'       => $orderItem['quantity'],
                            'selling_price'  => $productDetails['selling_price'],
                            'discount_price' => $discountPrice,
                            'campaign_id'    => $campaignId
                        ];
                    }

                    //3. Kampanya
                    if ($campaign['campaignDetail']->condition_amount > 0) {

                        $campaignId    = NULL;
                        $discountPrice = $productDetails['selling_price'];

                        if ($orderItems['totalAmount'] >= $campaign['campaignDetail']->condition_amount) {
                            $campaignId    = $campaign->id;
                            $discountPrice = ($campaign['campaign_discount'] * $productDetails['selling_price']);
                        }

                        $conditionAmount += $orderItem['quantity'] * $discountPrice;

                        $responseOrderItems[$campaign->id][] = [
                            'product_id'     => $orderItem['product_id'],
                            'quantity'       => $orderItem['quantity'],
                            'selling_price'  => $productDetails['selling_price'],
                            'discount_price' => $discountPrice,
                            'campaign_id'    => $campaignId
                        ];
                    }
                }
            }

            if (COUNT($campaignAuthorSellingPriceList) >= 2 AND $campaignAuthorSellingCampaignId) {

                $campaignAuthorMaxSellingPrice = max(array_column($campaignAuthorSellingPriceList, 0)); // En büyük değeri bul
                $oneGiftProductId = array_search([$campaignAuthorMaxSellingPrice], $campaignAuthorSellingPriceList);

                foreach ($responseOrderItems[$campaignAuthorSellingCampaignId] as $key => $item) {

                    if ($item['product_id'] == $oneGiftProductId) {
                        if ($responseOrderItems[$campaignAuthorSellingCampaignId][$key]['quantity'] > 1) {
                            $responseOrderItems[$campaignAuthorSellingCampaignId][] = [
                                'product_id'     => $item['product_id'],
                                'quantity'       => $responseOrderItems[$campaignAuthorSellingCampaignId][$key]['quantity'] - 1,
                                'selling_price'  => $productDetails['selling_price'],
                                'discount_price' => $productDetails['selling_price'],
                                'campaign_id'    => $item['campaign_id']

                            ];
                            $responseOrderItems[$campaignAuthorSellingCampaignId][$key]['quantity'] = 1;
                        }
                        $responseOrderItems[$campaignAuthorSellingCampaignId][$key]['discount_price'] = 0;

                    }
                }

                $conditionAuthorAmount -=  $campaignAuthorMaxSellingPrice;
            }

            $amountArray = [1 => $conditionAuthorAmount, 2 => $conditionAmount, 3 => $conditionAuthorNationalityAmount];

            $minAmount      = collect($amountArray)->filter(fn ($value) => $value > 0)->min();
            $minAmountKey   = array_search($minAmount, $amountArray);

            $response = ['orderItems' => $responseOrderItems[$minAmountKey], 'totalAmount' => $minAmount];

        } else {
            $response = ['orderItems' => $orderItems['orderItems'], 'totalAmount' => $orderItems['totalAmount']];
        }

        return $response;

    }


}
