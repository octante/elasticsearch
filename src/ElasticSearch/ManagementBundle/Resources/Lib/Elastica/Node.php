<?php

namespace ElasticSearch\ManagementBundle\Resources\Lib\Elastica;

/**
 * Elastica Node override for ruflin elastica bundle
 *
<<<<<<< HEAD
 * @author Issel Guberna <issel_gubernaathotmaildotcom>
 */

class Node extends \Elastica_Node
{
    protected $_node_name;
    protected $_response;

    public function __construct($node_name, \Elastica_Client $client)
    {
        $this->_node_name = $node_name;
        parent::__construct($node_name, $client);
    }

    public function getHotThreads()
    {
        $path = '_nodes/' . $this->_node_name . '/hot_threads';
        return $this->_client->request($path, \Elastica_Request::GET)->getData();
    }
}