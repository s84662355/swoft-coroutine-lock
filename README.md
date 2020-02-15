 swoft 框架的协程锁

 swoole 的版本必须>=4.2

 这个协程锁只限于同一个进程不同协程之间使用，因为swoft是一个多进程多协程的框架，
 但是不同进程之间没有不能做成阻塞，只有同一个进程不同协程之间才可以做成阻塞，

 具体应用可以起到一定的限流削峰的作用，比如缓存失效时，但是如果想用于保持数据一致性的话，还是用分布式锁或者mysql的锁

 composer require chenjiahao/swoft-coroutine-lock


获取读占锁 
参数 锁的名称 等待锁的超时时间 获取失败或者超时都会返回false
 CoroutineLockFactory::getUpdateLock('1111',10) 

获取共享锁
 CoroutineLockFactory::getShareLock('1111',10)

解锁
  CoroutineLockFactory::unlock('1111');