<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;
use Scoremasters\Inc\Abstracts\Competitions;

class SeasonLeagueCompetition extends Competitions {
    public \WP_post $post_object;
    public string $description;
    public string $type;
    public array $participants;
    public $standings;
    public bool $is_active;

    public function __construct(\WP_Post $post){

        if( 'scm-season-competition' !== get_post_type($post)){
            throw new Exception('Scoremasters\Inc\Abstracts\Competition invalid post type post id: ' . $post->ID . ' post type: get_post_type($post)');
        }

        $this->post_object = $post;
        $this->set_participants();
        $this->set_status();
    }

    public function set_participants(){
        //get all users of role = 'player'
        $args = array( 'role' => 'Player' );
        $participants = get_users($args);

        $this->participants = $participants;
    }

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

        $this->is_active = True;
    }

    protected function set_type(){
        //get taxonomy name

        $this->type = 'SeasonLeague';
    }

    public function calculate_score(){
        //όταν ένα παιχνίδι ολοκληρώνεται θα γίνεται ο υπολογισμός του σκορ
    }

    /*
    όταν δημιουργίται νέα διοργάνωση και συνδέεται με μια αγωνιστική σεζόν θα παίρνει ημερομηνία δημοσίευσης την 
    ημερομηνία που ξεκινά η σεζόν.
    */
}