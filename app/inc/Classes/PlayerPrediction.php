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

    public function get_prediction_data(): array {

        $predictions = $this->prediction;
        
        $prediction_data = array();

        foreach($predictions as $key => $prediction){
            $value = $prediction;

            if($prediction === '-' || $prediction === '' || $key === 'homeTeam_id' || $key === 'awayTeam_id'){
                continue;
            }

            $prediction_data[$key] = $value;
        }

        return $prediction_data;

    }

    public function __toString(): string {
        
        $data = $this->get_prediction_data();

        $str = 'Προβλέψεις Αγώνα --- ';

        foreach( $data as $key => $value ){

            if($key === 'Scorer'){
                $value = (get_post($value))->post_title;
            }

            if($key === 'SHMEIO'){
                $key = 'Σημείο';
            }

            if($key === 'score'){
                $key = 'Score';
            }

            if($key === 'Double Points'){
                $key = 'Διπλασιασμός Πόντων';
            }

            if($value === 'SHMEIO'){
                $value = 'Σημείο';
            }

            $str .= $key . ': ' . $value . " | ";
        }

        return $str;
    }
}