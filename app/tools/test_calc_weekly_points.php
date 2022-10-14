<?php

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\WeeklyMatchUps;
use Scoremasters\Inc\Classes\WeeklyChampionshipCompetition;
use Scoremasters\Inc\Services\CalculateWeeklyPoints;


// fixture id = 1121, 1374, 2124
// points, match_id



//get all past fixtures for this season;
$current_season = ScmData::get_current_season();
$curent_season_date = new \DateTime( $current_season->post_date, new \DateTimeZone('Europe/Athens'));

$args = array(
    'post_type' => 'scm-fixture',
    'post_status' => 'publish',
    'posts_per_page' => -1,
);

$fixtures = get_posts($args);

$match_data = array(
    'fixture_id' => 1121,
    'season_id' => 99,
);


$all_leagues = ScmData::get_all_leagues();
$weekly_competition_post = ScmData::get_current_scm_competition_of_type('weekly-championship');
$weekly_matchups = (new WeeklyMatchUps( $weekly_competition_post->ID ))->get_matchups();

//var_dump($weekly_matchups->by_fixture_id(1121)->by_league_id(843));


foreach($all_leagues as $league) {

    foreach($fixtures as $fixture){
        $matches = ScmData::get_all_matches_for_current_fixture($fixture->ID);
        $matchups = $weekly_matchups->by_fixture_id($fixture->ID)->by_league_id($league->ID);

        
            var_dump($fixture->post_title);

            $match_data = array(
                'fixture_id' => $fixture->ID,
                'season_id' => 99,
            );

            $calculate_weekly_points = new CalculateWeeklyPoints( $match_data, $matchups);
            $calculate_weekly_points->calculate()->save();
       

    }

    //$matchups = $weekly_matchups->by_fixture_id($match_data['fixture_id'])->by_league_id($league->ID);
    //$calculate_weekly_points = new CalculateWeeklyPoints( $match_data, $matchups);
    //$calculate_weekly_points->calculate()->save();
}
