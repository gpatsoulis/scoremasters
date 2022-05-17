<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

class League {

    public $league_players = array();
    public $post_data;

    public function __construct(\WP_Post $post){
        $this->post_data = $post;
    }

    public function setup_players(){

    }

    public function calculate_score(){
        
    }
}