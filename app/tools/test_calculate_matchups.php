<?php

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\WeeklyMatchUps;
use Scoremasters\Inc\Services\CalculateWeeklyMatchups;

// get competition WP_Post
$weekly_competition = ScmData::get_current_scm_competition_of_type('weekly-championship');

//var_dump($weekly_competition);

$matchups = new WeeklyMatchUps($weekly_competition->ID);

//get all fixtures

$all_fixtures = ScmData::get_all_fixtures_for_season();
//var_dump($all_fixtures);

foreach ($all_fixtures as $fixture) {

    $leagues_array = ScmData::get_all_leagues();

    foreach ($leagues_array as $league) {

        $calculate_matchups = (new CalculateWeeklyMatchups($matchups, $league->ID))
            ->for_league_id($league->ID)
            ->for_fixture_id($fixture->ID)
            ->save(); 

    }

}

