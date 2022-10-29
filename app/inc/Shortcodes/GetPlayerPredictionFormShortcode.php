<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\Player;

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
        ){
            //valid request
            $selected_fixture = get_post(filter_var($_POST['fixture_id'], FILTER_VALIDATE_INT)) ?? ScmData::get_current_fixture();
            $selected_user_id    = filter_var($_POST['player_id'], FILTER_VALIDATE_INT) ?? $user->ID;

            //get db data
            $predictions = ScmData::get_all_player_predictions_for_fixture( $selected_fixture, $selected_user_id );
        }

        //setup template data
        $fixtures = ScmData::get_all_fixtures_for_season();
        $players  = ScmData::get_all_scm_users();

        $data['fixtures'] = $fixtures;
        $data['players']  = $players;
        if(isset($predictions)){
            $data['predictions']  = $predictions;
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


        return $output;
    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\GetPlayerPredictionFormTemplate('div', 'scm-predictions-select', 'select-prediction-form', array('name' => 'player_id', 'value' => get_current_user_id()));
    }

    private function setup_prediction_data( $data ){
        if(!isset($data['predictions'])) {
            return '<!-- No availiable predictions >';
        }

        // map data for template
        /* 
        [
            'match_data' => ['name','date'],
            'preditions => [
                [ ... ]
            ]
        
        ]
        */


    }
}