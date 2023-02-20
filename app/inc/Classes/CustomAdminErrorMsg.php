<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;



class CustomAdminErrorMsg 
{
    private string $message;
    private string $post_id;
    private int $user_id;

    public function __construct( string $message, int $post_id, int $user_id ){
        $this->message = $message;
        $this->post_id = $post_id;
        $this->user_id = $user_id;
    }

    private function createErrorNotice():string {
    
        $output = <<<HTML
        <div class='error notice-error notice'><p>{$this->message}</p></div>
        HTML;

        return $output;
    }

    public function addMsgTransient($seconds = 120):void {

        $user_id = $this->user_id;
        $post_id = $this->post_id;
        $post_type = get_post_type($post_id);

        set_transient("{$post_type}_post_errors_{$post_id}_{$user_id}", $this->createErrorNotice(), $seconds);
    }

    public static function removeMsgTransient($post_type,$post_id,$user_id):bool 
    {
        //delete_transient( $post_type . '_post_errors_' . $postarr['ID'] .'_' . $user_id);
        $success = delete_transient( "{$post_type}_post_errors_{$post_id}_{$user_id}");

        return $success;
    }

}
