<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

class PlayerPrediction {

    public $player_id;
    public $match_id;
    public $prediction; // array['matchID' => ID, 'winner' => 12X, .....]

    public function __construct(\WP_Post $prediction){

        //check post type
        if( 'scm-prediction' !== get_post_type($prediction)){
            throw new \Exception( __METHOD__ . ' invalid post type post id: ' . $post->ID );
        }

        $prediction_data = unserialize($prediction->post_content);

        $this->prediction = $prediction_data;
        $this->player_id = $prediction->post_author;
        $this->match_id = (explode('-',$prediction->post_title))[0];
        
    }

    public function set_data(){
        /*
        prediction data will be stored as content
        with format:
        ------------------- Post Content -------------------
        matchID: id,
        winner: 12X,
        Scorer: PlayerID,
        UO: U/O,
        Double: true/false;
        ----------------------------------------------------
        Player prediction will have the same date as 
        the match to which it refers
        ----------------------------------------------------
        The Player will be the author of the post with post type prediction
        */
    }
}