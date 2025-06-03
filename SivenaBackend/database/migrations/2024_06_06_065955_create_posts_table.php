<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            // Primary Key
            $table->bigIncrements('id');
            // Fields
            $table->string('title');
            $table->text('content');
            $table->string('post_image')->nullable(); // post_image is optional
            $table->integer('shares')->default(0); // Default value for shares
            $table->integer('views')->default(0);
            // Foreign Keys
            $table->unsignedBigInteger('category_id'); // Reference to categories
            $table->unsignedInteger('author_id'); // Reference to users (author)
            // Timestamps
            $table->timestamps();
            // Indexes for performance
            $table->index('category_id');
            $table->index('author_id');
            // Foreign Key Constraints
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
