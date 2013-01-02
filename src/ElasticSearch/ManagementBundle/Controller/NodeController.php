<?php
/**
 * Nodes management controller
 *
 * User: isselguberna
 * Date: 26/12/12
 */

namespace ElasticSearch\ManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use ElasticSearch\ManagementBundle\Resources\Lib\Elastica\Node as Elastica_Node;
use Elastica_Cluster;

class NodeController extends Controller
{
    private $node_name;

    public function indexAction()
    {
        return $this->render('ElasticSearchManagementBundle:Node:index.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction ()
    {
        $node_names = (new \Elastica_Cluster(new \Elastica_Client()))->getNodeNames();
        $page_params = array(
            'nodes'      => $node_names,
            'tab'        => 'node',
            'page_title' => 'Nodes List'
        );
        return $this->render('ElasticSearchManagementBundle:Node:list.html.twig', $page_params);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function infoAction (Request $request)
    {
        $this->getNodeName($request);
        $node_info = $this->getNodeObject($this->node_name)->getInfo()->getData();
        $page_params = array(
            'node'          => $node_info,
            'tab'           => 'node',
            'page_title'    => 'Node Information > ' . $this->node_name
        );
        return $this->render('ElasticSearchManagementBundle:Node:info.html.twig', $page_params);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function statsAction (Request $request)
    {
        $this->getNodeName($request);
        $node_stats = $this->getNodeObject($this->node_name)->getStats()->getData();

        $response = array('store'   => $node_stats['indices']['store'],
                          'docs'    => $node_stats['indices']['docs'],
                          'indexing'=> $node_stats['indices']['indexing'],
                          'get'     => $node_stats['indices']['get'],
                          'search'  => $node_stats['indices']['search'],
                          'cache'   => $node_stats['indices']['cache'],
                          'merges'  => $node_stats['indices']['merges'],
                          'refresh' => $node_stats['indices']['refresh'],
                          'flush'   => $node_stats['indices']['flush']
                        );

        $page_params = array(
            'node'           => $response,
            'tab'            => 'node',
            'page_title'     => 'Node stats > ' . $this->node_name
        );
        return $this->render('ElasticSearchManagementBundle:Node:stats.html.twig', $page_params);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function hotthreadsAction (Request $request)
    {
        $this->getNodeName($request);
        $node_hot_threads = $this->getNodeObject($this->node_name)->getHotThreads();

        $hot_threads = (isset($node_hot_threads['message'])) ? $node_hot_threads['message'] : '';

        $page_params = array(
            'hot_threads'    => $hot_threads,
            'tab'            => 'node',
            'page_title'     => 'Node Hot Threads > ' . $this->node_name
        );
        return $this->render('ElasticSearchManagementBundle:Node:hotthreads.html.twig', $page_params);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    private function getNodeName (Request $request)
    {
        $this->node_name = $request->query->get('node');
    }

    /**
     * @param string $node_name
     * @return \Elastica_Node
     */
    private function getNodeObject ($node_name)
    {
        return new Elastica_Node($node_name, new \Elastica_Client());
    }
}