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
        <div class="scm-competition-points-container">
    <div class="scm-competition-points-single-container">
        <div>
            <h4 class="scm-competition">{$data['name']}</h4>
        </div>
        <div>
            <h2 class="scm-competition-points">Σύνολο πόντων : {$data['points']}</h2>
        </div>
        <div>
            <h2 class="scm-competition-points">θέση : {$data['position']}</h2>
        </div>
    </div>
    </div>
        
HTML;

        return $template_html;
    }

    public function get_css( array $data = array()):string
    {
        $css = <<<HTML
        <style>
        .scm-competition-points-container {
            display: flex;
            flex-direction: column;
            --flex-direction: initial;
            --flex-wrap: initial;
            --justify-content: initial;
            --align-items: initial;
            --align-content: initial;
            --gap: initial;
            --flex-basis: initial;
            --flex-grow: initial;
            --flex-shrink: initial;
            --order: initial;
            --align-self: initial;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: var(--flex-direction);
            flex-wrap: var(--flex-wrap);
            -webkit-box-pack: var(--justify-content);
            -ms-flex-pack: var(--justify-content);
            justify-content: var(--justify-content);
            -webkit-box-align: var(--align-items);
            -ms-flex-align: var(--align-items);
            align-items: var(--align-items);
            -ms-flex-line-pack: var(--align-content);
            align-content: var(--align-content);
            gap: var(--gap);
            -ms-flex-preferred-size: var(--flex-basis);
            flex-basis: var(--flex-basis);
            -webkit-box-flex: var(--flex-grow);
            -ms-flex-positive: var(--flex-grow);
            flex-grow: var(--flex-grow);
            -ms-flex-negative: var(--flex-shrink);
            flex-shrink: var(--flex-shrink);
            -webkit-box-ordinal-group: var(--order);
            -ms-flex-order: var(--order);
            order: var(--order);
            -ms-flex-item-align: var(--align-self);
            align-self: var(--align-self);
            margin-left: 30px
        }
        
        .scm-competition-points-single-container {
            margin-bottom: 10px;
        }

        h4.scm-competition {
            padding: 0;
            margin: 0px 0px -10px 0px;
            color: var(--e-global-color-text );
            font-family: var( --e-global-typography-text-font-family ), Sans-serif;
            font-size: var( --e-global-typography-text-font-size );
            font-weight: var( --e-global-typography-text-font-weight );
            text-transform: var( --e-global-typography-text-text-transform );
            line-height: 1.5;
            letter-spacing: var( --e-global-typography-000f162-letter-spacing );
            word-spacing: var( --e-global-typography-000f162-word-spacing );
        }
        
        h2.scm-competition-points {
            color: var( --e-global-color-primary );
            font-family: var( --e-global-typography-c9a579c-font-family ), Sans-serif;
            font-size: var( --e-global-typography-c9a579c-font-size );
            font-weight: var( --e-global-typography-c9a579c-font-weight );
            text-transform: var( --e-global-typography-c9a579c-text-transform );
            line-height: var( --e-global-typography-c9a579c-line-height );
            letter-spacing: var( --e-global-typography-c9a579c-letter-spacing );
            word-spacing: var( --e-global-typography-c9a579c-word-spacing );
            display: block;
            margin-block-start: 0.83em;
            margin-block-end: 0.83em;
            margin-inline-start: 0px;
            margin-inline-end: 0px;
            overflow-wrap: break-word;
        }
    </style>
HTML;

    return $css; 

    }
}