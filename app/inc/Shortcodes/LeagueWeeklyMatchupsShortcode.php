<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\Player;
use Scoremasters\Inc\Classes\WeeklyMatchUps;

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
            $new_pairs[] = array(get_userdata($pairs[$z])->display_name, get_userdata($pairs[$z + 1])->display_name);

        }

        echo '<pre>';
        var_dump($new_pairs);
        echo '</pre>';
        //get all players scores
        // SELECT * FROM 

    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\LeagueWeeklyMatchupsTemplate('div', 'scm-season-league-score', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }
}