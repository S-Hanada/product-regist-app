<?php
//継承元のコードを呼び出し
require_once('./validation/BaseValidation.php');

//メニュー入力のバリデーション
//traitから継承に変更しました。
class MenuValidation extends BaseValidation {
	public function check($input) {
		if(!parent::check($input)) {
			return false;
		}
		if(!is_numeric($input)) {
			$this->errors['NonNumeric'] = '数字以外が入力されています'.PHP_EOL;
			return false;
		}
		if($input > 6) {
			$this->errors['OtherMenu'] = '6つのメニュー以外は選べません'.PHP_EOL;
			return false;
		}
		return true;
	}
}
?>