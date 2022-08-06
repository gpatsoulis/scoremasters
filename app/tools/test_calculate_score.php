<?php

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Base\CalculateScore;
use Scoremasters\Inc\Classes\FootballMatch;
//calculate score for all players for all matches
//get all fixtures
$starttime = microtime(true);

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

            $players_score = get_user_meta((int) $player_id, 'score_points_seasonID_' . $season_id);

            //initialize
            if (!empty($players_score)) {
                $players_score = $players_score[0];
            } else {
                $players_score = array();
            }

            //points already in db
            if(isset($players_score['fixture_id_' . $fixture_id]['match_id_' . $match_id]['season-league']['points'])){
                continue;
            }
            
            //match points for season-league competition
            $players_score['fixture_id_' . $fixture_id]['match_id_' . $match_id]['season-league']['points'] = $score;


             // total points
            if(!isset($players_score['total_points']['season-league'])){
                $players_score['total_points']['season-league'] = 0;
            }

            $players_score['total_points']['season-league'] = intval($score) + intval($players_score['total_points']['season-league']);

            //weekly score for weekly championship competition
            if(!isset( $players_score['fixture_id_' . $fixture_id]['weekly-championship']['points'])){
                $players_score['fixture_id_' . $fixture_id]['weekly-championship']['points'] = 0;
            }
            $players_score['fixture_id_' . $fixture_id]['weekly-championship']['points'] += intval($score);


            // save player score
            $success = update_user_meta($player_id, 'score_points_seasonID_' . $season_id, $players_score);

            if (!$success) {
                error_log(__METHOD__ . ' error updating score metadata for user: ' . $player_id);
            }

            //update_user_meta($player_id, 'total_points', $old_meta_value['total_points']);
            //$find_key = preg_replace("/[^0-9.]/", "", 'fixture_id_850');

        }
    }

}


$endtime = microtime(true);
$timediff = $endtime - $starttime;

var_dump('timediff');
var_dump($timediff);
exit;
