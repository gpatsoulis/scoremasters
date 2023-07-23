<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Abstracts\Competition;
use Scoremasters\Inc\Base\ScmData;

class SeasonLeagueCompetition extends Competition {
    public \WP_post $post_object;

    public string $description;

    public array $participants;

    // taxonomy slug
    public string $type;

    public $standings;
    
    public bool $is_active;

    public function __construct(\WP_Post $post){

        //check if is scm-competiton post type
        if( 'scm-competition' !== get_post_type($post)){
            throw new \Exception( __METHOD__ . ' invalid post type post id: ' . $post->ID );
        }

        // set competition type by taxonomy term 
        $terms_array = get_the_terms($post, 'scm_competition_type');
        if(count($terms_array) !== 1){
            throw new \Exception(__METHOD__ .'  invalid post term post id: ' . $post->ID );
        }
        $this->type = $terms_array[0]->slug;

        $this->post_object = $post;

        $all_scm_users = ScmData::get_all_scm_users();

        $this->participants = ScmData::get_all_participants($all_scm_users);

        $this->is_active = ScmData::competition_is_active($post);
    }


    //set competion status - 'active'/'inactive'
    protected function set_status(){
        $scp_season = get_field('scm-season-competition',$this->post_object->ID);

        //todo: set acf fields
        //$fields = get_fields($scp_season->ID);
        // check if current season
        //todo: set dates
        /*if($start_date <= $today && $today <= $end_date){
            $this->$is_active = True;
            return;
        }  */ 

        $this->status = 'active';
    }

    protected function set_type(){
        // get taxonomy name
        // taxonomy type string 'scm_competition_type' is configuration 
        // todo: move to setup  or interface file

        $terms = get_the_terms( $this->$post_object, 'scm_competition_type' );

        if(false === $terms || is_wp_error( $terms )){
            throw new \Exception( __METHOD__ . ' no terms in scm_competition_type post id: ' . $this->$post_object->ID);
        }

        //use slug or term id
        $this->type = $terms[0]->slug;
    }

    public function get_players_shorted_by_score(): array {
       if(!$this->is_active){
         //todo short playrs for score in older
         return array();
       }
        $players_array = $this->participants;

        usort($players_array, array($this,'score_comparator'));

        return $players_array;

    }

    public function score_comparator($player_1,$player_2){
        //return $player_1->current_season_points < $player_2->current_season_points;
        return $player_2->current_season_points <=> $player_1->current_season_points ;
    }


    /*
    όταν δημιουργίται νέα διοργάνωση και συνδέεται με μια αγωνιστική σεζόν θα παίρνει ημερομηνία δημοσίευσης την 
    ημερομηνία που ξεκινά η σεζόν.
    */
}