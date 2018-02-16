<?php

namespace Sobit\Insights;

use JMS\Serializer\SerializerInterface;
use Sobit\Insights\Client\ClientInterface;
use Sobit\Insights\Persistence\EventManagerInterface;

/**
 * The EventManager class.
 *
 * @author Sobit Akhmedov <sobit.akhmedov@gmail.com>
 */
class EventManager implements EventManagerInterface
{
    /**
     * @var AbstractEvent[]
     */
    private $persistedEvents;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $appName;

    /**
     * @param ClientInterface     $client
     * @param SerializerInterface $serializer
     * @param string              $appName
     */
    public function __construct(ClientInterface $client, SerializerInterface $serializer, $appName)
    {
        $this->client     = $client;
        $this->serializer = $serializer;
        $this->appName    = $appName;

        $this->persistedEvents = [];
    }

    /**
     * {@inheritdoc}
     */
    public function persist(AbstractEvent $event)
    {
        $clonedEvent = clone $event;
        $clonedEvent->setAppName($this->appName);
        $this->persistedEvents[] = $clonedEvent;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $json = $this->serializer->serialize($this->persistedEvents, 'json');
        $this->client->insert($json);

        $this->persistedEvents = [];
    }
}
