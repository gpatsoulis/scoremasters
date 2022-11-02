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

        .scm-fixture-list {
				list-style: none;
				padding:0px;
		}

		.scm-fixture-list .scm-fixture-list-row {
			display: flex;
			justify-content: space-between;
			margin-top: 40px;
			margin-bottom: 0px;
		}

		.scm-fixture-list-row .scm-home-team, .scm-fixture-list-row .scm-away-team {
			-webkit-box-decoration-break: clone;
			box-decoration-break: clone;
	 		display: inline;
			font-family: var( --e-global-typography-9dd0905-font-family ), Sans-serif;
			font-size: var( --e-global-typography-9dd0905-font-size );
			font-weight: var( --e-global-typography-9dd0905-font-weight );
			line-height: var( --e-global-typography-9dd0905-line-height );
			letter-spacing: var( --e-global-typography-9dd0905-letter-spacing );
			word-spacing: var( --e-global-typography-9dd0905-word-spacing );
		    color: var( --e-global-color-primary );
			text-transform: uppercase;
		}
		.team-image{
			width:80px;
			display: block;
			object-fit: contain;
		}

		.home-container {
			width:40%;
			background-color: transparent;
			background-image: linear-gradient(120deg, var( --e-global-color-c15c190 ) 88%, #5E33DF00 19%);
			display:flex;
			padding:30px 80px 0px 30px;
			flex-direction: row;
			justify-content: flex-start;
			gap: 24px;
		}
		.away-container {
			width:40%;
			background-color: transparent;
			background-image: linear-gradient(60deg, #5E33DF00 12%, var( --e-global-color-c15c190 ) 0%);
			display:flex;
			padding:30px 30px 0px 80px;
			flex-direction: row;
			justify-content: flex-end;
			gap: 24px;
		}

		.match-details {
			width:20%;
			display: flex;
			position:relative;
			align-content: center;
	   		align-items: center;
			flex-direction: column;
			justify-content: space-between;
		}
		.stadium h5 , .match-date h5 {
			color: var( --e-global-color-primary );
			font-family: var( --e-global-typography-fee40cf-font-family ), Sans-serif;
			font-size: var( --e-global-typography-fee40cf-font-size );
			font-weight: var( --e-global-typography-fee40cf-font-weight );
			text-transform: var( --e-global-typography-fee40cf-text-transform );
			line-height: var( --e-global-typography-fee40cf-line-height );
			letter-spacing: var( --e-global-typography-fee40cf-letter-spacing );
			word-spacing: var( --e-global-typography-fee40cf-word-spacing );
			margin: 10px 0px 0px 0px;
			text-align: center;
		}
		/*  popup styles  */ 
		.team-names-container h3 {
			color: #fff !important;
			font-size: 20px !important;
			justify-content: space-between !important;
		}
		.team-names-container {
			display: flex;
			justify-content: space-around;
		}

		.scm-match-finished {
			display: flex;
			flex-direction: column;
			align-items: center;
			
		}
		.scm-match-finished h3 {
			margin: 5px 0 0 0 ;
		}
		.player-prediction {
			display: flex;
			background-color: var( --e-global-color-c15c190 );
			padding: 8px;
			color:#fff;
			justify-content: center;
			border-bottom: 2px solid #E1C983;
		}

		@media only screen and (max-width: 600px){

			.scm-fixture-list-row .scm-home-team, .scm-fixture-list-row .scm-away-team{
				font-size: 12px !important;
				text-align:center !important;
			}
			.home-container {
				padding: 30px 30px 0px 15px !important;
				flex-direction: column !important;
				gap: 0px !important;
				justify-content:space-around !important;
			}
			.away-container {
				padding: 30px 15px 0px 30px !important;
				flex-direction: column-reverse !important;
				gap: 0px !important;
				justify-content:space-around !important;
			}
			.stadium h5, .match-date h5{
				letter-spacing:0px !important;
				font-size:12px !important;
			}
			.team-image {
				width: 60% !important;
				align-self:center;
			}
		}
        </style>
HTML;

        return $css;
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

    private function show_predictions($data):string {
        if(!isset($data['predictions'])) {
            return '<!-- No availiable predictions >';
        }

        $output = '';
        foreach ($data['predictions_data'] as $template_data){

            $output .= self::single_prediction_template($template_data);
        }

        return $output;
    }

    private function single_prediction_template( $data ){

        $template_html = <<<HTML
        <div class="scm-fixture-list">
           <div class="scm-fixture-list-row" >
               <div class="home-container">
                    <div class="team-image">
                        {$data["home-team-image"]}
                    </div>
                    <h4 class="scm-home-team">
                        {$data["home-team-name"]}
                    </h4>
               </div>
               <div class="match-details">
                    <div class="match-sub-details">
                        <div class="stadium">
                            <h5>{$data["stadium"]}</h5>
                        </div>
                        <div class="match-date">
                            <h5>{$data["match-date-string"]}</h5>
                        </div>
                    </div>
               </div>
               <div class="away-container">
                   <h4 class="scm-away-team">
                       {$data["away-team-name"]}
                   </h4>
                   <div class="team-image">
                       {$data["away-team-image"]}
                   </div>
               </div>
           </div>
		   <p class="player-prediction">{$data["prediction-string"]}</p>
       </div>
HTML;

        return $template_html;

    }
}