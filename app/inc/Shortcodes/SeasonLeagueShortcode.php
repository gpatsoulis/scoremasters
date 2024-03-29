<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\SeasonLeagueCompetition;
use Scoremasters\Inc\Templates\SeasonLeagueTemplate;

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
        $output .= <<<HTML
        <!--<div class='season-league-player-points'>-->
            <p class='player_rank'>A/A</p>
            <p class='player_nick_name'>Όνομα</p>
            <p class='player_points'>Βαθμοί</p>
            <p class='player_league'>Πρωτάθλημα</p>
        <!--</div>-->
HTML;

        $aa = 1;
        foreach($players as $player){

            unset($data);

            if(!$player->can_make_predictions()){
                //continue;
            }

            $data = [];
            $data['aa'] = $aa;
            $aa +=1;
            $data['player_nick_name'] = $player->wp_player->user_login;
            $data['player_name']      = $player->wp_player->display_name;
            $data['player_points']    = $player->current_season_points;

            $league = '';
            $league_name = '';
            $league_thumbnail = '';
            $league_permalink = '';

            if( $player->get_league() ){
                $league = get_post($player->get_league());
                $league_name = $league->post_title;
                $league_permalink = get_permalink( $league->ID );
                $league_thumbnail = get_the_post_thumbnail($league->ID);
            }

            $data['player_league']    = $league_name;
            $data['league_image']     = $league_thumbnail;
            $data['league_url']       = $league_permalink;

            $output .= $this->template->get_html($data);
        }

        $output .= $this->template->container_end;
        $output .= $this->template->get_css();

        return $output;

    }

    public function get_template()
    {
        //$this->template = new \Scoremasters\Inc\Templates\SeasonLeagueTemplate('div', 'scm-season-league-score', '', array('name' => 'player_id', 'value' => get_current_user_id()));
        $this->template = new SeasonLeagueTemplate('div', 'scm-season-league-score', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }

}

