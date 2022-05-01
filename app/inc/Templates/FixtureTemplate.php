<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Templates;

use Scoremasters\Inc\Interfaces\TemplateInterface;

final class FixtureTemplate implements TemplateInterface
{
	public string $container_start;
	public string $container_end;

	public function __construct(string $container = 'div',string $class_name = '', string $id_name = '',array $data_items = array()){
		$this->container_start = "<{$container} class='{$class_name}' id='{$id_name}' data-{$data_items['name']}='{$data_items['value']}'>";
		$this->container_end = "</{$container}>";
	}

    public function get_html(array $data):string
    {
        $template_html = <<<HTML
        <div class="scm-fixture-list" data-player_id="{$data["player-id"]}">
           <div class="scm-fixture-list-row" data-match_id="{$data["match-id"]}" data-match_date_gmt="{$data["match-date-gmt"]}">
               <div class="home-container">
                           <div class="team-image">
                               {$data["home-team-image"]}
                           </div>
                           <h4 class="scm-home-team" data-team_id="{$data["home-team-id"]}">
                               {$data["home-team-name"]}
                           </h4>
               </div>
               <div class="match-details">
                           <div class="bet-button">
							   <button class='activate-prediction-popup'>Παίξε</button>
							   <!--
                               <form action="<? bloginfo('url'); ?>" method="get">
                                   <input type="submit" name="submit" value="Παίξε">
                               </form>
	-->
                           </div>
                           <div class="match-sub-details">
                               <div class="stadium">
                                   <h5>{$data["stadium"]}</h5>
                               </div>
                               <div class="match-date">
                                   <h5>{$data["match-date"]}</h5>
                               </div>
                           </div>
               </div>
               <div class="away-container">
                   <h4 class="scm-away-team" data-team_id="{$data["away-team-id"]}">
                       {$data["away-team-name"]}
                   </h4>
                   <div class="team-image">
                       {$data["away-team-image"]}
                   </div>
               </div>
           </div>
       </div>
HTML;

        return $template_html;
    }

    public function get_css( array $data = array()):string
    {
        $template_css = <<<HTML
		<style>
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

			.team-image {
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

		</style>
HTML;

        return $template_css;
    }
}
