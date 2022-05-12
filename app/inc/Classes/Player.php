<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Base\ScmData;

class Player {

    public $wp_player;

    public function __construct(\WP_User $user){
        
        $this->wp_player = $user;

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

    public function get_current_week_predictions(){

        $current_fixture = ScmData::get_current_fixture();

        $player_predictions = ScmData::get_all_player_predictions_for_fixture($current_fixture, $this->wp_player->ID);
        
        return $player_predictions;
    }


    public function can_play_double():bool {

        $predictions = $this->get_current_week_predictions();


        if(empty($predictions)){
            error_log('there are no predictions');
            return true;
        }

        $double_counter = 0;

        foreach( $predictions as $prediction ){
            $match_prediction = unserialize($prediction->post_content);

            $double = $match_prediction['Double Points'];

            if($match_prediction['Double Points'] !== ''){
                $double_counter += 1;
            }

            if($double_counter == 2){
                return false;
            }
        }

        return true;
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