<?php
/**
 * @package scoremasters
 *
 *
 */
namespace Scoremasters\Inc\Base;

use Scoremasters\Inc\Services\CalculateScoremastersCupPoints;

class ScoremastersCupSetup {

    public static function init()
    {
        add_action('new_fixture_published_event', array(static::class, 'trigger_players_cup_point_calculation'), 10, 1);
    }

    public static function trigger_players_cup_point_calculation( \WP_Post $fixture_post){
        

        if( $fixture_post->post_type !== 'scm-fixture'){
            return;
        }

        $prev_fixture = ScmData::get_previous_fixture();

        if(SCM_DEBUG){
            error_log( __METHOD__ . ' ----EVENT---- calculating cup points for fixture: ' .  $prev_fixture->ID);
        }

      

        $cup_competition_phase_array = ScmData::get_competition_phases_by_fixture_id($prev_fixture->ID );
        if(empty( $cup_competition_phase_array )){
            if(SCM_DEBUG){
                error_log( __METHOD__ . ' ---- no cup phase for fixture: ' .  $prev_fixture->ID);
            }
            return;
        }

        $cup_competition_phase = $cup_competition_phase_array[0];

        
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

        $cup_matchups = $matchups;
        $score_array = CalculateScoremastersCupPoints::calculate( $cup_matchups , $prev_fixture->ID);
        // $score_array = array [ [0:[player_id,cup_points],1:[player_id,cup_points]],[] ... ]

         //save points
        foreach( $score_array as $players_pairs ){
            
            $home_player_id = $players_pairs[0]['player_id'];
            $home_player_score = $players_pairs[0]['cup_points'];

            $away_player_id = $players_pairs[1]['player_id'];
            $away_player_score = $players_pairs[1]['cup_points'];
            
            $score_masters_cup_home = array(
                'score' => $home_player_score,
                'opponent_id' => $away_player_id,
                'phase_id'=> $cup_competition_phase->ID,
            );

            $score_masters_cup_away = array(
                'score' => $away_player_score,
                'opponent_id' => $home_player_id,
                'phase_id'=> $cup_competition_phase->ID,
            );

            if(SCM_DEBUG){
                //error_log();
            }

           
            //ugly 
            //todo: create players entity objects with save functionality

            //home player
            $season = ScmData::get_current_season();
            $home_payer_score_meta = get_user_meta( $home_player_id ,'score_points_seasonID_' . $season->ID,true);
            if(isset($home_payer_score_meta['fixture_id_' . $prev_fixture->ID ]['score-masters-cup'])){
                error_log( __METHOD__ . ' ---- error rewriting score data from scoremasters cup to players score meta');
            }
            $home_payer_score_meta['fixture_id_' . $prev_fixture->ID ]['score-masters-cup'] = $score_masters_cup_home;
            $home_success = update_user_meta($home_player_id ,'score_points_seasonID_' . $season->ID, $home_payer_score_meta);
            if( $home_success == false){
                error_log( __METHOD__ . ' error updating player\'s scoremasters cup data, home player_id: ' . $home_player_id . ' for fixture_id: ' . $prev_fixture->ID);
            }

            //away player
            $away_payer_score_meta = get_user_meta( $away_player_id ,'score_points_seasonID_' . $season->ID,true);
            if(isset($away_payer_score_meta['fixture_id_' . $prev_fixture->ID ]['score-masters-cup'])){
                error_log( __METHOD__ . ' ---- error rewriting score data from scoremasters cup to players score meta');
            }
            $away_payer_score_meta['fixture_id_' . $prev_fixture->ID ]['score-masters-cup'] = $score_masters_cup_away;
            $away_success = update_user_meta($away_player_id ,'score_points_seasonID_' . $season->ID, $away_payer_score_meta);
            if( $away_success == false){
                error_log(__METHOD__ . ' error updating player\'s scoremasters cup data, away player_id: ' . $away_player_id . ' for fixture_id: ' . $prev_fixture->ID);
            }
        }

        /*
        [ 'total_points' => ['season-league' => int,'weekly-championship' => int]
          'fixture_id_3709' => [ 
            'match_id_3631' => ['season-league' => ['points' => int ]], 
            'match_id_3637' => ...,
            'weekly-championship' => [ 'points' => int,'score' => int,'opponent_id' => int,'home_field_advantage' => boolean],
            'score-masters-cup' => [ 'score' => int ,'opponent_id' => int, 'phase_id'=> int]
            ]
        ]
        */
        

    }
}