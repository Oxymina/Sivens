<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id(); // Use id() for bigIncrements primary key by default
            $table->unsignedBigInteger('post_id');
            $table->unsignedInteger('user_id'); // Changed from poster_id, matches User model
            $table->text('content');      // Changed from comment_message
            $table->timestamps(); // Good practice to have timestamps
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
