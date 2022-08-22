<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Services;

class CalculateWeeklyPoints
{

    //public $match_id;
    public $fixture_id;
    public $season_id;
    public $matchups;

    public function __construct(array $match_data, array $matchups)
    {

        //$this->match_id = $match_data['match_id'];
        $this->fixture_id = $match_data['fixture_id'];
        $this->season_id = $match_data['season_id'];
        $this->matchups = $matchups;

    }

    public function calculate()
    {

        //$match_id = $this->match_id;
        $current_matchups = $this->matchups;
        $current_season_id = $this->season_id;
        $current_fixture_id = $this->fixture_id;

        if (SCM_DEBUG) {
            //var_dump($current_matchups);
        }
        //compare
        //if there's no score for player score = 0, opponents score is 3
        $points_array = [];
        for ($i = 0; $i < count($current_matchups); $i += 2) {

            $home_player_id = $current_matchups[$i];
            $home_player_points_array = get_user_meta(intval($home_player_id), 'score_points_seasonID_' . $current_season_id, true);

            //initialize
            if ($home_player_points_array === '') {
                $home_player_points_array = array();
            }

            if (!isset($home_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['points'])) {
                $home_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['points'] = 0;
            }
            $home_player_points = $home_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['points'];

            $away_player_id = $current_matchups[$i + 1];
            $away_player_points_array = get_user_meta(intval($away_player_id), 'score_points_seasonID_' . $current_season_id, true);
            if ($away_player_points_array === '') {
                $away_player_points_array = array();
            }
            if (!isset($away_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['points'])) {
                $away_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['points'] = 0;
            }
            $away_player_points = $away_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['points'];

            $score_diff = intval($home_player_points) - intval($away_player_points);

            //check if score is already calculated
            if (isset($home_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['score'])
                && isset($away_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['score'])
            ) {
                continue;
            }

            if($score_diff > 0){
                $home_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['score'] = 3;
                $away_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['score'] = 0;
            }
            if($score_diff < 0){
                $home_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['score'] = 0;
                $away_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['score'] = 3;
            }
            if($score_diff === 0){
                $home_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['score'] = 1;
                $away_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['score'] = 1;
            }
           

            $home_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['opponent_id'] = $away_player_id;
            $home_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['home_field_advantage'] = true;
            $away_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['opponent_id'] = $home_player_id;
            $away_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['home_field_advantage'] = false;

            //calculate totla points
            if (!isset($home_player_points_array['total_points']['weekly-championship'])) {
                $home_player_points_array['total_points']['weekly-championship'] = 0;
            }
            if (!isset($away_player_points_array['total_points']['weekly-championship'])) {
                $away_player_points_array['total_points']['weekly-championship'] = 0;
            }

            $home_player_points_array['total_points']['weekly-championship'] += $home_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['score'];
            $away_player_points_array['total_points']['weekly-championship'] += $away_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']['score'];

            $points_array[$home_player_id] = $home_player_points_array;
            $points_array[$away_player_id] = $away_player_points_array;

            if (false && SCM_DEBUG) {
                error_log(__METHOD__ . ' score_diff: ' .json_encode($score_diff));
                error_log(__METHOD__ . ' home_player_points: ' .json_encode($home_player_points));
                error_log(__METHOD__ . ' away_player_points: ' .json_encode($away_player_points));
                
                error_log(__METHOD__ . ' home_player_points_array ' .json_encode($home_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']));
                error_log(__METHOD__ . ' away_player_points_array ' . json_encode($away_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']));
                //echo('Home - ' . $home_player_id .' ' . 'Away - ' . $away_player_id . "\n");
                //var_dump($home_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']);
                //var_dump($away_player_points_array['fixture_id_' . $current_fixture_id]['weekly-championship']);
            }
        }

        $this->players_weekly_points = $points_array;

        return $this;
    }

    public function save()
    {

        $current_season_id = $this->season_id;

        foreach ($this->players_weekly_points as $player_id => $score_array) {

            $id = update_user_meta($player_id, 'score_points_seasonID_' . $current_season_id, $score_array);
            if ($id === false) {
                error_log(__METHOD__ . ' false when updating score_points array for user: ' . $player_id);
            }
        }
    }

}
