<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\WeeklyLeagueMatchUps;
use Scoremasters\Inc\Classes\LeaguesCupCompetition;
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

        $all_leagues =[];
        if(count($league_matchups->matchups) !== 0){
            $all_leagues = current($league_matchups->matchups);
            $all_leagues_sorted = $this->short_leagues_by_score($all_leagues);
        }

        //var_dump($all_leagues_sorted);
        
        $output = $this->template->container_start;
        $output = '<h3>Leagues Cup</h3>';

        $data = array();
        $data['fixture'] = (isset($fixture)) ? $fixture->post_name: (ScmData::get_current_fixture($fixture_id))->post_name;

        

        foreach($all_leagues_sorted as $position => $league_data) {

            $league = new League(get_post($league_data['league_id']));

            $lead_scorer = current($league->short_players_by_total_points());

            $form = LeaguesCupCompetition::get_league_form($league_data['league_id']);

            //$data['leagues'][$league_id] = [
            $data = [
                'score' => $league_data['score'],
                'total_points' => $league_data['total_points'],
                'name' => $league->post_data->post_title,
                'league_url' => get_permalink( $league->post_data->ID ),
                'league_name' => $league->post_data->post_title,
                'home_thumbnail' => get_the_post_thumbnail($league->post_data->ID),
                'lead_scorer' => $lead_scorer->wp_player->display_name,
                'lead_scorer_points' => $lead_scorer->current_season_points,
                'form' => $form,
                'total_matches' => count($form),
                'total_win' => $league_data['win'],
                'total_loss' => $league_data['loss'],
                'total_draw' => $league_data['draw'],
                'total_matches' => (int) $league_data['win'] + (int) $league_data['loss'] + (int) $league_data['draw'],
                'league_position' => $position + 1,
            ];

            $output .= $this->template->get_html($data);
        }

        //['league_id' => $this->matchUps[0], 'points' => $leagueApoints, 'score' => 0, 'opponent_id' => $this->matchUps[1]]

        $output .= $this->template->container_end;
        $output .= $this->template->get_css();

        return $output;
    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\LeaguesCupStandingsTemplate('div', 'scm-leagues-cup-standings', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }

    public function short_leagues_by_score($all_leagues){
        $data = [];
        foreach ($all_leagues as $league_id){
            $data[] = LeaguesCupCompetition::total_score($league_id);
        }

        usort($data, fn($a, $b) => $a['score'] <=> $b['score']);

        return $data;
    }
}
