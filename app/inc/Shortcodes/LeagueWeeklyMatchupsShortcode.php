<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\Player;
use Scoremasters\Inc\Classes\WeeklyMatchUps;

// todo: fix show points
//[Scoremasters\Inc\Shortcodes\LeagueWeeklyMatchupsShortcode]
class LeagueWeeklyMatchupsShortcode
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

        //use [Scoremasters\Inc\Shortcodes\FixturesSelectWeekShortcode]
        if (isset($_POST['fixture_id'])
            && isset($_POST['scm_fixture_setup'])
            && wp_verify_nonce($_POST['scm_fixture_setup'], 'submit_form')) {

            $post_value = filter_var($_POST['fixture_id'], FILTER_VALIDATE_INT);
        }

        $fixture_id = (isset($post_value)) ? $post_value : null;


        $current_league = get_post();

        $curent_season = ScmData::get_current_season();
        $curent_fixture = ScmData::get_current_fixture();

        //$player_league = $player->get_league();
        $current_league = get_post();
        $current_fixture = ScmData::get_current_fixture();

        $current_weekly_competition = ScmData::get_current_scm_competition_of_type('weekly-championship');

        if($current_weekly_competition->ID < 0){
            error_log( __METHOD__ . ' error output, get_current_scm_competition_of_type');
            return "";
        }


        $weekly_matchups = new WeeklyMatchUps($current_weekly_competition->ID);
        //$weekly_matchups->get_all_matchups();

        if(!$fixture_id){
            $fixture_id = $current_fixture->ID;
        }
        $pairs = $weekly_matchups->get_matchups()->by_fixture_id($fixture_id)->by_league_id($current_league->ID);

        $new_pairs = [];
        for ($i = 0; $i < (count($pairs) / 2); $i++) {

            $z = 2 * $i;
            //$new_pairs[] = array(get_userdata($pairs[$z])->display_name, get_userdata($pairs[$z + 1])->display_name);
            $new_pairs[] = array(get_userdata($pairs[$z]), get_userdata($pairs[$z + 1]));

        }

        //todo: WeeklyMatchUps, check for even number of players, else return empty array;


        $output = $this->template->container_start;
        $data = array();

        foreach ($new_pairs as $pair) {

            if (isset($pair[0]) && isset($pair[1])) {
                $data['home'] = $pair[0]->display_name;
                $data['away'] = $pair[1]->display_name;

                $home_user = $pair[0];
                $away_user = $pair[1];

                //todo: check if isset[fixture_id]
                $home_player = new Player($home_user);
                $away_player = new Player($away_user);

                $home_score = 0;
                $away_score = 0;
                if (isset($home_player->player_points['fixture_id_' . $fixture_id]['weekly-championship']['points'])) {
                    $home_score = $home_player->player_points['fixture_id_' . $fixture_id]['weekly-championship']['points'];
                }
                if (isset($away_player->player_points['fixture_id_' . $fixture_id]['weekly-championship']['points'])) {
                    $away_score = $away_player->player_points['fixture_id_' . $fixture_id]['weekly-championship']['points'];
                }

                $data['home_score'] = $home_score;
                $data['away_score'] = $away_score;
            }

            $output .= $this->template->get_html($data);
        }

        $output .= $this->template->container_end;
        $output .= $this->template->get_css();

        return $output;

    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\LeagueWeeklyMatchupsTemplate('div', 'scm-weekly-matchups', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }
}
