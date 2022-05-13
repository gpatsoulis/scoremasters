<?php


use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\Player;

/*
use Scoremasters\Inc\Base\CalculateScore;
use Scoremasters\Inc\Classes\FootballMatch;

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
*/
/*
$current_user = wp_get_current_user();

$player = new Player($current_user);

$predictions = $player->get_current_week_predictions();

var_dump($player->can_play_double());

exit;
*/
 
//find curretn fixture
//find all finished matches
//get all predictions for those matches
//get palyer names
// get predictions
// get calculated points

//export as csv
/**
 * fixture
 * player-name;player-id; prediction-id;predictions; points; match-name;match-id
 */

//get active week

$current_fixture = ScmData::get_current_fixture();

if (empty($current_fixture)) {
    error_log('exporter ---- no active fixture');
}

$matches = ScmData::get_finished_matches_for_fixture($current_fixture);

if (empty($matches)) {
    error_log('exporter ---- no available matches');
}

$all_predictions = ScmData::get_player_predictions_for_finished_matches($matches);
var_dump(count($all_predictions));

if (empty($all_predictions)) {
    error_log('exporter ---- no available predictions');
}

$all_predictions_alter = ScmData::get_all_player_prediction_for_fixture_by_title($matches);

var_dump(count($all_predictions_alter));
$all_predictions = $all_predictions_alter;
//alt solution based on search

$export_string = '';

$se = '"';
$export_string .= $current_fixture->post_title . ',,,,,,' . "\n";
$export_string .= 'player-name,SHMEIO,Under/Over,Score,Scorer,Double points,match name' . "\n";

foreach ($all_predictions as $prediction) {
    $player_name = (get_user_by('id', $prediction->post_author))->display_name;
    $predictions = unserialize($prediction->post_content);

    $shmeio = $predictions['SHMEIO'];
    $uo = $predictions['Under / Over'];
    $score = $predictions['score'];

    $scorer = '';
    $tmp_scorer = get_post(intval($predictions['Scorer']));

    if ($tmp_scorer) {
        $scorer = $tmp_scorer->post_title;
    }

    $double = $predictions['Double Points'];

    $match_name = get_post(intval(explode('-', $prediction->post_title)[0]))->post_title;

    //$export_string .= $match_name . ',,,,,,'."\n";
    $export_string .= '"' . $player_name . '","' . $shmeio . '","' . $uo . '","' . $score . '","' . $scorer . '","' . $double . '","' . $match_name . '"' . "\n";
}

$current_date = new \DateTime();
$current_date->setTimezone(new \DateTimeZone('Europe/Athens'));

//file_put_contents(__DIR__ . '/export_' . $current_date->format('Y-m-d H:i') . '.csv', $export_string);
//var_dump($export_string);

exit;
