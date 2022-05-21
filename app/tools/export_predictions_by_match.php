<?php

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\Player;

$match = get_post(1844);
$predictions = ScmData::get_players_predictions_for_match($match);

var_dump(count($predictions));
$export_string = '';
$export_string .= $match->post_title . ',,,,,,' . "\n";
$export_string .= 'player-name,SHMEIO,Under/Over,Score,Scorer,Double points,match name' . "\n";

foreach ($predictions as $prediction) {
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

file_put_contents(__DIR__ . '/export_' . $current_date->format('Y-m-d H:i') . '.csv', $export_string);

exit;
