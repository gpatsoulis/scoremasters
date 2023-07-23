<?php

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\WeeklyMatchUps;
use Scoremasters\Inc\Services\CalculateWeeklyMatchups;
use Scoremasters\Inc\Classes\LeaguesCupCompetition;
use Scoremasters\Inc\Classes\League;
use Scoremasters\Inc\Classes\WeeklyLeagueMatchUps;
use Scoremasters\Inc\Services\CalculateWeeklyLeagueMatchups;


//require_once __DIR__ . '/app/tools/calculate_weekly_score_for_fixture.php';
//require_once __DIR__ . '/app/tools/calculate_cup_score.php';
//scm_match_trigger_players_weekly_point_calculation(7349);
//var_dump($test->get_all_matchups());

echo 'hello';

echo do_shortcode ('[Scoremasters\Inc\Shortcodes\LeaguesCupShortcode]');

//echo do_shortcode ('[Scoremasters\Inc\Shortcodes\GetPlayerPredictionFormShortcode]');
//var_dump( ScmData::get_all_cup_rounds_for_current_season() );
//echo do_shortcode ('[Scoremasters\Inc\Shortcodes\CupShortcode]');

//$dynamikotites = get_post_meta( 5966, 'scm-match-team-capabilityrange', true );

/*
$scm = new LeaguesCupCompetition(get_post(13502));

$matchups = new WeeklyLeagueMatchUps($scm->competition_id);

$calc = new CalculateWeeklyLeagueMatchups($matchups);
$calc->for_fixture_id(13500);

var_dump($calc);
*/
exit;
