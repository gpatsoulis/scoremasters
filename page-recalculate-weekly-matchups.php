<?php

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\WeeklyMatchUps;
use Scoremasters\Inc\Services\CalculateWeeklyMatchups;

if(!is_admin()) exit;

//only for first fixture!!!!

//prev_fixture should be default post
$prev_fixture = ScmData::get_previous_fixture();
if($prev_fixture->ID > 0) exit('Only for first fixture!');

//if current fixture is default post no published fixture exists
$current_fixture = ScmData::get_current_fixture();
if($current_fixture->ID < 0) exit('No active published-active fixture!');


function add_weekly_championship_players_matchups( \WP_Post $fixture_post)
    {
        
        if(SCM_DEBUG){
            error_log( __METHOD__ . ' calculating weekly matchups' );
        }
        // weekly-championship

        // get competition WP_Post
        // todo: check competition is in current season 
        $weekly_competition = ScmData::get_current_scm_competition_of_type('weekly-championship');

        $matchups = new WeeklyMatchUps($weekly_competition->ID);

        // get all active leagues WP_Post[]
        $leagues_array = ScmData::get_all_leagues();

        foreach ($leagues_array as $league) {

            var_dump($league->post_title);
            $calculate_matchups = (new CalculateWeeklyMatchups($matchups, $league->ID))
                ->for_league_id($league->ID)
                ->for_fixture_id($fixture_post->ID)
                ->save();
        }

        // setup matchups for each championship
        // save matchups in custom meta for each championship

        // seasonid_XXX [ 'fid_XXX' => ['leagueid_XXX' => ['pairs'],'leagueid_XXX' => ['pairs']]];

    }

    add_weekly_championship_players_matchups($current_fixture);
    exit('Matchups Recalculation Done!');
