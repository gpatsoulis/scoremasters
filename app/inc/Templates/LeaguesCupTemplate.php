<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Templates;

use Scoremasters\Inc\Interfaces\TemplateInterface;

final class LeaguesCupTemplate implements TemplateInterface
{
	public  $container_start;
	public  $container_end;

	public function __construct(string $container = 'div',string $class_name = '', string $id_name = '',array $data_items = array()){
		$this->container_start = "<{$container} class='{$class_name}' id='{$id_name}' data-{$data_items['name']}='{$data_items['value']}'>";
		$this->container_end = "</{$container}>";
	}

    public function get_html(array $data):string{

        if(empty($data)){
            return '<!-- No Player League Data -->';
        }

        $template_html = <<<HTML
        <div>{$data['home']}</div>
        <div>{$data['home_points']}</div>
        <div>{$data['away']}</div>
        <div>{$data['away_points']}</div>
HTML;

        return $template_html;

    }

    public function get_css( array $data = array()):string
    {
        $css = <<<HTML
        <style>

        </style>
HTML;

        return $css;
    }

}