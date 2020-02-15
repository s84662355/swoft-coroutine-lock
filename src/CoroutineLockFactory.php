<?php declare(strict_types=1);

namespace cjhswoftCoroutineLock;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Co;
use Swoft\Concern\ArrayPropertyTrait;
use Swoft\Connection\Pool\Contract\ConnectionInterface;
use Illuminate\Database\Connection;
use Swoft\Bean\Concern\PrototypeTrait;
use Swoft\Bean\BeanFactory;

/**
 * Class CoroutineLockFactory
 *
 * @since 2.0
 *
 */
class CoroutineLockFactory
{    

    private static $lock_arr = [];


    private static function getLock($name,$type,$wait_time = 0)
    {
        $coroutine_id = sprintf('%d.%d', Co::tid(), Co::id() );
        if(empty(self::$lock_arr[$name])){
            self::$lock_arr[$name] = BeanFactory::getBean(CoroutineLock::class);
        }

        switch ($type) {
            case CoroutineLock::UPDATE:
                return self::$lock_arr[$name] ->getUpdateLock($coroutine_id,$wait_time);
                break;
            case CoroutineLock::SHARE:
                return self::$lock_arr[$name] ->getShareLock($coroutine_id,$wait_time);
                break;
            default:
                return false;
                break;
        }
    } 


    public static function getUpdateLock($name, $wait_time = 0)
    {
         return self::getLock($name,CoroutineLock::UPDATE,$wait_time);
    }
 
    public static function getShareLock($name, $wait_time = 0)
    {
         return self::getLock($name,CoroutineLock::SHARE,$wait_time);
    }

    public static function unlock($name)
    {
        if(empty(self::$lock_arr[$name])){
            return false;
        } 
        $coroutine_id = sprintf('%d.%d', Co::tid(), Co::id() );
        return self::$lock_arr[$name] -> unlock($coroutine_id);
    }

    public static function release( )
    {
        foreach ($self::$lock_arr as $key => $value) {
              self::unlock($key);
        }

    }
}