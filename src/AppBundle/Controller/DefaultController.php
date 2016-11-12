<?php

namespace AppBundle\Controller;

// use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends Controller
{

    public function __construct()
    {
    }

    public function indexAction()
    {

        return new Response('Welcome!');
    }
}

