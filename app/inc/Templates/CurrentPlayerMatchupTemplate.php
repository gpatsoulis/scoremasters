<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Templates;

use Scoremasters\Inc\Interfaces\TemplateInterface;

final class CurrentPlayerMatchupTemplate implements TemplateInterface
{
	public  $container_start;
	public  $container_end;

	public function __construct(string $container = 'div',string $class_name = '', string $id_name = '',array $data_items = array()){
		$this->container_start = "<{$container} class='{$class_name}' id='{$id_name}' data-{$data_items['name']}='{$data_items['value']}'>";
		$this->container_end = "</{$container}>";
	}

    public function get_html(array $data):string{

        if(empty($data)){
            return '<!-- No MatchUp Data -->';
        }

        $template_html = <<<HTML
        <div class="headsup-container">
    <div class="headsup-title">
        <h2><span class="colored-text">Επόμενος</span> Αντίπαλος</h2>
        <h4> Ζευγάρια Αγωνιστικής - {$data['fixture']}</h4>
    </div>
    <div class="versus-container">
        <div class="scm-player-vs">
            <img src="https://scoremasters.gr/wp-content/uploads/2022/07/Manchester_United_FC_crest.svg.png">
            <h5>{$data['home']}</h5>
        </div>
        <div style="align-self: center">
            <h3 class="versus-vs">V.S</h3>
        </div>
        <div class="scm-player-vs">
            <img src="https://scoremasters.gr/wp-content/uploads/2022/07/Manchester_United_FC_crest.svg.png">
            <h5>{$data['away']}</h5>
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
    .headsup-title {
        -webkit-box-decoration-break: clone;
        box-decoration-break: clone;
        font-family: var( --e-global-typography-secondary-font-family ), Sans-serif;
        font-size: var( --e-global-typography-secondary-font-size );
        font-weight: var( --e-global-typography-secondary-font-weight );
        text-transform: var( --e-global-typography-secondary-text-transform );
        color: var( --e-global-color-primary );
        padding: 0;
        margin: 0;
        text-align: center;
        display: block;
        margin-bottom: 20px;
    }
    
    .colored-text {
        color: var( --e-global-color-accent );
    }
    
    .versus-container {
        display: flex;
        justify-content: space-around;
    }
    
    .scm-player-vs {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .scm-player-vs img {
        max-width: 150px;
    }
    
    .scm-player-vs h5 {
        color: var( --e-global-color-primary );
        font-family: var( --e-global-typography-fee40cf-font-family ), Sans-serif;
        font-size: var( --e-global-typography-fee40cf-font-size );
        font-weight: var( --e-global-typography-fee40cf-font-weight );
        text-transform: var( --e-global-typography-fee40cf-text-transform );
        line-height: var( --e-global-typography-fee40cf-line-height );
        letter-spacing: var( --e-global-typography-fee40cf-letter-spacing );
        word-spacing: var( --e-global-typography-fee40cf-word-spacing );
        margin: 10px 0px 0px 0px;
    }
    
    .versus-vs {
        color: var( --e-global-color-primary );
        font-family: var( --e-global-typography-9dd0905-font-family ), Sans-serif;
        font-size: var( --e-global-typography-9dd0905-font-size );
        font-weight: var( --e-global-typography-9dd0905-font-weight );
        line-height: var( --e-global-typography-9dd0905-line-height );
        letter-spacing: var( --e-global-typography-9dd0905-letter-spacing );
        word-spacing: var( --e-global-typography-9dd0905-word-spacing );
        text-align: center;
    }
    
    @media screen and (max-width:360px){
        .versus-container {
            display: flex;
            justify-content: space-around;
            flex-direction: column;
        }
    }
    
</style>
HTML;

        return $css;

    }
}