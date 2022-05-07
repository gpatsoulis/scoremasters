<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Base;

class PlayerSetup {

    public static function init(){
        add_filter('user_register', 'Scoremasters\Inc\Classes\PlayerSetup::setup_custom_user_meta', 10, 4);
    }

    public static function setup_custom_user_meta( int $user_id  ){
        
        if(metadata_exists( 'user', $user_id, 'score_points' )){
            error('\"score_points\" metadata exists for user with id: ' . $user_id);
            throw new Exception('\"score_points\" metadata exists for user with id: ' . $user_id);
        }
    
        $id = update_user_meta( $user_id, 'score_points', '', true );
    
        if( true === $id){
            error('\"score_points\" metadata exists for user with id: ' . $user_id);
            throw new Exception('\"score_points\" metadata exists for user with id: ' . $user_id);
        }
    
        if( false === $id){
            error('fail to create \"score_points\" metadata for user with id: ' . $user_id);
            throw new Exception('fail to create \"score_points\" metadata for user with id: ' . $user_id);
        }
    }

}