<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Templates;

use Scoremasters\Inc\Interfaces\TemplateInterface;

final class LeaguesCupTemplate implements TemplateInterface
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

        $home_players = $this->getHomePlayersHTML($data['home_players'],$data['fixture']);
        $away_players = $this->getAwayPlayersHTML($data['away_players'],$data['fixture']);

        $template_html = <<<HTML

        <div class="leaguescup-fixture-results">
            <div class="leaguescup-home-league">
                <div class="leaguescup-home-team-name">
                    <h2>{$data['home']}</h2>
                </div>
                <a href="{$data['home_league_url']}">
                    <div class="scm-leaguescup-leaguelogo-home">{$data['home_thumbnail']}</div>
                </a>
                <div class="player-list">
                    {$home_players}
                    <!--<div class="league-player">
                        <div class="leaguescup-player-name">Φραγκίσκος Σαγκινέτος</div>
                        <div class="leaguescup-player-score">32</div>
                    </div>
                    <div class="league-player">
                        <div class="leaguescup-player-name">Φραγκίσκος Σαγκινέτος</div>
                        <div class="leaguescup-player-score">32</div>
                    </div>-->
                </div>
            </div>
            <div class="leaguescup-middleboard">
                <div class="leaguescup-scoreboard">
                    <span class="leaguescup-home-score">{$data['home_points']}</span>
                    <span class="leaguescup-away-score">{$data['away_points']}</span>
                </div>
                <div class="leaguescup-logo">
                    <img src="https://scoremasters.gr/wp-content/uploads/2022/04/LEAGUES-CUP.png"></img>
                </div>
            </div>
            <div class="leaguescup-away-league">
                <div class="leaguescup-away-team-name">
                    <h2>{$data['away']}</h2>
                </div>
                <a href="{$data['away_league_url']}">
                                <div class="scm-leaguescup-leaguelogo-away">{$data['away_thumbnail']}</div>
                </a>
                <div class="player-list">
                        {$away_players}
                        <!--<div class="league-player">
                            <div class="leaguescup-player-score">32</div>
                            <div class="leaguescup-player-name">Φραγκίσκος Σαγκινέτος</div>
                        </div>
                        <div class="league-player">
                            <div class="leaguescup-player-score">32</div>
                            <div class="leaguescup-player-name">Φραγκίσκος Σαγκινέτος</div>
                        </div>-->
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
            .leaguescup-fixture-results {
                display:flex;
                justify-content:space-around;
                width: 100%;
                margin-bottom: 32px;
            }

            .leaguescup-home-league {
                color: #fff;
                width: 40%;
                background-color: var( --e-global-color-c15c190 );
                display: flex;
                padding: 0% 2% 2% 2%;
                flex-direction: column;
                justify-content: flex-start;
                gap: 24px;
            }
            
            .leaguescup-home-team-name h2 {
                text-align: start;
                margin-bottom: 0px;
                height:120px;
            }

            .leaguescup-home-team-name h2::after {
                content: "";
                border-bottom: 1px solid var(--e-global-color-accent);
                display: block;
                width: 265%;
                position: relative;
                top: 10px;
            }

            .scm-leaguescup-leaguelogo-home {
                display:flex;
                justify-content:start;
            }

            .scm-leaguescup-leaguelogo-away {
                display:flex;
                justify-content:end;
            }

            .leaguescup-home-league img {
                width:150px;
                display: inline;
                align-self: start;
                margin-left: 2%;
            }
            
            .leaguescup-scoreboard {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                width:100%;
                padding: 0% 3%
            }

            .leaguescup-middleboard {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                width:20%;
            }

            .leaguescup-logo {
                padding:20%;
            }

            .leaguescup-away-league {
                color: #fff;
                width: 40%;
                background-color: var( --e-global-color-c15c190 );
                display: flex;
                padding: 0% 2% 2% 2%;
                flex-direction: column;
                justify-content: flex-start;
                gap: 24px;
            }
            
            .leaguescup-away-team-name h2 {
                text-align: end;
                margin-bottom: 0px;
                height:120px;
            }
            
            .leaguescup-away-league img {
                width:150px;
                display: inline;
                align-self: end;
                margin-right: 2%;
            }
            
            .player-list {
                width: 100%;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
            
            .league-player {
                width: 100%;
                display: flex;
                flex-direction: row;
                justify-content: space-between;
            }

            .leaguescup-player-name {
                color:#96989B;
                font-weight:400;
            }

            .leaguescup-home-league .leaguescup-player-name{
                text-align:start;
            }

            .leaguescup-home-league a,.leaguescup-away-league a {
                height:200px;
            }

            .leaguescup-away-league .leaguescup-player-name{
                text-align:end;
            }

            .leaguescup-player-score {
                font-weight:600;
                margin:0% 4%;
            }

            span.leaguescup-home-score , span.leaguescup-away-score {
                color: var( --e-global-color-accent );
                font-family: var( --e-global-typography-c8c68e1-font-family ), Sans-serif;
                font-size: var( --e-global-typography-secondary-font-size );
                font-weight: var( --e-global-typography-c8c68e1-font-weight );
                text-transform: var( --e-global-typography-c8c68e1-text-transform );
                line-height: var( --e-global-typography-c8c68e1-line-height );
                letter-spacing: var( --e-global-typography-c8c68e1-letter-spacing );
                word-spacing: var( --e-global-typography-c8c68e1-word-spacing );
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

            @media only screen and (max-width: 600px) {
                .leaguescup-home-team-name h2, .leaguescup-away-team-name h2 {
                    font-size:16px;
                    height:40px;
                }

                .leaguescup-home-league a,.leaguescup-away-league a {
                    height:100px;
                }

                .leaguescup-away-league img, .leaguescup-home-league img {
                    max-width:80px;
                }

                span.leaguescup-away-score, span.leaguescup-home-score {
                    font-size:32px;
                }
            }

        </style>
HTML;

        return $css;
    }

    public function getHomePlayersHTML(array $players,string $fixture_id){//$data['fixture']
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

    public function getAwayPlayersHTML(array $players,string $fixture_id){//$data['fixture']
        $html = '';
        
        foreach($players as $player){
            $html .= <<<HTML
                <div class="league-player">
                    <div class="leaguescup-player-score">{$player->current_fixture_points()}</div>
                    <div class="leaguescup-player-name">{$player->wp_player->display_name}</div>
                </div>
HTML; 
        }
        return $html;
    }

}