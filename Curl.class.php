<?php
    final class Curl{
        private $resource;

        public function cUrl($url, $timeout = 30, $post_data = null){
            $retry = 0;	//重試旗標
            //$cookie_jar = 'cookie.txt';
            $this->resource = curl_init();
            curl_setopt($this->resource, CURLOPT_URL, $url);
            if($post_data != null){
                curl_setopt($this->resource, CURLOPT_POST, true);
                curl_setopt($this->resource, CURLOPT_POSTFIELDS, $post_data);
            }
            curl_setopt($this->resource, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($this->resource, CURLOPT_CONNECTTIMEOUT, 15);
            //curl_setopt($this->resource, CURLOPT_COOKIEFILE, $cookie_jar);
            //curl_setopt($this->resource, CURLOPT_COOKIEJAR, $cookie_jar);
            curl_setopt($this->resource, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->resource, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($this->resource, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->resource, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($this->resource, CURLOPT_FTP_USE_EPSV, false);
            curl_setopt($this->resource, CURLOPT_VERBOSE, true);
            curl_setopt($this->resource, CURLOPT_MAXREDIRS, 4);
            //curl_setopt($this->resource, CURLOPT_PROGRESSFUNCTION, array($this, '__progress'));
            //curl_setopt($this->resource, CURLOPT_NOPROGRESS, false); // needed to make progress function work
            do{
                //if($retry > 0){
                //    $msg = sprintf("Retry curl %s time(s) at: %s\n\n", $retry, $url);
                //    echo $msg;
                //}
                //sleep(5);
                $content = curl_exec($this->resource);
                $info = curl_getinfo($this->resource);
                $retry ++;
            }while($retry < 10 && ! $this->__chk_curl_download($info));	//最多重試10次
            return array('content' => $content,
                        'info' => $info);
        }

        private function __chk_curl_download($info){
            //$msg = sprintf('Curl error at "%s",', $info['url']);
            if( ! isset($info['size_download']) || $info['size_download'] == 0
                || ! is_numeric($info['size_download'])){
                //$this->__error_log($msg.'size_download error.', 'curl_error');
                return false;
            }
            if( ! isset($info['download_content_length']) || $info['download_content_length'] == 0
                || ! is_numeric($info['download_content_length'])){
                //$this->__error_log($msg.'download_content_length error.', 'curl_error');
                return false;
            }
            if($info['size_download'] !== $info['download_content_length']){
                //$this->__error_log($msg.'file size is inequality.', 'curl_error');
                return false;
            }
            return true;
        }

        private function __progress($download_size, $downloaded, $upload_size, $uploaded)
        {
            //return -1;	//停止cUrl
        }

    }


?>
