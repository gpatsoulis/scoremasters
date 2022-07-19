
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
$league_id = 843;

foreach ($all_fixtures as $fixture) {

        $calculate_matchups = (new CalculateWeeklyMatchups($matchups, $league_id))
            ->for_league_id($league_id)
            ->for_fixture_id($fixture->ID)
            ->save();

}