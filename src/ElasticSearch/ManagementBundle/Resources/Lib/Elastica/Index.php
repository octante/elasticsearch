<?php

namespace ElasticSearch\ManagementBundle\Resources\Lib\Elastica;

/**
 * Elastica Cluster override for ruflin elastica bundle
 *
<<<<<<< HEAD
 * @author Issel Guberna <issel_gubernaathotmaildotcom>
 */

class Index extends \Elastica_Index
{
    protected $_index_name;
    protected $_response;

    public function __construct(\Elastica_Client $client, $index_name)
    {
        $this->_index_name = $index_name;
        parent::__construct($client, $index_name);
    }

    public function snapshot()
    {
        // index/type/snapshot!!
        $path = $this->_index_name . '/_gateway/snapshot';
        return $this->_client->request($path, \Elastica_Request::GET)->getData();
    }
}