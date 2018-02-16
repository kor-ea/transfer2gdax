<?php

namespace Sobit\Insights\Persistence;

use Sobit\Insights\AbstractEvent;

/**
 * The EventManagerInterface interface.
 *
 * @author Sobit Akhmedov <sobit.akhmedov@gmail.com>
 */
interface EventManagerInterface
{
    /**
     * Persists the event.
     *
     * @param AbstractEvent $event
     *
     * @return void
     */
    public function persist(AbstractEvent $event);

    /**
     * Merges all the persisted events and submits them to Insights.
     *
     * @return void
     */
    public function flush();
}
