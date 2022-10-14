<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\Player;

//[Scoremasters\Inc\Shortcodes\PlayerProfileLeagueShortcode]
class PlayerProfileLeagueShortcode
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

        $league_id = $player->get_league();

        $league = get_post($league_id);

        $data['league_name'] = $league->post_title;
        $data['league_image_url'] = get_the_post_thumbnail_url($league);

        $output = $this->template->container_start;
        $output .= $this->template->get_html($data);
        $output .= $this->template->container_end;
        $output .= $this->template->get_css();

        return $output;
    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\PlayerProfileLeagueTemplate('div', 'scm-player-league-info', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }
}