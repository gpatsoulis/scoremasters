<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Base;

class ProPlayerSetup {

    public static function init(){
        add_filter( 'rest_scm-pro-player_query', array(static::class,'rest_request_by_meta'), 99, 2 );
    }

    /**
     * Add meta fields support in rest API for post type `scm-pro-player`
     * 
     * @param   array   $args       Contains by default pre written params.
     * @param   array   $request    Contains params values passed through URL request.
     * @return  array   $args       New array with added custom params and its values.
     */

    static function rest_request_by_meta( array $args, \WP_REST_Request $request){

        if(!isset($request['meta_value']) || !isset($request['meta_key'])) return $args;

        $meta_value_len = strlen($request['meta_value']);

        $args += array(
            'meta_key' => $request['meta_key'],
            'meta_value' => sprintf('a:1:{i:0;s:%d:"%s";}',$meta_value_len, $request['meta_value']),
            'meta_query' => (isset($request['meta_query'])) ? $request['meta_query']:'',
        );
        
        return $args;
    }

}

//a:1:{i:0;s:3:"243";}
//http://scoremasters.test/wp-json/wp/v2/scm-pro-player?meta_key=scm-player-team&meta_value=109&per_page=30&_fields=id,status,type,featured_media,acf,title
