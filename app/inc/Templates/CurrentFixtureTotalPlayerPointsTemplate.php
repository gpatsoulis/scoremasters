<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Templates;

use Scoremasters\Inc\Interfaces\TemplateInterface;

final class CurrentFixtureTotalPlayerPointsTemplate implements TemplateInterface
{
	public  $container_start;
	public  $container_end;

	public function __construct(string $container = 'div',string $class_name = '', string $id_name = '',array $data_items = array()){
		$this->container_start = "<{$container} class='{$class_name}' id='{$id_name}' data-{$data_items['name']}='{$data_items['value']}'>";
		$this->container_end = "</{$container}>";
	}

    public function get_html(array $data):string{
        $template_html = <<<HTML
   <div class='scm-total-points'>
        <h4 class="scm-total-points__title scm-competition">{$data['fixture_title']}</h4>
        <h4 class="scm-total-points__points scm-competition-points">Σύνολο πόντων : {$data['total_points']}</h4>
    </div>
HTML;

        return $template_html;
    }

    public function get_css( array $data = array()):string
    {
        $css = <<<HTML
        <style>
            .scm-total-points{
                margin-left: 30px;
            }
            .scm-total-points__title {
            }
            .scm-total-points__points {
               margin: 16.6px 0;
               font-size: 20px !important;
               text-transform: var( --e-global-typography-c9a579c-text-transform );
            }
        </style>
        HTML;
        return $css;
    }
}