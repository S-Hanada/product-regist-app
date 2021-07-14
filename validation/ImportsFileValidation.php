<?php
//継承元のコードを呼び出し
require_once('./validation/BaseValidation.php');

//ID照合機能
class ImportsFileValidation extends BaseValidation {
	public function check($input) {
		if(!parent::check($input)) {
			return false;
		}
		if($input > REGIST::getImportsFilekey()) {
			$this->errors['NotExisImportsFileKey'] = '存在しないファイルの番号です'.PHP_EOL.PHP_EOL;
			return false;
		}
		return true;
	}
}
?>