<?php
/**
 * @package scoremasters
 * 
 * When new competition is created set publish date 
 * same as the current scm-seasom. Every active competion
 * will have the same publish date as the current 
 * active season.
 */

namespace Scoremasters\Inc\Base;

use Scoremasters\Inc\Base\ScmData;

class CompetitionSetup {

    public static function init(){
        ///add_filter('acf/update_value/name=scm-season-competition', 'Scoremasters\Inc\Base\CompetitionSetup::scm_competition_update_post_date', 10, 4);
        add_filter('acf/update_value/name=scm-season-competition',array(static::class,'scm_competition_update_post_date'),10, 4);
    }

    /**
     * Updates scm-competion post date with the date of the active season
     * when post linked to an scm-season post
     * 
     * @param mixed        $value     The field value
     * @param (int|string) $post_id   The post ID where the value is saved
     * @param array        $field     The field array containing all settings.
     * @param mixed        $original  The original value before modification
     */
    public static function scm_competition_update_post_date( $value, $post_id, array $field, $original  ){

        return $value;
        
        if(get_post_type($post_id) !== 'scm-competition'){
            return $value;
        }

        $scm_season = ScmData::get_current_season();

        $updated = wp_update_post(array('ID' => $post_id,'post_date' =>  $scm_season->post_date));

        if(is_wp_error( $updated )){
            error_log($updated->get_error_messages());
        }
        
        return $value;
    }

}