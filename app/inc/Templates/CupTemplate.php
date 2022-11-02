<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Templates;

use Scoremasters\Inc\Interfaces\TemplateInterface;

final class CupTemplate implements TemplateInterface
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
        $p1_name = $data['p1_name'];
        $p2_name = $data['p2_name'];
        $p1_score = 0;
        $p2_score = 0;
        
        $inner_tmp = '<!-- inner html -->';
        foreach($data['rounds'] as $round){
            $fixture_title = $round['fixture']->post_title;
            $p1_points = $round['p1_points'];
            $p2_points = $round['p2_points'];

            $p1_score += $round['p1_score'];
            $p2_score += $round['p2_score'];


            $inner_tmp .= $inner_html = <<<HTML
            <div class="week-score">
                <div class="week-score-home"><h4>{$p1_points}</h4></div>
                <div class="week-number">{$fixture_title}</div>
                <div class="week-score-away"><h4>{$p2_points}</h4></div>
            </div>
HTML;

        }


        $template_html = <<<HTML

        <div class="headsup-row">

            <div class="scm-player-headsup-home">
                <h4>{$p1_name}</h4>
            </div>
            <span class="score1">{$p1_score}</span>
            <span class="vs">-</span>
            <span class="score2">{$p2_score}</span>
            <div class="scm-player-headsup-away">
                <h4>$p2_name</h4>
            </div>
        </div>

        <div class="fixture-results">
           <!-- inner html -->
           {$inner_tmp}
            
        </div>
HTML;

  

        return $template_html;
    }

    public function get_css(array $data = array()): string
    {
        $template_css = <<<HTML
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
    .week-score {
        display: flex;
    background-color: var( --e-global-color-c15c190 );
    padding: 8px;
    color: #fff;
    justify-content: center;
    border-bottom: 2px solid #E1C983;
    }

    .week-score-home , .week-score-away {
        height:50px;
        width: 50px;
        background:#0A1015;
        display: flex;
        justify-content: center;
    }

     .week-score-home h4, .week-score-away h4{
        margin:0px;

    }

    .week-score {
        display: flex;
        flex-wrap: nowrap;
        justify-content: space-evenly;
        align-items: baseline;
    }

    .week-number {
        color:#848484;
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
    }
    .scm-player-headsup-home {
        padding: 16px;
        margin:  0px 16px 0px 16px;
    }

    .fixture-results {
        margin-bottom: 4em;  
    }
}

        </style>
HTML;

        return $template_css;
    }
}
