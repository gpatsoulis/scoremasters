<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\WeeklyLeagueMatchUps;
use Scoremasters\Inc\Classes\League;

// todo: fix show points
//[Scoremasters\Inc\Shortcodes\LeaguesCupStandingsShortcode]
class LeaguesCupStandingsShortcode
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
        //use [Scoremasters\Inc\Shortcodes\LeaguesCupStandingsShortcode]
        if (isset($_POST['fixture_id'])
            && isset($_POST['scm_fixture_setup'])
            && wp_verify_nonce($_POST['scm_fixture_setup'], 'submit_form')) {

            $post_value = filter_var($_POST['fixture_id'], FILTER_VALIDATE_INT);
        }

        $fixture_id = (isset($post_value)) ? $post_value : null;

        if(!$fixture_id){
            $fixture = ScmData::get_current_fixture();
            $fixture_id = $fixture->ID;
        }


        $current_leaguesCup_competition = ScmData::get_current_scm_competition_of_type('leagues-cup');
        $league_matchups = new WeeklyLeagueMatchUps($current_leaguesCup_competition->ID);
        $league_matchups->get_all_matchups();
        $current_matchups = $league_matchups->for_fixture_id($fixture_id);

        $output = $this->template->container_start;
        $output = '<h3>Leagues Cup</h3>';

        $data = array();
        $data['fixture'] = (isset($fixture)) ? $fixture->post_name: (ScmData::get_current_fixture($fixture_id))->post_name;

        for ($i = 0; $i < count($current_matchups); $i += 2) {
            $leagueH = new League(get_post($current_matchups[$i])); 
            $playersH = array_slice($leagueH->short_players_by_fixture_points($fixture_id), 0, 4);

            $leagueA = new League(get_post($current_matchups[$i + 1]));
            $playersA = array_slice($leagueA->short_players_by_fixture_points($fixture_id), 0, 4);
            

            $data['home'] = $leagueH->post_data->post_title;
            $data['home_points'] = $leagueH->get_leagues_cup_total_points_for_fixture($fixture_id);
            $data['home_thumbnail'] = get_the_post_thumbnail($leagueH->post_data->ID);
            $data['home_league_url'] = get_permalink( $leagueH->post_data->ID );
            $data['home_players'] = $playersH;

            $data['away'] = $leagueA->post_data->post_title;
            $data['away_points'] = $leagueA->get_leagues_cup_total_points_for_fixture($fixture_id);
            $data['away_thumbnail'] = get_the_post_thumbnail($leagueA->post_data->ID);
            $data['away_league_url'] = get_permalink( $leagueA->post_data->ID );
            $data['away_players'] = $playersA;

            $output .= $this->template->get_html($data);
            /*
            
            */
        }

        $output .= $this->template->container_end;
        $output .= $this->template->get_css();

        return $output;
    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\LeaguesCupStandingsTemplate('div', 'scm-leagues-cup-standings', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }
}
