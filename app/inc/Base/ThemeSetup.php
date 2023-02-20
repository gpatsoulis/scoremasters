<?php
/**
 * @package scoremasters
 *

 *
 */
namespace Scoremasters\Inc\Base;
use Scoremasters\Inc\Classes\Player; 

class ThemeSetup
{
    public static function init()
    {
        add_filter('wp_nav_menu_objects', array(static::class, 'scm_menu_objects'), 10, 2);
        add_filter('use_block_editor_for_post_type',array(static::class,'disableBlockEditorForCustomPostTypes'),10,2);
        add_action( 'admin_notices', array(static::class,'addAdminNotice') );
    }

    public static function scm_menu_objects($sorted_menu_items, $args)
    {

        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $player = new Player($current_user);
            $league_id = $player->get_league();

            if($league_id){
                $league = get_post($league_id);
            }


            $league = get_post($league_id);
        }

        foreach ($sorted_menu_items as &$menu_item) {

            if ($menu_item->post_title === 'scm-league-name') {
                
                //$menu_item->title = $league->post_title;
                $menu_item->title = 'Εβδομαδιαίο Πρωτάθλημα';

                //todo: url = Λίστα Πρωταθλημάτων
                $menu_item->url = '#';
                if(isset($league)){
                    $menu_item->url = $league->guid;
                }

            }

        }

        return $sorted_menu_items;
    }

    public static function disableBlockEditorForCustomPostTypes( $current_status, $post_type ){
        if ($post_type === 'scm-fixture') return false;
        //if ($post_type === 'scm-match') return false;

        return $current_status;
    }

    public static function addAdminNotice (){
        global $current_screen;

        if($current_screen->parent_base !== 'edit') return;
        if($current_screen->post_type !== 'scm-fixture') return;

        //global $post;
        //var_dump($post); 

        if(!isset($_REQUEST['post'])) return;

        if(!($post_id = filter_var($_REQUEST['post'], FILTER_VALIDATE_INT))) return;

        $user_id = get_current_user_id();
        
        if( $error_msg = get_transient( $current_screen->post_type . '_post_errors_' . $post_id .'_' . $user_id) ) {
            echo $error_msg;
            //delete_transient( $current_screen->post_type . '_post_errors_' . $post_id .'_' . $user_id );
        }

    }

}
