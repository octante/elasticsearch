<?php
/**
 * Cluster management controller
 *
 * User: isselguberna
 * Date: 26/12/12
 */

namespace ElasticSearch\ManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ElasticSearch\ManagementBundle\Resources\Lib\Elastica\Cluster as Elastica_Cluster;
use Elastica_Client;

class ClusterController extends Controller
{

    /**
     * Load cluster state page and show related data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stateAction ()
    {
        $cluster_state = $this->getClusterObject()->getState();

        $result                          = array();
        $result['cluster_name']          = isset($cluster_state['cluster_name']) ? $cluster_state['cluster_name'] : '';
        $result['master_node']           = isset($cluster_state['master_node']) ? $cluster_state['master_node'] : '';
        $result['blocks']                = isset($cluster_state['blocks']) ? $cluster_state['blocks'] : array();

        $result['nodes']                 = isset($cluster_state['nodes']) ? $cluster_state['nodes'] : array();
        $result['indices']               = isset($cluster_state['metadata']['indices']) ? $cluster_state['metadata']['indices'] : array();

        $result['routing_table_indices'] = isset($cluster_state['routing_table']['indices']) ? $cluster_state['routing_table']['indices'] : array();
        $result['routing_nodes']         = isset($cluster_state['routing_nodes']) ? $cluster_state['routing_nodes'] : array();

        $result['allocations']           = isset($cluster_state['allocations']) ? $cluster_state['allocations'] : array();

        $page_params = array(
                             'cluster_state' => $result,
                             'tab'           => 'cluster',
                             'page_title'    => 'Cluster State'
                            );

        return $this->render('ElasticSearchManagementBundle:Cluster:state.html.twig', $page_params);
    }

    /**
     * Load cluster health page and show related data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function healthAction ()
    {
        $elastica_cluster = $this->getClusterObject()->getHealth();
        $page_params = array(
            'tab'            => 'cluster',
            'page_title'     => 'Cluster Health',
            'cluster_health' => $elastica_cluster
        );

        return $this->render('ElasticSearchManagementBundle:Cluster:health.html.twig', $page_params);
    }

    /**
     * Return an Elastic_Cluster object
     *
     * @return \ElasticSearch\ManagementBundle\Resources\Lib\Elastica\Cluster
     */
    private function getClusterObject ()
    {
        return new Elastica_Cluster(new Elastica_Client());
    }
}