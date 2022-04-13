<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

class FootballPlayer {
    public $name;
    public $post_data;
    public $performance;

    public function __construct(\WP_Post $post){
        $this->post_data = $post;
    }

    public function get_team(){
        
    }

    public function get_performance(){

    }

    //test
}