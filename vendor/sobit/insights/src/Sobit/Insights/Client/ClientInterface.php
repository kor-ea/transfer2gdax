<?php

namespace Sobit\Insights\Client;

/**
 * The ClientInterface interface.
 *
 * @author Sobit Akhmedov <sobit.akhmedov@gmail.com>
 */
interface ClientInterface
{
    /**
     * Submits the event(s) to Insights.
     *
     * @param string $json The JSON data set
     *
     * @return array The JSON decoded response
     */
    public function insert($json);

    /**
     * Queries Insights events database.
     *
     * @param string $nrql The NRQL event query
     *
     * @return array The JSON decoded response
     */
    public function query($nrql);
}
