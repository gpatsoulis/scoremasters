<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

//[Scoremasters\Inc\Shortcodes\FixturesWeeklyPlayerPointsShortcode]
class FixturesWeeklyPlayerPointsShortcode
{
    public $template;
    public $name;

    public function __construct()
    {
        $this->name = static::class;

        $this->get_template();
    }

    public function register_shortcode()
    {
        add_shortcode($this->name, array($this, 'output'));
    }

    public function output(){
        //get current user

        
        $current_user = wp_get_current_user();

        //get active seasonID
        $args = array(
            'post_type' => 'scm-season',
            'posts_per_page' => 1,
            'post_status' => 'publish',
        );
        //check for success
        $current_seasons = get_posts($args);

        if(empty($current_seasons)){
            error_log('error no published season ');
            return "<!-- No valid scm-season -->"; 
        }

        $current_seasonID = $current_seasons[0]->ID;

        //get user points
        $user_points_meta_array = get_user_meta($current_user->ID,'score_points_seasonID_' . strval($current_seasonID) );


        //get current ficture week
        $args = array(
            'post_type' => 'scm-fixture',
            'post_status' => 'publish',
            'posts_per_page' => 1,
        );

        //get active week
        $fixtures = get_posts($args);

        if (empty($fixtures)) {
            error_log('error no published fixture ');
            return "<!-- No valid scm-fixture -->";
        }

        ///////////////////debug/////////////////////////
        //var_dump($user_points_meta_array);

        $current_fixture = $fixtures[0];

        if(!isset($user_points_meta_array[0]['fixture_id_' . strval($current_fixture->ID)])){
            return "<!-- No points yet this week -->";
        }

        $array_matches = $user_points_meta_array[0]['fixture_id_' . strval($current_fixture->ID)];
    


        //$find_key = preg_replace("/[^0-9.]/", "", 'fixture_id_850');

        $output = $this->template->container_start;
       
        foreach($array_matches as $match_id => $points){

            $match_int_id = intval(preg_replace("/[^0-9.]/", "", $match_id));
            $match = get_post($match_int_id);

            $match_title = $match->post_title;

            $user_points_for_match = $points;

            $data = array('match_title' => $match_title,'user_points_for_match' => $user_points_for_match);

            $output .= $this->template->get_html($data);
        }


        $output .= $this->template->get_css();


        return $output;
    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\FixturesWeeklyScoreTemplate('div','scm-fixture-points-list','',array('name' => 'player_id','value' => get_current_user_id( ) ));
    }

}