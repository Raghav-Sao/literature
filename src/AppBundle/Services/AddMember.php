<?php

namespace AppBundle\Services;

class AddMember
{
    function __construct($initializeGame, $asdf)
    {
        $this->initializeGame = $initializeGame;
        $this->asdf = $asdf;
    }
    public function AddMember($user, $session) {
        // $game_id = $session->get('game_id');
        // if($game_id) {
        //     return $game_id."_old";
        // }
        // $this->initializeGame->initializeGame();
        // return $game_id."_new";
        return "dd";
    }
}


