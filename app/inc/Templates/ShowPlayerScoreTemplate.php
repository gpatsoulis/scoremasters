<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Templates;

use Scoremasters\Inc\Interfaces\TemplateInterface;

final class ShowPlayerScoreTemplate implements TemplateInterface
{
	public  $container_start;
	public  $container_end;

	public function __construct(string $container = 'div',string $class_name = '', string $id_name = '',array $data_items = array()){
		$this->container_start = "<{$container} class='{$class_name}' id='{$id_name}' data-{$data_items['name']}='{$data_items['value']}'>";
		$this->container_end = "</{$container}>";
	}

    public function get_html(array $data):string{

        $str = __METHOD__ ;
        if(empty($data)){
            return "<!-- { $str } Score Data -->";
        }

        $template_html = <<<HTML
        <!-- No Content -->
        <p>{$data['name']} : {$data['points']}</p>
        
HTML;

        return $template_html;
    }

    public function get_css( array $data = array()):string
    {

    }
}