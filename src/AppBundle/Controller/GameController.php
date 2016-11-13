<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class GameController extends BaseController
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
    public function startAction()
    {
        $this->redirectIfUserActiveInAGame();
        $game_id = md5(uniqid(rand(), true));
        $session_id = $session->getId();
        $this->redis->hMset($game_id, 
                                        "start_at",     "today", 
                                        "game_id",      $game_id, 
                                        "total_user" ,  1,
                                        "user1",        $session_id
                                        ); //seting gmae id in HMSET
        
        $session->set('game_id', $game_id);

        // $data['message'] = 'started';
        // $pusher->trigger('test_channel', 'my_event', $data);
        return $game_id;
        return new response("s");

    }

    /**
     *
     * @return
     */
    public function joinAction()
    {
    }

    // /**
    //  * @Route("/start-game", name="start-game")
    //  */
    // public function startGame(Request $request)
    // {
    //     $session = $request->getSession();
    //     if(!$session->isStarted()) {
    //         $session->start();
    //     }
    //     $options = array('encrypted' => true);
    //     $pusher = new Pusher('0a4e78670f01ad56c33a', '9fdc265469c62346177d', '268031', $options);
    //     $initializeGameService = $this->container->get('app_bundle.initialize_game');
    //     $user1 = $initializeGameService->InitializeGame($session, $pusher);
    //     $response = new Response($user1, Response::HTTP_OK, array('content-type' => 'text/html'));
    //     return $response;
    // }


    // /**
    //  * @Route("/add-member", name="add-member")
    //  */
    // public function addGameMember(Request $request)
    // {
    //     $session = $request->getSession();
    //     if(!$session->isStarted()) {
    //         $session->start();
    //     }
    //     $session_id = $session->getId();
    //     $game_id = $request->query->get('game_id');
    //     $user = $request->query->get('user');
    //     $initializeGameService = $this->container->get('app_bundle.add_member');
    //     $result = $initializeGameService->addMember($game_id, $user, $session);
    //     $response = new Response($result, Response::HTTP_OK, array('content-type' => 'text/html'));
    //     return $response;
    // }

    // /**
    //  * @Route("/", name="root")
    //  */
    // public function indexAction(Request $request)
    // {
    //     $options = array('encrypted' => true);
    //     $pusher = new Pusher('0a4e78670f01ad56c33a', '9fdc265469c62346177d', '268031', $options);
    //     $data['message'] = 'hello world';
    //     $pusher->trigger('test_channel', 'my_event', $data);
    //     $cardDistributionService = $this->container->get('app_bundle.card_distribution');
    //     $user1 = $cardDistributionService->CardDistribution();
    //     $response = new Response($user1, Response::HTTP_OK, array('content-type' => 'text/html'));
    //     return $response;
    // }

    // /**
    //  * @Route("/pub-sub", name="pub-sub")
    //  */
    // public function pubSubAction(Request $request)
    // {
    //     return $this->render('default/pub-sub.html', [
    //         'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
    //     ]);
    // }
}
