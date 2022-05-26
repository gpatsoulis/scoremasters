<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Templates;

use Scoremasters\Inc\Interfaces\TemplateInterface;

final class FixturesSelectWeekTemplate implements TemplateInterface
{
	public string $container_start;
	public string $container_end;

	public function __construct(string $container = 'div',string $class_name = '', string $id_name = '',array $data_items = array()){
		$this->container_start = "<{$container} class='{$class_name}' id='{$id_name}' data-{$data_items['name']}='{$data_items['value']}'>";
		$this->container_end = "</{$container}>";
	}

    public function get_html(array $data):string{
        $template_html = <<<HTML
  <option value="{$data['fixture_id']}">{$data['fixture_title']} <!--{$data['fixture_start_date']} - {$data['fixture_end_date']}--></option>
HTML;

        return $template_html;
    }

    public function get_css( array $data = array()):string
    {

    }
}