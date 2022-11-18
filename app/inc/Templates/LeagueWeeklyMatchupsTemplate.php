<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Templates;

use Scoremasters\Inc\Interfaces\TemplateInterface;

final class LeagueWeeklyMatchupsTemplate implements TemplateInterface
{
	public  $container_start;
	public  $container_end;

	public function __construct(string $container = 'div',string $class_name = '', string $id_name = '',array $data_items = array()){
		$this->container_start = "<{$container} class='{$class_name}' id='{$id_name}' data-{$data_items['name']}='{$data_items['value']}'>";
		$this->container_end = "</{$container}>";
	}

    public function get_html(array $data):string{

        if(empty($data)){
            return '<!-- No available pairs-->';
        }

        $template_html = <<<HTML
<div class="headsup-row">
    <div class="scm-player-headsup-home">
        <h4>{$data['home']}</h4>
    </div>
    <span class="score1">{$data['home_score']}</span>
    <span class="vs">-</span>
    <span class="score2">{$data['away_score']}</span>
    <div class="scm-player-headsup-away">
        <h4>{$data['away']}</h4>
    </div>
</div>
HTML;

        return $template_html;
    }

    public function get_css( array $data = array()):string
    {
$css = <<<HTML
        <style>
.headsup-row {
        display:flex;
        justify-content:space-around;
        width: 100%;
        margin-bottom: 32px;
    }
    
    .scm-player-headsup-home {
        color: #fff;
        width: 40%;
        background-color: transparent;
        background-image: linear-gradient(120deg, var( --e-global-color-c15c190 ) 88%, #5E33DF00 19%);
        display: flex;
        padding: 30px 80px 0px 30px;
        flex-direction: row;
        justify-content: flex-start;
        gap: 24px;
    }
    
    .scm-player-headsup-away {
        color: #fff;
        width: 40%;
        background-color: transparent;
        background-image: linear-gradient(60deg, #5E33DF00 12%, var( --e-global-color-c15c190 ) 0%);
        display: flex;
        padding: 30px 30px 0px 80px;
        flex-direction: row;
        justify-content: flex-end;
        gap: 24px;
    }
    
    span.vs {
        color: var( --e-global-color-accent );
        font-family: var( --e-global-typography-c8c68e1-font-family ), Sans-serif;
        font-size: var( --e-global-typography-c8c68e1-font-size );
        font-weight: var( --e-global-typography-c8c68e1-font-weight );
        text-transform: var( --e-global-typography-c8c68e1-text-transform );
        line-height: var( --e-global-typography-c8c68e1-line-height );
        letter-spacing: var( --e-global-typography-c8c68e1-letter-spacing );
        word-spacing: var( --e-global-typography-c8c68e1-word-spacing );
    }

    span.score1, span.score2 {
        color: var( --e-global-color-accent );
        font-family: var( --e-global-typography-c8c68e1-font-family ), Sans-serif;
        font-size: var( --e-global-typography-secondary-font-size );
        font-weight: var( --e-global-typography-c8c68e1-font-weight );
        text-transform: var( --e-global-typography-c8c68e1-text-transform );
        line-height: var( --e-global-typography-c8c68e1-line-height );
        letter-spacing: var( --e-global-typography-c8c68e1-letter-spacing );
        word-spacing: var( --e-global-typography-c8c68e1-word-spacing );
    }
    
    @media only screen and (max-width: 600px)
{
     .scm-player-headsup-away h4 {
    font-size: 16px !important;
    text-align: right !important;
    }
 
    .scm-player-headsup-away {
         padding: 10px 20px 0px 80px;
    }
 
    .scm-player-headsup-home h4 {
        font-size: 16px !important;
        text-align: left !important;
        padding: 
    }
    .scm-player-headsup-home {
        padding: 10px 80px 0px 10px;
    }
 }
       
        </style>
HTML;

        return $css;
    }
}