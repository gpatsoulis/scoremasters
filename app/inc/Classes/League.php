<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

class League {

    public $league_participants = array();
    public $post_data;
    public $status;

    public function __construct(\WP_Post $scm_league){
        $this->post_data = $scm_league;
        $this->league_participants = ScmData::get_league_participants($scm_league);
    }

}