<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        $this->init();

        $this->ensureNoGame();

        $content = $this->render('AppBundle:Default:index.html.twig')
                        ->getContent();

        return new Response($content);
    }

    public function demoAction()
    {

        $content = $this->render('AppBundle:Default:demo.html.twig')
                        ->getContent();

        return new Response($content);
    }
}
