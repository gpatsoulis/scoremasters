<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\Player;
use Scoremasters\Inc\Classes\WeeklyMatchUps;

//[Scoremasters\Inc\Shortcodes\CurrentPlayerMatchupShortcode]
class CurrentPlayerMatchupShortcode
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

        $player = new Player(wp_get_current_user());
        $palyer_league = $player->get_league();
        $current_fixture = ScmData::get_current_fixture();

        $current_weekly_competition = ScmData::get_current_scm_competition_of_type('weekly-championship');
        $weekly_matchups = new WeeklyMatchUps($current_weekly_competition->ID);

        $pairs = $weekly_matchups->get_matchups()->by_fixture_id($current_fixture->ID)->by_league_id($palyer_league);

        $new_pairs = [];
        for ($i = 0; $i < (count($pairs) / 2); $i++) {

            $z = 2 * $i;
            $new_pairs[] = array($pairs[$z], $pairs[$z + 1]);

        }

        $my_pair = current(array_filter($new_pairs, fn($pair_array) => in_array($player->player_id, $pair_array)));

        //$data['home'] = ($my_pair[0] === $player->player_id) ? $player : ( get_user_by( 'id', $my_pair[0] ) )->display_name;
        //$data['away'] = ($my_pair[1]=== $player->player_id) ? $player : ( get_user_by( 'id', $my_pair[1] ) )->display_name;
        $data['home'] = (get_user_by('id', $my_pair[0]))->display_name;
        $data['away'] = (get_user_by('id', $my_pair[1]))->display_name;
        $data['fixture'] = urldecode($current_fixture->post_name);

        //get next weeks pairs
        $next_feature = ScmData::get_next_future_fixture();
        $future_pairs = $weekly_matchups->get_matchups()->by_fixture_id($next_feature->ID)->by_league_id($palyer_league);
        $new_future_pairs = [];
        for ($i = 0; $i < (count($future_pairs) / 2); $i++) {

            $z = 2 * $i;
            $new_future_pairs[] = array($future_pairs[$z], $future_pairs[$z + 1]);

        }
        $my_future_pair = current(array_filter($new_future_pairs, fn($pair_array) => in_array($player->player_id, $pair_array)));

        $future_data['home'] = (get_user_by('id', $my_future_pair[0]))->display_name;
        $future_data['away'] = (get_user_by('id', $my_future_pair[1]))->display_name;
        $future_data['fixture'] = urldecode($next_feature->post_name);

        if (SCM_DEBUG) {
            echo '<pre>';
            var_dump($pairs);
            //var_dump($new_pairs);
            var_dump($future_pairs);
            //var_dump($current_fixture);
            //var_dump($weekly_matchups->get_all_matchups());
            //var_dump($data);
            //var_dump($my_pair);
            //var_dump($player->player_id);
            echo '</pre>';
        }

        $output = $this->template->container_start;
        $output .= $this->template->get_html($data);

        $output .= $this->template->get_html($future_data);
        $output .= $this->template->container_end;

        return $output;

    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\CurrentPlayerMatchupTemplate('div', 'scm-season-league-score', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }

}
