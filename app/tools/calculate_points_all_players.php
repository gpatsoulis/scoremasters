<?php

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Base\DataQuery;
use Scoremasters\Inc\Base\CalculateScore;
use Scoremasters\Inc\Classes\FootballMatch;


$starttime = microtime(true);
//get all fixtures for current season

//get all matches for current fixture

//calculate match points

//save match points

// or 

// get all predictions

$args = array(
    'post_type' => 'scm-prediction',
    'post_status' => 'any',
    'posts_per_page' => -1,
);

$predictions = get_posts($args);

//var_dump(count($predictions));

$data_to_insert_in_db = [];
$current_season = ScmData::get_current_season();




foreach($predictions as $prediction){

    //var_dump($prediction);

    $match_id = explode('-',$prediction->post_title)[0];
    $match = (new FootballMatch(intval($match_id)))->setup_data();
    $points = CalculateScore::calculate_points_after_prediction_submit($prediction, $match);

    //var_dump($match_id);

    $date_args = array(
        'post_type' => 'scm-fixture',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        //'meta_value' => 'a:4:{i:0;s:4:"1894";i:1;s:4:"1895";i:2;s:4:"1896";i:3;s:4:"1897";}'
        'meta_query' => array(
            array(
                'key' => 'week-matches_0_week-match',
                'value' => 's:4:"'. $match->match_id .'";',
                'compare' => 'LIKE'
            )
            
        )
    );

    $fixtures = get_posts($date_args);

    //var_dump(count($fixtures));
//SELECT * FROM `wp_postmeta` where meta_key = 'week-matches_0_week-match' and meta_value LIKE '%s:4:"1896"%' 
    $data_to_insert_in_db[] = array(
        'player_id' => $prediction->post_author,
        'season_id' => $current_season->ID,
        'match_id' => $match->match_id,
        'score' => $points,
        'fixture_id' => $fixtures[0]->ID,
    );

}

//var_dump( count($data_to_insert_in_db));

foreach ($data_to_insert_in_db as $player_score_data) {

    $player_id = strval($player_score_data['player_id']);
    $match_id = strval($player_score_data['match_id']);
    $fixture_id = strval($player_score_data['fixture_id']);
    $season_id = strval($player_score_data['season_id']);
    $score = $player_score_data['score'];

    //todo: use array1+array2 or array_merge
    $old_meta_value = get_user_meta((int) $player_id, 'score_points_seasonID_' . $season_id);

    if(!is_array($old_meta_value)){
        error_log(__METHOD__ . ' error not found score_points_seasonID_' . $season_id);
    }

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

    if(!isset($old_meta_value['total_points'])){
        $old_meta_value['total_points'] = 0;
    }

    $old_meta_value['total_points'] = intval($score) + intval($old_meta_value['total_points']);

    $success = update_user_meta($player_id, 'score_points_seasonID_' . $season_id, $old_meta_value);

    if (!$success) {
        error_log(__METHOD__ . ' error updating score metadata for user: ' . $player_id);
    }

    update_user_meta($player_id, 'total_points', $old_meta_value['total_points']);

    //$find_key = preg_replace("/[^0-9.]/", "", 'fixture_id_850');

}

$endtime = microtime(true);
$timediff = $endtime - $starttime;

var_dump('timediff');
var_dump($timediff);
exit;
