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

        $home_players = $this->getPlayersHTML($data['home_players'],$data['fixture']);
        $away_players = $this->getPlayersHTML($data['away_players'],$data['fixture']);

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
                    Π+
                </div>
                <div class="leaguescup-minuspoints leaguescup-header">
                    Π-
                </div>
            </div>
            <!-- Standing Rows-->
            <div class="leaguescupstandingsrow">
                <div class="leaguescup-position">
                    1
                </div>
                    <div class="leaguescup-tea-logmo">
                        <img src="http://localhost/wpsetup/wp-content/uploads/2022/06/antikouvades.png">
                        </img>
                    </div>
                    <div class="team-bestplayer">
                        <div class="team-name">Roligans</div>
                        <div class="bestplayer">Γρηγόρης Μαθιουδάκης</div>
                    </div>
                <div class="leaguescup-points-space">
                    <div class="leaguescup-points">42</div>
                    <div class="leaguescup-bestplayerpoints">379</div>
                </div>
                <div class="statsandpoints">
                    <div class="top-row">
                        <div class="column-1">
                                <div class="leaguescup-matches">18</div>
                        </div>
                        <div class="column-2">
                            <div class="leaguescup-wins">14</div>
                        </div>
                        <div class="column-3">
                            <div class="leaguescup-draws">0</div>
                        </div>
                        <div class="column-4">
                            <div class="leaguescup-loses">4</div>
                        </div>
                        <div class="column-5">
                            <div class="leaguescup-pluspoints">2007</div>
                        </div>
                        <div class="column-6">
                            <div class="leaguescup-minuspoints">1521</div>
                        </div>
                    </div>
                    <div class="bottom-row">
                        <div class="leaguescup-stats-w">N</div>
                        <div class="leaguescup-stats-l">H</div>
                        <div class="leaguescup-stats-d">I</div>
                        <div class="leaguescup-stats-w">N</div>
                        <div class="leaguescup-stats-l">H</div>
                        <div class="leaguescup-stats-d">I</div>
                        <div class="leaguescup-stats-w">N</div>
                        <div class="leaguescup-stats-l">H</div>
                        <div class="leaguescup-stats-d">I</div>
                        <div class="leaguescup-stats-w">N</div>
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
            }
            
        </style>
HTML;

        return $css;
    }

    public function getPlayersHTML(array $players,string $fixture_id){//$data['fixture']
        $html = '';
        
        foreach($players as $player){
            $html .= <<<HTML
                <div class="league-player">
                    <div class="leaguescup-player-name">{$player->wp_player->display_name}</div>
                    <div class="leaguescup-player-score">{$player->current_fixture_points()}</div>
                </div>
HTML; 
        }
        return $html;
    }

}