<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Abstracts\Competition;
use Scoremasters\Inc\Base\ScmData;

class CategoryChampionshipCompetition extends Competition {

    public \WP_post $post_object;

    public array $participants;

    // taxonomy slug
    public string $type;

    public $standings;
    
    public bool $is_active;

    public function __construct(\WP_Post $scm_competition,\WP_Post $scm_league){

        if( 'scm-competition' !== get_post_type($scm_competition)){
            throw new \Exception(__METHOD__ . ' invalid post type post id: ' . $post->ID );
        }

        //check taxonomy
        
        //$terms_array = get_the_terms($scm_competition, 'scm_competition_type');
     
        $terms_array = get_the_terms($scm_competition->ID, 'scm_competition_type');
        //var_dump($terms_array);

        if(count($terms_array) !== 1){
            throw new \Exception( __METHOD__ .' inval no terms in scm_competition_type post id: ' . $post->ID );
        }

        $this->type = $terms_array[0]->slug;

        $this->post_object = $scm_league;
        $this->participants = ScmData::get_league_participants($scm_league);
        // todo : fix check for active league
        //$this->is_active = ScmData::league_is_active($scm_competition);
    }

    public function get_players_shorted_by_score(){
       
        if(!$this->is_active){
            //todo short playrs for score in older
            return array();
          }
          
        $players_array = $this->participants;

        usort($players_array, array($this,'score_comparator'));

        return $players_array;

    }

    public function score_comparator($player_1,$player_2){
        return $player_1->current_season_points < $player_2->current_season_points;
    }


    /*
    όταν δημιουργίται νέα διοργάνωση και συνδέεται με μια αγωνιστική σεζόν θα παίρνει ημερομηνία δημοσίευσης την 
    ημερομηνία που ξεκινά η σεζόν.
    */

}