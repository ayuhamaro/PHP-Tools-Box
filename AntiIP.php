<?php

class AntiIP
{
    const REGEX = '/([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/';

    const FIREWALL_ADD_CMD = 'sudo firewall-cmd --add-rich-rule=\'rule family=ipv4 source address="%s" reject\' --permanent';

    const FIREWALL_RELOAD_CMD = 'sudo firewall-cmd --reload';

    private $exclude_ip = array('xx.xx.xx.xx', 'xx.xx.xx.xx');

    private $block_ip_file = 'block_list.json';

    private $log_file = 'log.txt';

    private $apache_log_list = array('/var/log/httpd/xxx-access_log',
                                    '/var/log/httpd/xxx-error_log',
                                );

    function __construct()
    {
        $this->log_file = dirname(__FILE__).'/'.$this->log_file;
        if( ! file_exists($this->log_file)){
            file_put_contents($this->log_file, '');
        }

        $this->__save_and_display_log("程式初始化開始");

        $this->block_ip_file = dirname(__FILE__).'/'.$this->block_ip_file;
        if( ! file_exists($this->block_ip_file)){
            file_put_contents($this->block_ip_file, '[]');
        }

        $this->__save_and_display_log("程式初始化完成");
    }

    private function __get_datetime()
    {
        return date('Y-m-d H:i:s');
    }

    private function __save_and_display_log($msg)
    {
        file_put_contents($this->log_file, $this->__get_datetime()."：$msg\r\n", FILE_APPEND);
        echo $this->__get_datetime()."：$msg\r\n";
    }

    private function __fetch_ip($log_data)
    {
        $log_data_array = explode("\n", $log_data);
        $ip_list = array();

        if(count($log_data_array) === 0)
        {
            $this->__save_and_display_log("Log沒有資料");
            return $ip_list;
        }

        foreach($log_data_array as $log_row)
        {
            preg_match(self::REGEX, $log_row, $matches);
            if(count($matches) === 5)
            {
                if(filter_var($matches[0], FILTER_VALIDATE_IP) !== false &&
                    ! in_array($matches[0], $ip_list) &&
                    ! in_array($matches[0], $this->exclude_ip))
                {
                    $ip_list[] = $matches[0];
                }
            }
        }

        return $ip_list;
    }

    private function __add_firewall_rule($block_ip_list)
    {
        if(count($block_ip_list) > 0)
        {
            foreach($block_ip_list as $block_ip)
            {
                $this->__save_and_display_log("新增IP $block_ip");
                exec(sprintf(self::FIREWALL_ADD_CMD, $block_ip));
            }
            $this->__save_and_display_log("防火牆重新讀取");
            exec(self::FIREWALL_RELOAD_CMD);
        }
        else
        {
            $this->__save_and_display_log("無新增封鎖IP");
        }
    }

    public function execute()
    {
        $this->__save_and_display_log("讀取已封鎖IP清單");
        $blocked_ip_list = json_decode(file_get_contents($this->block_ip_file));

        $log_ip_list = array();
        $consolidate_ip_list = array();

        foreach($this->apache_log_list as $apache_log_file)
        {
            if(file_exists($apache_log_file))
            {
                $this->__save_and_display_log("開啟Log檔 $apache_log_file");
                $log_data = file_get_contents($apache_log_file);

                $this->__save_and_display_log("從Log檔取出存取IP");
                $log_ip_list = array_merge($log_ip_list, $this->__fetch_ip($log_data));
            }

        }

        if(count($log_ip_list) === 0)
        {
            $this->__save_and_display_log("無法從Log檔取出存取IP");
            return false;
        }

        foreach($log_ip_list as $log_ip)
        {
            if( ! in_array($log_ip, $consolidate_ip_list))
            {
                $consolidate_ip_list[] = $log_ip;
            }
        }

        $this->__save_and_display_log("比對新增加IP清單");
        $block_ip_list = array_diff($consolidate_ip_list, $blocked_ip_list);

        if(count($block_ip_list) > 0)
        {
            $this->__add_firewall_rule($block_ip_list);
            $this->__save_and_display_log("更新已封鎖IP清單");
            $new_blocked_ip_list = array_merge($block_ip_list, $blocked_ip_list);
            file_put_contents($this->block_ip_file, json_encode($new_blocked_ip_list));
        }
        else
        {
            $this->__save_and_display_log("無需增加IP");
        }
    }

}

$AntiIP = new AntiIP;
$AntiIP->execute();
