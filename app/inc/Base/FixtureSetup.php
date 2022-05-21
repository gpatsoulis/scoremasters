<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Base;

use Scoremasters\Inc\Classes\Player;

class FixtureSetup
{

    public static function init()
    {
        add_filter('acf/update_value/name=week-start-date', array(static::class, 'scm_fixture_update_post_date'), 10, 4);
        add_action('elementor_pro/forms/new_record', array(static::class, 'scm_player_prediction'), 10, 2);
        //add_filter('acf/update_value/name=week-start-date', 'Scoremasters\Inc\Base\FixtureSetup::scm_fixture_update_post_date', 10, 4);
        //add_action('elementor_pro/forms/new_record', 'Scoremasters\Inc\Base\FixtureSetup::scm_player_prediction', 10, 2);
    }

    public static function scm_fixture_update_post_date($value, $post_id, array $field, $original)
    {

        if (get_post_type($post_id) !== 'scm-fixture') {
            return $value;
        }

        //(string)$value format: 20220406
        $start_date = \DateTime::createFromFormat('Ymd', $value)->setTime(0, 0);
        //wp post_date format: 0000-00-00 00:00:00
        $wp_formated_date = $start_date->format('Y-m-d H:i:s');

        $updated = wp_update_post(array('ID' => $post_id, 'post_date' => $wp_formated_date));

        if (is_wp_error($updated)) {
            error_log($updated->get_error_messages());
        }

        return $value;
    }

    public static function scm_player_prediction($record, $ajax_handler)
    {

        $form_name = $record->get_form_settings('form_name');

        if ($form_name !== 'scm-prediction-form') {
            error_log(static::class . ' - invalid form name');
            //send error message to ajax handler
            return;
        }

        $form_data = $record->get_formatted_data();
        $form_meta = $record->get_form_meta(array('page_url'));

        $raw_req_url = $form_meta['page_url']['value'];

        if (!filter_var($raw_req_url, FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED)) {
            error_log(static::class . ' - invalid form url');
            //send error message to ajax handler
            return;
        }

        $req_url = parse_url($raw_req_url);

        parse_str($req_url['query'], $url_query_params);
        $filtered_url_query_params = array();

        //{"page_id":"692","player_id":"2","match_id":"850","homeTeam_id":"133","awayTeam_id":"138"}

        $valid_keys = array('page_id', 'player_id', 'match_id', 'homeTeam_id', 'awayTeam_id', 'match_date');

        foreach ($url_query_params as $param_key => $param_value) {

            if (!in_array($param_key, $valid_keys, true)) {
                continue;
            }

            if (!filter_var($param_value, FILTER_VALIDATE_INT)) {
                continue;
            }

            $filtered_url_query_params[$param_key] = $param_value;
        }

        //if $req_url === false log error, stop action, return error message to fron end

        //todo: check valid form name
        //todo: check if player can make predictions
        //todo: check if is for active week
        //todo: check for valid data
        //save or update data
        //$post_date = gmdate('Y-m-d H:i:s' ,$filtered_url_query_params['match_date']);

        $post_date = new \DateTime();
        $post_date->setTimezone(new \DateTimeZone('Europe/Athens'));
        $post_date->setTimestamp($filtered_url_query_params['match_date']);
       

        //$match_date = new \DateTime($match->post_date, new \DateTimeZone('Europe/Athens'));

        //file_put_contents(__DIR__ . '/date.txt', json_encode($filtered_url_query_params['match_date']) . "\n",FILE_APPEND);
        //file_put_contents(__DIR__ . '/date.txt', json_encode($post_date->format('Y-m-d H:i:s')) . "\n",FILE_APPEND);
        //$post_date = get_date_from_gmt( $post_date_gmt );

        $form_data['homeTeam_id'] = $filtered_url_query_params['homeTeam_id'];
        $form_data['awayTeam_id'] = $filtered_url_query_params['awayTeam_id'];

        $player_prediction_post = array(
            'post_author' => $filtered_url_query_params['player_id'],
            'post_date' => $post_date->format('Y-m-d H:i:s'),
            'post_content' => serialize($form_data),
            'post_title' => $filtered_url_query_params['match_id'] . '-' . $filtered_url_query_params['player_id'],
            'post_type' => 'scm-prediction',
        );

        $existing_player_prediction = get_page_by_title($player_prediction_post['post_title'], OBJECT, 'scm-prediction');

        if ($existing_player_prediction) {
            $player_prediction_post['ID'] = $existing_player_prediction->ID;
        }

        //check if player can play for Double Points
        if (is_array($existing_player_prediction)) {
            error_log(static::class . ' - too many posts with type: "scm-prediction", should be only one');
            throw new Exception(static::class . ' many existing_player_prediction');
        }

        
        if(is_null($existing_player_prediction)){
            $is_new_prediction = true;
        }else{
            $is_new_prediction = false;
        }

        $double_points = $form_data['Double Points'];

        //if player has selected "double points"
        if ($double_points) {

            //if this is new prediction
            if ( $is_new_prediction ) {

                $player_id = $filtered_url_query_params['player_id'];
                $player = new Player(get_user_by('id', $player_id));

                //if player can't make predictions then
                if (!$player->can_play_double()) { 

                    $msg = 'Η επιλογή δηπλασιασμού επιτρέπεται μέχρι δύο φορές.';
                    $ajax_handler->add_error_message($msg);
                    $ajax_handler->is_success = false;
                    return;
                }
            }

            //if this is old prediction but with no double
            if(!$is_new_prediction  && unserialize($existing_player_prediction->post_content)['Double Points'] == ''){

                $player_id = $filtered_url_query_params['player_id'];
                $player = new Player(get_user_by('id', $player_id));

                //if player can't make predictions then
                if (!$player->can_play_double()) { 

                    $msg = 'Η επιλογή δηπλασιασμού επιτρέπεται μέχρι δύο φορές.';
                    $ajax_handler->add_error_message($msg);
                    $ajax_handler->is_success = false;
                    return;
                }
            }
        }

        $current_dateTime = new \DateTime();
        $current_dateTime->setTimezone(new \DateTimeZone('Europe/Athens'));

        if ($current_dateTime > $post_date) {
            $msg = 'Δεν επιτρέπεται η αλλάγη της πρόβλεψης μετά την έναρξη του αγώνα';
            $ajax_handler->add_error_message($msg);
            $ajax_handler->is_success = false;
            return;
        }

        // save user prediction
        $player_prediction = wp_insert_post($player_prediction_post);
        $ajax_handler->is_success = true;

    }

}
