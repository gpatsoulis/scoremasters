<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Templates;

use Scoremasters\Inc\Interfaces\TemplateInterface;

final class SeasonLeagueTemplate implements TemplateInterface
{
    public $container_start;
    public $container_end;

    public function __construct(string $container = 'div', string $class_name = '', string $id_name = '', array $data_items = array())
    {
        $this->container_start = "<{$container} class='{$class_name}' id='{$id_name}' data-{$data_items['name']}='{$data_items['value']}'>";
        $this->container_end = "</{$container}>";
    }

    public function get_html(array $data): string
    {
        $template_html = <<<HTML
<!--<div class='season-league-player-points'>-->
    <p class='player_rank'>{$data['aa']}</p>
    <!--<p class='player_nick_name'>{$data['player_nick_name']}</p>-->
    <p class='player_name'>{$data['player_name']}</p>
    <p class='player_points'>{$data['player_points']}</p>
    <div class='league_data'>
        <h4 class='player_league'>{$data['player_league']}</h4>
        
        <a href="{$data['league_url']}" class='league_image'>
                {$data['league_image']}
        </a>
        
        
    </div>
<!--</div>-->
HTML;

        return $template_html;
    }

    public function get_css(array $data = array()): string
    {
        $template_css = <<<HTML
        <style>
            .league_data {
                width: 100%;
                display: grid;
                grid-template-columns: 2fr 1fr;
                grid-template-rows: auto;
                padding: 0 20px;
            }

            .player_league {
                color: white;
            }

            .scm-season-league-score {
                display: grid;
                grid-template-columns: 1fr 3fr 1fr 2fr;
                grid-template-rows: auto;
                max-width: 800px;
                margin:0 auto;

                align-items: center;

                border: 1px solid var( --e-global-color-accent );
                overflow: scroll;
            }

            .scm-season-league-score p:nth-child(-n+4) {
                background-color: var( --e-global-color-accent );
                color: black;
               
            }
            
            .scm-season-league-score p {
                padding: 10px 20px;
                margin: 0;
                border-bottom: 1px solid var( --e-global-color-accent );
                color: white;
            }
            


        </style>
HTML;
        return $template_css;
    }
}
