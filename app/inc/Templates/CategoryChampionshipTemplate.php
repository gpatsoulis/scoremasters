<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Templates;

use Scoremasters\Inc\Interfaces\TemplateInterface;

final class CategoryChampionshipTemplate implements TemplateInterface
{
	public  $container_start;
	public  $container_end;

	public function __construct(string $container = 'div',string $class_name = '', string $id_name = '',array $data_items = array()){
		$this->container_start = "<{$container} class='{$class_name}' id='{$id_name}' data-{$data_items['name']}='{$data_items['value']}'>";
		$this->container_end = "</{$container}>";
	}

    public function get_html(array $data):string{
        $template_html = <<<HTML
<div class='season-league-player-points'>
    <p class='player_nick_name'>{$data['aa']}</p>
  <p class='player_nick_name'>{$data['player_nick_name']}</p>
  <p class='player_name'>{$data['player_name']}</p>
  <p class='player_points'>{$data['player_points']}</p>
  <!--<p class='player_league'>{$data['player_league']}</p>-->
</div>
HTML;

        return $template_html;
    }

    public function get_css( array $data = array()):string
    {
        $template_css = <<<HTML
        <style>
            .season-league-player-points {
                display:flex;
                justify-content: space-evenly;
                width:50%;
                
            }

            .season-league-player-points p{
                padding: 15px 10px;
                border: solid white 1px;
                margin: 0;
            }
            .season-league-player-points  p:nth-child(1)  {
                width: 100px; 
            }
            .season-league-player-points  p:nth-child(2)  {
                width: 200px; 
            }

            .season-league-player-points  p:nth-child(3)  {
                width: 300px; 
            }

            .season-league-player-points  p:nth-child(4)  {
                flex-grow: 2;
                width: 100px;
            }

         
        </style>
HTML;
        return $template_css;
    }
}