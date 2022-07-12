<?php

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\WeeklyMatchUps;
use Scoremasters\Inc\Services\CalculateWeeklyMatchups;

// get competition WP_Post
$weekly_competition = ScmData::get_current_scm_competition_of_type('weekly-championship');

//var_dump($weekly_competition);

$matchups = new WeeklyMatchUps($weekly_competition->ID);

$fixture_ids = [1121, 1374];

foreach ($fixture_ids as $fixture_id) {

    $leagues_array = ScmData::get_all_leagues();

//var_dump($leagues_array);

    foreach ($leagues_array as $league) {

        $calculate_matchups = (new CalculateWeeklyMatchups($matchups, $league->ID))
            ->for_league_id($league->ID)
            ->for_fixture_id($fixture_id)
            ->save();

    }

}
// get all active leagues WP_Post[]
