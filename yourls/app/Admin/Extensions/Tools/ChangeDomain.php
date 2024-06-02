<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class ChangeDomain extends AbstractTool
{
	protected function script()
	{
		return <<<EOT
	$('#batch_change_domain').submit(function() {
		let old_domain = $("input[name=old_domain]").val();
		let new_domain = $("input[name=new_domain]").val();
		$.ajax({
			method: 'post',
			url: 'batch_change_domain',
			data: {
				_token: LA.token,
				old_domain: old_domain,
				new_domain: new_domain
			},
			success: function (response) {
				if (response['message'] == 'ok') {
					toastr.success('一鍵更換域名操作成功');
				}
				else {
					toastr.error(response['message']);
				}
			},
			error: function () {
				toastr.error('一鍵更換域名操作失敗');
			},
		});
	});
EOT;
	}

	public function render()
	{
		Admin::script($this->script());

		return view('admin.tools.changedomain');
	}
}
