<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
	'prefix'        => config('admin.route.prefix'),
	'namespace'     => config('admin.route.namespace'),
	'middleware'    => config('admin.route.middleware'),
	'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

	$router->get('/', 'HomeController@index')->name('home');

	$router->post('batch_change_domain', 'ShorturlSettingController@batchChangeDomain');

	$router->resource('products', ProductController::class);
	$router->resource('shorturls', ShorturlController::class);
	$router->resource('shorturl-setting', ShorturlSettingController::class);
	$router->resource('daily-click-report', DailyClickReportController::class);
});


