<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Templates;

use Scoremasters\Inc\Interfaces\TemplateInterface;

final class FixturesWeeklyScoreTemplate implements TemplateInterface
{
	public string $container_start;
	public string $container_end;

	public function __construct(string $container = 'div',string $class_name = '', string $id_name = '',array $data_items = array()){
		$this->container_start = "<{$container} class='{$class_name}' id='{$id_name}' data-{$data_items['name']}='{$data_items['value']}'>";
		$this->container_end = "</{$container}>";
	}

    public function get_html(array $data):string{
        $template_html = <<<HTML

        <div class='scm-points-list-item'>
        <p class='scm-item-match-title'>{$data['match_title']}</p>
        <p class='scm-item-match-points'> Κερδισμένοι Πόντοι: {$data['user_points_for_match']}</p>
        </div>
HTML;

        return $template_html;
    }

    public function get_css( array $data = array()):string{
        $template_css = <<<HTML
		<style>
            .scm-points-list-item {
                display: flex;
                color: white;
            }
            .scm-item-match-title{
                margin-right: 20px;
            }
        </style>
HTML;

        return $template_css;
    }
}