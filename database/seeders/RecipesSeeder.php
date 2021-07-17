<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Recipe;

class RecipesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //truncate
        // Recipe::truncate();

        //faker
        // $faker = Faker\Factory::create('ja_JP');

        //insert
        for ($i = 0; $i < 10; $i++) {
            $data['name'] = 'test';
            $data['category'] = '紅茶';
            $data['img_path'] = 'test';
            $data['user_id'] = 1;

            $recipe = Recipe::create($data);
            $recipe->save();
        }
    }
}
