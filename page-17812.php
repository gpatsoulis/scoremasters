<?php

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\WeeklyLeagueMatchUps;
use Scoremasters\Inc\Services\CalculateLeaguesCupPoints;

if (!current_user_can('manage_options')) {
    exit;
}

echo '-- recalcution started!!!';

$weekly_competition = ScmData::get_current_scm_competition_of_type('leagues-cup');
if ($weekly_competition->ID < 0) {
    error_log(__METHOD__ . ' ----ERROR EVENT---- error calculating add_leagues_cup_matchups');
    return;
}

$matchups = new WeeklyLeagueMatchUps($weekly_competition->ID);
$all = $matchups->get_all_matchups();

var_dump($all);

$currentMatchups = $matchups->for_fixture_id(17298);

$score = new CalculateLeaguesCupPoints($currentMatchups, 17298);
$score->calculate();
$score->save();

echo '-- recalcution finished!!!';
