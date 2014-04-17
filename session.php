<?php

class Mysession{

    private $session = array();
    public $index = 'xxx_user_session';

    public function __construct(){
        if( ! session_id())
        {
            session_start();
        }
    }

    public function set($key, $value){
        $this->session = $this->get();
        $this->session[$key] = $value;
        $_SESSION[$this->index] = json_encode($this->session, JSON_UNESCAPED_UNICODE);
        return $_SESSION[$this->index];
    }

    public function get($key = FALSE){
        if( ! isset($_SESSION[$this->index])){
            $_SESSION[$this->index] = json_encode($this->session, JSON_UNESCAPED_UNICODE);
        }else{
            $this->session = json_decode($_SESSION[$this->index], TRUE);
        }
        if($key !== FALSE){
            if(array_key_exists($key, $this->session)){
                return $this->session[$key];
            }else{
                return FALSE;
            }
        }else{
            return $this->session;
        }
    }

    public function del($key = FALSE){
        if($key !== FALSE){
            $this->session = $this->get();
            if(array_key_exists($key, $this->session)){
                unset($this->session[$key]);
                $_SESSION[$this->index] = json_encode($this->session, JSON_UNESCAPED_UNICODE);
            }
        }else{
            $this->destroy();
        }
    }

    public function destroy(){
        $this->session = array();
        session_unset();
        session_destroy();
    }
}
