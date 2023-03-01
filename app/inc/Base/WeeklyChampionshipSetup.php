<?php
/**
 * @package scoremasters
 *
 *
 */
namespace Scoremasters\Inc\Base;

use Scoremasters\Inc\Classes\WeeklyMatchUps;
use Scoremasters\Inc\Services\CalculateWeeklyMatchups;
use Scoremasters\Inc\Services\CalculateWeeklyPoints;


class WeeklyChampionshipSetup {

    public static function init()
    {
        add_action('new_fixture_future_event', array(static::class, 'add_weekly_championship_players_matchups'), 10, 1);
        add_action('new_fixture_published_event', array(static::class, 'scm_match_trigger_players_weekly_point_calculation'), 15, 3);
    }

    /**
     *  Create matchups when a new fixture is created with status future
     * 
     */
    public static function add_weekly_championship_players_matchups(\WP_Post $fixture_post)
    {

        if( $fixture_post->post_type !== 'scm-fixture'){
            return;
        }

        if(SCM_DEBUG){
            error_log( __METHOD__ . ' ----EVENT---- calculating weekly matchups for fixture: ' .  $fixture_post->ID);
        }
        //weekly-championship

        // get competition WP_Post
        // todo: check competition is in current season 
        $weekly_competition = ScmData::get_current_scm_competition_of_type('weekly-championship');
        if($weekly_competition->ID < 0){
            error_log( __METHOD__ . ' ----ERROR EVENT---- error calculating weekly_championship_players_matchups');
            return;
        }

        $matchups = new WeeklyMatchUps($weekly_competition->ID);

        // get all active leagues WP_Post[]
        $leagues_array = ScmData::get_all_leagues();

        foreach ($leagues_array as $league) {

            $calculate_matchups = (new CalculateWeeklyMatchups($matchups, $league->ID))
                ->for_league_id($league->ID)
                ->for_fixture_id($fixture_post->ID)
                ->save();
        }

        error_log( __METHOD__ . ' ----END MATCHUP CALC----');
        // setup matchups for each championship
        // save matchups in custom meta for each championship

        // seasonid_XXX [ 'fid_XXX' => ['leagueid_XXX' => ['pairs'],'leagueid_XXX' => ['pairs']]];
    }

    // todo: use match object instead of match_data array
    public static function scm_match_trigger_players_weekly_point_calculation(string $new_status, string $old_status, \WP_Post $fixture_post)
    {
        
        if( $fixture_post->post_type !== 'scm-fixture'){
            return;
        }

        $prev_fixture = ScmData::get_previous_fixture();
        if($prev_fixture->post_title === 'default'){
            return;
        }


       if(SCM_DEBUG){
        error_log( __METHOD__ . ' ----EVENT---- calculating weekly points for fixture: ' . $prev_fixture->ID . ' current_fixture: ' . $fixture_post->ID);
       }

        $match_data = array(
            'fixture_id' => $prev_fixture->ID,
            'season_id' => (ScmData::get_current_season())->ID,
        );

        $matches = get_field('week-matches', $prev_fixture->ID)[0]['week-match'];
        //usort($matches, self::date_compare);

        $all_leagues = ScmData::get_all_leagues();
        $weekly_competition_post = ScmData::get_current_scm_competition_of_type('weekly-championship');
        
        if($weekly_competition_post->ID < 0){
            error_log( __METHOD__ . ' error calculating players_weekly_point_calculation');
            return;
        }

        $weekly_matchups = (new WeeklyMatchUps($weekly_competition_post->ID))->get_matchups();
        //$weekly_competition = new WeeklyChampionshipCompetition( $weekly_competition_post, $weekly_matchups );

        foreach ($all_leagues as $league) {

            $matchups = $weekly_matchups->by_fixture_id($prev_fixture->ID)->by_league_id($league->ID);
            $calculate_weekly_points = new CalculateWeeklyPoints($match_data, $matchups);
            $calculate_weekly_points->calculate()->save();

        }

    }
}