<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyClickReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_click_report', function (Blueprint $table) {
			// 每日點擊報表
			$table->unsignedInteger('shorturl_id');
			$table->unsignedInteger('product_id');
			$table->foreign('shorturl_id')->references('id')->on('shorturl');
			$table->foreign('product_id')->references('id')->on('product');
			$table->index('shorturl_id');
			$table->index('product_id');
			$table->integer('total_click');
			$table->date('date');
			$table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_click_report');
    }
}
