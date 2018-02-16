<?php

namespace Sobit\Insights;

use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;

/**
 * The AbstractEvent class.
 *
 * @author Sobit Akhmedov <sobit.akhmedov@gmail.com>
 *
 * @ExclusionPolicy("all")
 */
abstract class AbstractEvent
{
    /**
     * @var string
     *
     * @Expose
     * @Type("string")
     * @Accessor(getter="getAppName")
     * @SerializedName("appName")
     */
    protected $appName;

    /**
     * @var string
     *
     * @Expose
     * @Type("string")
     * @Accessor(getter="getEventType")
     * @SerializedName("eventType")
     */
    protected $eventType;

    /**
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * @param string $appName
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;
    }

    /**
     * @return string
     */
    abstract public function getEventType();
}
