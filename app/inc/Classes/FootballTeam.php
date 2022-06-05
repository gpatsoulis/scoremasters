<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

class FootballTeam {
    public $name;
    public $origin;
    public $strength;
    public $post_data;

    public function __construct(\WP_Post $post){
        $this->post_data = $post;
    }

    public function get_players(){
        
    }
}