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
        $query = $request->request->get('query');
        $from  = $request->request->get('from');
        $limit = $request->request->get('limit');
        if ($query != '') {
            $this->aQuery = json_decode($query, true);

            if ($from != '')   $this->addExtraParams(array('from' => $from));
            if ($limit != '')  $this->addExtraParams(array('size' => $limit));

            $exists_fields = (isset($this->aQuery['fields']));

            $request = (new Query_Request(new Elastica_Client(), 'website/_search', 'POST', array(), $this->aQuery))->send()->getData();
        }

        $page_params = array(
            'results'       => null,
            'query'         => $query,
            'tab'           => 'query',
            'page_title'    => 'Query Tool',
            'from'          => $from,
            'limit'         => $limit
        );

        $fields = array();
        if ($query != '') {
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

            $page_params['total']   = $total;
            $page_params['results'] = $results;
            $page_params['fields']  = $fields;
        }
        return $this->render('ElasticSearchManagementBundle:Query:query.html.twig', $page_params);
    }

    private function addExtraParams($params)
    {
        foreach ($params as $field => $value) {
            $this->aQuery[$field] = $value;
        }
    }
}