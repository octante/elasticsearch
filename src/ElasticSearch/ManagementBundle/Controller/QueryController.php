<?php
/**
 * User: isselguberna
 * Date: 26/12/12
 */
namespace ElasticSearch\ManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use ElasticSearch\ManagementBundle\Resources\Lib\Elastica\Request as Query_Request;

use Elastica_Client;

class QueryController extends Controller
{
    protected $aQuery;

    public function queryAction (Request $request)
    {
        $query      = $request->request->get('query');
        $from       = $request->request->get('from');
        $index_name = $request->request->get('index_name');
        $type_name  = $request->request->get('type_name');

        // Limit results to 50
        $limit  = ($request->request->get('limit') != '' && $request->request->get('limit') < 200) ? $request->request->get('limit') : 50;
        $fields = array();
        $page_params = array(
            'query'                => $query,
            'tab'                  => 'query',
            'page_title'           => 'Query Tool',
            'index_name'           => $index_name,
            'index_filter_options' => $this->getIndices()
        );

        if ($query != '') {
            $this->aQuery = json_decode($query, true);

            if ($from != '')   $this->addExtraParams(array('from' => $from));
            if ($limit != '')  $this->addExtraParams(array('size' => $limit));

            $exists_fields = (isset($this->aQuery['fields']));

            $url = '';
            $url .= ($index_name != '') ? '/' . $index_name : '';
            $url .= ($type_name != '') ? '/' . $type_name : '';

            $request = (new Query_Request(new Elastica_Client(), $url . '/_search', 'POST', array(), $this->aQuery))->send()->getData();

            $hits         = $request['hits']['hits'];
            $total        = $request['hits']['total'];
            if ($total > 0 && count($hits) > 0) {
                foreach ($hits as $key => $hit) {
                    $results_list = (!$exists_fields) ? $hit['_source'] : $hit['fields'];
                    foreach ($results_list as $field => $value) {
                        $results[$key][$field] = $value;
                        $fields[] = $field;
                    }
                }
                $fields = array_unique($fields);
            } else {
                $total = 0;
                $results = array();
                $fields = array();
            }

            $page_params['total']           = $total;
            $page_params['results']         = $results;
            $page_params['fields']          = $fields;
            $page_params['from']            = $from;
            $page_params['limit']           = $limit;
            $page_params['indice_name']     = $index_name;
            $page_params['type_name']       = $type_name;

            $page_params = array_merge($page_params, $this->getIndiceTypes($index_name));
        }

        return $this->render('ElasticSearchManagementBundle:Query:query.html.twig', $page_params);
    }

    /**
     * Ajax request to return types dropdown
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTypesDropdownAction (Request $request)
    {
        $indice_name = $request->query->get('indice_name');

        $page_params = $this->getIndiceTypes($indice_name);

        return $this->render('ElasticSearchManagementBundle:Query:type_dropdown.html.twig', $page_params);
    }

    /**
     * Add extraparams to query array, for example skip, limit, etc..
     *
     * @param $params
     */
    private function addExtraParams($params)
    {
        foreach ($params as $field => $value) {
            $this->aQuery[$field] = $value;
        }
    }

    /**
     * Get cluster available indices list
     *
     * @return array
     */
    private function getIndices ()
    {
        $cluster_state = (new \Elastica_Cluster(new \Elastica_Client()))->getState();
        $indices       = array_keys($cluster_state['metadata']['indices']);

        $indice = array();
        foreach ($indices as $index) {
            $indice[$index] = $index;
        }
        return $indice;
    }

    /**
     * Return page params related to type dropdown
     *
     * @param $indice_name
     * @return array
     */
    private function getIndiceTypes ($indice_name)
    {
        if ('' != $indice_name) {
            if ($indice_name != '') {
                $indice_mapping = (new \Elastica_Index(new \Elastica_Client(), $indice_name))->getMapping();
                foreach ($indice_mapping[$indice_name] as $type => $properties) {
                    $indice_types[$type] = $type;
                }
            } else {
                $indice_types = array();
            }

            $page_params = array (
                'type_filter_options' => $indice_types
            );
        } else {
            $page_params['no_index'] = true;
        }

        return $page_params;
    }
}