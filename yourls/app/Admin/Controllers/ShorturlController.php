<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Shorturl;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ShorturlController extends AdminController
{
	/**
	 * Title for current resource.
	 *
	 * @var string
	 */
	protected $title = '縮網址';

	/**
	 * Make a grid builder.
	 *
	 * @return Grid
	 */
	protected function grid()
	{
		$grid = new Grid(new Shorturl());

		$grid->column('id', __('縮網址ID'))->sortable();
		$grid->column('name', __('縮網址名稱'))->sortable();
		$grid->column('status', __('狀態'))->using(
			['0' => '停用', '1' => '啟用']
		)->sortable();
		// $grid->column('created_at', __('創建時間'))->sortable();
		// $grid->column('updated_at', __('最後編輯時間'))->sortable();

		$grid->actions(function ($actions) {
			$actions->disableDelete();
			$actions->disableView();
		});

		$grid->disableColumnSelector();
		$grid->disableRowSelector();

		$grid->expandFilter();

		$grid->filter(function ($filter) {
			$filter->disableIdFilter();
			$filter->equal('id', '縮網址ID');
			$filter->like('name', '縮網址名稱');
			$filter->equal('status', '狀態')->radio([
				0 => '停用',
				1 => '啟用'
			]);
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
		$show = new Show(Shorturl::findOrFail($id));

		$show->field('id', __('縮網址Id'));
		$show->field('name', __('縮網址名稱'));
		$show->field('status', __('狀態'))->using(
			['0' => '停用', '1' => '啟用']
		);
		$show->field('created_at', __('創建時間'));
		$show->field('updated_at', __('最後編輯時間'));

		$show->panel()
			->tools(function ($tools) {
				$tools->disableEdit();
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
		$form = new Form(new Shorturl());

		$form->text('name', __('縮網址名稱'))
			->creationRules(['required', "unique:shorturl"])
			->updateRules(['required', "unique:shorturl,name,{{id}}"]);
		$form->select('status', '狀態')
			->options([0 => '停用', 1 => '啟用'])
			->rules('required')
			->default(1);

		$form->disableEditingCheck(); // 繼續編輯
		$form->disableCreatingCheck(); // 繼續創建
		$form->disableViewCheck(); // 查看

		$form->tools(function ($tools) {
			$tools->disableDelete();
			$tools->disableView();
		});

		return $form;
	}
}
