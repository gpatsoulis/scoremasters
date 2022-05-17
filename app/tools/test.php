<?php

use Scoremasters\Inc\Base\CalculateScore;
use Scoremasters\Inc\Classes\FootballMatch;

/*
$current_user = wp_get_current_user();

$player = new Player($current_user);

$predictions = $player->get_current_week_predictions();

var_dump($player->can_play_double());

exit;
*/


$id = 1482;
$match = (new FootballMatch(1482))->setup_data();
$match_date = new \DateTime($match->post_data->post_date);

$args = array(
    'post_type' => 'scm-prediction',
    'post_status' => 'any',
    'posts_per_page' => -1,
    's' => '1482' . '-',
);

$predictions = get_posts($args);

$points_table = get_option('points_table');

//todo: get current ficture
$fixcture = get_posts(array('post_type' => 'scm-fixture', 'post_status' => 'publish', 'posts_per_page' => 1));
$season = get_posts(array('post_type' => 'scm-season', 'post_status' => 'publish', 'posts_per_page' => 1));
//array('fixture_id' => $fixcture[0]->ID, 'season_id' => $season->ID)

$data_to_insert_in_db = array();

foreach ($predictions as $prediction) {

    $points = CalculateScore::calculate_points_after_prediction_submit($prediction, $match);

    //file_put_contents(__DIR__ . '/calc_pred_match.txt', 'total points: '.$points . "\n",FILE_APPEND);

    $data_to_insert_in_db[] = array(
        'player_id' => $prediction->post_author,
        'name' => get_the_author_meta('display_name',$prediction->post_author),
        'prediction' => unserialize($prediction->post_content),
        'season_id' => $season[0]->ID,
        'fixture_id' => $fixcture[0]->ID,
        'match_id' => $match->post_data->ID,
        'match_name' => $match->post_data->post_title,
        'score' => $points,
    );

}

var_dump($data_to_insert_in_db);

exit;