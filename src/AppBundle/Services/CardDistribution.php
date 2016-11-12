<?php

namespace AppBundle\Services;

class CardDistribution
{
    function __construct($redis)
    {
        $this->redis = $redis;
    }
    public function CardDistribution() {

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
                    $this->redis->SADD("game1_user1", $total_card[$i*4+$j]);
                }
                if($i%4==1){
                    $this->redis->SADD("game1_user2", $total_card[$i*4+$j]);
                }
                if($i%4==2){
                    $this->redis->SADD("game1_user3", $total_card[$i*4+$j]);
                }
                if($i%4==3){
                    $this->redis->SADD("game_1user4", $total_card[$i*4+$j]);
                }
            }
        }
        return "ok";
    }
    public function InitializeGame($session, $pusher) {
        $is_new_game = true;
        $game_id = $session->get("game_id");
        if($game_id) { //Alredy game Started we set game_id afetr user so user is alredy
            $is_new_game = false;
            return $game_id."_old";
        }
        $game_id = md5(uniqid(rand(), true));
        $session_id = $session->getId();
        $this->redis->hMset($game_id, "start_at", "today", "game_id", $game_id, "total_user" , 1); //seting gmae id in HMSET
        $user_list = $game_id . '_user_list';
        $this->redis->hMset($user_list, "game_id", $game_id, 1 , $session_id); //set user in HMSET
        $session->set('game_id', $game_id);

        // $data['message'] = 'started';
        // $pusher->trigger('test_channel', 'my_event', $data);
        return $game_id;
    }
    public function AddMember($passed_game_id, $user, $session) {
        $game_id = $session->get('game_id');
        if($game_id) {
            // $response = new Response($game_id."_old", Response::HTTP_OK, array('content-type' => 'text/html'));
            return "_old"; //redirect to game
        }
        if(!$this->redis->hGetAll($passed_game_id) || empty($passed_game_id)) {
            return "no such game:" . $passed_game_id;
        }
        if(!is_numeric($user) || $user > 4 || $user < 1) {
            return "invalid user:" . $user;   
        }
        $user_list = $passed_game_id . '_user_list';
        if($this->redis->hExists($user_list, $user)) {
            return "alredy full for this position". $user_list ."  n " .$user; //redirect
        }
        $session_id = $session->getId();
        $session->set("game_id", $passed_game_id);
        $this->redis->hMset($user_list, $user , $session_id); //set user in HMSET
        $this->redis->hIncrBy($passed_game_id, "total_user", 1); //increase total user count
        return $user_list;
    }
}


