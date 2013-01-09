<?php
/**
 * Indices management controller
 *
 * User: isselguberna
 * Date: 26/12/12
 */

namespace ElasticSearch\ManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use ElasticSearch\ManagementBundle\Resources\Lib\Elastica\Index as Elastica_Index;

use Elastica_Cluster;
use Elastica_Client;

class IndexController extends Controller
{
    /**
     * @var index_name
     */
    protected $index_name;

    /**
     * List All cluster indices
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction ()
    {
        $cluster_state = (new Elastica_Cluster(new Elastica_Client()))->getState();
        $indices       = array_keys($cluster_state['metadata']['indices']);

        $page_params = array(
            'indices'       => $indices,
            'tab'           => 'indice',
            'page_title'    => 'Indices List'
        );

        return $this->render('ElasticSearchManagementBundle:Index:list.html.twig', $page_params);
    }

    /**
     * Get indice stats page
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function statsAction (Request $request)
    {
        $index_stats = $this->getIndexObject($request)->getStats()->getData();
        $response = array();

        if (isset($index_stats['_all']['indices'][$this->index_name])) {

            $index_stats = $index_stats['_all']['indices'][$this->index_name]['primaries'];

            $response = array('store'   => $index_stats['store'],
                              'docs'    => $index_stats['docs'],
                              'indexing'=> $index_stats['indexing'],
                              'get'     => $index_stats['get'],
                              'search'  => $index_stats['search'],
                            );
        }

        $page_params = array(
            'indice'        => $response,
            'tab'           => 'indice',
            'page_title'    => 'Indices Stats > ' . $this->index_name
        );

        return $this->render('ElasticSearchManagementBundle:Index:stats.html.twig', $page_params);
    }

    /**
     * Load indice mapping page
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mappingAction (Request $request)
    {
        $index_mapping = $this->getIndexObject($request)->getMapping();

        $page_params = array(
            'types_mapping' => $index_mapping[$this->index_name],
            'tab'           => 'indice',
            'page_title'    => 'Indices Mapping > ' . $this->index_name
        );

        return $this->render('ElasticSearchManagementBundle:Index:mapping.html.twig', $page_params);
    }

    /**
     * Load indice optimize popup and optimize it if necessary
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function optimizeAction (Request $request)
    {
        if ($request->query->get('doaction') != '') {
            $result = $this->getIndexObject($request)->optimize();
            $show_result = true;
        } else {
            $this->setIndexName($request);
            $show_result = false;
        }

        $page_params = array(
            'show_result'   => $show_result,
            'indice_name'   => $this->index_name,
            'tab'           => 'indice',
            'page_title'    => 'Indices Optimize > ' . $this->index_name
        );

        if ($show_result) {
            $page_params['error']  = $result->hasError();
            $page_params['shards'] = $result->getShardsStatistics();
        }

        return $this->render('ElasticSearchManagementBundle:Index:optimize.html.twig', $page_params);
    }

    /**
     * Do an indice snapshot, this feature is not developed yet
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function snapshotAction (Request $request)
    {
        // TODO: This feature is not developed yet
        $page_params = array(
            'show_result'   => true,
            'indice_name'   => 'lorem ipsum',
            'tab'           => 'indice',
            'page_title'    => 'Indices Snapshot > lorem ipsum'
        );

        return $this->render('ElasticSearchManagementBundle:Index:snapshot.html.twig', $page_params);
    }

    /**
     * Load indice delete popup and delete it if necessary
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction (Request $request)
    {
        $result = null;
        $error  = false;
        if ($request->query->get('doaction') != '') {
            $result = $this->getIndexObject($request)->delete()->getData();
            $error  = !($result['ok'] == true);
            $show_result = true;
        } else {
            $this->setIndexName($request);
            $show_result = false;
        }

        $page_params = array(
            'show_result'   => $show_result,
            'indice_name'   => $this->index_name,
            'tab'           => 'indice',
            'page_title'    => 'Indices Optimize > ' . $this->index_name
        );

        if ($request->query->get('doaction') != '') {
            $page_params['error'] = $error;
        }

        return $this->render('ElasticSearchManagementBundle:Index:delete.html.twig', $page_params);
    }

    /**
     * Return an Elastica_Index object
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \ElasticSearch\ManagementBundle\Resources\Lib\Elastica\Index
     */
    protected function getIndexObject (Request $request)
    {
        $this->setIndexName($request);
        return new Elastica_Index(new Elastica_Client(), $this->index_name);
    }

    /**
     * Set class variable indice_name from template value
     *
     * @param $request
     */
    protected function setIndexName ($request)
    {
        $this->index_name = $request->query->get('indice');
    }
}