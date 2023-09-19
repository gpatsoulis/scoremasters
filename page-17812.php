<?php 

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Services\CalculateLeaguesCupPoints;
use Scoremasters\Inc\Classes\WeeklyLeagueMatchUps;

if (!current_user_can( 'manage_options' )) exit;

$matchups = new WeeklyLeagueMatchUps(17298);
$matchups = new WeeklyLeagueMatchUps(17264);
$matchups->get_all_matchups();
$currentMatchups = $matchups->for_fixture_id(17298);

$score = new CalculateLeaguesCupPoints($currentMatchups, 17298);
$score->calculate();
$score->save();

echo '-- recalcution finished!!!';