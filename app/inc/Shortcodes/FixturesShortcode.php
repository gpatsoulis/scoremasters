<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

class FixturesShortcode
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

    public function output()
    {
        $output = '';
        $data = array();

        $args = array(
            'post_type' => 'scm-fixture',
            'post_status' => 'publish',
            'posts_per_page' => 1,
        );

        //get active week
        $posts = get_posts($args);

        if (empty($posts)) {
            return "<!-- No valid scm-fixture -->";
        }

        $output .= $this->template->container_start;

        $post = $posts[0];

        $matches = get_field('week-matches', $post->ID);

        if ($matches) {
            foreach ($matches[0]['week-match'] as $match) {

                $data["player-id"] = get_current_user_id( );
                $data['match-id'] = $match->ID;
                $repeater_teams = get_field("match-teams", $match->ID);

                $data["home-team-image"] = get_the_post_thumbnail($repeater_teams[0]["home-team"][0]->ID);
                $data["home-team-name"] = $repeater_teams[0]["home-team"][0]->post_title;
                $data["home-team-id"] = $repeater_teams[0]["home-team"][0]->ID;

                $data["stadium"] = $repeater_teams[0]["home-team"][0]->post_content;
                $data["match-date"] = $match->post_date_gmt;

                $data["away-team-name"] = $repeater_teams[0]["away-team"][0]->post_title;
                $data["away-team-id"] = $repeater_teams[0]["away-team"][0]->ID;
                $data["away-team-image"] = get_the_post_thumbnail($repeater_teams[0]["away-team"][0]->ID);

                $output .= $this->template->get_html($data);
            }
        }

        $output .= $this->template->get_css();

        return $output;
    }

    public function get_template()
    {

        $this->template = new \Scoremasters\Inc\Templates\FixtureTemplate('div','scm-fixture-games-list','',array('name' => 'player_id','value' =>get_current_user_id( ) ));

    }

}
