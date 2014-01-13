<?php
//資料原型物件,強迫必須繼承且提供公用方法,讓繼承物件無法直接更改資料陣列
abstract class Data_Object{
	private $data_array = array();
	
	protected function __construct($data_pattern){
		$this->data_array = $data_pattern;
	}
	
	final public function get($key = null){
		if($key == null){
			return $this->data_array;
		}else{
			if($this->key_exist($key)){
				return $this->data_array[$key];
			}
			return null;
		}
	}
	
	final public function batch_get(array $keys){
		$result = array();
		foreach ($keys as $key) {
			if($this->key_exist($key)){
				$result[$key] = $this->data_array[$key];
			}
		}
		return $result;
	}
	
	final public function set(string $key, $value){
		if($this->key_exist($key)){
			$this->data_array[$key] = $value;
		}
	}
	
	final public function batch_set(array $data){
		foreach ($data as $key => $value) {
			if($this->key_exist($key)){
				$this->data_array[$key] = $value;
			}
		}
	}
	
	private function key_exist($key){
		if(array_key_exists($key, $this->data_array)){
			return true;
		}
		return false;
	}
}

//實際的資料物件,用來定義資料模型及特有的方法
//除了持久化資料的類別,例如DB以外,不與其他類別互動
class Member_Data extends Data_Object{
	public function __construct(){
		$member_data = array(				//資料模型與預設值
			'id' => 1,						//會員編號	
			'name' => 'Maro',				//會員名稱
			'create' => '2014-01-10',		//建立日期
		);
		parent::__construct($member_data);	//定義資料模型
	}
	
	public function load_from_db(){
		//從DB讀取之類的
	}
}

//資料操作物件,與其他類別互動
class Member_Handle{
	public function __construct(){
		$member_data = new Member_Data();
		$this->dothing($member_data);
	}
	
	public function dothing(Member_Data $member_data){	//強制傳入處理函式的參數型別,以確定資料模型正確
		$member_data->batch_set(array('id' => 2, 'name' => 'Test', 'test' => 'fake'));	//故意嘗試更新一個不存在的鍵
		var_dump($member_data->get());	//輸出的結果將會保有原來資料模型的架構
	}
	
	
}

$demo = new Member_Handle();


?>
