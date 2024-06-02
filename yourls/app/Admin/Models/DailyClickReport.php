<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use App\Admin\Models\Shorturl;
use Illuminate\Pagination\LengthAwarePaginator;

class DailyClickReport extends Model
{
	//
	protected $table = 'daily_click_report';
	// save()不使用updated_at, created_at
	public $timestamps = false;

	public function paginate()
	{

		ini_set('memory_limit', -1);
		set_time_limit(0);

		$per_page = Request::get('per_page', 20);
		$page = Request::get('page', 1);
		$start = ($page - 1) * $per_page;

		$sdate = Carbon::today('Asia/Taipei')->subDays(0)->startOfDay();
		$edate = Carbon::today('Asia/Taipei')->subDays(0)->endOfDay();

		$date = Request::get('date', NULL);
		if (!is_null($date)) {
			if (isset($date['start'])) {
				$sdate = Carbon::createFromFormat('Y-m-d', $date['start'])->startOfDay();
			}
			if (isset($date['end'])) {
				$edate = Carbon::createFromFormat('Y-m-d', $date['end'])->endOfDay();
			}
		}

		// 預設排序
        $orderby = "shorturl.name";
        $sort_type = "asc";

        $input_sort = Request::get('_sort', NULL);
        if (!is_null($input_sort)) {
            switch ($input_sort['column']) {
                case 'sum_total_click':
                    $orderby = "sum_total_click";
                break;
            }

            $sort_type = $input_sort['type'];
        }

		$result = Shorturl::join('daily_click_report', function ($join) {
			$join->on('shorturl.id', '=', 'daily_click_report.shorturl_id');
		})
			->selectRaw("shorturl.id, shorturl.name, SUM(daily_click_report.total_click) AS sum_total_click")
			->where("daily_click_report.date", ">=", $sdate)
			->where("daily_click_report.date", "<=", $edate)
			->groupBy('daily_click_report.shorturl_id')
			->orderBy($orderby, $sort_type);

		$product_id = Request::get('product_id', NULL);
		if (!is_null($product_id)) {
			$result = $result->where('daily_click_report.product_id', '=', $product_id);
		}

		$result = $result->get();

		// 總筆數
		$total = $result->count();

		// 當前頁面資料
		$results = $result->skip($start)->take($per_page)->toArray();

		$converted_results = static::hydrate($results);

		$paginator = new LengthAwarePaginator($converted_results, $total, $per_page);

		$paginator->setPath(url()->current());

		return $paginator;
	}

	public function orderBy($column, $direction = 'asc')
	{
		return $this;
	}

}
