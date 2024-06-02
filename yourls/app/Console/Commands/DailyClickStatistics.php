<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Admin\Models\ClickRecord;
use App\Admin\Models\DailyClickReport;
use App\Admin\Models\Product;
use App\Admin\Models\Shorturl;
use App\Admin\Models\ShorturlSetting;
use Carbon\Carbon;
use Exception;


class DailyClickStatistics extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'stats:daily-click {--D|date= : 指定日期 2022-02-01 00:00:00 預設當天日期} {--S|shorturl= : 指定shorturl} {--p|product= : 指定產品名稱} {--d|days=0 : 指定的日期往後天數 最大30} {--Y|yesterday : 指定日期前一天點擊統計} {--R|dailyclickreport : 紀錄每日點擊報表} {--s|show : 顯示結果} {--r|removeExpiryData : 刪除過期資料}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = '執行每日點擊統計';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// 產品名稱
		$product = $this->option('product');
		if ($product == '') {
			$product_id = null;
		} else {
			$data = Product::where('name', '=', $product)->first();
			if (is_null($data)) {
				$this->error("產品 $product 不存在");
			}
			$product_id = $data->id;
		}

		// 縮網址名稱
		$shorturl = $this->option('shorturl');
		if ($shorturl == '') {
			$shorturl_ids = array();
			$shorturl_datas = Shorturl::all();
			foreach ($shorturl_datas as $shorturl_data) {
				$setting_amount = ShorturlSetting::where('shorturl_id', '=', $shorturl_data->id)->count();
				if ($setting_amount > 0) {
					$shorturl_ids[] = $shorturl_data->id;
				}
			}
		} else {
			$data = Shorturl::where('name', '=', $shorturl)->first();
			if (is_null($data)) {
				$this->error("產品 $shorturl 不存在");
			}
			$shorturl_ids = [$data->id];
		}

		// 天數
		$days = $this->option('days');
		try {
			$days = (int)$days;
		} catch (Exception $e) {
			$this->error("$days 轉整數錯誤: $e");
		}
		if ($days > 30) {
			$this->error('days引數 超過30');
		}

		// 計算報表
		$result = array();
		foreach ($shorturl_ids as $shorturl_id) {
			// 開始日期
			$date = $this->option('date');
			$tz = 'Asia/Taipei';
			try {
				if ($date == '') {
					$date = Carbon::today()->startOfDay();
				} else {
					$date = Carbon::parse($date, $tz)->startOfDay();
				}

				$y = $this->option('yesterday');
				if ($y == '1') {
					$date->subDay();
				}
			} catch (Exception $e) {
				$this->error("日期錯誤 : $date\n$e");
			}

			for ($i = $days; $i > -1; $i--) {
				$result[] = $this->create_click_report($date, $shorturl_id, $product_id);
				$date->addDay();
			}
		}

		// 紀錄每日點擊報表
		if ($this->option('dailyclickreport') == '1') {
			$this->key_in_daily_click_report($result);
		}

		// 輸出結果
		if ($this->option('show') == '1') {
			foreach ($result as $value) {
				$d = $value['date'];
				$s = $value['shorturl'];
				$p = '';
				foreach ($value['products'] as $product) {
					$name = $product['product_name'];
					$click = $product['click'];
					$p .= "$name($click) ";
				}
				$c = $value['total_click'];
				$this->info("date: $d | shorturl: $s | product(click): $p| total_click: $c");
			}
		}

		if ($this->option('removeExpiryData') == '1') {
			$this->remove_expiry_data();
		}
	}

	protected function create_click_report($date, $shorturl_id, $product_id = null)
	{
		// 創建單日點擊紀錄
		$report = array(
			'products' => array()
		);

		$tz = 'Asia/Taipei';
		$start_date = Carbon::parse($date, $tz)->startOfDay();
		$end_date = Carbon::parse($date, $tz)->endOfDay();

		$report['date'] = $start_date->toDateString();

		if (!is_null($product_id)) {
			$product_name = Product::find($product_id)->name;
			$report['products'][$product_id] = array(
				'product_name' => $product_name,
				'click' => 0
			);
		} else {
			$datas = ShorturlSetting::where('shorturl_id', '=', $shorturl_id)->get();
			foreach ($datas as $data) {
				$product_name = Product::find($data->product_id)->name;
				if (!array_key_exists($data->product_id, $report['products'])) {
					$report['products'][$data->product_id] = array(
						'product_name' => $product_name,
						'click' => 0
					);
				}
			}
		}

		if (!is_null($shorturl_id)) {
			$shorturl_data = Shorturl::where('id', '=', $shorturl_id)->first();
			$report['shorturl'] = $shorturl_data->name;
		}

		$total_click = 0;
		foreach (array_keys($report['products']) as $product_id) {
			$rules = array();
			$rules[] = array('shorturl_id', '=', $shorturl_id);
			$rules[] = array('product_id', '=', $product_id);
			$rules[] = array('created_at', '>', $start_date);
			$rules[] = array('created_at', '<', $end_date);
			$click = ClickRecord::where($rules)->count();
			$report['products'][$product_id]['click'] = $click;
			$total_click += $click;
		}

		$report['total_click'] = $total_click;

		return $report;
	}

	protected function key_in_daily_click_report($result)
	{
		// 新增或更新 daily_click_report
		foreach ($result as $value) {
			$date = $value['date'];
			$shorturl = $value['shorturl'];
			$shorturl_id = Shorturl::where('name', '=', $shorturl)->first();
			$shorturl_id = $shorturl_id->id;
			foreach ($value['products'] as $product) {
				$product_name = $product['product_name'];
				$product_id = Product::where('name', '=', $product_name)->first();
				$product_id = $product_id->id;
				$click = $product['click'];

				$data = DailyClickReport::where([
					['date', '=', $date],
					['shorturl_id', '=', $shorturl_id],
					['product_id', '=', $product_id]
				])->first();
				if (is_null($data)) {
					DailyClickReport::insert([
						'date' => $date,
						'shorturl_id' => $shorturl_id,
						'product_id' => $product_id,
						'total_click' => $click
					]);
				} else {
					$dailydata = DailyClickReport::where([
						['date', '=', $date],
						['shorturl_id', '=', $shorturl_id],
						['product_id', '=', $product_id]
					])->first();
					$dailydata->total_click = $click;
					$dailydata->save();
				}
			}
		}
	}

	protected function remove_expiry_data()
	{
		// 刪除一年以上的資料
		$oneYearAgo = date('Y-m-d', strtotime('-1 year'));
		DailyClickReport::where('date', '<', $oneYearAgo)->delete();

		// 半年
		$halfYearAgo = date('Y-m-d', strtotime('-6 months'));
		ClickRecord::where('updated_at', '<', $halfYearAgo)->delete();
	}
}
