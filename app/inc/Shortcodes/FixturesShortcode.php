<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

class FixtureShortcode
{
    public $template;
    public $name;
    public $data;

    public function __construct(Scoremasters\Inc\Interfaces\TemplateInterface $template)
    {
        $this->template = $template;
    }

    public function register_shortcode()
    {
        add_shortcode(static::class, array($this,'output'));
    }

    public function output()
    {

        $args = array(
            'post_type' => 'scm-fixture',
            'post_status' => 'publish',
            'post_per_page' => 1,
        );

        $posts = get_posts($args);

        foreach ($posts as $post) {
            $matches = get_field('week-matches');
            if ($matches) {
                foreach ($matches[0]['week-match'] as $match) {
                    
                    $repeater_teams = get_field("match-teams", $match->ID); //var_dump($repeater_teams);

                    $data["home-team-image"] = get_the_post_thumbnail($repeater_teams[0]["home-team"][0]->ID);
                    $data["home-team-name"] = $repeater_teams[0]["home-team"][0]->post_title;
                    $data["stadium"] = $repeater_teams[0]["home-team"][0]->post_content;
                    $data["match-date"] = $match->post_date_gmt;
                    $data["away-team-name"] = $repeater_teams[0]["away-team"][0]->post_title;
                    $data["away-team-image"] = get_the_post_thumbnail($repeater_teams[0]["away-team"][0]->ID);

                    $this->template->render_html($data);
                }
            }
        }

        $this->template->render_css($data);


    }

}
