<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\Player;
use Scoremasters\Inc\Classes\WeeklyMatchUps;

//[Scoremasters\Inc\Shortcodes\ShowPlayerScoreShortcode]
class ShowPlayerScoreShortcode
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
        //$palyer_league = $player->get_league();
        //$current_fixture = ScmData::get_current_fixture();

       $season_points = $player->current_season_points;
       $weekly_competition_points = $player->weekly_competition_points;

        $data_arr = array(
            'season-league' => $season_points,
            'weekly-championship' => $weekly_competition_points,
            'championship-category' => $season_points,
        );

        if (SCM_DEBUG) {
            echo '<pre>';
            var_dump($data_arr);
            echo '</pre>';
        }

        $output = $this->template->container_start;

        foreach($data_arr as $championship_name => $points){
            
            $name = get_term_by('slug', $championship_name, 'scm_competition_type');

            $data =array( 'name' => $name->name,'points' => $points) ;
            $output .= $this->template->get_html($data);
        }

        
        $output .= $this->template->container_end;

        return $output;

    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\ShowPlayerScoreTemplate('div', 'player-score', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }

}
