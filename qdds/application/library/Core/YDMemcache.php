<?php
/**
 memcache的初始化及操作
 public  function get($key)
 public  function set($avgArr)
 public  function delete($key)
**/
namespace Core;
use Custom\YDLib;

class YDMemcache
{
    private static $_instance;//memcache对象

    //初始化memcache
    public function __construct($host, $port)
    {
    	if (!self::$_instance) {
         	self::$_instance = new \Memcache();
        	self::$_instance->addServer($host, $port);//添加memcache服务器
    	}
    }

    //获取memcache中缓存中的值 返回的数组仅仅包含从服务端找到的key-value
    public function get($key)
    {
        $key = __MEMCACHE_KEY__.$key;
        return self::$_instance->get($key);
    }
	
    //设置memcache中缓存的值 key value 为必选项
    public function set($key,$value,$expire=0,$flag=0)
    {
        //对set的参数进行处理
		$key = __MEMCACHE_KEY__.$key;
        if ($flag != 0) {//使用memcache_compressed指定的值进行压缩
            return self::$_instance->set($key, $value, MEMCACHE_COMPRESSED, $expire);
        }
        return self::$_instance->set($key, $value, 0, $expire);
    }

    //删除一个key对应的元素或者批量删除多个元素
    public function delete($key)
    {
        $keyArr = array();
        if (!is_array($key)) {//单个key值
            $keyArr[] = $key;
        } else {
            $keyArr = $key;
        }
        //删除缓存
        foreach ($keyArr as $k => $v) {
        	$v = __MEMCACHE_KEY__.$v;
            $del = self::$_instance->delete($v);
            if ($del == false) {
                return false;
            }
        }
        return true;
    }
	
    //清除所有缓存的数据
    //实际上没有释放资源，它仅仅将所有的缓存标记为过期，这样可以使新的缓存来覆盖被占的内存空间
    public function flush()
    {
        return self::$_instance->flush();
    }
	
		
}
