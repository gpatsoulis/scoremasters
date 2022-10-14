<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;

//[Scoremasters\Inc\Shortcodes\FixturesSelectWeekShortcode]
class FixturesSelectWeekShortcode
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

        $fixtures = ScmData::get_all_fixtures_for_season(); 

        $output = $this->template->container_start;

        $action = htmlspecialchars($_SERVER['REQUEST_URI']);
        

        $output .= <<<HTML
        <form action="{$action}" method="post">
            <select name="fixture_id" id="scm-fixtures-selection-week">
HTML;
        if(empty($fixtures)){
            $data = array(
                'fixture_id' => -99,
                'fixture_title' => 'Δεν υπάρχουν αγωνιστικές εβδομάδες',
                'fixture_start_date' => '',
                'fixture_end_date' => '',
            );

            $output .= $this->template->get_html($data);
        }

        foreach( $fixtures as $fixture ){

            $data = array(
                'fixture_id' => $fixture->ID,
                'fixture_title' => $fixture->post_title,
                'fixture_start_date' => get_field('week-start-date',$fixture->ID),
                'fixture_end_date' => get_field('week-end-date',$fixture->ID),
            );

            $output .= $this->template->get_html($data);
        }

        $nonce = wp_nonce_field( 'submit_form', 'scm_fixture_setup' );

        $output .= <<<HTML
            </select>
            {$nonce}
            <input type="submit" name="submit" value="Προβολή" />
        </form>
HTML;

        $output .= $this->template->container_end;

        return $output;
    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\FixturesSelectWeekTemplate('div', 'scm-fixture-select', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }
}