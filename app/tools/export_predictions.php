<?php


use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\Player;

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
