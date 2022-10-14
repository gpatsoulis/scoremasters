<?php
/**
 * @package scoremasters
 * scm-season-competition
 */

namespace Scoremasters\Inc\Abstracts;

abstract class Competition {

    public \WP_post $post_object;
    public string $description;
    public string $type;
    public array $participants;
    public $standings;

    public function __construct(\WP_Post $post){

        if( 'scm-season-competition' !== get_post_type($post)){
            throw new Exception('Scoremasters\Inc\Abstracts\Competition invalid post type post id: ' . $post->ID . ' post type: get_post_type($post)');
        }

        $this->post_object = $post;
        //$this->set_participants();
    }

    //abstract public function set_participants();
    //abstract public function get_standings();
}