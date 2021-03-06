<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;

//[Scoremasters\Inc\Shortcodes\FixturesShortcode]
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
        //todo: check for valid match date
        // if date has passed disable "play button" from shortcode

        $post_value = null;
        // 'scm_fixture_setup' -> name of nonce field

        //var_dump($_POST);

        if (isset($_POST['fixture_id'])
            && isset($_POST['scm_fixture_setup'])
            && wp_verify_nonce($_POST['scm_fixture_setup'], 'submit_form')) {

            $post_value = filter_var($_POST['fixture_id'], FILTER_VALIDATE_INT);
        }

        $fixture_id = ($post_value) ? $post_value : null;

        $output = '';
        $data = array();

        $post = ScmData::get_current_fixture($fixture_id);

        $output .= $this->template->container_start;
        $matches = ScmData::get_all_matches_for_current_fixture($fixture_id);

        $current_date = new \DateTime();
        $current_date->setTimezone(new \DateTimeZone('Europe/Athens'));

        if ($matches) {
            foreach ($matches as $match) {

                $data['openForPredictions'] = true;

                //when creating new datetime always set timezone
                $match_date = new \DateTime($match->post_date, new \DateTimeZone('Europe/Athens'));

                //scm-full-time-score
                //scm-full-time-home-score
                //scm-full-time-away-score

                if ($current_date > $match_date) {
                    $data['openForPredictions'] = false;

                    $score_acf_group = get_field('scm-full-time-score', $match->ID);
                    $score_home = $score_acf_group['scm-full-time-home-score'];
                    $score_away = $score_acf_group['scm-full-time-away-score'];

                    $data["match-score"] = $score_home . ' - ' . $score_away;
                }

                $data["player-id"] = get_current_user_id();
                $data['match-id'] = $match->ID;

                //error
                $data['match-date'] = $match_date->getTimestamp();

                $repeater_teams = get_field("match-teams", $match->ID);

                $data["home-team-name"] = $repeater_teams[0]["home-team"][0]->post_title;
                $data["home-team-id"] = $repeater_teams[0]["home-team"][0]->ID;
                $data["home-team-image"] = get_the_post_thumbnail($repeater_teams[0]["home-team"][0]->ID);
                $data['home-team-capability'] = get_field('scm-team-capabilityrange', $repeater_teams[0]["home-team"][0]->ID);

                $data["stadium"] = $repeater_teams[0]["home-team"][0]->post_content;
                $data["match-date-string"] = $match->post_date;

                $data["away-team-name"] = $repeater_teams[0]["away-team"][0]->post_title;
                $data["away-team-id"] = $repeater_teams[0]["away-team"][0]->ID;
                $data["away-team-image"] = get_the_post_thumbnail($repeater_teams[0]["away-team"][0]->ID);
                $data['away-team-capability'] = get_field('scm-team-capabilityrange', $repeater_teams[0]["away-team"][0]->ID);

                $output .= $this->template->get_html($data);
            }
        }

        $output .= $this->template->container_end;
        
        $output .= $this->template->get_css();

        return $output;
    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\FixtureTemplate('div', 'scm-fixture-games-list', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }

}
