<?php

namespace Sobit\Insights\Client;

/**
 * The Client class.
 *
 * @author Sobit Akhmedov <sobit.akhmedov@gmail.com>
 */
class Client implements ClientInterface
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $accountId;

    /**
     * @var string
     */
    private $insertKey;

    /**
     * @var string
     */
    private $queryKey;

    /**
     * Constructs the object.
     *
     * @param \GuzzleHttp\ClientInterface $client    The instance of Guzzle client
     * @param string                      $accountId The New Relic account ID
     * @param string                      $insertKey The Insights API insert key
     * @param string                      $queryKey  The Insights API query key
     */
    public function __construct(\GuzzleHttp\ClientInterface $client, $accountId, $insertKey, $queryKey)
    {
        $this->accountId = $accountId;
        $this->client    = $client;
        $this->insertKey = $insertKey;
        $this->queryKey  = $queryKey;
    }

    /**
     * {@inheritdoc}
     */
    public function insert($json)
    {
        $url     = "https://insights-collector.newrelic.com/v1/accounts/{$this->accountId}/events";
        $options = [
            'body'    => $json,
            'headers' => ['X-Insert-Key' => $this->insertKey, 'Content-Type' => 'application/json'],
        ];

        return $this->client->post($url, $options)->json();
    }

    /**
     * {@inheritdoc}
     */
    public function query($nrql)
    {
        $url     = "https://insights-collector.newrelic.com/v1/accounts/{$this->accountId}/query";
        $options = [
            'query'   => ['nrql' => $nrql],
            'headers' => ['X-Query-Key' => $this->queryKey, 'Accept' => 'application/json'],
        ];

        return $this->client->get($url, $options)->json();
    }
}
