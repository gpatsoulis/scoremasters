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
        
        if(metadata_exists( 'user', $user_id, 'stored_predictions' )){
            throw new Exception('\"stored_predictions\" metadata exists for user with id: ' . $user_id);
        }
    
        $id = update_user_meta( $user_id, 'stored_predictions', '', true );
    
        if( true === $id){
            throw new Exception('\"stored_predictions\" metadata exists for user with id: ' . $user_id);
        }
    
        if( false === $id){
            throw new Exception('fail to create \"stored_predictions\" metadata for user with id: ' . $user_id);
        }
    }

}