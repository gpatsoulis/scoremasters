<?php
/**
 * @package scoremasters
 */

 namespace Scoremasters\Inc\Services;

 use Scoremasters\Inc\Classes\Player;

class CalculateForm {

    public $player;

    // instead of player use class that implements canHaveScore interface
    public function __construct( Player $player ){
        $this->player = $player;
    }

    public function getForm():array {
        $score_per_fixture = $this->player->player_points;

        $last_10_fixtures = array_slice($score_per_fixture, -10);

        $form = [];

        foreach ( $last_10_fixtures as $fixture_games ){
            if(isset($fixture_games['weekly-championship']['score'])){
                $week_score = $fixture_games['weekly-championship']['score'];
                $result_string = $this->getResult($week_score);
                array_unshift($form,$result_string);
            } 
        }

        return $form;
    }

    public function getResult( int $score ):string {

        $result = null;

        switch( $score ){
            case 0:
                $result = 'H';
                break;
            case 1:
                $result = 'I';
                break;
            case 3:
                $result = 'N';
                break;
        }

        return $result;
    }

}
/*
a:12:{
    s:16:"fixture_id_10686";a:7:{
        s:14:"match_id_10672";a:1:{s:13:"season-league";a:1:{s:6:"points";i:8;}}
        s:19:"weekly-championship";a:4:{s:6:"points";i:14;s:5:"score";i:1;s:11:"opponent_id";i:3;s:20:"home_field_advantage";b:1;}
        s:14:"match_id_10679";a:1:{s:13:"season-league";a:1:{s:6:"points";i:0;}}
        s:14:"match_id_10696";a:1:{s:13:"season-league";a:1:{s:6:"points";i:2;}}
        s:14:"match_id_10698";a:1:{s:13:"season-league";a:1:{s:6:"points";i:0;}}
        s:14:"match_id_10700";a:1:{s:13:"season-league";a:1:{s:6:"points";i:4;}}
        s:14:"match_id_10701";a:1:{s:13:"season-league";a:1:{s:6:"points";i:0;}}
    }
    s:12:"total_points";a:2:{
        s:13:"season-league";i:169;
        s:19:"weekly-championship";i:19;
    }
*/