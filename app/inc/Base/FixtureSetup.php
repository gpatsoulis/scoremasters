<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Base;

use Scoremasters\Inc\Shortcodes\FixtureShortcode;

class FixtureSetup {

    public static function init(){
        add_filter('acf/update_value/name=week-start-date', array(static::class,'scm_fixture_update_post_date'), 10, 4);
        add_action('elementor_pro/forms/new_record', array(static::class,'scm_player_prediction'), 10, 2);
        //add_filter('acf/update_value/name=week-start-date', 'Scoremasters\Inc\Base\FixtureSetup::scm_fixture_update_post_date', 10, 4);
        //add_action('elementor_pro/forms/new_record', 'Scoremasters\Inc\Base\FixtureSetup::scm_player_prediction', 10, 2);
    }

    public static function scm_fixture_update_post_date( $value, $post_id, array $field, $original  ){
        
        if(get_post_type($post_id) !== 'scm-fixture'){
            return $value;
        }
       
        //(string)$value format: 20220406
        $start_date = \DateTime::createFromFormat('Ymd',$value)->setTime(0,0);
        //wp post_date format: 0000-00-00 00:00:00
        $wp_formated_date = $start_date->format('Y-m-d H:i:s');
        
        $updated = wp_update_post(array('ID' => $post_id,'post_date' =>  $wp_formated_date));

        if(is_wp_error( $updated )){
            error_log($updated->get_error_messages());
        }
        
        return $value;
    }

    public static function scm_player_prediction($record, $ajax_handler){

        $form_name = $record->get_form_settings('form_name');

        if($form_name !== 'scm-prediction-form'){
            error_log( static::class . ' - invalid form name');
            //send error message to ajax handler
            return;
        }

        //debug
        //file_put_contents(__DIR__ . '/name_form_data.txt', json_encode($form_name) . "\n",FILE_APPEND);
        $form_data = $record->get_formatted_data();
        //debug
        //file_put_contents(__DIR__ . '/form_data.txt', json_encode($form_data) . "\n",FILE_APPEND);

        $form_meta = $record->get_form_meta(array('page_url'));
        //debug
        //file_put_contents(__DIR__ . '/form_meta.txt', json_encode($form_meta) . "\n",FILE_APPEND);
        
        $raw_req_url = $form_meta['page_url']['value'];

        if(!filter_var($raw_req_url, FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED)){
            error_log( static::class . ' - invalid form url');
            //send error message to ajax handler
            return;
        }

        $req_url = parse_url($raw_req_url);

        parse_str($req_url['query'],$url_query_params);
        $filtered_url_query_params = array();

        //{"page_id":"692","player_id":"2","match_id":"850","homeTeam_id":"133","awayTeam_id":"138"}

        $valid_keys = array('page_id','player_id','match_id','homeTeam_id','awayTeam_id','match_date');
        foreach($url_query_params as $param_key => $param_value){

            if(!in_array($param_key,$valid_keys,true)) continue;

            if(!filter_var($param_value, FILTER_VALIDATE_INT)) continue;

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
        $post_date->setTimestamp($filtered_url_query_params['match_date']);

        //file_put_contents(__DIR__ . '/date.txt', json_encode($filtered_url_query_params['match_date']) . "\n",FILE_APPEND);
        //file_put_contents(__DIR__ . '/date.txt', json_encode($post_date->format('Y-m-d H:i:s')) . "\n",FILE_APPEND);
        //$post_date = get_date_from_gmt( $post_date_gmt );

        $form_data['homeTeam_id'] = $filtered_url_query_params['homeTeam_id'];
        $form_data['awayTeam_id'] = $filtered_url_query_params['awayTeam_id'];

        $player_prediction_post = array(
            'post_author' => $filtered_url_query_params['player_id'],
            'post_date' => $post_date->format('Y-m-d H:i:s'),
            //'post_date_gmt' => $post_date_gmt,
            'post_content' => serialize($form_data),
            'post_title' => $filtered_url_query_params['match_id'] . '-' . $filtered_url_query_params['player_id'],
            'post_type' => 'scm-prediction',
        );

        $existing_player_prediction = get_page_by_title( $player_prediction_post['post_title'],OBJECT,'scm-prediction');

        
        if(is_array($existing_player_prediction)){
            $existing_player_prediction = $existing_player_prediction[0];
            error_log( static::class . ' - too many posts with type: "scm-prediction", should be only one');
        }

        if($existing_player_prediction){
            $player_prediction_post['ID'] = $existing_player_prediction->ID;
        }

        $current_dateTime = new \DateTime();

        //$player_prediction = wp_insert_post($player_prediction_post);
        

        if($current_dateTime <= $post_date){

            $player_prediction = wp_insert_post($player_prediction_post);

            $ajax_handler->is_success = true;

        }else{
            $msg = 'Δεν επιτρέπεται η αλλάγη της πρόβλεψης μετά την έναρξη του αγώνα';
            $ajax_handler->add_error_message($msg);
            //$ajax_handler->add_error($field['id'], $msg);
            $ajax_handler->is_success = false;
            return;
        }

        

    }

}