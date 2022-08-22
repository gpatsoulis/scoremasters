<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\WeeklyChampionshipCompetition;
use Scoremasters\Inc\Classes\Player;
use Scoremasters\Inc\Classes\WeeklyMatchUps;

//[Scoremasters\Inc\Shortcodes\WeeklyChampionshipShortcode]
class WeeklyChampionshipShortcode
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

    public function output():string 
    {

        $current_league = get_post();
        $curent_competition = ScmData::get_current_scm_competition_of_type('weekly-championship');

        $weekly_matchups = new WeeklyMatchUps( $curent_competition->ID );
        $weeklyCompetition = new WeeklyChampionshipCompetition($curent_competition,$weekly_matchups);

        $participants = $weeklyCompetition->get_participants_by_league_id($current_league->ID)->short();
        //var_dump($participants);

        $output = $this->template->container_start;
        $output .= <<<HTML
        <!--<div class='season-league-player-points'>-->
            <p class='player_rank'>A/A</p>
            <p class='player_nick_name'>Όνομα</p>
            <p class='player_points'>Βαθμοί</p>
        <!--</div>-->
HTML;
        $aa = 1;
        foreach($participants as $player){

            unset($data);
            $data = [];
            $data['aa'] = $aa;
            $aa +=1;
            $data['player_nick_name'] = $player->wp_player->user_login;
            $data['player_name']      = $player->wp_player->display_name;
            $data['player_points']    = $player->weekly_competition_points;

            //$data['player_league']    = $player->get_league();

            $output .= $this->template->get_html($data);
        }

        $output .= $this->template->container_end;
        $output .= $this->template->get_css();

        return $output;
    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\WeeklyChampionshipTemplate('div', 'scm-weekly-championship-score', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }

}