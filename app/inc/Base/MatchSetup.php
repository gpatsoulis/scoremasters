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

    public static function calculate_points_after_prediction_submit(\WP_Post $prediction_post, FootballMatch $match): int
    {

        
        $prediction = $prediction_post;

        $prediction_content = unserialize($prediction->post_content);

        $player_id = $prediction->post_author;

        //boolean for double points
        $double_shmeio = $double_uo = $double_score = $double_scorer = false;

        $dynamikotita_home_team = $match->home_team_dynamikotita;
        $dynamikotita_away_team = $match->away_team_dynamikotita;

        $column = strval($dynamikotita_home_team - $dynamikotita_away_team);

        $points_table = $match->points_table;

        $prediction_points_shmeio = $prediction_points_under_over = $prediction_points_score = 0;
        $double_shmeio = $double_uo = $double_score = $double_scorer = false;

        $prediction_points_shmeio = $points_table[$column][$prediction_content["SHMEIO"]];
        $prediction_points_under_over = $points_table[$column][$prediction_content["Under / Over"]];
        $prediction_points_score = $points_table[$column][$prediction_content["score"]];

        $home_team = $match->home_team;
        $away_team = $match->away_team;

        $actual_scorers = $match->scorers;

        $Half_time_score = $match->half_time_score;
        $final_score = $match->final_score;

        $total_points = 0;


        //debug
        error_log(static::class . ' calculating points playerID: ' . $player_id);

        //check if prediction matches shmeio result

        if (intval($Half_time_score["scm-half-time-home-score"]) > intval($Half_time_score["scm-half-time-away-score"])) {

            if ((intval($final_score["scm-full-time-home-score"]) > intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "1/1") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            } elseif ((intval($final_score["scm-full-time-home-score"]) == intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "1/X") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            } elseif ((intval($final_score["scm-full-time-home-score"]) < intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "1/2") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            }

        } elseif (intval($Half_time_score["scm-half-time-home-score"]) == intval($Half_time_score["scm-half-time-away-score"])) {

            if ((intval($final_score["scm-full-time-home-score"]) > intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "X/1") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            } elseif ((intval($final_score["scm-full-time-home-score"]) == intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "X/X") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            } elseif ((intval($final_score["scm-full-time-home-score"]) < intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "X/2") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            }

        } elseif (intval($Half_time_score["scm-half-time-home-score"]) < intval($Half_time_score["scm-half-time-away-score"])) {

            if ((intval($final_score["scm-full-time-home-score"]) > intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "2/1") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            } elseif ((intval($final_score["scm-full-time-home-score"]) == intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "2/X") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            } elseif ((intval($final_score["scm-full-time-home-score"]) < intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "2/2") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            }

        }

        if ((intval($final_score["scm-full-time-home-score"]) > intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "-/1") {

            $total_points = $total_points + intval($prediction_points_shmeio);
            $double_shmeio = true;
        }

        if ((intval($final_score["scm-full-time-home-score"]) == intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "-/X") {

            $total_points = $total_points + intval($prediction_points_shmeio);
            $double_shmeio = true;
        }

        if ((intval($final_score["scm-full-time-home-score"]) < intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "-/2") {

            $total_points = $total_points + intval($prediction_points_shmeio);
            $double_shmeio = true;
        }

        //debug
        error_log(static::class . ' calculating points playerID: ' . $player_id . ' total points: ' . $total_points);

        //check if prediction matches u/o result

        $total_goals = intval($final_score["scm-full-time-home-score"]) + intval($final_score["scm-full-time-away-score"]);

        if ($total_goals < 1.5 && $prediction_content["Under / Over"] == "Under 1.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals < 2.5 && $prediction_content["Under / Over"] == "Under 2.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals < 3.5 && $prediction_content["Under / Over"] == "Under 3.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals < 4.5 && $prediction_content["Under / Over"] == "Under 4.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals > 0.5 && $prediction_content["Under / Over"] == "Over 0.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals > 1.5 && $prediction_content["Under / Over"] == "Over 1.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals > 2.5 && $prediction_content["Under / Over"] == "Over 2.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals > 3.5 && $prediction_content["Under / Over"] == "Over 3.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals > 4.5 && $prediction_content["Under / Over"] == "Over 4.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        }

        //These option are not in select option yet, because they are in Greek--- Start //

        elseif ($total_goals < 3 && $prediction_content["Under / Over"] == "1 ή 2") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals > 1 && $total_goals < 4 && $prediction_content["Under / Over"] == "2 ή 3") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals > 2 && $total_goals < 5 && $prediction_content["Under / Over"] == "3 ή 4") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals > 3 && $total_goals < 6 && $prediction_content["Under / Over"] == "4 ή 5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals == 1 && $prediction_content["Under / Over"] == "Ακριβώς 1") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals == 2 && $prediction_content["Under / Over"] == "Ακριβώς 2") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals == 3 && $prediction_content["Under / Over"] == "Ακριβώς 3") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals == 4 && $prediction_content["Under / Over"] == "Ακριβώς 4") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals == 5 && $prediction_content["Under / Over"] == "Ακριβώς 5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($final_score["scm-full-time-home-score"] > 0 && $final_score["scm-full-time-away-score"] > 0 && $prediction_content["Under / Over"] == "goal goal – ναι") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif (($final_score["scm-full-time-home-score"] == 0 || $final_score["scm-full-time-away-score"] == 0) && $prediction_content["Under / Over"] == "goal goal – οχι") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        }
        //These option are not in select option yet, because they are in Greek--- End //

        //check if prediction matches u/o result

        if (($final_score["scm-full-time-home-score"] . "-" . $final_score["scm-full-time-away-score"]) == $prediction_content["score"]) {

            $total_points = $total_points + intval($prediction_points_score);
            $double_score = true;
        }

        //check if prediction matches scorers result

        $p_scorer = (intval($prediction_content["Scorer"]));

        if (in_array($p_scorer, $actual_scorers)) {
            $prediction_points_scorer = 0;
            if (get_field("scm-player-position", $p_scorer) == "Επιθετικός") {

                $prediction_points_scorer = 4;

            } elseif (get_field("scm-player-position", $p_scorer) == "Μέσος") {

                $prediction_points_scorer = 8;

            } elseif (get_field("scm-player-position", $p_scorer) == "Αμυντικός") {

                $prediction_points_scorer = 9;

            }

            $total_points = $total_points + $prediction_points_scorer;
            $double_scorer = true;
        }

        //check for double points
        if ($prediction_content["Double Points"] == "SHMEIO" && $double_shmeio) {
            $total_points = $total_points + intval($prediction_points_shmeio);
        }

        if ($prediction_content["Double Points"] == "UNDER / OVER" && $double_uo) {
            $total_points = $total_points + intval($prediction_points_under_over);
        }

        if ($prediction_content["Double Points"] == "SCORER" && $double_scorer) {
            $total_points = $total_points + $prediction_points_scorer;
        }

        //return ("Total Points: " . $total_points);

        //debug
        error_log(static::class . ' calculating points playerID: ' . $player_id . ' total points: ' . $total_points);

        return $total_points;
    }

}
