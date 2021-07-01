<?php
//修正箇所：継承元のコードを呼び出し
require_once('./validation/BaseValidation.php');

//ID照合機能
class ProductValidation extends BaseValidation {
	public function check($input) {
		if(!parent::check($input)) {
			return false;
		}
		if(!PRODUCT::isExisByProduct($input)) {
			$this->errors['NotExisByProduct'] = '登録されていない商品idです'.PHP_EOL.PHP_EOL;
			return false;
		}
		return true;
	}
}
?>