<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Campaign;
use App\Models\CampaignDetail;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use File;
use Illuminate\Support\Facades\Hash;


class ProductJsonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::insert([
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make(fake()->password(6,10)) // password
        ]);

        $fakeCampaignArray = [
            [
                'campaign_name'     => "Sabahattin Ali'nin Roman kitaplarında 2 üründen 1 tanesi bedava.",
                'campaign_discount' => '100',
                'condition_author'  => 3
            ], [
                'campaign_name'     => "Yerli Yazar Kitaplarında %5 indirim",
                'campaign_discount' => '0.95',
                'condition_author_nationality'  => 'tr'
            ], [
                'campaign_name'     => "200 TL ve üzeri alışverişlerde sipariş toplamına %5 indirim",
                'campaign_discount' => '0.95',
                'condition_amount'  => 200
            ]

        ];
        foreach ($fakeCampaignArray as $fakeCampaign) {


            $campaignId = Campaign::insertGetId([
                'campaign_name'     => $fakeCampaign['campaign_name'],
                'campaign_discount' => $fakeCampaign['campaign_discount'],
                'created_at'        => Carbon::now()
            ]);
            if ($campaignId) {

                $fakeValue = end($fakeCampaign);
                $conditionIndex = key($fakeCampaign);
                CampaignDetail::insert([
                    'campaign_id'   => $campaignId,
                    $conditionIndex => $fakeValue,
                    'created_at'    => Carbon::now()

                ]);
            }

        }

        $productJsonFile = File::get('database/data/products.json');
        $products = json_decode($productJsonFile);
        foreach ($products as $item) {

            $author = Author::firstOrCreate([
                'author_name'        => $item->author
            ], [
                    'author_name'        => $item->author,
                    'created_at'         => Carbon::now()
                ]
            );

            $authorId = $author->id;

            $category = Category::firstOrCreate([
                'category_name' => $item->category_title
            ], [
                'category_name' => $item->category_title,
                'created_at'    => Carbon::now()
            ]);

            $categoryId = $category->id;

            Product::firstOrCreate([
                'product_name'      => $item->title,
            ], [
                    'product_name'      => $item->title,
                    'product_quantity'  => $item->stock_quantity,
                    'category_id'       => $categoryId,
                    'author_id'         => $authorId,
                    'selling_price'     => $item->list_price,
                    'created_at'        => Carbon::now()
                ]
            );
        }
    }
}
