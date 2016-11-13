<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Constants\Service;
use AppBundle\Constants\SessionKey;
use AppBundle\Utility;

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
        $this->init();

        try {
            
            $this->redirectIfUserActiveInAGame();
            $session = $this->session;
            $userId = $session->getId();
            $gameId = Utility::newGameId();
            $createdAt = Utility::currentTimeStamp();
            list($game, $user) = $this->gameService->initializeGame($gameId, $createdAt, $userId);
            $session->set('g_id', $gameId);
        } catch (\Exception $e) {

            return $this->handleException($e);
        }
        return new JsonResponse([
            "game" => $game->toArray(),
            "user" => $user->toArray(),
        ]);
        // // $data['message'] = 'started';
        // // $pusher->trigger('test_channel', 'my_event', $data);
    }

    /**
     *
     * @return JsonResponse
     */
    public function indexIdAction($id)
    {
        $this->init();

        try {

            $game = $this->gameService->fetchById($id);

            $this->gameService->validateGame($game, $this->userId);

            $user = $this->gameService->fetchUserById($this->userId);

        } catch (\Exception $e) {

            return $this->handleException($e);
        }

        return new JsonResponse([
            "game" => $game->toArray(),
            "user" => $user->toArray(),
        ]);
    }

    /**
     *
     * @return
     */
    public function joinAction()
    {
    }


    /**
     * @param string $id
     * @param string $card
     * @param string $fromUserId
     * @param string $toUserId
     */
    public function moveCardAction(
        string $id,
        string $card,
        string $fromUserId,
        string $toUserId)
    {

        // Using session data
        // $id       = $this->gameId
        // $toUserId = $this->userId

        $this->init();
        // $this->validateGame();

        // His turn
        // Validate move conditions; At least one card of a color
        // Update data
        // Check game status - Win/Loose etc
        // Publish response data too
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
