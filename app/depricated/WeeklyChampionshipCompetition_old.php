<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Abstracts\Competition;
use Scoremasters\Inc\Base\ScmData;

class WeeklyChampionshipCompetition extends Competition {

    public int $competition_id;
    public int $league_id;
 
    // array of players
    public array $participants;

    // taxonomy slug
    public string $type;
    public bool $is_active;

    public $matchups;
    public $score_tables;

    public function __construct(\WP_Post $scm_competition,\WP_Post $scm_league){

        if( 'scm-competition' !== get_post_type($scm_competition)){
            throw new \Exception(__METHOD__ . ' invalid post type post id: ' . $scm_competition->ID );
        }

        //check taxonomy "weekly-championship"
        $terms_array = get_the_terms($scm_competition->ID, 'scm_competition_type');
        //var_dump($terms_array);

        if(count($terms_array) !== 1){
            throw new \Exception( __METHOD__ .' invalid no terms in scm_competition_type post id: ' . $scm_competition->ID );
        }

        $this->type = $terms_array[0]->slug;

        $this->competition_id = $scm_competition->ID;
        $this->league_id = $scm_league->ID;

        //array of Players
        $this->participants = ScmData::get_league_participants($scm_league);
        $this->is_active = ScmData::competition_is_active($scm_competition);


        // meta key = 'weekly_matchups
        $this->matchups = new WeeklyMatchUps( $scm_competition->ID, $scm_league->ID  );

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