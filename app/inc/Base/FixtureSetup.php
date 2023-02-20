<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Base;

use Scoremasters\Inc\Classes\CustomAdminErrorMsg;

class FixtureSetup
{

    public static function init()
    {
        //add custom event when publishing a new post
        add_action('transition_post_status', array(static::class, 'new_fixture_published'), 20, 3);
        
        //check for errors, before saving post
        add_filter('wp_insert_post_empty_content',array(static::class,'filter_post_data'),99,2);

        //edit post data, set post_type to future
        add_filter('wp_insert_post_data',array(static::class,'edit_post_data'),10,4);

        //add custom event when seting a new post to future
        add_action('post_updated',array(static::class,'new_fixture_future'),10,3);
        
        // actions run on fixture end
        // todo: change action from 'transition_post_status' to custom cron job 
    }

   
    public static function new_fixture_published( string $new_status, string $old_status, \WP_Post $fixture_post ){

        $post_type = 'scm-fixture';

        if( $fixture_post->post_type !== $post_type){
            return;
        }

        // use transition_post_status hook
        if ($old_status === $new_status) {
            return;
        }

        if($old_status !== 'future'){
            return;
        }

        if ($new_status !== 'publish') {
            return;
        }
        
        if( SCM_DEBUG ){
            error_log( __METHOD__ . ' ---- new fixture published ---EVENT--- ! id: ' .  $fixture_post->ID );
        }

        do_action('new_fixture_published_event', $new_status, $old_status, $fixture_post);
        
        /*
        if (!array_key_exists('new_fixture_published_event', $GLOBALS['wp_filter'])) {
            do_action('new_fixture_published_event', $new_status, $old_status, $fixture_post);
        }
        */

    }

    public static function new_fixture_future(int $post_ID, \WP_Post $post_after, \WP_Post $post_before){
        
        if($post_before->post_status !== 'draft'){
            return;
        }

        if($post_after->post_status !== 'future'){
            return;
        }

        if( SCM_DEBUG ){
            error_log( __METHOD__ . ' ---- new fixture  ---FUTURE EVENT--- ! id: ' .  $post_after->ID );
        }

        do_action('new_fixture_future_event', $post_after);

    }

    public static function edit_post_data($data, $postarr, $unsanitized_postarr, $update){

        $post_type = 'scm-fixture';

        if( $data['post_type'] !== $post_type){
            return $data;
        }

        $action = (isset($postarr['action'])) ? $postarr['action']: '';
        $post_status = $postarr['post_status'];

        if( SCM_DEBUG ){
            error_log( __METHOD__ . ' ---- Edit Post Data ---EVENT--- ! id: ' . $postarr['ID'] . ' action: ' . $action . ' post status: '. $post_status);
        }

        if( $data['post_status'] == 'auto-draft'){
            return $data;
        }

        if(!isset($postarr['acf'])){
            if( SCM_DEBUG ){
                error_log( __METHOD__ . ' ---- Edit Post Data ---EVENT--- ! ACF DATE NOT SET ');
            }
            return $data;
        }

        $start_date = $postarr['acf']['field_62378ca9cd747'];
        $acf_fixture_start_date = new \DateTime($start_date, new \DateTimeZone('Europe/Athens'));
        $current_post_date = new \DateTime($data['post_date'], new \DateTimeZone('Europe/Athens'));
        $current_date = new \DateTime('',new \DateTimeZone('Europe/Athens'));

        if( $current_post_date->format('Y-m-d H:i:s') === $acf_fixture_start_date->format('Y-m-d H:i:s')){
            if( SCM_DEBUG ){
                error_log( __METHOD__ . ' ---- Edit Post Data ---EVENT--- ! SAME ACF START DATE ');
            }
            return $data;
        }

        // Update future fixture date if status = future
        if( ($data['post_status'] === 'future') && ($postarr['original_post_status'] === 'future') ){

            if( ($acf_fixture_start_date != $current_post_date) && ($acf_fixture_start_date > $current_date)){
                $data['post_date'] = $acf_fixture_start_date->format('Y-m-d H:i:s');
                $data['post_date_gmt'] = get_gmt_from_date($acf_fixture_start_date->format('Y-m-d H:i:s'));
            }

            if( SCM_DEBUG ){
                error_log( __METHOD__ . ' ---- UPDATE Fixture DATE ---EVENT--- ! id: ' . $postarr['ID'] . ' author_id: ' . $data['post_author']);
            }

            return $data;
        }

        if( ($data['post_status'] === 'publish') &&  ($postarr['original_post_status'] === 'publish')){
            //What can change when post is published
        }

        // Set fixture date to future if post status = publish --- Initial Publish Event --- runs only once
        if( ($data['post_status'] === 'publish') && ($postarr['original_post_status'] === 'auto-draft') ){
            if( $acf_fixture_start_date > $current_post_date) {
                $data['post_date'] = $acf_fixture_start_date->format('Y-m-d H:i:s');
                $data['post_date_gmt'] = get_gmt_from_date($acf_fixture_start_date->format('Y-m-d H:i:s'));
                $data['post_status'] = 'future';
    
                if( SCM_DEBUG ){
                    error_log( __METHOD__ . ' ---- Set Fixture To Future ---EVENT--- ! id: ' . $postarr['ID'] . ' author_id: ' . $data['post_author']);
                }
    
                return $data;
            }
        }
        

        if( SCM_DEBUG ){
            error_log( __METHOD__ . ' ---- Edit Post Data ---EVENT--- ' . $acf_fixture_start_date->format('Y-m-d H:i:s') . ' --- ' . $current_post_date->format('Y-m-d H:i:s'));
            error_log( __METHOD__ . ' ---- Edit Post Data ---EVENT--- ! END EVENT ');
        }

        return $data;
    }

    public static function filter_post_data($maybe_empty, $postarr){

        $post_type = 'scm-fixture';

        if( $postarr['post_type'] !== $post_type){
            return $maybe_empty;
        }

        if(!isset($postarr['action']) || ( $postarr['action'] !== 'editpost') ){
            return $maybe_empty;
        }

        if(!isset($postarr['original_post_status']) || !in_array($postarr['original_post_status'],['draft','auto-draft']) ){
            return $maybe_empty;
        }

        if( $postarr['original_post_status'] === 'publish' ){
            return $maybe_empty;
        }

        if( SCM_DEBUG ){
            error_log( __METHOD__ . ' ---- new fixture ---FILTER DATA EVENT--- ! original_post_status: ' . $postarr['original_post_status']);
        }

        $current_date = new \DateTime('',new \DateTimeZone('Europe/Athens'));
        $acf_post_date = new \DateTime($postarr['acf']['field_62378ca9cd747'], new \DateTimeZone('Europe/Athens'));

        if($acf_post_date < $current_date){
            //setup error msg
            $user_id = get_current_user_id();
            $msg = new CustomAdminErrorMsg('test error in: ' . __METHOD__ ,$postarr['ID'],$user_id);
            $msg->addMsgTransient();

            if( SCM_DEBUG ){
                error_log( __METHOD__ . ' ---- new fixture ---ERROR FILTER DATA EVENT--- ! original_post_status: ' . $postarr['original_post_status']);
            }

            return true;
        }

        return $maybe_empty;
    }
}