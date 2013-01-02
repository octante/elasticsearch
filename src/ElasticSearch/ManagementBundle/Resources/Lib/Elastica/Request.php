<?php

namespace ElasticSearch\ManagementBundle\Resources\Lib\Elastica;

/**
 * Elastica Cluster override for ruflin elastica bundle
 *
 * @author Issel Guberna <issel_gubernaathotmaildotcom>
 */

use ElasticSearch\ManagementBundle\Resources\Lib\Elastica\Transport\Http as Elastica_Transport_Http;

class Request extends \Elastica_Request
{
    public function __construct(\Elastica_Client $client, $path, $method, $data = array(), array $query = array())
    {
        parent::__construct($client, $path, $method, $data, $query);
    }

    /**
     * Returns an instance of the transport type
     *
     * @return Elastica_Transport_Abstract Transport object
     * @throws Elastica_Exception_Invalid  If invalid transport type
     */
    public function getTransport()
    {
        return new Elastica_Transport_Http($this);
    }

    /**
     * Sends request to server
     *
     * @return Elastica_Response Response object
     */
    public function send()
    {
        $log = new \Elastica_Log($this->getClient());
        $log->log($this);

        $transport = $this->getTransport();

        $servers = $this->getClient()->getConfig('servers');

        if (empty($servers)) {
            $params = array(
                'url' => $this->getClient()->getConfig('url'),
                'host' => $this->getClient()->getHost(),
                'port' => $this->getClient()->getPort(),
                'path' => $this->getClient()->getConfig('path'),
            );
            $response = $transport->exec($params);
        } else {

            // Set server id for first request (round robin by default)
            if (is_null(self::$_serverId)) {
                self::$_serverId = rand(0, count($servers) - 1);
            } else {
                self::$_serverId = (self::$_serverId + 1) % count($servers);
            }

            $server = $servers[self::$_serverId];

            $response = $transport->exec($server);
        }

        return $response;
    }
}