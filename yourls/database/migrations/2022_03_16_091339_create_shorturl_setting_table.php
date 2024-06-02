<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShorturlSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shorturl_setting', function (Blueprint $table) {
			// 縮網址設定
			$table->increments('id');
			$table->unsignedInteger('shorturl_id');
			$table->unsignedInteger('product_id');
			$table->integer('weight'); // 權重
			$table->text('android_url')->nullable();
			$table->text('ios_url')->nullable();
			$table->text('other_url')->nullable();
            $table->timestamps();
			$table->foreign('shorturl_id')->references('id')->on('shorturl');
			$table->foreign('product_id')->references('id')->on('product');
			$table->index('shorturl_id');
			$table->index('product_id');
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
        Schema::dropIfExists('shorturl_setting');
    }
}
