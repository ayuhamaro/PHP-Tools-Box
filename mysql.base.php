<?php
    class mysqlBase
    {
        private static $Db;
        //建構子建立新資料庫連線
        public function __construct()
        {
            self::$Db = new mysqli('host','dbUserId','dbUserPassword','dbName');
            //設定資料庫讀取用UTF-8
            self::$Db->query('SET NAMES utf8');
            self::$Db->query('SET CHARACTER_SET_CLIENT=utf8');
            self::$Db->query('SET CHARACTER_SET_RESULTS=utf8');
        }
        //解構子
        public function __destruct(){}
        //一般查詢
        public static function query($queryStr)
        {
            return self::$Db->query($queryStr);
        }
        //插入並傳回ID
        public static function insertAndGetId($queryStr)
        {
            self::$Db->query($queryStr);
            return self::$Db->insert_id;
        }
        //預備敘述式，可以避免資料注入
        public static function prepare($queryStr)
        {
            return self::$Db->prepare($queryStr);
        }
        //回傳多筆或強制單筆資料
        public static function resultToArray($result, $forceSingle = false){
            $array = array();
            if($result->field_count == 1){
                while($row = $result->fetch_array()){
                    $array[] = $row[0];
                }
            }else{
                if($forceSingle){
                    $array = $result->fetch_assoc();
                }else{
                    while($row = $result->fetch_assoc()){
                        $array[] = $row;
                    }
                }
            }
            $result->free();
            return $array;
        }
        //擷取值
        public static function resultToValue($result){
            $row = $result->fetch_array();
            $result->free();
            return $row[0];
        }
    }
?>