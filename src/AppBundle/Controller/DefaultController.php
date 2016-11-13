<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use AppBundle\Constants\Service;
use AppBundle\Constants\SessionKey;

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

        $this->redirectIfUserActiveInAGame();

        return new Response(
            $this->render('AppBundle:Default:index.html.twig')->getContent()
        );
    }
}
