<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Templates;

use Scoremasters\Inc\Interfaces\TemplateInterface;

final class CurrentPlayerMatchupTemplate implements TemplateInterface
{
	public  $container_start;
	public  $container_end;

	public function __construct(string $container = 'div',string $class_name = '', string $id_name = '',array $data_items = array()){
		$this->container_start = "<{$container} class='{$class_name}' id='{$id_name}' data-{$data_items['name']}='{$data_items['value']}'>";
		$this->container_end = "</{$container}>";
	}

    public function get_html(array $data):string{

        if(empty($data)){
            return '<!-- No MatchUp Data -->';
        }

        $template_html = <<<HTML
        <!-- No Content -->
        <h4> Ζευγάρια Αγωνιστικής - {$data['fixture']}</h4>
        <div>
        <p class="home">{$data['home']}</p>
        <p class="away">{$data['away']}</p>
        <div>
HTML;

        return $template_html;
    }

    public function get_css( array $data = array()):string
    {

    }
}