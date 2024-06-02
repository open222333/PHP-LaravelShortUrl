<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClickRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('click_record', function (Blueprint $table) {
			// 點擊紀錄
			$table->unsignedInteger('shorturl_id');
			$table->unsignedInteger('product_id');
			$table->foreign('shorturl_id')->references('id')->on('shorturl');
			$table->foreign('product_id')->references('id')->on('product');
			$table->index('shorturl_id');
			$table->index('product_id');
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
        Schema::dropIfExists('click_record');
    }
}
