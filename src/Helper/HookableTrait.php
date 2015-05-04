<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/30/15
 * Time: 11:44
 */

namespace MicronCMS\Helper;


/**
 * Class HookableTrait
 * @package MicronCMS\Helper
 */
trait HookableTrait
{
    /**
     * @var \SplPriorityQueue
     */
    protected $hooks;

    /**
     * @return $this
     */
    protected function initializeHooks()
    {
        $this->hooks = new \SplPriorityQueue();
        return $this;
    }

    /**
     * @param int $event
     * @param callable $hook
     * @param int $priority
     * @return $this
     */
    public function addHook($event, callable $hook, $priority = -1)
    {
        $this->hooks->insert(new Hook($hook, $event), $priority);
        return $this;
    }

    /**
     * @param int $event
     * @param array $arguments
     */
    public function triggerHooks($event, array $arguments = [])
    {
        $this->hooks->rewind();

        while ($this->hooks->valid()){
            /** @var Hook $hook */
            $hook = $this->hooks->current();

            $hook->triggerIfMatching($event, $arguments);

            if ($hook->isStopped()) {
                break;
            }

            $this->hooks->next();
        }
    }
}