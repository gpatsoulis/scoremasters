<?php

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\WeeklyMatchUps;
use Scoremasters\Inc\Services\CalculateWeeklyMatchups;


require_once dirname(__FILE__) . '/app/inc/tools/calculate_weekly_score_for_fixture.php';
scm_match_trigger_players_weekly_point_calculation(7349);
//var_dump($test->get_all_matchups());

echo 'hello';

echo do_shortcode ('[Scoremasters\Inc\Shortcodes\GetPlayerPredictionFormShortcode]');
//var_dump( ScmData::get_all_cup_rounds_for_current_season() );
//echo do_shortcode ('[Scoremasters\Inc\Shortcodes\CupShortcode]');

//$dynamikotites = get_post_meta( 5966, 'scm-match-team-capabilityrange', true );



exit;
