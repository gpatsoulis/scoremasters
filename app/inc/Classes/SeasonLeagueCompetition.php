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

    public string $type;

    public $standings;
    
    public bool $is_active;

    public function __construct(\WP_Post $post){

        if( 'scm-season-competition' !== get_post_type($post)){
            throw new Exception('Scoremasters\Inc\Classes\SeasonLeagueCompetition invalid post type post id: ' . $post->ID . ' post type: get_post_type($post)');
        }

        $this->post_object = $post;

        $all_scm_users = ScmData::get_all_scm_users();

        $this->participants = ScmData::get_all_participants($all_scm_users);

        $this->is_active = ScmData::league_is_active($post);
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
            throw new Exception('Scoremasters\Inc\Classes\SeasonLeagueCompetition no terms in scm_competition_type post id: ' . $this->$post_object->ID);
        }

        //use slug or term id
        $this->type = $terms[0]->slug;
    }

    public function calculate_score(){
        //όταν ένα παιχνίδι ολοκληρώνεται θα γίνεται ο υπολογισμός του σκορ
        //get all players for competition
        //get player points
        //add points to existing points
        //save new points to db
        $players_array = $this->get_participatns();

    }


    /*
    όταν δημιουργίται νέα διοργάνωση και συνδέεται με μια αγωνιστική σεζόν θα παίρνει ημερομηνία δημοσίευσης την 
    ημερομηνία που ξεκινά η σεζόν.
    */
}