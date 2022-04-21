<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Base;

class CompetitionSetup {

    public static function init(){
        add_filter('acf/update_value/name=scm-season-competition', 'Scoremasters\Inc\Classes\CompetitionSetup::scm_competition_update_post_date', 10, 4);
    }

    public static function scm_competition_update_post_date( $value, $post_id, array $field, $original  ){
        
        if(get_post_type($post_id) !== 'scm-competition'){
            return $value;
        }

        $scm_season = get_post((int)$value[0]);

        $updated = wp_update_post(array('ID' => $post_id,'post_date' =>  $scm_season->post_date));

        if(is_wp_error( $updated )){
            error_log($updated->get_error_messages());
        }
        
        return $value;
    }

}