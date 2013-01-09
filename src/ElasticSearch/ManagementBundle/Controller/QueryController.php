<?php
/**
 * Query tool
 *
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
    /**
     * @var executed_query
     */
    protected $executed_query;

    /**
     *  Load query tool page and execute the query if necessary
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function queryAction (Request $request)
    {
        $query      = $request->request->get('query');
        $from       = $request->request->get('from');
        $index_name = $request->request->get('index_name');
        $type_name  = $request->request->get('type_name');
        $limit      = ($request->request->get('limit') != '' && $request->request->get('limit') < 200)
                            ? $request->request->get('limit')
                            : 50; // Limit results to 50

        $page_params = array(
            'query'                => $query,
            'tab'                  => 'query',
            'page_title'           => 'Query Tool',
            'index_name'           => $index_name,
            'index_filter_options' => $this->getIndices(),                   // Load indices to dropdown
            'type_filter_options'  => $this->getIndiceTypes($index_name),    // Load types to dropdown
            'not_selected_indice'  => (empty($index_name))                   // Don't load types dropdown if no indice is selected
        );

        if ($query != '') {
            $this->executed_query = json_decode($query, true);

            if ($from != '')   $this->addExtraParams(array('from' => $from));
            if ($limit != '')  $this->addExtraParams(array('size' => $limit));

            $exists_fields = (isset($this->executed_query['fields']));

            $url = '';
            $url .= ($index_name != '') ? '/' . $index_name : '';
            $url .= ($type_name != '') ? '/' . $type_name : '';

            $request = (new Query_Request(new Elastica_Client(), $url . '/_search', 'POST', array(), $this->executed_query))->send()->getData();

            $hits         = $request['hits']['hits'];
            $total        = $request['hits']['total'];
            $fields       = array();

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
        }

        return $this->render('ElasticSearchManagementBundle:Query:query.html.twig', $page_params);
    }

    /**
     * Ajax request to return types dropdown
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTypesDropdownAjaxAction (Request $request)
    {
        $indice_name = $request->query->get('indice_name');

        $page_params['type_filter_options'] = $this->getIndiceTypes($indice_name);

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
            $this->executed_query[$field] = $value;
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
     *
     * @return array
     */
    private function getIndiceTypes ($indice_name)
    {
        $indice_types = array();

        if ('' != $indice_name) {
            if ($indice_name != '') {
                $indice_mapping = (new \Elastica_Index(new \Elastica_Client(), $indice_name))->getMapping();
                foreach ($indice_mapping[$indice_name] as $type => $properties) {
                    $indice_types[$type] = $type;
                }
            } else {
                $indice_types = array();
            }
        }

        return $indice_types;
    }
}