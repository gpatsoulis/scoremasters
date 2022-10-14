<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\CategoryChampionshipCompetition;
use Scoremasters\Inc\Classes\Player;

//[Scoremasters\Inc\Shortcodes\CategoryChampionshipShortcode]
class CategoryChampionshipShortcode
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

        //$user = wp_get_current_user();
        //$current_player = new Player($user);
        //$current_players_league = get_post($current_player->get_league());

        $player = new Player(wp_get_current_user());
        $league_id = $player->get_league();
        
        $current_league = get_post($league_id);

        //$current_league = get_post();

        $curent_competition = ScmData::get_current_scm_competition_of_type('championship-category');

        //var_dump($curent_competition->post_title,$curent_competition->post_date,$curent_competition->ID);

        $weekly_championship = new CategoryChampionshipCompetition( $curent_competition, $current_league );

        $players = $weekly_championship->get_players_shorted_by_score();

        $output = $this->template->container_start;

        $output .= <<<HTML
        <!--<div class='season-league-player-points'>-->
            <p class='player_rank'>A/A</p>
            <p class='player_nick_name'>Όνομα</p>
            <p class='player_points'> Πόντοι </p>
        <!--</div>-->
HTML;

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
        $this->template = new \Scoremasters\Inc\Templates\CategoryChampionshipTemplate('div', 'scm-season-league-score', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }

}

