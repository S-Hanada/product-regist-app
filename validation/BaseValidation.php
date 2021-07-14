<?php
//共通部分を継承用のクラスでまとめる
 class BaseValidation {
 	//$errorsプロパティを共通プロパティとして定義
	public $errors = [];

	//checkメソッドの共通部分を定義
	public function check($input) {
		if(empty($input)) {
			$this->errors['NoInput'] = '入力が空です'.PHP_EOL;
			return false;
		}
		return true;
	}

    //エラーメッセージを返すゲッターメソッドを定義
	public function getErrorMessages() {
		return $this->errors;
	}
}
?>