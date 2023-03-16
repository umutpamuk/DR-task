<?php

namespace Database\Seeders;

use App\Models\Author;
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
        $productJsonFile = File::get('database/data/products.json');
        $products = json_decode($productJsonFile);

        User::insert([
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make(fake()->password(6,10)) // password
        ]);

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
