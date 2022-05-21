<?php

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Base\CalculateScore;
use Scoremasters\Inc\Classes\FootballMatch;
//calculate score for all players for all matches
//get all fixtures
$points_table = get_option('points_table');
$season = get_posts(array('post_type' => 'scm-season', 'post_status' => 'publish', 'posts_per_page' => 1));


$args = array(
    'post_type' => 'scm-fixture',
    'post_status' => 'publish',
    'posts_per_page' => -1,
);

$all_fixtures = get_posts($args);

foreach ($all_fixtures as $fixture){
    $matches = ScmData::get_all_matches_for_fixture($fixture);
    var_dump($fixture->post_title);

    foreach($matches as $match){

        var_dump($match->post_title);

        $predictions = ScmData::get_players_predictions_for_match($match);

        $fmatch = (new FootballMatch($match->ID))->setup_data();

        $data_to_insert_in_db = array();

        if(empty($predictions)) continue;

        foreach ($predictions as $prediction) {

            //$points = self::calculate_points_after_prediction_submit($prediction, $match);

            
            $points = CalculateScore::calculate_points_after_prediction_submit($prediction, $fmatch);

            //file_put_contents(__DIR__ . '/calc_pred_match.txt', 'total points: '.$points . "\n",FILE_APPEND);

            $data_to_insert_in_db[] = array(
                'player_id' => $prediction->post_author,
                'season_id' => $season[0]->ID,
                'fixture_id' => $fixture->ID,
                'match_id' => $fmatch->post_data->ID,
                'score' => $points,
            );
   
        }

        foreach ($data_to_insert_in_db as $player_score_data) {

            $player_id = strval($player_score_data['player_id']);
            $match_id = strval($player_score_data['match_id']);
            $fixture_id = strval($player_score_data['fixture_id']);
            $season_id = strval($player_score_data['season_id']);
            $score = $player_score_data['score'];

            //todo: use array1+array2 or array_merge
            $old_meta_value = get_user_meta((int) $player_id, 'score_points_seasonID_' . $season_id);

            if (!empty($old_meta_value)) {
                $old_meta_value = $old_meta_value[0];
            } else {
                $old_meta_value = array();
            }

            if (isset($old_meta_value['fixture_id_' . $fixture_id])) {
                $merged_matches = array_merge($old_meta_value['fixture_id_' . $fixture_id], array('match_id_' . $match_id => $score));
                $old_meta_value['fixture_id_' . $fixture_id] = $merged_matches;
            } else {
                $old_meta_value['fixture_id_' . $fixture_id] = array('match_id_' . $match_id => $score);
            }

            $success = update_user_meta($player_id, 'score_points_seasonID_' . $season_id, $old_meta_value);

            if (!$success) {
                error_log('error updating score metadata for user: ' . $player_id);
                //throw new \Exception('error updating score metadata for user: ' . $player_id);
            }

            //$find_key = preg_replace("/[^0-9.]/", "", 'fixture_id_850');

        }
    }

}
