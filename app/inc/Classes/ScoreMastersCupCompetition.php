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

         $this->post_object = $competition;

    }

    public function init_get_participants(){
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

        if($curent_seasonleague->ID < 0){
            error_log( __METHOD__ . ' error init_get_participants');
            $this->participants = array();
            return;
        }

        $season_league = new SeasonLeagueCompetition( $curent_seasonleague );
        $players = $season_league->get_players_shorted_by_score();

        $participants = array_slice($players,0,$participants_no);

        $this->participants = [ [ 'round' => 0, 'participants' => $participants ] ];

    }

    public function save_participants( $participants ):bool {

        // save at end of each round

        $season = get_post_meta( $this->post_object->ID, 'scm-season-competition', true  );

        $season_id = $season[0];

        if( $season_id === false || $season_id === '' ){
            error_log( __METHOD__ . '  meta($season_id) invalid $post_id (non-numeric, zero, or negative value) id: ' . $this->post_object->ID);
            return false;
        }

    
        $key = 'scm_cup_participants_seasonID_' . $season_id;

        $current_participants = get_post_meta( $this->post_object->ID, $key, false);

        if( $current_participants === false){
            error_log( __METHOD__ . ' meta($current_participants) invalid $post_id (non-numeric, zero, or negative value) id: ' . $this->post_object->ID);
            return false;
        }

        // get the last round saved and add +1 for new
        $round = 0;
        $last_entry = current($current_participants);
        if(isset( $last_entry['round'] ) ){
            $round = $last_entry['round'] + 1;
        }


        $data = [ 'round' => $round, 'participants' => $participants ];

        $current_participants[] = $data;

        $success = update_post_meta($this->post_object->ID, $key, $current_participants );

        if( !$success ){
            error_log( __METHOD__ . ' error updating meta ' . $key . ' round: ' . $round);
            return false;
        }

        return true;

    }



    //save participants for each round
    // participants: [ 'round' => int, [ phase => int, participants => array [ player_id ], ... ]

}