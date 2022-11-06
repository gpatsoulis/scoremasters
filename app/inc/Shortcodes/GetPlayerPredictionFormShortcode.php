<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Services\CalculatePossiblePlayerPoints;
use Scoremasters\Inc\Classes\FootballMatch;

//[Scoremasters\Inc\Shortcodes\GetPlayerPredictionFormShortcode]
class GetPlayerPredictionFormShortcode
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

        if (isset($_POST['scm_get_players_predictions'])
            && wp_verify_nonce($_POST['scm_get_players_predictions'], 'submit_form')
        ) {
            //valid request
            $ui_fixture_id = filter_var($_POST['fixture_id'], FILTER_VALIDATE_INT);

            $selected_fixture = ( $ui_fixture_id ) ? get_post( intval($ui_fixture_id) ) : ScmData::get_current_fixture();

            $ui_user_id = filter_var($_POST['player_id'], FILTER_VALIDATE_INT);
            $selected_user_id = ( $ui_user_id ) ? $ui_user_id : $user->ID;

            //get db data
            $predictions = ScmData::get_all_player_predictions_for_fixture($selected_fixture, $selected_user_id);
        }

        //setup template data
        $fixtures = ScmData::get_all_fixtures_for_season();
        $players = ScmData::get_all_scm_users();

        $data['fixtures'] = $fixtures;
        $data['players'] = $players;
        if (isset($predictions)) {
            $data['predictions'] = $predictions;
            $data = self::setup_prediction_data( $data );
        }

        $output = $this->template->container_start;

        //todo: check if user has access to scm-players predictions
        /*
        if( false || !$scm_user->can_make_predictions() ){

        $output .= '<p> Θα πρέπει να δημιουργίσεται λογαριασμό! </p>';
        $output .= $this->template->container_end;

        return $output;
        }*/

        $output .= $this->template->get_html($data);
        $output .= $this->template->get_css();

        return $output;
    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\GetPlayerPredictionFormTemplate('div', 'scm-predictions-select', 'select-prediction-form', array('name' => 'player_id', 'value' => get_current_user_id()));
    }

    private function setup_prediction_data($data)
    {
        if (!isset($data['predictions'])) {
            return '<!-- No availiable predictions >';
        }

        $template_data = [];
        foreach ($data['predictions'] as $prediction) {

            $scm_prediction = unserialize($prediction->post_content);

            $info = explode('-', $prediction->post_title);
            $match_id = $info[0];

            $repeater_teams = get_field("match-teams", $match_id);

            $template_data[$prediction->post_title]["home-team-name"] = $repeater_teams[0]["home-team"][0]->post_title;
            $template_data[$prediction->post_title]["home-team-id"] = $repeater_teams[0]["home-team"][0]->ID;
            $template_data[$prediction->post_title]["home-team-image"] = get_the_post_thumbnail($repeater_teams[0]["home-team"][0]->ID);

            $template_data[$prediction->post_title]["stadium"] = $repeater_teams[0]["home-team"][0]->post_content;

            $template_data[$prediction->post_title]["away-team-name"] = $repeater_teams[0]["away-team"][0]->post_title;
            $template_data[$prediction->post_title]["away-team-id"] = $repeater_teams[0]["away-team"][0]->ID;
            $template_data[$prediction->post_title]["away-team-image"] = get_the_post_thumbnail($repeater_teams[0]["away-team"][0]->ID);

            $template_data[$prediction->post_title]["match-date-string"] = get_field('match-date', $match_id);

            $template_data[$prediction->post_title]["stadium"] = $repeater_teams[0]["home-team"][0]->post_content;

            $possible_points = new CalculatePossiblePlayerPoints($prediction);

            $current_match = new FootballMatch($match_id);
            $possible_points->get_points($current_match);
            $prediction_string = (string) $possible_points;
            $template_data[$prediction->post_title]['prediction-string'] = $prediction_string;
        }

        $data['predictions_data'] = $template_data;

        return $data;
    }
}
