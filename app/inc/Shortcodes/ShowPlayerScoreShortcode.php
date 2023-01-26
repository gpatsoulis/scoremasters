<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\Player;
use Scoremasters\Inc\Classes\WeeklyMatchUps;
use Scoremasters\Inc\Classes\SeasonLeagueCompetition;
use Scoremasters\Inc\Classes\CategoryChampionshipCompetition;

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
        
        
        $competition_post = ScmData::get_current_scm_competition_of_type('season-league');

        $seasonleague = new SeasonLeagueCompetition($competition_post);
        $positions = $seasonleague->get_players_shorted_by_score();

        $aa = 0;
        foreach ( $positions as $s_player){
            $aa += 1;
            if($s_player->player_id === $player->player_id){
                break;
            }
        }
        $seasonleague_position = $aa;
        

        $competition_post = ScmData::get_current_scm_competition_of_type('championship-category');
        $league_post = get_post($player->get_league());

        $category_championship = new CategoryChampionshipCompetition($competition_post,$league_post);
        $positions = $category_championship->get_players_shorted_by_score();

        $aa = 0;
        foreach ( $positions as $s_player){
            $aa += 1;
            if($s_player->player_id === $player->player_id){
                break;
            }
        }

        $category_position = $aa;

        //$palyer_league = $player->get_league();
        //$current_fixture = ScmData::get_current_fixture();

       $season_points = $player->current_season_points;
       $weekly_competition_points = $player->weekly_competition_points;

        $data_arr = array(
            'season-league' => array('points' => $season_points, 'position' => $seasonleague_position),
            'weekly-championship' => array('points' => $weekly_competition_points, 'position' => ''),
            'championship-category' => array('points' => $season_points,'position' => $category_position),
        );

        if (SCM_DEBUG) {
            //echo '<pre>';
            //var_dump($data_arr);
            //echo '</pre>';
        }

        $output = $this->template->container_start;

        foreach($data_arr as $championship_name => $stats){
            
            $name = get_term_by('slug', $championship_name, 'scm_competition_type');

            $data =array( 'name' => $name->name,'points' => $stats['points'],'position' => $stats['position']) ;
            $output .= $this->template->get_html($data);
        }

        
        $output .= $this->template->container_end;

        $output .= $this->template->get_css();

        return $output;

    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\ShowPlayerScoreTemplate('div', 'player-score', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }

}
