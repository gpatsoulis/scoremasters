<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

class FootballMatch {

    /**
    * Post type scm-teams 
    * @param WP_Post $home_team
    */
    public $home_team;

    /**
     * @param int $home_team_dynamikotita
     */
    public $home_team_dynamikotita;

    /**
    * Post type scm-teams 
    * @param WP_Post $home_team
    */
    public $away_team;

    /**
     * @param int $away_team_dynamikotita
     */
    public $away_team_dynamikotita;

    /**
     * Array with scm-pro-players ids
     * @param array $scorers
     */
    public $scorers;

    /**
     * scm-match 
     * @param WP_Post $post_data
     */
    public $post_data;

    /**
     * @param array $points_table
     */
    public $points_table;

    //todo
    /**
     * acf group field
     * @param array $final_score
     */
    public $final_score;
    public $Half_time_score;

    public function __construct(int $match_id){

        $this->post_data = get_post($match_id);
        $this->points_table=get_option('points_table');
    }

    public function setup_data(){

        $this->get_teams();
        $this->get_scorers();
        $this->get_dynamicotites();
        $this->get_score();

        return $this;
    }

    protected function get_teams(){

        $teams = get_field('match-teams',$this->post_data->ID);

        $this->home_team = $teams[0]['home-team'][0];
        $this->away_team = $teams[0]['away-team'][0];

    }

    protected function get_scorers(){

        $acf_scorers = get_field('scm-scorers',$this->post_data->ID);
        $scorers = [];
        foreach($acf_scorers as $acf_score){
            //$match_scorer[] = array('scm-scorers' => $acf_score['scm-scorers'],'scm-goal-minute'=>$acf_score['scm-goal-minute']);
            $scorers[] = $acf_score['scm-scorers'][0]->ID;
        }

        $this->scorers = $scorers;
    }

    protected function get_dynamicotites(){

        $this->dynamikotita_home_team = intval(get_post_meta($home_team->ID));
        $this->dynamikotita_away_team = intval(get_post_meta($away_team->ID));

    }

    protected function get_score(){

        $this->Half_time_score = get_field('scm-half-time-score',$this->post_data->ID);
        $this->final_score = get_field('scm-full-time-score',$this->post_data->ID);

    }

}