<?php
//継承元のコードを呼び出し
require_once('./validation/BaseValidation.php');

//取引継続入力のバリデーション
//traitから継承に変更しました。
class InputContinue extends BaseValidation {
	public function check($input) {
		if(!parent::check($input)) {
			return false;
		}
		if($input !== 'y' && $input !== 'n') {
			$this->errors['OtherYorN'] = 'yかnで答えてください'.PHP_EOL;
			return false;
		}
		return true;
	}
}
?>