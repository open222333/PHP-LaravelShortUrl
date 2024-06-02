<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as HttpRequest;

use App\Admin\Models\ShorturlSetting;
use App\Admin\Models\Shorturl;
use App\Admin\Models\ClickRecord;

use Exception;
use Carbon\Carbon;

class RedirectToProductUrl extends Controller
{
	public function redirectToProductUrl(HttpRequest $request)
	{
		function get_rand(array $item_array)
		{
			$total = array_sum($item_array);
			// 機率陣列迴圈
			foreach ($item_array as $key => $value) {
				$num = mt_rand(1, $total);
				if ($num <= $value) {
					// $result = $key;
					break;
				} else {
					$total -= $value;
				}
			}
			return $key;
		}

		function get_device_type($user_agent)
		{
			// 判斷 ios android other
			if (strpos($user_agent, 'iPhone') || strpos($user_agent, 'iPad')) {
				return 'ios';
			} else if (strpos($user_agent, 'Android')) {
				return 'android';
			} else {
				return 'other';
			}
		}

		// 根據縮網址 跳轉產品網址
		$status_code = 200;
		$status = 'success';

		$shorturl = $request->shorturl;
		$shorturl_data = Shorturl::where('name', $shorturl)->first();

		if (is_null($shorturl_data)) {
			$status_code = 404;
			return response()->json([
				'message' => 'Not Page',
			], $status_code);
		}

		$shorturl_id = $shorturl_data->id;

		try {
			$datas = ShorturlSetting::where([
				['shorturl_id', '=', $shorturl_id],
				['shorturl.status', '=', 1],
				['product.status', '=', 1]
			])
				->join('shorturl', 'shorturl_setting.shorturl_id', '=', 'shorturl.id')
				->join('product', 'shorturl_setting.product_id', '=', 'product.id')
				->select('shorturl_setting.*', 'shorturl.status', 'product.status')
				->get();

			if (count($datas) == 0) {
				return response()->json([
					'message' => "$shorturl No ShorturlSetting",
					'status' => $status,
				], $status_code);
			}

			$weight_array = array();
			foreach ($datas as $data) {
				// array(shorturl_setting_id, weight)
				$weight_array[$data->id] = $data->weight;
			}
			$shorturl_setting_id = get_rand($weight_array);
		} catch (Exception $e) {
			return response()->json([
				'message' => 'Error',
				'status' => $status,
			], $status_code);
		}

		$setting = ShorturlSetting::where('id', $shorturl_setting_id)->first();

		// 紀錄點擊
		ClickRecord::insert([
			'shorturl_id' => $setting->shorturl_id,
			'product_id' => $setting->product_id,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		]);

		$ua = $request->server('HTTP_USER_AGENT');
		$device = get_device_type($ua);
		if ($device == 'ios') {
			$url = $setting->ios_url;
		} elseif ($device == 'android') {
			$url = $setting->android_url;
		} else {
			$url = $setting->other_url;
		}

		return redirect($url, $status = 301);
	}
}
