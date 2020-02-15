<?php declare(strict_types=1);


namespace cjhswoftCoroutineLock\Listener;


use ReflectionException;
use Swoft\Bean\BeanFactory;
use Swoft\Bean\Exception\ContainerException;
use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\SwoftEvent;
use Swoft\Log\Helper\CLog;
use cjhswoftCoroutineLock\CoroutineLockFactory;
/**
 * Class CoroutineDestoryListener
 *
 * @since 2.0
 *
 * @Listener(event=SwoftEvent::COROUTINE_DESTROY)
 */
class CoroutineDestoryListener implements EventHandlerInterface
{
    /**
     * @param EventInterface $event
     *
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function handle(EventInterface $event): void
    {
             CoroutineLockFactory:: release( );

        /// CLog::info('Close  CoroutineDestoryListener  laravel connection on %s!',  $event->getName());
    }
}