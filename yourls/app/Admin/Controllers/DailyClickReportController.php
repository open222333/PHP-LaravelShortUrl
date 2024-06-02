<?php

namespace App\Admin\Controllers;

use App\Admin\Models\DailyClickReport;
use App\Admin\Models\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class DailyClickReportController extends AdminController
{
	/**
	 * Title for current resource.
	 *
	 * @var string
	 */
	protected $title = '每日點擊報表';

	/**
	 * Make a grid builder.
	 *
	 * @return Grid
	 */
	protected function grid()
	{
		$grid = new Grid(new DailyClickReport());

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

		$product_id = Request::get('product_id', NULL);

		$grid->column('name', '縮網址名稱')
			->sortable()
			->expand(function ($model) use ($sdate, $edate, $product_id) {
				$columns = ['日期', '產品', '點擊數'];
				$datas = DailyClickReport::where([
					['shorturl_id', '=', $this->id],
					['date', '>=', $sdate],
					['date', '<=', $edate]
				])
					->join('product', function ($join) {
						$join->on('product.id', '=', 'daily_click_report.product_id');
					})
					->select('date', 'product.name', 'total_click')
					->orderBy('date');

				if (!is_null($product_id)) {
					$datas = $datas->where('product.id', '=', $product_id);
				}

				$datas = $datas->get();

				return new Table($columns, $datas->toArray());
			});
		$grid->column('sum_total_click', '總點擊數')->sortable();

		$grid->disableCreateButton();
		$grid->disableActions();
		$grid->disableColumnSelector();
		$grid->disableRowSelector();

		$grid->tools(function ($tools) {
			$tools->batch(function ($batch) {
				$batch->disableDelete();
			});
		});

		$grid->expandFilter();

		$grid->filter(function ($filter) {
			$filter->disableIdFilter();
			$filter->between('date', '日期')->date();

			$products = Product::all();
			$product_names = array();
			foreach ($products as $product) {
				$product_names[$product->id] = $product->name;
			}
			$filter->equal('product_id', '產品名稱')->select($product_names);
		});

		return $grid;
	}

	/**
	 * Make a show builder.
	 *
	 * @param mixed $id
	 * @return Show
	 */
	protected function detail($id)
	{
		$show = new Show(DailyClickReport::findOrFail($id));

		$show->field('shorturl_id', __('縮網址ID'));
		$show->field('product_id', __('產品ID'));
		$show->field('total_click', __('總點擊數'));
		$show->field('date', __('日期'));

		$show->panel()
			->tools(function ($tools) {
				$tools->disableEdit();
				$tools->disableDelete();
			});;

		return $show;
	}

	/**
	 * Make a form builder.
	 *
	 * @return Form
	 */
	protected function form()
	{
		$form = new Form(new DailyClickReport());

		$form->number('shorturl_id', __('Shorturl id'));
		$form->number('product_id', __('Product id'));
		$form->number('total_click', __('Total click'));
		$form->date('date', __('Date'))->default(date('Y-m-d'));

		$form->tools(function ($tools) {
			$tools->disableDelete();
			$tools->disableView();
		});

		return $form;
	}
}
