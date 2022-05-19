<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Base;

use Scoremasters\Inc\Classes\FootballMatch;
use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Base\CalculateScore;

class MatchSetup
{
    public static function init()
    {
        add_filter('acf/update_value/name=match-date', array(static::class, 'scm_match_update_post_date'), 10, 4);
        add_filter('acf/update_value/name=scm-match-end-time', array(static::class, 'scm_match_trigger_players_point_calculation'), 99, 4);
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

        $end_time = get_post_meta($post_id,'scm-match-end-time');

        if(isset($end_time[0]) && $end_time[0] === $value){
            return $value;
        }

        //file_put_contents(__DIR__ . '/event_debug.txt', '----------------------------- '. "\n",FILE_APPEND);
        //file_put_contents(__DIR__ . '/event_debug.txt', 'from wp ---- '.json_encode($end_time) . "\n",FILE_APPEND);
        //file_put_contents(__DIR__ . '/event_debug.txt', 'from acf original ---- '.json_encode($original) . "\n",FILE_APPEND);
        //file_put_contents(__DIR__ . '/event_debug.txt', 'from acf value ---- '.json_encode($value) . "\n",FILE_APPEND);

        //calculate score
        //todo: run function in async with rest api
        $data_to_insert_in_db = self::calculate_player_points(intval($post_id));

        //file_put_contents(__DIR__ . '/player_data.txt', json_encode($data_to_insert_in_db) . "\n",FILE_APPEND);
        self::insert_player_points_to_db($data_to_insert_in_db);

        return $value;
    }

    public static function calculate_player_points(int $macth_id)
    {

        $match = (new FootballMatch($macth_id))->setup_data();

        $match_date = new \DateTime($match->post_data->post_date);

        $predictions = ScmData::get_players_predictions_for_match($match->post_data);

        //file_put_contents(__DIR__ . '/my_predictions.txt', json_encode(count($predictions)) . "\n", FILE_APPEND);
        //----------------------------------------

        $points_table = get_option('points_table');

        //todo: get current ficture
        $fixcture = get_posts(array('post_type' => 'scm-fixture', 'post_status' => 'publish', 'posts_per_page' => 1));
        $season = get_posts(array('post_type' => 'scm-season', 'post_status' => 'publish', 'posts_per_page' => 1));
        //array('fixture_id' => $fixcture[0]->ID, 'season_id' => $season->ID)

        $data_to_insert_in_db = array();

        foreach ($predictions as $prediction) {

            //$points = self::calculate_points_after_prediction_submit($prediction, $match);
            $points = CalculateScore::calculate_points_after_prediction_submit($prediction, $match);

            //file_put_contents(__DIR__ . '/calc_pred_match.txt', 'total points: '.$points . "\n",FILE_APPEND);

            $data_to_insert_in_db[] = array(
                'player_id' => $prediction->post_author,
                'season_id' => $season[0]->ID,
                'fixture_id' => $fixcture[0]->ID,
                'match_id' => $match->post_data->ID,
                'score' => $points,
            );
   
        }

        //file_put_contents(__DIR__ . '/calc_pred_match.txt', 'data to insert: '.json_encode($data_to_insert_in_db) . "\n",FILE_APPEND);

        return $data_to_insert_in_db;
    }

    public static function insert_player_points_to_db(array $data)
    {

        foreach ($data as $player_score_data) {

            $player_id = strval($player_score_data['player_id']);
            $match_id = strval($player_score_data['match_id']);
            $fixture_id = strval($player_score_data['fixture_id']);
            $season_id = strval($player_score_data['season_id']);
            $score = $player_score_data['score'];

            //todo: use array1+array2 or array_merge
            $old_meta_value = get_user_meta((int) $player_id, 'score_points_seasonID_' . $season_id);

            if (!empty($old_meta_value)) {
                $old_meta_value = $old_meta_value[0];
            } else {
                $old_meta_value = array();
            }

            if (isset($old_meta_value['fixture_id_' . $fixture_id])) {
                $merged_matches = array_merge($old_meta_value['fixture_id_' . $fixture_id], array('match_id_' . $match_id => $score));
                $old_meta_value['fixture_id_' . $fixture_id] = $merged_matches;
            } else {
                $old_meta_value['fixture_id_' . $fixture_id] = array('match_id_' . $match_id => $score);
            }

            $success = update_user_meta($player_id, 'score_points_seasonID_' . $season_id, $old_meta_value);

            if (!$success) {
                error_log('error updating score metadata for user: ' . $player_id);
                //throw new \Exception('error updating score metadata for user: ' . $player_id);
            }

            //$find_key = preg_replace("/[^0-9.]/", "", 'fixture_id_850');

        }

    }

    //When user sets scm-match-end-time, restrict user from editing acf fields, filter by post id
    //default options -> current season, curent fixture ,

}
