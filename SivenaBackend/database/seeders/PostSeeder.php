<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('posts')->insert([
            'title' => 'Title 1',
            'content' => 'Content 1',
            'post_image' => 'image1.jpg',
            'author' => 'Author 1',
            'likes' => 10,
            'comments' => 5,
            'shares' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
