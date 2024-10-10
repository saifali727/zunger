<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('description')->nullable();
            $table->string('location')->nullable();
            $table->enum('watch_status', ['Everyone', 'Friends', 'Onlyme']);
            $table->boolean('comment_status')->default(1);
            $table->boolean('duet_status')->default(1);
            $table->boolean('stitch_status')->default(1);
            $table->boolean('quality_uploads')->default(1);
            $table->string('url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}