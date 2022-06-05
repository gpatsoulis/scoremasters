<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Base;

use Scoremasters\Inc\Classes\Player;

class PlayerSetup
{

    public static function init()
    {
        add_filter('user_register', 'Scoremasters\Inc\Classes\PlayerSetup::setup_custom_user_meta', 10, 4);
        //add_filter('acf/update_value/name=scm-user-players-list',array(static::class,'assign_player_in_scm_league'),5,4);
        add_action('acf/save_post', array(static::class, 'assign_player_in_scm_league'), 5, 1);
    }

    /**
     * Add meta key 'total_points' for scm-player when player is registere
     * 
     */
    public static function setup_custom_user_meta(int $user_id)
    {

        
        if (metadata_exists('user', $user_id, 'total_points')) {
            error(__METHOD__ . ' \"total_points\" metadata exists for user with id: ' . $user_id);
            throw new Exception('\"total_points\" metadata exists for user with id: ' . $user_id);
        }

        $id = update_user_meta($user_id, 'total_points', 0, true);

        if (true === $id) {
            error(__METHOD__ . ' \"total_points\" metadata exists for user with id: ' . $user_id);
            throw new Exception('\"total_points\" metadata exists for user with id: ' . $user_id);
        }

        if (false === $id) {
            error(__METHOD__  . ' fail to create \"total_points\" metadata for user with id: ' . $user_id);
            throw new Exception('fail to create \"total_points\" metadata for user with id: ' . $user_id);
        }
        
    }

    public static function assign_player_in_scm_league($post_id)
    {

        if (get_post_type($post_id) !== 'scm_league') {
            return;
        }

        $field = get_field_object('scm-user-players-list', $post_id);
        $key = $field['key'];
        // check for field 'scm-user-players-list'

        if (!isset($_POST['acf'][$key])) {
            return;
        }

        $league = get_post($post_id);

        //$all_values = get_fields( $post_id );

        $old_values = get_field('scm-user-players-list', $post_id);

        if(!$old_values){
            $old_values = array();
        }

        $old_values_array = array();
        foreach ($old_values as $acf_old_key_value_array) {
            $value = $acf_old_key_value_array['scm-user-player'];
            if(!$value){
                continue;
            }
            $old_values_array[] = $value;
        }

        $new_values = array();
        if(isset($_POST['acf'][$key])){
            $new_values = $_POST['acf'][$key];
        }
        
        $new_values_array = array();
        foreach ($new_values as $acf_key_value_array) {
            $value = array_values($acf_key_value_array)[0];
            if(intval($value) == 0){
                continue;
            }
            $new_values_array[] = intval($value);
        }


        $get_old_array_dif = array_diff($old_values_array, $new_values_array);
        $get_new_array_dif = array_diff($new_values_array, $old_values_array);

        if (!empty($get_new_array_dif)) {
            //players added to current scm_league
            foreach ($get_new_array_dif as $player_id) {

                $wp_user = get_user_by('id', $player_id);
                $player = new Player($wp_user);

                $success = $player->set_scm_league($league->ID);
                if ($success) {
                    $player->send_message('added to new scm_league', 'player added to scm_leage: ' . $league->post_title);
                    $player->wp_player->add_role('scm-user');
                }
            }
        }

        if (!empty($get_old_array_dif)) {
            //players removed from current scm_league
            foreach ($get_old_array_dif as $player_id) {

                $wp_user = get_user_by('id', $player_id);
                $player = new Player($wp_user);

                $success = $player->remove_from_current_league();
                if ($success) {
                    $player->send_message('removed from scm_league', 'player removed from scm_leage: ' . $league->post_title);
                    $player->wp_player->remove_role('scm-user');
                }
            }
        }

        // -------------------- debug -------------------------
        if(SCM_DEBUG ){
            file_put_contents(SCM_DEBUG_PATH . '/assign_player_in_scm_league.json', 'get_old_array_dif ' . json_encode($get_old_array_dif) . "\n", FILE_APPEND);
            file_put_contents(SCM_DEBUG_PATH . '/assign_player_in_scm_league.json', 'get_new_array_dif ' . json_encode($get_new_array_dif) . "\n", FILE_APPEND);
            //file_put_contents(__DIR__ . '/debug.json','all_values ' .  json_encode($all_values) . "\n",  FILE_APPEND);
            file_put_contents(SCM_DEBUG_PATH . '/assign_player_in_scm_league.json', 'old_values_array ' . json_encode($old_values_array) . "\n", FILE_APPEND);
            file_put_contents(SCM_DEBUG_PATH . '/assign_player_in_scm_league.json', 'new_values_array ' . json_encode($new_values_array) . "\n", FILE_APPEND);
            file_put_contents(SCM_DEBUG_PATH . '/assign_player_in_scm_league.json', "\n", FILE_APPEND);
        }
        // -------------------- debug -------------------------
        

    }

}
