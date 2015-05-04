<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/30/15
 * Time: 11:46
 */

namespace MicronCMS\Helper;

use MicronCMS\AbstractCompilable;


/**
 * Class Hook
 * @package MicronCMS\Helper
 */
class Hook extends AbstractCompilable
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var int
     */
    protected $event;

    /**
     * @var bool
     */
    protected $stopped = false;

    /**
     * @param callable $callback
     * @param int $event
     */
    public function __construct(callable $callback, $event)
    {
        $this->callback = $callback;
        $this->event = $event;
    }

    /**
     * @return boolean
     */
    public function isStopped()
    {
        return $this->stopped;
    }

    /**
     * @param boolean $stopped
     * @return $this
     */
    public function setStopped($stopped)
    {
        $this->stopped = $stopped;
        return $this;
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @return int
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param string $event
     * @param array $arguments
     */
    public function triggerIfMatching($event, array $arguments)
    {
        if ($event === $this->event) {
            array_unshift($arguments, $this);
            call_user_func_array($this->callback, $arguments);
        }
    }
}