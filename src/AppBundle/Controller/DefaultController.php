<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        $this->init();

        $this->checkIfUserActiveInAGame();

        $content = $this->render('AppBundle:Default:index.html.twig')
                        ->getContent();

        return new Response($content);
    }
}
