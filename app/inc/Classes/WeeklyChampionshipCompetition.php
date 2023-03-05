<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Abstracts\Competition;
use Scoremasters\Inc\Base\ScmData;

class WeeklyChampionshipCompetition extends Competition {

    public int $competition_id;
    
    public string $type = 'weekly-championship';
    public bool $is_active;
    public WeeklyMatchUps $matchups;
    public $closure;

    public function __construct(\WP_Post $scm_competition, WeeklyMatchUps $matchups){

        if( 'scm-competition' !== get_post_type($scm_competition)){
            throw new \Exception(__METHOD__ . ' invalid post type post id: ' . $scm_competition->ID );
        }

        //check taxonomy "weekly-championship"
        $terms_array = get_the_terms($scm_competition->ID, 'scm_competition_type');


        if(count($terms_array) !== 1){
            throw new \Exception( __METHOD__ .' invalid no terms in scm_competition_type post id: ' . $scm_competition->ID );
        }

        //$this->type = $terms_array[0]->slug;

        $this->competition_id = $scm_competition->ID;
        
        //wtf
        $this->is_active = ScmData::competition_is_active($scm_competition);

        $this->matchups = $matchups;

        // meta key = 'weekly_matchups
    }

    public function get_participants_by_league_id( $league_id ){

        $league = get_post($league_id);
        $participants = ScmData::get_league_participants($league);
        

        $this->closure =  function () use ( $participants ){

            if(!$this->is_active){
                //todo short players for score in older seasons
                //return array();
            }
            
            usort($participants, array($this,'score_comparator'));
            return $participants;
        };

        return $this;

    }

    public function score_comparator($player_1,$player_2){
        return $player_2->weekly_competition_points <=> $player_1->weekly_competition_points ;
    }

    public function short( ){
        
        $func = $this->closure;

        //todo: 
        return $func();
        
    }
}