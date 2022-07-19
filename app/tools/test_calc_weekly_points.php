<?php

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\WeeklyMatchUps;
use Scoremasters\Inc\Classes\WeeklyChampionshipCompetition;
use Scoremasters\Inc\Services\CalculateWeeklyPoints;


// fixture id = 1121, 1374, 2124
// points, match_id





$match_data = array(
    'fixture_id' => 1121,
    'match_id' => 1116,
    'season_id' => 99,
);

$all_leagues = ScmData::get_all_leagues();
$weekly_competition_post = ScmData::get_current_scm_competition_of_type('weekly-championship');
$weekly_matchups = (new WeeklyMatchUps( $weekly_competition_post->ID ))->get_matchups();

var_dump($weekly_matchups->by_fixture_id(1121)->by_league_id(843));


foreach($all_leagues as $league) {

    $matchups = $weekly_matchups->by_fixture_id($match_data['fixture_id'])->by_league_id($league->ID);
    $calculate_weekly_points = new CalculateWeeklyPoints( $match_data, $matchups);
    $calculate_weekly_points->calculate()->save();
}
