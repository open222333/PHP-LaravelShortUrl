<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShorturlTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shorturl', function (Blueprint $table) {
			// 縮網址
            $table->increments('id');
			$table->string('name', 50)->unique();
			$table->integer('status')->default(1)->comment('0:停用,1:啟用');
            $table->timestamps();
			$table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shorturl');
    }
}
