<?php

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\WeeklyMatchUps;
use Scoremasters\Inc\Services\CalculateWeeklyMatchups;


//var_dump($test->get_all_matchups());

echo 'hello';

//echo do_shortcode ('[Scoremasters\Inc\Shortcodes\GetPlayerPredictionFormShortcode]');
//var_dump( ScmData::get_all_cup_rounds_for_current_season() );
echo do_shortcode ('[Scoremasters\Inc\Shortcodes\CupShortcode]');

exit;
