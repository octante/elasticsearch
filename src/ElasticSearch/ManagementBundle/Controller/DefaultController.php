<?php

namespace ElasticSearch\ManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ElasticSearchManagementBundle:Default:index.html.twig');
    }
}
