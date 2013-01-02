<?php

namespace ElasticSearch\ManagementBundle\Resources\Lib\Elastica;

/**
 * Elastica Cluster override for ruflin elastica bundle
 *
<<<<<<< HEAD
 * @author Issel Guberna <issel_gubernaathotmaildotcom>
 */

class Cluster extends \Elastica_Cluster
{
    public function __construct(\Elastica_Client $client)
    {
        parent::__construct($client);
    }

    public function getHealth ($args = array())
    {
        $arg = '';
        foreach ($args as $k => $v) {
            $arg .= '&' . $k . '=' . $v;
        }
        $arg = ($arg != '') ? '?' . trim($arg, '&') : '';

        $path = '_cluster/health' . $arg;
        $this->_response = $this->_client->request($path, \Elastica_Request::GET);
        return $this->getResponse()->getData();
    }
}