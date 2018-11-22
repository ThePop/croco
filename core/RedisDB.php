<?php

class RedisDB
{
    private $redis;

    function __construct() {
        $conf = parse_ini_file('config.ini', true);
        $this->redis = new Redis();
        $this->redis->connect($conf['redis']['host'], $conf['redis']['port'], 0);
        $this->redis->FLUSHALL(); //clr DB
    }

    function get($_key, $_id = '')
    {
        $key = $_id ? ('croco:'.$_key.':'.$_id) : $_key;
        return unserialize(stripslashes($this->redis->get($key)));
    }

    function set($_key, $_id, $obj)
    {
        $key = 'croco:' . $_key . ':' . $_id;
        $this->redis->set($key, addslashes(serialize($obj)));

    }

    function del($_key, $_id = '')
    {
        $key = $_id ? ('croco:'.$_key.':'.$_id) : $_key;
        $this->redis->expire($key, 0);
    }
}