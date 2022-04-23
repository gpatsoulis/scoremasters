<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Base;

use Scoremasters\Inc\Shortcodes\FixtureShortcode;

class FixtureSetup {

    public static function init(){
        add_filter('acf/update_value/name=week-start-date', 'Scoremasters\Inc\Classes\FixtureSetup::scm_fixture_update_post_date', 10, 4);
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

}