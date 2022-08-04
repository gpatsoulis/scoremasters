<?php
/**
 * @package scoremasters
 *
 * Set match post date same as match-date
 * When a match is finished trigger player point calculation
 *
 */

namespace Scoremasters\Inc\Base;

use Scoremasters\Inc\Classes\CalculateMatchScore;

class MatchSetup
{
    public static function init()
    {
        add_filter('acf/update_value/name=match-date', array(static::class, 'scm_match_update_post_date'), 10, 4);
        add_filter('acf/update_value/name=scm-match-end-time', array(static::class, 'scm_match_trigger_players_point_calculation'), 99, 4);
        //add_action('scm_calculate_match_points_finished', array(static::class, 'scm_match_trigger_players_weekly_point_calculation'), 10, 2);
        //add_action('scm_calculate_match_points_finished', array(static::class, 'scm_match_trigger_players_scoresmasters_cup_point_calculation'), 15, 2);
    }

    /**
     * Updates scm-match post date with the date of the scheduled match
     * when user updates "match-date" acm custom field.
     *
     * @param mixed        $value     The field value
     * @param (int|string) $post_id   The post ID where the value is saved
     * @param array        $field     The field array containing all settings.
     * @param mixed        $original  The original value before modification
     */
    public static function scm_match_update_post_date($value, $post_id, array $field, $original)
    {

        if (get_post_type($post_id) !== 'scm-match') {
            return $value;
        }

        //(string)$value format: 20220406 todo: change acf time format to 'Y-m-d H:i:s'
        $start_date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
        //wp post_date format: 0000-00-00 00:00:00

        $wp_formated_date = $start_date->format('Y-m-d H:i:s');

        $updated = wp_update_post(array('ID' => $post_id, 'post_date' => $wp_formated_date));

        if (is_wp_error($updated)) {
            error_log($updated->get_error_messages());
        }

        return $value;
    }

    /**
     * Initiates the calculate_points_after_prediction_submit function when
     * match is finished, triggered by updating "scm-match-end-time"acf field
     *
     * @param mixed        $value     The field value
     * @param (int|string) $post_id   The post ID where the value is saved
     * @param array        $field     The field array containing all settings.
     * @param mixed        $original  The original value before modification
     */
    public static function scm_match_trigger_players_point_calculation($value, $post_id, array $field, $original)
    {

        if (get_post_type($post_id) !== 'scm-match') {
            return $value;
        }

        // scm-full-time-score
        //$target_fields = ['scm-half-time-score','scm-full-time-score'];
        $target_fields = ['scm-match-end-time'];

        if (!in_array($field['name'], $target_fields)) {
            return $value;
        }

        $end_time = get_post_meta($post_id, 'scm-match-end-time');

        if (isset($end_time[0]) && $end_time[0] === $value) {
            return $value;
        }

        $calc_score = new CalculateMatchScore(intval($post_id));

        $calc_score->get_predictions()
            ->calculate_points()
            ->save_points()
            ->export_csv_predictions()
            ->send_predictions_by_email();

        /**
         * 'player_id' => id
         * 'season_id' => id
         * 'fixture_id' => id
         * 'match_id' => id
         * 'score' => (int) player points
         */

        $match_data = array(
            'fixture_id' => $calc_score->current_fixture->ID,
            'match_id' => $post_id,
            'season_id' => $calc_score->current_season->ID,
        );

        if (!array_key_exists('scm_calculate_match_points_finished', $GLOBALS['wp_filter'])) {
            do_action('scm_calculate_match_points_finished', $match_data, $calc_score->data_to_insert_in_db);
        }

        return $value;
    }

    // todo: use match object instead of match_data array
    public static function scm_match_trigger_players_weekly_point_calculation($match_data, $players_data)
    {

        // find if is the last match of current fixture
        // and calculate weekly-championship score

        $matches = get_field('week-matches',$match_data['fixture_id'])[0]['week-match'];
        //usort($matches, self::date_compare);


        $all_leagues = ScmData::get_all_leagues();
        $weekly_competition_post = ScmData::get_current_scm_competition_of_type('weekly-championship');
        $weekly_matchups = (new WeeklyMatchUps($weekly_competition_post->ID))->get_matchups();
        //$weekly_competition = new WeeklyChampionshipCompetition( $weekly_competition_post, $weekly_matchups );

        foreach ($all_leagues as $league) {

            $matchups = $weekly_matchups->by_fixture_id($match_data['fixture_id'])->by_league_id($league->ID);
            $calculate_weekly_points = new CalculateWeeklyPoints($match_data, $matchups);
            $calculate_weekly_points->calculate()->save();

        }

    }

    public static function date_compare($match1,$match2){
        $datetime1 = new \DateTime($match1->post_date, new \DateTimeZone('Europe/Athens'));
        $datetime2 = new \DateTime($match2->post_date, new \DateTimeZone('Europe/Athens'));
        return $datetime1 < $datetime2;
    }

    public static function scm_match_trigger_players_scoresmasters_cup_point_calculation($match_data, $players_data)
    {

        $args = array(
            'post_type' => 'scm-fixture',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );

        $no_of_fixtures = get_posts($args);

        // score masters cup starts at fixture number 3 each season
        if (count($no_of_fixtures) < 3) {
            return;
        }

        //check if there is competition round

        $competition_round = get_post_meta($match_data['fixture_id'], 'competition_round_for_season_id_' . $match_data['season_id'], true);

        if ($competition_round_id === false) {
            throw new Exception(__METHOD__ . ' invalid fixture_id');
        }

        if ($competition_round_id === '') {
            return;
        }

        if (!isset($competition_round['score-masters-cup']['round_id'])) {
            return;
        }

        $scoremasterscup_round_id = $competition_round['score-masters-cup']['round_id'];

        $competition = (get_field('scm-related-competition', $scoremasterscup_round_id))[0];

        if ($competition !== 'score-masters-cup') {
            throw new Exception(__METHOD__ . ' invalid competition round competition relationship');
        }

        $matchups = (get_field('groups_headsup', $scoremasterscup_round_id));

        $pairs = [];
        $i = 0;
        foreach ($matchups as $group) {

            foreach ($group['group__headsup'] as $player) {
                $pairs[$i][] = $player['scm-group-player']->ID;
            }
            $i += 1;

        }

        // get cup pairs
        // add points to each player

    }

    //When user sets scm-match-end-time, restrict user from editing acf fields, filter by post id
    //default options -> current season, curent fixture ,

}
