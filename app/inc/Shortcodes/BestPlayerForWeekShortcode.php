<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\Player;

//[Scoremasters\Inc\Shortcodes\BestPlayerForWeekShortcode]
class BestPlayerForWeekShortcode
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

        $curent_season = ScmData::get_current_season();
        $curent_fixture = ScmData::get_current_fixture();

        //get all players scores
        // SELECT * FROM 

    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\CategoryChampionshipTemplate('div', 'scm-season-league-score', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }
}