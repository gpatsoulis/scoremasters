<?php
use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\WeeklyMatchUps;
use Scoremasters\Inc\Services\CalculateWeeklyPoints;

function scm_match_trigger_players_weekly_point_calculation( $fixture_id )
    {
 

        //$prev_fixture = ScmData::get_previous_fixture();

        $prev_fixture = get_post($fixture_id);

        var_dump($prev_fixture->ID);
        
        if($prev_fixture->post_title === 'default'){
            return;
        }

       if(SCM_DEBUG){
        error_log( __METHOD__ . ' calculating weekly points for fixture: ' . $prev_fixture->ID);
       }

        $match_data = array(
            'fixture_id' => $prev_fixture->ID,
            'season_id' => (ScmData::get_current_season())->ID,
        );

        $matches = get_field('week-matches', $prev_fixture->ID)[0]['week-match'];
        //usort($matches, self::date_compare);

        $all_leagues = ScmData::get_all_leagues();
        $weekly_competition_post = ScmData::get_current_scm_competition_of_type('weekly-championship');
        $weekly_matchups = (new WeeklyMatchUps($weekly_competition_post->ID))->get_matchups();
        //$weekly_competition = new WeeklyChampionshipCompetition( $weekly_competition_post, $weekly_matchups );

        foreach ($all_leagues as $league) {

            $matchups = $weekly_matchups->by_fixture_id($prev_fixture->ID)->by_league_id($league->ID);

            $calculate_weekly_points = new CalculateWeeklyPoints($match_data, $matchups);
            //$calculate_weekly_points->calculate()->save();
            $calculate_weekly_points->calculate();

            echo '<pre>';
            var_dump( $matchups );
            echo '</pre>';
        }

    }