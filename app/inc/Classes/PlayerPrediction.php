<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

class PlayerPrediction {

    public $predictions; // array['matchID' => ID, 'winner' => 12X, .....]

    public function __construct($post){
        
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

/*
$date_query = array(
    'column'  => 'post_date',
    'compare' => '=',
    array(
        'year' => ,
        'month' =>,
        'day' => 
    )

);

$args = array(
    'author' => PlayerID,
    'post_type' => 'predictions',
    'date_query' => $date_query,
);
*/ 