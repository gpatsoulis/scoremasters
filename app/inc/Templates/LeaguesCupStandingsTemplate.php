<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Templates;

use Scoremasters\Inc\Interfaces\TemplateInterface;

final class LeaguesCupStandingsTemplate implements TemplateInterface
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

        $form = $data['form'];
        $html_form = $this->setFormHtml($form);


        $template_html = <<<HTML

        <div class="leaguescupstandings">
            <div class="leaguescupstandingsheader">
                <div class="leaguescup-position leaguescup-header">
                    Θέση
                </div>
                <div class="leaguescup-team-logo"></div>
                    <div class="leaguescup-team-name">Ομάδα</div>
                <div class="leaguescup-points leaguescup-header">
                    Π
                </div>
                <div class="leaguescup-matches leaguescup-header">
                    Α
                </div>
                <div class="leaguescup-wins leaguescup-header">
                    Ν
                </div>
                <div class="leaguescup-draws leaguescup-header">
                    Ι
                </div>
                <div class="leaguescup-loses leaguescup-header">
                    Η
                </div>
                <div class="leaguescup-pluspoints leaguescup-header">
                    <!-- Π+ -->
                </div>
                <div class="leaguescup-minuspoints leaguescup-header">
                    <!-- Π- -->
                </div>
            </div>
            <!-- Standing Rows-->
            <div class="leaguescupstandingsrow">
                <div class="leaguescup-position">
                    {$data['league_position']}
                </div>
                    <div class="leaguescup-tea-logmo">
                        {$data['home_thumbnail']}
                    </div>
                    <div class="team-bestplayer">
                        <div class="team-name">{$data['league_name']}</div>
                        <div class="bestplayer">{$data['lead_scorer']}</div>
                    </div>
                <div class="leaguescup-points-space">
                    <div class="leaguescup-points">{$data['score']}</div>
                    <div class="leaguescup-bestplayerpoints">{$data['lead_scorer_points']}</div>
                </div>
                <div class="statsandpoints">
                    <div class="top-row">
                        <div class="column-1">
                                <div class="leaguescup-matches">{$data['total_matches']}</div>
                        </div>
                        <div class="column-2">
                            <div class="leaguescup-wins">{$data['total_win']}</div>
                        </div>
                        <div class="column-3">
                            <div class="leaguescup-draws">{$data['total_draw']}</div>
                        </div>
                        <div class="column-4">
                            <div class="leaguescup-loses">{$data['total_loss']}</div>
                        </div>
                        <div class="column-5">
                            <div class="leaguescup-pluspoints"></div>
                        </div>
                        <div class="column-6">
                            <div class="leaguescup-minuspoints"></div>
                        </div>
                    </div>
                    <div class="bottom-row">
                        {$html_form}
                    </div>
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
            .leaguescupstandings {
                display: flex;
                width: 100%;
                flex-direction: column;
                justify-content: space-around;
            }

            .leaguescupstandingsheader {
                display: grid;
                grid-template-columns: 1fr 3fr 5fr 2fr 1fr 1fr 1fr 1fr 1fr 1fr;
            }
            
            .leaguescupstandingsheader {
                color: var(--e-global-color-accent);
            }
            
            .leaguescup-team .leaguescup-header {
                display: flex;
                flex-direction: row;
                
            }
            
            .leaguescupstandingsrow {
                align-items: center;
                border-bottom: 1px solid var(--jkit-txt-m-color);
                padding-bottom: 20px;
                margin-bottom:20px;
                display: grid;
                grid-template-columns: 1fr 3fr 5fr 2fr 6fr;
            }

            .leaguescupstandingsrow .leaguescup-position  {
                color: var( --e-global-color-accent );
                font-family: var( --e-global-typography-c8c68e1-font-family ), Sans-serif;
                font-size: var( --e-global-typography-secondary-font-size );
                font-weight: var( --e-global-typography-c8c68e1-font-weight );
                text-transform: var( --e-global-typography-c8c68e1-text-transform );
                line-height: var( --e-global-typography-c8c68e1-line-height );
                letter-spacing: var( --e-global-typography-c8c68e1-letter-spacing );
                word-spacing: var( --e-global-typography-c8c68e1-word-spacing );
            }
            
            .leaguescup-team-space .leaguescup-team-logo {
                max-width: 25%;
                padding: 20px 40px;
            }
            
            .leaguescup-team-space {
                display: flex;
                flex-grow:4;
                flex-direction: row;
            }
            
            .leaguescup-tea-logmo img {
                max-width: 80%;
            }
            
            .team-bestplayer {
                align-items: center;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            
            .team-name {
                width: 100%;
                color: var(--e-global-color-primary );
                font-family: var(--e-global-typography-c8c68e1-font-family ), Sans-serif;
                font-size: var(--e-global-typography-c8c68e1-font-size );
                font-weight: var(--e-global-typography-c8c68e1-font-weight );
                text-transform: var(--e-global-typography-c8c68e1-text-transform );
                line-height: var(--e-global-typography-c8c68e1-line-height );
                letter-spacing: var(--e-global-typography-c8c68e1-letter-spacing );
                word-spacing: var(--e-global-typography-c8c68e1-word-spacing );
            }
            
            .bestplayer , .leaguescup-bestplayerpoints {
                width: 100%;
                color: #96989B;
                font-weight: 400;
            }
            
            .leaguescup-points , .leaguescup-bestplayerpoints {
                text-align: center;
            }
            
            .leaguescup-points-space .leaguescup-points {
                color: var(--e-global-color-accent );
                font-family: var(--e-global-typography-c8c68e1-font-family ), Sans-serif;
                font-size: var(--e-global-typography-secondary-font-size );
                font-weight: var(--e-global-typography-c8c68e1-font-weight );
                text-transform: var(--e-global-typography-c8c68e1-text-transform );
                line-height: var(--e-global-typography-c8c68e1-line-height );
                letter-spacing: var(--e-global-typography-c8c68e1-letter-spacing );
                word-spacing: var(--e-global-typography-c8c68e1-word-spacing );
            }

            .top-row {
                display: grid;
                grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr ;
                font-weight:500;
                padding-bottom: 10px;
            }
            
            .leaguescup-matches {
                color: #96989B;
            }
            
            .leaguescup-pluspoints, .leaguescup-minuspoints {
                color: var(--e-global-color-accent );
            }
            
            .bottom-row {
                display: grid;
                grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr 1fr 1fr 1fr 1fr ;
            }
            
            .leaguescup-stats-w, .leaguescup-stats-l , .leaguescup-stats-d{
                text-align: center;
                border-radius: 8px;
                margin-right: 8px;
            }
            
            .leaguescup-stats-w{
                background-color: green;
            }
            
            .leaguescup-stats-l{
                background-color: red;
            }
            
            .leaguescup-stats-d{
                background-color: orange;
            }
            
            @media screen and (max-width:767px){
                .leaguescupstandings {
                    width:150%;
                    overflow: scroll;
                }
                
                .leaguescupstandingsrow .leaguescup-position, .leaguescup-points-space .leaguescup-points, .team-name {
                    font-size: 16px;
                }

                .team-name , .leaguescup-points-space .leaguescup-points {
                    font-size:20px;
                }
            }
            
        </style>
HTML;

        return $css;
    }

    public function setFormHtml(array $form): string
    {
        $html = '';
        foreach ($form as $symbol){
            $lower_symbol = strtolower($symbol);
            $html .= <<<HTML
                <div class="leaguescup-stats-{$lower_symbol}">{$symbol}</div>
HTML;
        }
        
        return $html;
    }

    

}