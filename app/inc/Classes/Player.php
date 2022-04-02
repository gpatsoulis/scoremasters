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


//
add_action('user_register','setup_custom_user_meta');

function setup_custom_user_meta($user){

    if(metadata_exists( 'user', $user->ID, 'stored_predictions' )){
        throw new Exception('\"stored_predictions\" metadata exists for user with id: ' . $user->ID);
    }

    $id = update_user_meta( $user->ID, 'stored_predictions', '', true );

    if( true === $id){
        throw new Exception('\"stored_predictions\" metadata exists for user with id: ' . $user->ID);
    }

    if( false === $id){
        throw new Exception('fail to create \"stored_predictions\" metadata for user with id: ' . $user->ID);
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