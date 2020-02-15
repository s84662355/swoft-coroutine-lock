<?php declare(strict_types=1);

namespace cjhswoftCoroutineLock;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Co;
use Swoft\Concern\ArrayPropertyTrait;
use Swoft\Connection\Pool\Contract\ConnectionInterface;
use Illuminate\Database\Connection;
use Swoft\Bean\Concern\PrototypeTrait;
use swoole\Coroutine;
/**
 * Class CoroutineLock
 *
 * @since 2.0
 *
 * @Bean(scope=Bean::PROTOTYPE)
 */
class CoroutineLock
{   
    use PrototypeTrait;

    const UPDATE = 0;
    const SHARE  = 1;

    private $lock_type = 0;

    private $is_lock = false;

    private $coroutine_id_arr = [];

    private $share_lock_count = 0;


    /**
     *
     * @param array $items
     *
     * @return static
     */
    public static function new( ): self
    {
        $self   = self::__instance();
        return $self;
    }

    public function lock($coroutine_id,$type,$wait_time = 0)
    {      
           if( $this->getlock($coroutine_id,$type) )
              return true;
            
           $wait_time  = $wait_time * 1000 * 1000;
           while($wait_time > 0)
           {
            
                if( $this->getlock($coroutine_id,$type) )
                    return true;

                 
                 usleep(1000 * 100);
                $wait_time = $wait_time - 1000 * 100;
           }

           return false;
    }


    public function getlock($coroutine_id,$type)
    {
        
        if(!$this->is_lock)
        {
            $this->setLock($coroutine_id,$type);
            return true;
        }else{
           switch ($this->lock_type) {
               case static::UPDATE:
                   if($type == static::UPDATE) {
                              if(isset( $this->coroutine_id_arr[$coroutine_id]))
                                return true;
                   }
                   return false;
                   break;
               case static::SHARE:

                    if($type == static::UPDATE) return false;

                    if(isset( $this->coroutine_id_arr[$coroutine_id]))
                                return true;

                    $this->setLock($coroutine_id,$type);
                    return true;
                   break;
           }
        }
    }

    public function unlock($coroutine_id)
    {
         if(!isset( $this->coroutine_id_arr[$coroutine_id]))
            return false;

         switch ($this->lock_type) {
             case static::UPDATE:
                  $this->coroutine_id_arr = [];
                  $this->is_lock = false;

                 break;
             case static::SHARE:

                 unset($this->coroutine_id_arr[$coroutine_id]);
                 $this->share_lock_count --;

                 if($this->share_lock_count<=0){
                     $this->share_lock_count = 0;
                      $this->coroutine_id_arr = [];
                      $this->is_lock = false;

                 }






                 break;     
             default:
                 # code...
                 break;
         }

        return true;

    }



    protected function setLock($coroutine_id,$type)
    {
        $this->is_lock = true;
        $this->coroutine_id_arr[$coroutine_id] = 1;
 
        $this->lock_type =   $type;
        if($type == static :: SHARE){
            $this->share_lock_count ++;
        } 
    }

    public function getUpdateLock($coroutine_id,$wait_time)
    {
         return $this-> lock($coroutine_id,static::UPDATE,$wait_time);
    }


    public function getShareLock($coroutine_id,$wait_time)
    {
         return $this-> lock($coroutine_id,static::SHARE,$wait_time);
    }

}