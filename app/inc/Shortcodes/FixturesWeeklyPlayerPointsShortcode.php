<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;

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

    public function output()
    {
        //get current user
        $current_user = wp_get_current_user();

        //get active seasonID
        $current_seasons = ScmData::get_current_season();

        if (is_null($current_seasons)) {
            error_log('error no published seasons ');
            return "<!-- No valid scm-season -->";
        }

        //get user points
        $user_points_meta_array = get_user_meta($current_user->ID, 'score_points_seasonID_' . strval($current_seasons->ID));

        ///////////////////debug/////////////////////////
        var_dump($user_points_meta_array);

        //get current ficture week

        $post_value = null;

        if (isset($_POST['fixture_id'])
            && isset($_POST['scm_fixture_setup'])
            && wp_verify_nonce($_POST['scm_fixture_setup'], 'submit_form')) {

            var_dump($_POST);

            $post_value = filter_var($_POST['fixture_id'], FILTER_VALIDATE_INT);
        }

        $fixture_id = ($post_value) ? $post_value : null;

        $current_fixture = ScmData::get_current_fixture($fixture_id);

        if (is_null($current_fixture)) {
            error_log('error no published fixture ');
            return "<!-- No valid scm-fixture -->";
        }

        if (!isset($user_points_meta_array[0]['fixture_id_' . strval($current_fixture->ID)])) {
            return "<!-- No points yet this week -->";
        }

        $array_matches = $user_points_meta_array[0]['fixture_id_' . strval($current_fixture->ID)];

        //$find_key = preg_replace("/[^0-9.]/", "", 'fixture_id_850');

        $output = $this->template->container_start;

        foreach ($array_matches as $match_id => $points) {

            $match_int_id = intval(preg_replace("/[^0-9.]/", "", $match_id));
            $match = get_post($match_int_id);

            $match_title = $match->post_title;

            $user_points_for_match = $points;

            $data = array('match_title' => $match_title, 'user_points_for_match' => $user_points_for_match);

            $output .= $this->template->get_html($data);
        }

        $output .= $this->template->container_end;
        $output .= $this->template->get_css();

        return $output;
    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\FixturesWeeklyScoreTemplate('div', 'scm-fixture-points-list', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }

}
