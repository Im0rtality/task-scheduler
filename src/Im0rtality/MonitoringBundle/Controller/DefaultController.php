<?php

namespace Im0rtality\MonitoringBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('Im0rtalityMonitoringBundle:Default:index.html.twig', array('name' => $name));
    }
}
