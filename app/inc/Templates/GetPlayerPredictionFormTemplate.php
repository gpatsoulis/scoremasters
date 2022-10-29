<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Templates;

use Scoremasters\Inc\Interfaces\TemplateInterface;

final class GetPlayerPredictionFormTemplate implements TemplateInterface
{
	public  $container_start;
	public  $container_end;

	public function __construct(string $container = 'div',string $class_name = '', string $id_name = '',array $data_items = array()){
		$this->container_start = "<{$container} class='{$class_name}' id='{$id_name}' data-{$data_items['name']}='{$data_items['value']}'>";
		$this->container_end = "</{$container}>";
	}

    public function get_html(array $data):string{

        $action = htmlspecialchars($_SERVER['REQUEST_URI']);
        $nonce = wp_nonce_field('submit_form', 'scm_get_players_predictions');

        $datalist_fixtures = self::setup_fixtures_datalist($data['fixtures']);
        $datalist_players  = self::setup_players_datalist($data['players']);
        $prediction_output = self::show_predictions($data);

        

        $template_html = <<<HTML
        <form action="{$action}" method="post">
            <input list="scm-fixtures-list" name="fixture_id" id="fixture_name_selection">
                <datalist id="scm-fixtures-list">
                    {$datalist_fixtures}
                </datalist>
            <input list="scm-players-list" name="player_id" id="player_name_selection">
                <datalist id="scm-players-list">
                    {$datalist_players}
                </datalist>
            {$nonce}
            <input type="submit" name="submit" value="Προβολή Προβλέψεων" />
        </form>
        {$prediction_output}
HTML;

        return $template_html;
    }

    public function get_css( array $data = array()):string
    {
        $css = <<<HTML
        <style>
        form {
            color: black;
        }
        </style>
HTML;
    }

    private function setup_fixtures_datalist( array $fixtures ):string {

        $output = ''; 
        foreach ($fixtures as $fixture ){
            $fixture_id = $fixture->ID;
            $fixture_title = $fixture->post_title;

            $output .= "<option value=\"{$fixture_id}\">{$fixture_title}</option>";
        }

        return $output;
    }

    private function setup_players_datalist( array $players ):string {

        $output = ''; 
        foreach ($players as $player ){
            $player_id = $player->ID;
            $player_name = $player->display_name;

            $output .= "<option value=\"{$player_id}\">{$player_name}</option>";

        }

        return $output;
    }

    private function show_predictions($data){
        if(!isset($data['predictions'])) {
            return '<!-- No availiable predictions >';
        }

        // create output data for predictions

    }
}