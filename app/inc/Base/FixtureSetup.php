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
        //if($form_name != 'scm-prediction-form') return;
         //debug
        file_put_contents(__DIR__ . '/name_form_data.txt', json_encode($form_name) . "\n",FILE_APPEND);
        $form_data = $record->get_formatted_data();
         //debug
        file_put_contents(__DIR__ . '/form_data.txt', json_encode($form_data) . "\n",FILE_APPEND);

        //todo: check valid form name
        //todo: check if player can make predictions
        //todo: check if is for active week
        //todo: check for valid data

        //save or update data

        /*
        $prediction_post = array(
            'post_author' => $player_id,
            'post_date' => $scm_match_start_date,
            'post_content' => serilize($data),
            'post_title' => $match_id . '|' . $player_id,
            'post_type' => 'scm-prediction',
        );

        wp_insert_post();
*/
    }

}