<?php
/**
 * @package scoremasters
 *
 */

namespace Scoremasters\Inc\Base;

use Scoremasters\Inc\Classes\WeeklyLeagueMatchUps;
use Scoremasters\Inc\Services\CalculateWeeklyLeagueMatchups;
use Scoremasters\Inc\Services\CalculateLeaguesCupPoints;

class LeaguesCupCompetitionSetup
{
    public static function init()
    {
        add_action('new_fixture_future_event', array(static::class, 'add_leagues_cup_matchups'), 10, 1);
        add_action('new_fixture_published_event', array(static::class, 'scm_match_trigger_leagues_cup_weekly_point_calculation'), 15, 3);
    }

    public static function add_leagues_cup_matchups(\WP_Post $fixture_post)
    {

        if ($fixture_post->post_type !== 'scm-fixture') {
            return;
        }

        if (SCM_DEBUG) {
            error_log(__METHOD__ . ' ----EVENT---- calculating legues cup matchups for fixture: ' . $fixture_post->ID);
        }

        $weekly_competition = ScmData::get_current_scm_competition_of_type('leagues-cup');
        if ($weekly_competition->ID < 0) {
            error_log(__METHOD__ . ' ----ERROR EVENT---- error calculating add_leagues_cup_matchups');
            return;
        }

        $matchups = new WeeklyLeagueMatchUps($weekly_competition->ID);
        $calculateMatchups = new CalculateWeeklyLeagueMatchups($matchups);

        $calculateMatchups->for_fixture_id($fixture_post->ID);
        $calculateMatchups->save();

        if (SCM_DEBUG) {
            error_log(__METHOD__ . ' ----END LEAGUES CUP MATCHUP CALC----');
        }

    }

    public static function scm_match_trigger_leagues_cup_weekly_point_calculation(string $new_status, string $old_status, \WP_Post $fixture_post)
    {

        if ($fixture_post->post_type !== 'scm-fixture') {
            return;
        }

        $prev_fixture = ScmData::get_previous_fixture();
        if ($prev_fixture->post_title === 'default') {
            return;
        }

        if (SCM_DEBUG) {
            error_log(__METHOD__ . ' ----EVENT---- calculating leagues cup points for fixture: ' . $prev_fixture->ID . ' current_fixture: ' . $fixture_post->ID);
        }

        $weekly_competition = ScmData::get_current_scm_competition_of_type('leagues-cup');
        if ($weekly_competition->ID < 0) {
            error_log(__METHOD__ . ' ----ERROR EVENT---- error calculating add_leagues_cup_matchups');
            return;
        }

        $matchups = new WeeklyLeagueMatchUps($weekly_competition->ID);

        $currentMatchups = $matchups->for_fixture_id($fixture_post->ID);

        //CalculateLeaguesCupPoints class needs rewrite
        $score = new CalculateLeaguesCupPoints($currentMatchups, $fixture_post->ID);
        $score->calculate();
        $score->save();


    }
}
