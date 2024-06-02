<?php

namespace App\Admin\Controllers;

use App\Admin\Models\ShorturlSetting;
use App\Admin\Models\Product;
use App\Admin\Models\Shorturl;
use App\Admin\Extensions\Tools\ChangeDomain;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ShorturlSettingController extends AdminController
{
	/**
	 * Title for current resource.
	 *
	 * @var string
	 */
	protected $title = '縮網址設定';

	/**
	 * Make a grid builder.
	 *
	 * @return Grid
	 */
	protected function grid()
	{
		$grid = new Grid(new ShorturlSetting());
		$grid->model()->orderBy('shorturl_id');
		$grid->column('id')->hide();
		$grid->column('shorturl_id', __('縮網址ID'))->sortable();
		$grid->column('shorturl_name', __('縮網址名稱'))
			->display(function () {
				return Shorturl::find($this->shorturl_id)->name;
			});
		$grid->column('product_id', __('產品ID'))->sortable();
		$grid->column('product_name', __('產品名稱'))
			->display(function () {
				return Product::find($this->product_id)->name;
			});
		$grid->column('weight', __('權重'))->sortable();
		$grid->column('android_url', __('Android URL'))->sortable();
		$grid->column('ios_url', __('iOS URL'))->sortable();
		$grid->column('other_url', __('Other URL'))->sortable();
		// $grid->column('created_at', __('創建時間'))->sortable();
		// $grid->column('updated_at', __('最後編輯時間'))->sortable();

		$grid->expandFilter();

		$grid->filter(function ($filter) {
			$filter->disableIdFilter();

			$shorturls = Shorturl::all();
			$shorturl_names = array();
			foreach ($shorturls as $shorturl) {
				$shorturl_names[$shorturl->id] = $shorturl->name;
			}
			$filter->equal('shorturl_id', '縮網址名稱')->select($shorturl_names);

			$products = Product::all();
			$product_names = array();
			foreach ($products as $product) {
				$product_names[$product->id] = $product->name;
			}
			$filter->equal('product_id', '產品名稱')->select($product_names);
		});

		$grid->disableColumnSelector();
		$grid->disableRowSelector();

		$grid->actions(function ($actions) {
			$actions->disableDelete();
			$actions->disableView();
		});

		$grid->tools(function ($tools) {
			$tools->append(new ChangeDomain());
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
		$show = new Show(ShorturlSetting::findOrFail($id));

		$show->field('shorturl_id', __('縮網址ID'));
		$show->field('product_id', __('產品ID'));
		$show->field('shorturl_name', __('縮網址名稱'));
		$show->field('product_name', __('產品名稱'));
		$show->field('weight', __('權重'));
		$show->field('android_url', __('Android URL'));
		$show->field('ios_url', __('iOS URL'));
		$show->field('other_url', __('Other URL'));
		$show->field('created_at', __('創建時間'));
		$show->field('updated_at', __('最後編輯時間'));

		$show->panel()
			->tools(function ($tools) {
				$tools->disableDelete();
			});

		return $show;
	}

	/**
	 * Make a form builder.
	 *
	 * @return Form
	 */
	protected function form()
	{
		$shorturls = Shorturl::all();
		$shorturl_options = array();
		foreach ($shorturls as $shorturl) {
			$shorturl_options[$shorturl->id] = $shorturl->name;
		}

		$products = Product::all();
		$product_options = array();
		foreach ($products as $product) {
			$product_options[$product->id] = $product->name;
		}

		$form = new Form(new ShorturlSetting());

		$form->select('shorturl_id', __('縮網址名稱'))
			->options($shorturl_options)
			->rules('required');

		$form->select('product_id', __('產品名稱'))
			->options($product_options)
			->rules('required');

		$form->number('weight', __('權重'))->default(0)->min(0);
		$form->text('android_url', __('Android URL'))->default(null);
		$form->text('ios_url', __('iOS URL'))->default(null);
		$form->text('other_url', __('Other URL'))->default(null);

		$form->disableCreatingCheck();
		$form->disableEditingCheck(); // 繼續編輯
		$form->disableViewCheck(); // 查看

		$form->tools(function ($tools) {
			// $tools->disableList();
			$tools->disableDelete();
			$tools->disableView();
		});

		return $form;
	}

	public function batchChangeDomain(Request $request)
	{
		// 批量更換域名  Android URL ,iOS URL ,Other URL
		$message = 'ok';
		$status_code = 200;

		DB::beginTransaction();
		try {
			$new_domain = Request::get('new_domain', NULL);
			if (is_null($new_domain)) {
				throw new Exception('missing new_domain argument.');
			}
			$old_domain = Request::get('old_domain', NULL);
			if (is_null($old_domain)) {
				throw new Exception('missing old_domain argument.');
			}

			$datas = ShorturlSetting::all();
			foreach ($datas as $data) {
				$pat = '/[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+\.?/';
				try {
					if (!is_null($data->android_url)) {
						preg_match($pat, $data->android_url, $matches_android_url);
						if ($matches_android_url[0] == $old_domain) {
							$new_android_url = preg_replace($pat, $new_domain, $data->android_url, 1);
							$data->android_url = $new_android_url;
							$data->save();
						}
					}
					if (!is_null($data->ios_url)) {
						preg_match($pat, $data->ios_url, $matches_ios_url);
						if ($matches_ios_url[0] == $old_domain) {
							$new_ios_url = preg_replace($pat, $new_domain, $data->ios_url, 1);
							$data->ios_url = $new_ios_url;
							$data->save();
						}
					}
					if (!is_null($data->other_url)) {
						preg_match($pat, $data->other_url, $matches_other_url);
						if ($matches_other_url[0] == $old_domain) {
							$new_other_url = preg_replace($pat, $new_domain, $data->other_url, 1);
							$data->other_url = $new_other_url;
							$data->save();
						}
					}
				} catch (Exception $e) {
					$message = 'replace error';
					$status_code = 500;
				}
			}
		} catch (Exception $e) {
			$message = 'unknow error';
			$status_code = 500;
		}
		DB::commit();

		return response()->json([
			'message' => $message,
		], $status_code);
	}
}
