<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\Player;

//[Scoremasters\Inc\Shortcodes\SelectLeagueShortcode]
class SelectLeagueShortcode
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
        $user = wp_get_current_user();
        $scm_user = new Player($user);

        //var_dump($_POST);

        if (isset($_POST['scm_league_id'])
            && isset($_POST['scm_league_setup'])
            && wp_verify_nonce($_POST['scm_league_setup'], 'submit_form')) {

            $selected_league_id = filter_var($_POST['scm_league_id'], FILTER_VALIDATE_INT);

            $scm_user->set_scm_league($selected_league_id);
        }

        $output = $this->template->container_start;

        if($scm_user->can_make_predictions()){

            $output .= '<p> Έχει γίνει επιλογή πρωταθλήματος </p>';
            $output .= $this->template->container_end;

            return $output;
        }

        $public_leagues = ScmData::get_public_leagues();

        $action = htmlspecialchars($_SERVER['REQUEST_URI']);

        $output .= <<<HTML
        <form action="{$action}" method="post">
            <select name="scm_league_id" id="scm_league_id">
HTML;

        foreach ($public_leagues as $league) {

            $data = array(
                'league_id' => $league->ID,
                'league_title' => $league->post_title,
            );

            $output .= $this->template->get_html($data);
        }

        $nonce = wp_nonce_field('submit_form', 'scm_league_setup');

        $output .= <<<HTML
            </select>
            {$nonce}
            <input type="submit" name="submit" value="Επιλογή" />
        </form>
HTML;

        return $output;
    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\SelectLeagueTemplate('div', 'scm-league-select', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }
}
