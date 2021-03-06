<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\SeasonLeagueCompetition;

//[Scoremasters\Inc\Shortcodes\SeasonLeagueShortcode]
class SeasonLeagueShortcode
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

        $curent_seasonleague = ScmData::get_current_scm_competition_of_type('season-league');
        $season_league = new SeasonLeagueCompetition( $curent_seasonleague );

        $players = $season_league->get_players_shorted_by_score();

        $output = $this->template->container_start;

        $aa = 1;
        foreach($players as $player){

            unset($data);
            $data = [];
            $data['aa'] = $aa;
            $aa +=1;
            $data['player_nick_name'] = $player->wp_player->user_login;
            $data['player_name']      = $player->wp_player->display_name;
            $data['player_points']    = $player->current_season_points;
            $data['player_league']    = $player->get_league();

            $output .= $this->template->get_html($data);
        }

        $output .= $this->template->container_end;
        $output .= $this->template->get_css();

        return $output;

    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\SeasonLeagueTemplate('div', 'scm-season-league-score', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }

}

