<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

class League {

    public array $league_participants;
    public $post_data;
    public $status;

    public function __construct(\WP_Post $scm_league){
        $this->post_data = $scm_league;
        $this->league_participants = ScmData::get_league_participants($scm_league);
    }

    /* the league has even number of playeres and the number is greater than 4
    */
    public function headsup_ready():bool{

        //check no of participants greater than 4
        $participants_no = count($this->league_participants);
        if( $participants_no < 4 ){
            return false;
        }

        //check no of participants is even
        if( $participants_no % 2 !== 0 ){
            return false;
        }

        return true;

    }

}