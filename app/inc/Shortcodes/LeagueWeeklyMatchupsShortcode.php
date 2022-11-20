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

        $current_league = get_post();
        
        
        $curent_season = ScmData::get_current_season();
        $curent_fixture = ScmData::get_current_fixture();

        //$player_league = $player->get_league();
        $current_league = get_post();
        $current_fixture = ScmData::get_current_fixture();

        $current_weekly_competition = ScmData::get_current_scm_competition_of_type('weekly-championship');
        $weekly_matchups = new WeeklyMatchUps($current_weekly_competition->ID);
        //$weekly_matchups->get_all_matchups();

        $pairs = $weekly_matchups->get_matchups()->by_fixture_id($current_fixture->ID)->by_league_id($current_league->ID);

        $new_pairs = [];
        for ($i = 0; $i < (count($pairs) / 2); $i++) {

            $z = 2 * $i;
            //$new_pairs[] = array(get_userdata($pairs[$z])->display_name, get_userdata($pairs[$z + 1])->display_name);
            $new_pairs[] = array(get_userdata($pairs[$z]), get_userdata($pairs[$z + 1]));

        }

        //todo: WeeklyMatchUps, check for artio number o players, else return empty array;

        //echo '<pre>';
        //var_dump($new_pairs);
        //echo '</pre>';
        
        
        $output = $this->template->container_start;
        $data = array();

        foreach($new_pairs as $pair){

            if(isset($pair[0]) && isset($pair[1])){
                $data['home'] = $pair[0]->display_name;
                $data['away'] = $pair[1]->display_name;

                $home_user = $pair[0];
                $away_user = $pair[1];

                //todo: check if isset[fixture_id]
                $data['home_score'] = (new Player($home_user))->player_points['fixture_id_'.$current_fixture->ID]['weekly-championship']['points'];
                $data['away_score'] = (new Player($away_user))->player_points['fixture_id_'.$current_fixture->ID]['weekly-championship']['points'];
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