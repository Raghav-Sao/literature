<?php

namespace AppBundle\Services;

use AppBundle\Models;

/**
 *
 */
class Game extends BaseService
{

    protected $redis;
    protected $pubSub;
    protected $knowledge;

    /**
     * @param object                          $logger
     * @param object                          $redis
     * @param Services\PubSub\PubSubInterface $pubSub
     * @param Services\Knowledge              $knowledge
     *
     * @return
     */
    public function __construct(
        $logger,
        $redis,
        PubSub\PubSubInterface $pubSub,
        Knowledge $knowledge)
    {
        parent::__construct($logger);

        $this->redis     = $redis;
        $this->pubSub    = $pubSub;
        $this->knowledge = $knowledge;
    }

    /**
     * @param string $gameId
     *
     * @return null|Models\Game
     */
    public function fetchById(
        string $gameId)
    {
        $redisGameResults = $this->redis->hgetall($gameId);

        if (empty($redisGameResults)) {

            return null;
        } else {

            return new Models\Game($gameId, $redisGameResults);
        }
    }

    /**
     * @param Models\Game $game
     *
     * @return null
     */
    public function delete(
        Models\Game $game)
    {
        // TODO
        // - Clean redis data for given gameId
    }

    /**
     * @param string $userId
     *
     * @return null|Models\User
     */
    public function fetchUserById(
        string $userId)
    {
        $redisUserCards = $this->redis->smembers($userId);

        if (empty($redisUserCards)) {

            return null;
        } else {

            return new Models\User($userId, $redisUserCards);
        }
    }

    /**
     * @param string $sessionId
     *
     * @return null
     */
    public function initializeGame(
        string $gameId,
        string $createdAt,
        string $userId)
    {
        list($CardU1, $CardU2, $CardU3, $CardU4) = $this->CardDistribution();
        var_dump($gameId);
        $redisInitializeResults = $this->redis->hMset(
            $gameId,
            "created_at",   $createdAt, 
            "total_user" ,  1,
            "u1",           $sessionId,
            "status",       "active",
            "next_turn",    "u1",
            "u1_card",      implode(" ", $CardU1)
        );


    }


    private function CardDistribution() {

        $total_card = array();
        $card_color = ['C', 'D', 'H', 'S'];
        while(count($total_card) < 48) {
            $color = (int)(rand(1,51)/13);
            $card_no = (int)(rand(4,52)/4);
            $card =   $card_color[$color] . $card_no;
            if ( !in_array( $card, $total_card ) && $card_no != 7) {
                array_push($total_card, $card);
            }
        }
        $user1 = array();
        $user2 = array();
        $user3 = array();
        $user4 = array();
        for($i=0; $i<12; $i++) {
            for($j=0; $j<=3; $j++) {
                if($i%4==0){
                    array_push($user1, $total_card[$i*4+$j]);
                }
                if($i%4==1){
                    array_push($user2, $total_card[$i*4+$j]);
                }
                if($i%4==2){
                    array_push($user3, $total_card[$i*4+$j]);
                }
                if($i%4==3){
                    array_push($user4, $total_card[$i*4+$j]);
                }
            }
        }
        return array($user1, $user2, $user3, $user4);
    }
}
