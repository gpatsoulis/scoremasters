<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Abstracts\Competition;
use Scoremasters\Inc\Classes\SeasonLeagueCompetition;
use Scoremasters\Inc\Base\ScmData;

class ScoreMastersCupCompetition extends Competition {

    public \WP_post $post_object;

    public string $description;

    public array $participants;

    // taxonomy slug
    public string $type;

    //public $standings;

    public bool $is_active;

    //cup rounds
    public array $rounds;


    public function __construct( \WP_Post $competition ){

         //check if is scm-competiton post type
         if( 'scm-competition' !== get_post_type($competition)){
            throw new \Exception( __METHOD__ . ' invalid post type post id: ' . $competition->ID );
        }

         // set competition type by taxonomy term 
         $terms_array = get_the_terms($competition, 'scm_competition_type');
         if(count($terms_array) !== 1){
             throw new \Exception(__METHOD__ .'  invalid post term post id: ' . $competition->ID );
         }

         $this->type = $terms_array[0]->slug;

         $this->post_object = $post;

    }

    public static function init_get_participants(){
        //get all players
        $all_players = ScmData::get_all_players();

        $players_no = count($all_players);
        //var_dump($players_no);
        
        $power = 0;
        $number = 2;
        
        $exp = pow($number,$power);
        
        while (($players_no - $exp) > 0) {
        
            $power += 1;
            $exp = pow($number,$power);
        }
        
        $participants_no = pow($number,$power - 1);
        
        // get the first XX players with the highest score
        
        $curent_seasonleague = ScmData::get_current_scm_competition_of_type('season-league');
        $season_league = new SeasonLeagueCompetition( $curent_seasonleague );
        $players = $season_league->get_players_shorted_by_score();

        $participants = array_slice($players,0,$participants_no);

        $this->participants = $participants;

    }

}