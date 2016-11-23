<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class DefaultController extends BaseController
{

    /**
     *
     * @return
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * @return Response
     */
    public function indexAction()
    {
        $this->init();

        try {
            $this->checkIfUserActiveInAGame();
        } catch (\Exception $e) {
            return $this->handleException($e);
        }

        return new Response(
            $this->render("AppBundle:Default:index.html.twig")->getContent()
        );
    }
}
