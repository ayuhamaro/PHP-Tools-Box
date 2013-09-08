<?php
    //Ver 0.1
    //用一個抽象把取得資料的方法包裝起來，由程式自行去處理快取的動作
    abstract class cache{
        const CACHE_TYPE_JSON = 'json'; //以JSON格式儲存快取
        const CACHE_TYPE_SERIAL = 'serial'; //以序列化格式儲存快取

        public static $cacheType = self::CACHE_TYPE_JSON;   //預設以JSON儲存快取
        public static $cacheMkdirMode = 0777;   //快取資料夾的權限

        //因為在一次的行程中會反覆使用，所以使用static模式。且為了避免繼承時被複寫而使用final模式
        static final function getData($cachePath = '/path/to', $cacheFileName = 'filename.ext',
                                      $callback = array(), $parameter = array(), $forceUpdateCache = false){
            if(!$forceUpdateCache){ //強制更新的話就不讀取快取，進行查詢、更新快取並傳回
                $result = @file_get_contents($cachePath.'/'.$cacheFileName);
                if($result !== false){
                    switch(self::$cacheType){
                        case 'json':
                            return json_decode($result);
                            break;
                        case 'serial':
                            return unserialize($result);
                            break;
                    }
                }
            }
            $result = call_user_func_array($callback, $parameter);
            switch(self::$cacheType){
                case 'json':
                    $dataString = json_encode($result);
                    break;
                case 'serial':
                    $dataString = serialize($result);
                    break;
            }
            if(!is_dir($cachePath)){
                mkdir($cachePath, self::$cacheMkdirMode, true);
            }
            if(@file_put_contents($cachePath.'/'.$cacheFileName, $dataString) !== false){
                return $result;
            }
            return false;
        }
    }

    //產生一個類別來當資料Model，要繼承cache唷
    class getContent extends cache{
        public function getDataExample($currentPage, $lastPage){    //資料查詢函式可以獨立運作，或被getData呼叫接指定的參數進來
            $dataExample = new dataExample($currentPage, $lastPage);    //示範假設你的查詢式有參數需要傳入
            return $dataExample;    //懶得寫模擬的讀取方法...
        }
    }

    //做一個假的資料來源，模擬MySQLi、PDO那之類
    class dataExample{
        public $currentPage = null; //故意預設是Null
        public $lastPage = null;    //故意預設是Null
        public $data = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);    //簡單的假資料

        public function __construct($currentPage, $lastPage){   //接入指定的查詢參數
            $this->currentPage = $currentPage;  //改變傳回的物件屬性，這樣才知道是否生效
            $this->lastPage = $lastPage;    //改變傳回的物件屬性，這樣才知道是否生效
        }
    }

    //示範最基本的用法，你會發現第一次執行時，Dump的物件是dataExample，之後都是stdClass，快取就生效囉
    $getContent = new getContent();
    var_dump($getContent::getData('/home/test/cache', 'cache.json', array($getContent, 'getDataExample'), array(1, 10)));
?>
