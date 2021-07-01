<?php
//メニュー選択バリデーションクラス
require_once('./validation/MenuValidation.php');
//商品名登録に関するバリデーションクラス
require_once('./validation/ProductValidation.php');
//継続入力のバリデーションクラス
require_once('./validation/InputContinueValidation.php');
//インポートファイル番号のバリデーションクラス
require_once('./validation/ImportsFileValidation.php');

//商品リストクラスを定義
class PRODUCT {
	//商品リストを静的に定義
	public static $product_list = array();

	//商品一覧を取得するゲッターメソッド 
	public static function getProductList() {
		return self::$product_list;
	}

	//商品を登録
	public static function setProductList($input) {
		self::$product_list[] = $input;
	}

	//商品リストを初期化
	public static function resetProductList() {
		self::$product_list = [];
	}

	//商品idがあるかチェック。
    public static function isExisByProduct($id) {
    	return in_array($id, array_column(self::$product_list, 'id'));
    }

	//商品を削除
    public static function deleteProduct($id) {
    	unset(self::$product_list[$id]);
    }
}

//商品登録システム本体
class REGIST {
	//idを設定
	public $id = 0;
	public static $file_key;
	const CHART = '1';
	const REGIST = '2';
	const DELETE = '3';
	const OUTPUT = '4';
	const IMPORTS = '5';
	const FINISH = '6';

	//商品マスター登録システム本体
	public function registSystem() {
		//メインメニュー呼び出し
		$input = $this->mainMenu();
		//各入り口への分岐
		$this->menuEntrance($input);
	}

	//メインメニュー
	public function mainMenu() {
		echo 'メニューを選択してください'.PHP_EOL.PHP_EOL;
		echo '1, 商品一覧表示'.PHP_EOL;
		echo '2, 商品登録'.PHP_EOL;
		echo '3, 商品削除'.PHP_EOL;
		echo '4, 商品CSV出力'.PHP_EOL;
		echo '5, インポート'.PHP_EOL;
		echo '6, 終了'.PHP_EOL;
		$input = trim(fgets(STDIN));
		//メニュー入力のバリデーション
		$menu_validation = new MenuValidation();
		if(!$menu_validation->check($input)) {
			//エラ〜メッセージを表示
			$errors = $menu_validation->getErrorMessages();
			foreach($errors as $error) {
				echo $error;
			}
			return $this->mainMenu();
		}
		return $input;
	}

	//各メニューへ移動
	public function menuEntrance($input) {
		//商品一覧表示へ
		if($input === self::CHART) {
			$this->productChart();
		} elseif($input === self::REGIST) {
			$this->productRegist();
		} elseif($input === self::DELETE) {
		
			$this->productDelete();
		} elseif($input === self::OUTPUT) {
			$this->productCsvOutput();
		} elseif($input === 
			self::IMPORTS) {
			$this->productImports();
		} elseif($input === self::FINISH) {
			$this->productFinish();
		} 
	}

	//商品一覧表示
	public function productChart() {
		echo '商品一覧表示'.PHP_EOL.PHP_EOL;
		//商品一覧を取得
		$products = PRODUCT::getProductList();
		//商品が登録されていない場合の処理
		if(empty($products)) {
			echo '登録されている商品がありません'.PHP_EOL.PHP_EOL;
		} else {
			//一覧表示
			foreach($products as $key => $product) {
				echo sprintf('id: %d', $product['id']).PHP_EOL;
				echo sprintf('商品名: %s', $product['product_name']).PHP_EOL;
				echo sprintf('JANコード: %s', $product['jancode']).PHP_EOL.PHP_EOL;
			}
		}
		//メインメニューに戻る
		return $this->registSystem();
	}

	//JANコードを生成
	public function generateJancode() {
		//ランダムな9桁の数字を生成
		$rand = mt_rand(100000000,999999999);
		//ユニークなIDを生成
		$this->id++;
		//idの0埋めをsprintfで実装
		$id = sprintf('%03d', $this->id);
		//JANコードを作成
		$jancode = $rand.$id;
		return $jancode;
	}

	//商品マスター登録
	public function productRegist() {
		echo '商品名を入力してください'.PHP_EOL.PHP_EOL;;
		$input_name = trim(fgets(STDIN));
		//商品登録のバリデーションを呼び出し
		$validation = new BaseValidation;
		if(!$validation->check($input_name)) {
			//エラ〜メッセージを表示
			$errors = $validation->getErrorMessages();
			foreach($errors as $error) {
				echo $error;
			}
			//再起処理
			return $this->productRegist();
		}
		//JANコードを取得
		$jancode = $this->generateJancode();
		//同じidがあった場合はインクリメント
		//*補足*削除を行ったCSVファイルを作成し、そのファイルをインポートした場合でのみ、商品登録をするとidが被る事に対する対策
		if(PRODUCT::isExisByProduct($this->id)) {
			$this->id++;
		}

		$product = [];
		$product['id'] = $this->id;
		$product['product_name'] = $input_name;
		$product['jancode'] = $jancode;

		echo PHP_EOL.'以下の商品を登録しました'.PHP_EOL.PHP_EOL;
		echo sprintf('id: %d', $product['id']).PHP_EOL;
		echo sprintf('商品名: %s', $product['product_name']).PHP_EOL;
		echo sprintf('JANコード: %s', $product['jancode']).PHP_EOL.PHP_EOL;
		//商品を登録
		PRODUCT::setProductList($product);
		//登録作業を終了するかの処理
		if(!$this->askExitRegist()) {
			//メインメニューに戻る
			return $this->registSystem();
		}
		//再起処理
		return $this->productRegist();
	}

	//商品マスター登録をを続けるかを尋ねる
	public function askExitRegist() {
		echo '商品マスター登録を続けますか？ y:n'.PHP_EOL.PHP_EOL;
		$input = trim(fgets(STDIN));
		//入力のバリデーション
		$input_continue = new InputContinue();
		if (!$input_continue->check($input)) {
			//エラ〜メッセージを表示
			$errors = $input_continue->getErrorMessages();
			foreach($errors as $error) {
				echo $error;
			}
			return $this->askExitRegist();
		}
		if ($input === 'n') {
			return false;
		}
		return true;
	}

	//商品削除
	public function productDelete() {
		echo '削除する商品idを入力してください'.PHP_EOL;
		$input_id = trim(fgets(STDIN));
		$validation = new ProductValidation;
		if(!$validation->check($input_id)) {
			//エラ〜メッセージを表示
			$errors = $validation->getErrorMessages();
			foreach($errors as $error) {
				echo $error;
			}
			//削除操作を続けるかの操作
			if(!$this->askExitDelete()) {
				//メインメニューに戻る
				return $this->registSystem();
			}
			//再起処理
			return $this->productDelete();
		}
		//商品リストを取得
		$products = PRODUCT::getProductList();
		//配列のインデックスと合わせるため-1
		$input_id = $input_id - 1;
		echo sprintf('id: %d', $products[$input_id]['id']).PHP_EOL;
		echo sprintf('商品名: %s', $products[$input_id]['product_name']).PHP_EOL;
		echo sprintf('JANコード: %s', $products[$input_id]['jancode']).PHP_EOL.PHP_EOL;
		//削除するかの判定
		if($this->askDelete()) {
			echo sprintf('id: %d、商品名「%s」を削除しました', $products[$input_id]['id'], $products[$input_id]['product_name']).PHP_EOL;
			//商品を削除
			PRODUCT::deleteProduct($input_id);
		}
		//削除操作を続けるかの操作
		if(!$this->askExitDelete()) {
			//メインメニューに戻る
			return $this->registSystem();
		}
		//再起処理
		return $this->productDelete();
	}

	//削除操作を続けるかを尋ねる
	public function askExitDelete() {
		echo '商品削除を続けますか？ y:n'.PHP_EOL.PHP_EOL;
		$input = trim(fgets(STDIN));
		//入力のバリデーション
		$input_continue = new InputContinue();
		if (!$input_continue->check($input)) {
			//エラ〜メッセージを表示
			$errors = $input_continue->getErrorMessages();
			foreach($errors as $error) {
				echo $error;
			}
			return $this->askExitDelete();
		}
		if($input === 'n') {
			return false;
		}
		return true;
	}

	//商品削除するかを尋ねる判定
	public function askDelete() {
		echo 'この商品情報を削除しますか？ y:n'.PHP_EOL.PHP_EOL;
		$input = trim(fgets(STDIN));
		//入力のバリデーション
		$input_continue = new InputContinue();
		if(!$input_continue->check($input)) {
			//エラ〜メッセージを表示
			$errors = $input_continue->getErrorMessages();
			foreach($errors as $error) {
				echo $error;
			}
			//再起処理
			return $this->askDelete();
		}
		if($input === 'n') {		
			return false;
		}
		return true;
	}

	//商品CSV出力
	public function productCsvOutput() {
		//CSVで出力するかの判定
		if(!$this->askCsvOutput()) {
			//メインメニューに戻る
			return $this->registSystem();
		}
		//ファイルの書き込み
		$this->fileOutput();
		//完了後メニューに戻る
		if(!$this->askExitCsvOutput()) {
			return $this->productCsvOutput();
		}
		return $this->registSystem();
	}

	//CSV出力するかの判定
	public function askCsvOutput() {
		echo '商品一覧をCSVで出力しますか？ y:n'.PHP_EOL.PHP_EOL;
		$input = trim(fgets(STDIN));
		//入力のバリデーション
		$input_continue = new InputContinue();
		if (!$input_continue->check($input)) {
			//エラ〜メッセージを表示
			$errors = $input_continue->getErrorMessages();
			foreach($errors as $error) {
				echo $error;
			}
			return $this->askCsvOutput();
		}
		if ($input === 'n') {
			return false;
		}
		return true;
	}

	//CSVファイルの出力
	public function fileOutput() {
		//時間を日本時間に指定
		date_default_timezone_set('Asia/Tokyo');
		//フルパスを定義
		$file = './csv/item_list_'.date("YmdHis").'.csv';
		//フルパスからディレクトリ部分を関数で取得
		$dir = dirname($file);
		//フルパスからファイル名のみを取得
		$filename = basename($file);
		//ディレクトリが無ければディレクトリを作成
		if(!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}
		//商品一覧を取得
		$products = PRODUCT::getProductList();
		//商品が登録されていない場合の処理
		if(empty($products)) {
			echo '登録されている商品がありません'.PHP_EOL.PHP_EOL;
		} else {	
			//1行目のに項目名を挿入
			$col_name = array('ID', '商品名', 'JANコード');
			array_unshift($products, $col_name);
			//書き込みモードでファイルを開く
			$op_file = fopen($file, 'w');
			foreach($products as $key => $product) {
				$product = mb_convert_encoding($product, 'SJIS');
				$line = implode(',' , $product);
				fwrite($op_file, $line.PHP_EOL);
			}
			fclose($op_file);
			echo sprintf('CSVファイル %s が作成されました', $filename).PHP_EOL.PHP_EOL;
			//作成したCSVファイルをimportsフォルダにコピー
			if($this->askFileCopy()) {
				return $this->fileCopy($file, $filename);
			}
		}
	}

	//CSVファイルをimportsフォルダにコピーするかの処理
	public function askFileCopy() {
		echo '作成したCSVファイルをimportsフォルダにコピーしますか？ y:n'.PHP_EOL.PHP_EOL;
		$input = trim(fgets(STDIN));
		//入力のバリデーション
		$input_continue = new InputContinue();
		if (!$input_continue->check($input)) {
			//エラ〜メッセージを表示
			$errors = $input_continue->getErrorMessages();
			foreach($errors as $error) {
				echo $error;
			}
			return $this->askfileCopy();
		}
		if($input === 'n') {
			return false;
		}
		return true;
	}

	//importsフォルダへのコピー処理
	public function fileCopy($csvpath, $filename) {
		//インポート先のフルパスを定義
		$importspath = './imports/'.$filename;
		//フルパスからディレクトリ部分を関数で取得
		$dir = dirname($importspath);
		//ディレクトリが無ければディレクトリを作成
		if(!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}
		//ファイルのコピー
		copy($csvpath, $importspath);
		echo 'importsフォルダに '.$filename.' をコピーしました'.PHP_EOL.PHP_EOL;
	}

	//CSV出力を終えメニューに戻るかの判定
	public function askExitCsvOutput() {
		echo 'メニューに戻りますか？ y:n'.PHP_EOL.PHP_EOL;
		$input = trim(fgets(STDIN));
		//入力のバリデーション
		$input_continue = new InputContinue();
		if (!$input_continue->check($input)) {
			//エラ〜メッセージを表示
			$errors = $input_continue->getErrorMessages();
			foreach($errors as $error) {
				echo $error;
			}
			return $this->askExitCsvOutput();
		}
		if($input === 'n') {
			return false;
		}
		return true;
	}

	//商品CSVからインポート
	public function productImports() {
		//入力からファイルパスを取得する
		$file_path = $this->productImportsInput();
		//インポートする
		$this->fileImports($file_path);
		//完了後メニューに戻る
		if(!$this->askExitProductImports()) {
			return $this->productImports();
		}
		return $this->registSystem();
	}

	//入力からファイルパスを取得する機能
	public function productImportsInput() {
		$dir = './imports';
		//ディレクトリとファイルがなければ
		if(!file_exists($dir) || !glob($dir.'/*.csv')) {
			echo 'importsフォルダ内にファイルがありません'.PHP_EOL.PHP_EOL;
			return $this->registSystem();
		} else {
			//importsディレクトリ内のファイルを取得
			$files = glob($dir.'/*.csv');
		}
		echo 'インポートするファイルの番号を選択してください'.PHP_EOL.PHP_EOL;
		foreach($files as $key => $file) {
			$this->setImportsFilekey($key);
			echo sprintf('%s. %s', self::$file_key, basename($file)).PHP_EOL;
		}
		echo PHP_EOL;
		$input = trim(fgets(STDIN));
		//入力のバリデーション
		$input_filekey = new ImportsFileValidation();
		if (!$input_filekey->check($input)) {
			//エラ〜メッセージを表示
			$errors = $input_filekey->getErrorMessages();
			foreach($errors as $error) {
				echo $error;
			}
			return $this->productImportsInput();
		}
		//インプットした値からファイルを選択する
		return $files[$input - 1];
	}

	//ファイルパスからインポート
	public function fileImports($path) {
		//ファイルの読み込み
		$op_file = fopen($path, 'r');
		$count = 0;
		//フィールドデータを一時的に格納する配列
		$tmp_line = [];
		//インデックスを書き換えたインポート用の配列
		$line = [];
		//インポート前に商品リストを初期化
		PRODUCT::resetProductList();
		//idのカウントにインポートした分を上乗せする
		$this->id = 0;
		while(!feof($op_file)) {
			//行を取得
			$field_datas = fgets($op_file);
			//改行を置換
			$field_datas = str_replace(PHP_EOL, "", $field_datas);
			//エンコードを戻す
			$field_datas = mb_convert_encoding($field_datas, 'UTF-8', 'SJIS');
			//読み込んだ行にデータがあれば行を生成
			if(!empty($field_datas)) {
				//ヘッダーの項目名は飛ばす
				if($count > 0) {
					//行のデータをカンマ区切りの配列で取得
					$tmp_line[$count] = explode(',', $field_datas);
					//別の配列にインデックスを書き換えて格納
					$line[$count]['id'] = (int)$tmp_line[$count][0];
					$line[$count]['product_name'] = $tmp_line[$count][1];
					$line[$count]['jancode'] = $tmp_line[$count][2];
					//商品を登録
					PRODUCT::setProductList($line[$count]);
				}
				$count++;
			}
		}
		fclose($op_file);
		//idのカウントにインポートした分を上乗せ
		$this->id = $this->id + ($count - 1);
		//ファイル名を取得
		$filename = basename($path);
		echo 'ファイル '.$filename.' のインポートが完了しました'.PHP_EOL.PHP_EOL;
	}

	//インポートを終えメニューに戻るかの判定
	public function askExitProductImports() {
		echo 'メニューに戻りますか？ y:n'.PHP_EOL.PHP_EOL;
		$input = trim(fgets(STDIN));
		//入力のバリデーション
		$input_continue = new InputContinue();
		if (!$input_continue->check($input)) {
			//エラ〜メッセージを表示
			$errors = $input_continue->getErrorMessages();
			foreach($errors as $error) {
				echo $error;
			}
			return $this->askExitProductImports();
		}
		if($input === 'n') {
			return false;
		}
		return true;
	}

	//importsフォルダにあるファイル数をセット
	public function setImportsFilekey($index) {
		self::$file_key = $index + 1;
	}

	//importsフォルダにあるファイル数をゲット
	public static function getImportsFilekey() {
		return self::$file_key;
	}

	//終了
	public function productFinish() {
		exit('プログラムを終了します');
	}
}

$regist = new REGIST();
$regist->registSystem();
?>