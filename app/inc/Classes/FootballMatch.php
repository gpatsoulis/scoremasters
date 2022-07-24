<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

class FootballMatch {

    public $match_id;
    public $match_date;
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
    public $half_time_score;

    public function __construct(int $match_id){

        $this->match_id = $match_id;

        //remove out of construct and do checks there
        $wp_match = get_post($match_id);

        if(is_null($wp_match)){
            error_log(static::class . ' error match id -> get_post returned null');
        }

        $this->post_data = $wp_match;
        $this->match_date =  new \DateTime($wp_match->post_date, new \DateTimeZone('Europe/Athens'));
        
        $points_table = get_field('scm_custom_match_points_table', $match_id);
        $this->points_table=$points_table;
        if(!$points_table){
            $this->points_table=get_option('points_table');
        }

        
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
        var_dump($acf_scorers);
        if(!$acf_scorers){
            $this->scorers = array();
            return; 
        }

        $scorers = [];
        foreach($acf_scorers as $acf_score){
            
            //$match_scorer[] = array('scm-scorers' => $acf_score['scm-scorers'],'scm-goal-minute'=>$acf_score['scm-goal-minute']);
            $scorers[] = $acf_score['scm-scorers'][0]->ID;
        }

        $this->scorers = $scorers;
    }

    protected function get_dynamicotites(){

        //$this->home_team_dynamikotita = intval(get_field('scm-team-capabilityrange',$this->home_team->ID));
        $this->home_team_dynamikotita = intval(get_post_meta( intval($this->home_team->ID), 'scm-team-capabilityrange', true ));
        //$this->away_team_dynamikotita = intval(get_field('scm-team-capabilityrange',$this->away_team->ID));
        $this->away_team_dynamikotita = intval(get_post_meta( intval($this->home_team->ID), 'scm-team-capabilityrange', true ));


    }

    protected function get_score(){

        $this->half_time_score = get_field('scm-half-time-score',$this->post_data->ID);
        $this->final_score = get_field('scm-full-time-score',$this->post_data->ID);

    }

}