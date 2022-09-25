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

}
