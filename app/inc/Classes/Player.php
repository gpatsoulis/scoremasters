<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

class Player {

    public $wp_user;
    public $stored_predictions;

    public function __construct(WP_User $user){
        $this->wp_user = $user;
        $this->predictions = $this->get_stored_predictions($user);
    }

    public function get_stored_predictions($user){

        if(!metadata_exists( 'user', $user->ID, 'stored_predictions' )){
            throw new Exception('Non existent \"stored_predictions\" metadata  for user with id: ' . $user->ID);
        }

        $stored_predictions = get_user_meta($user->ID,'stored_predictions');

        return $stored_predictions;
    }

    public function make_prediction(PlayerPrediction $prediction ){

    }
   
}


/*
prediction schema

{   
    match_id: (int),
    winner: (string) 1,2,X
    scorrer: (array) [ (int) FootballPlayer->id]
    under-over: ???,
    Double: (bool) true/false //use twice per week
}

*/