<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\CategoryChampionshipCompetition;
use Scoremasters\Inc\Classes\Player;

//[Scoremasters\Inc\Shortcodes\CupShortcode]
class CupShortcode
{
    public $template;
    public $name;

    public function __construct()
    {
        $this->name = static::class;

        $this->get_template();
    }

    public function register_shortcode()
    {
        add_shortcode($this->name, array($this, 'output'));
    }

    public function output(){

        //curent cup round - phase
        //player pairs
        //fixtures for cup round

        $cup_competition_phase = ScmData::get_current_phase_for_competition('score-masters-cup');

        if( $cup_competition_phase->post_title == 'default'){
            $cup_competition_phase = ScmData::get_current_phase_for_competition('score-masters-cup','future');
        }



        $output = $this->template->container_start;

        if( $cup_competition_phase->post_title == 'default' ){
            return $output = '<!-- No Cup Competition -->' ;
        }

        // competition data
        $competition = get_field('scm-related-competition', $cup_competition_phase->ID)[0];
        $competition_season = get_field('scm-season-competition', $competition->ID)[0];
        $cup_phase_fixtures_array = get_field('scm-related-week', $cup_competition_phase->ID);

        //get pairs for current round-phase
        $matchups = $this->get_matchups_array( $cup_competition_phase );
    
        
        $template_data = $this->get_template_data( $matchups,$cup_phase_fixtures_array,$competition_season);

        foreach($template_data as $data){
            $output .= $this->template->get_html($data);
        }

        $output .= $this->template->container_end;
        $output .= $this->template->get_css();

        return $output;

    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\CupTemplate('div', 'scm-cup-score', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }

    public function get_matchups_array(\WP_Post $cup_competition_phase ):array {


        //$competition_phase_array = get_post_meta( $current_fixture->ID, 'competition_phase', true);


        $acf_matchups = get_field('groups__headsup', $cup_competition_phase->ID);
        // $matchups array of wp_users
        $matchups = [];
        foreach ($acf_matchups as $group) {
            $players = [];
            foreach ($group['group__headsup'] as $acf_player) {
                $players[] = $acf_player['scm-group-player'];
            }

            $matchups[] = $players;
        }

        return $matchups;
    }

    public function get_template_data( $matchups, $fixtures_array,$competition_season ){

        
        $output_data = [];

        foreach( $matchups as $players_pair){

            $data = [];
            $data['p1_name'] = $players_pair[0]->display_name;
            $data['p2_name'] = $players_pair[1]->display_name;


          

            foreach( $fixtures_array as $fixture_obj ){

                $score_data_p1 = $this::get_points_per_fixture($players_pair[0],$fixture_obj->ID,$competition_season);
                $score_data_p2 = $this::get_points_per_fixture($players_pair[1],$fixture_obj->ID,$competition_season);

                $data['rounds'][] = [
                    'fixture'   => $fixture_obj, 
                    'p1_points' => $score_data_p1['fixture_points'],
                    'p1_score'  => $score_data_p1['cup_score'],
                    'p2_points' => $score_data_p2['fixture_points'],
                    'p2_score'  => $score_data_p2['cup_score'],
                ];
            }

            $output_data[] = $data;

        }

        return $output_data;

        /*
    [ 0:
       [ p1_name: string, 
         p2_name: string, 
         p1_score: int, 
         p2_score: int, 
         rounds:[ 0:[fixture: \WP_Post,p1_points: int, p2_points:int ],
                  1:[fixture: \WP_Post,p1_points: int, p2_points:int ],  
                ] 
        ],
      1:[ .... ],
    ]

      */
    }

    private function get_points_per_fixture( $player, $fixture ,$competition_season ): array {


        $key = 'score_points_seasonID_' . $competition_season->ID; 
        $score = get_user_meta( $player->ID, $key, true );
        $fixture_id = 'fixture_id_' . $fixture;

        

        if(!isset( $score[ $fixture_id ][ 'score-masters-cup' ])){
            return [ 'cup_score' => 0,'fixture_points' => 0 ] ;
        }

        $score = $score[ $fixture_id ][ 'score-masters-cup' ][ 'score' ];
        $points = $score[ $fixture_id ][ 'weekly-championship' ][ 'points' ];


        return ['cup_score' => $score,'fixture_points' => $points];
    }

}

