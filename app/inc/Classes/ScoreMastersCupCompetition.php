<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Abstracts\Competition;
use Scoremasters\Inc\Base\ScmData;

class ScoreMastersCupCompetition extends Competition {
    public static function get_participants(){
        //get all players
        $all_players = ScmData::get_all_players();

        $players_no = count($all_players);
        var_dump($players_no);
        
        $power = 0;
        $number = 2;
        
        $exp = pow($number,$power);
        
        while (($players_no - $exp) > 0) {
        
            $power += 1;
            $exp = pow($number,$power);
        }
        
        $participants_no = pow($number,$power - 1);
        


    }

   
}