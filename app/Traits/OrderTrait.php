<?php

namespace App\Traits;

use App\Models\Campaign;
use App\Models\Product;

trait OrderTrait
{
    /**
     * @param $request
     * @return false|string
     */
    public function checkout($request)
    {
        $totalAmount = null;
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

    /**
     * @param $orderItems
     * @return array
     */
    public function applyCampaign($orderItems)
    {
        $conditionAuthorAmount = 0;
        $conditionAmount = 0;
        $conditionAuthorNationalityAmount = 0;
        $responseOrderItems = [];
        $campaignAuthorSellingPriceList = [];
        $campaignAuthorSellingCampaignId = null;

        $activeCampaign = Campaign::all();

        if (COUNT($activeCampaign) > 0) {
            foreach ($activeCampaign as $campaign) {

                foreach ($orderItems['orderItems'] as $orderItem) {

                    $productDetails = Product::find($orderItem['product_id']);

                    // 1. Kampanya
                    if ($campaign['campaignDetail']->condition_author && $campaign['campaignDetail']->condition_category_id) {

                        $campaignId = null;

                        // Kampanya Koşulları Kontrol
                        if ($campaign['campaignDetail']->condition_author == $productDetails->author_id && $campaign['campaignDetail']->condition_category_id == $productDetails->category_id) {

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

                        $campaignId    = null;
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

                        $campaignId    = null;
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

            if (COUNT($campaignAuthorSellingPriceList) >= 2 && $campaignAuthorSellingCampaignId) {

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
