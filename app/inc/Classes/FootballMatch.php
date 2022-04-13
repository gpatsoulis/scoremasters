<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

class FootballMatch {
    public $home_team;
    public $away_team;
    public $scorers;
    public $post_data;

    public function __construct(\WP_Post $post){
        $this->post_data = $post;
    }

    public function get_score(){

    }
}