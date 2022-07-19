<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Services;


class CalculateWeeklyPoints
{

    public $match_id;
    public $fixture_id;
    public $season_id;
    public $matchups;


    public function __construct(array $match_data, array $matchups  )
    {

        $this->match_id = $match_data['match_id'];
        $this->fixture_id = $match_data['fixture_id'];
        $this->season_id = $match_data['season_id'];
        $this->matchups = $matchups;

    }

    public function calculate(  ){

        $match_id = $this->match_id;
        $current_matchups = $this->matchups;
        $current_season_id = $this->season_id;
        $current_fixture_id = $this->fixture_id;


        //compare
        //if there's no score for player score = 0, opponents score is 3
        $points_array = [];
        for( $i = 0; $i < count($current_matchups); $i += 2){

            if(SCM_DEBUG){
                //var_Dump($current_matchups);
            }
            
            $home_player_id = $current_matchups[$i];
            $home_player_points_array = get_user_meta( intval($home_player_id), 'score_points_seasonID_' . $current_season_id, true);
            if($home_player_points_array === ''){
                $home_player_points_array = array();
            }

            if(SCM_DEBUG){
                var_dump($home_player_points_array);
            }
            
            if(!isset($home_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['season-league']['points'])){
                $home_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['season-league']['points'] = 0;
            }
            $home_player_points = $home_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['season-league']['points'];

            $away_player_id = $current_matchups[$i+1];
            $away_player_points_array = get_user_meta( intval($away_player_id), 'score_points_seasonID_' . $current_season_id, true);
            if($away_player_points_array === ''){
                $away_player_points_array = array();
            }
            if(!isset($away_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['season-league']['points'])){
                $away_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['season-league']['points'] = 0;
            }
            $away_player_points = $away_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['season-league']['points'];


            $score_diff = intval($home_player_points) - intval($away_player_points);

            //check if score is already calculated
            if( isset($home_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['weekly-championship']['points'])
                && isset($away_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['weekly-championship']['points'])
                ){
                    continue;
            }

            switch ( $score_diff ) {
                case ($score_diff > 0):
                    $home_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['weekly-championship']['points'] = 3;
                    $away_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['weekly-championship']['points'] = 0 ;
                    break;
                case ($score_diff < 0):
                    $home_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['weekly-championship']['points'] = 0 ;
                    $away_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['weekly-championship']['points'] = 3 ;
                    break;
                case ($score_diff == 0):
                    $home_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['weekly-championship']['points'] = 1 ;
                    $away_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['weekly-championship']['points'] = 1 ;
                    break;
            }

            $home_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['weekly-championship']['opponent_id'] = $away_player_id;
            $home_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['weekly-championship']['home_field_advantage'] = true;
            $away_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['weekly-championship']['opponent_id'] = $home_player_id;
            $away_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['weekly-championship']['home_field_advantage'] = false;

            //calculate totla points
            if(!isset( $home_player_points_array['total_points']['weekly-championship'])){
                $home_player_points_array['total_points']['weekly-championship'] = 0;
            }
            if(!isset( $away_player_points_array['total_points']['weekly-championship'])){
                $away_player_points_array['total_points']['weekly-championship'] = 0;
            }

            $home_player_points_array['total_points']['weekly-championship'] += $home_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['weekly-championship']['points'];
            $away_player_points_array['total_points']['weekly-championship'] += $away_player_points_array['fixture_id_' . $current_fixture_id]['match_id_' . $match_id]['weekly-championship']['points'];

            $points_array[$home_player_id] = $home_player_points_array;
            $points_array[$away_player_id] = $away_player_points_array;
        }

        $this->players_weekly_points = $points_array;

        return $this;
    }

    public function save( ){

        $current_season_id = $this->season_id;

        foreach($this->players_weekly_points as $player_id => $score_array ){

            $id = update_user_meta($player_id, 'score_points_seasonID_' . $current_season_id, $score_array);
            if( $id === false ){
                error_log(__METHOD__ . ' false when updating score_points array for user: ' . $player_id );
            }
        }
    }

}
