<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\Player;

//[Scoremasters\Inc\Shortcodes\CurrentFixtureTotalPlayerPointsShortcode]
class CurrentFixtureTotalPlayerPointsShortcode
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
        $data['total_points'] = $player->current_fixture_points();
        $data['fixture_title'] = (ScmData::get_current_fixture())->post_title ?? 'Τρέχουσα Εβδομάδα';

        $output = $this->template->container_start;
        $output .= $this->template->get_html($data);
        $output .= $this->template->container_end;
        $output .= $this->template->get_css();

        return $output;

    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\CurrentFixtureTotalPlayerPointsTemplate('div', 'scm-total-fixture-score', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }
}