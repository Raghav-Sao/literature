<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class DefaultController extends Controller
{

    /**
     *
     * @return
     */
    public function __construct()
    {
    }

    /**
     *
     * @return Response
     */
    public function indexAction()
    {

        return new Response('Welcome!');
    }
}
