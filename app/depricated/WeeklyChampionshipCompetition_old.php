<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Abstracts\Competition;
use Scoremasters\Inc\Base\ScmData;

class WeeklyChampionshipCompetition extends Competition {

    public \WP_post $competition_object;
    public \WP_post $league_object;
 
    public array $participants;

    // taxonomy slug
    public string $type;

    public $standings;
    
    public bool $is_active;

    public $matchups;

    public function __construct(\WP_Post $scm_competition,\WP_Post $scm_league){

        if( 'scm-competition' !== get_post_type($scm_competition)){
            throw new \Exception(__METHOD__ . ' invalid post type post id: ' . $scm_competition->ID );
        }

        //check taxonomy
        $terms_array = get_the_terms($scm_competition->ID, 'scm_competition_type');
        //var_dump($terms_array);

        if(count($terms_array) !== 1){
            throw new \Exception( __METHOD__ .' invalid no terms in scm_competition_type post id: ' . $scm_competition->ID );
        }

        $this->type = $terms_array[0]->slug;

        $this->competition_object = $scm_competition;
        $this->league_object = $scm_league;
        $this->participants = ScmData::get_league_participants($scm_league);
        $this->is_active = ScmData::competition_is_active($scm_competition);


        // meta key = 'weekly_matchups
        $this->matchups = new WeeklyMatchUps( $scm_competition->ID, $scm_league->ID  );

    }


    public function calculate_players_score( $match_id ){

        $array_of_user_ids = $this->matchups->get_current_matchups();

        if(empty($array_of_user_ids) || $array_of_user_ids === ''){
            return array();
        }

        //get players scores 
        // compare scores for each matchup
        // calculate new score
        // save new score
    }

    public function get_players_shorted_by_score(){
       
        $players_array = $this->participants;

        usort($players_array, array($this,'score_comparator'));

        return $players_array;

    }

    public function score_comparator($player_1,$player_2){
        return $player_1->current_season_points < $player_2->current_season_points;
    }

}