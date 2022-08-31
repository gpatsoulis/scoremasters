<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Abstracts\Competition;
use Scoremasters\Inc\Classes\SeasonLeagueCompetition;
use Scoremasters\Inc\Base\ScmData;

class ScoreMastersCupCompetition extends Competition {


    public static function init_get_participants(){
        //get all players
        $all_players = ScmData::get_all_players();

        $players_no = count($all_players);
        //var_dump($players_no);
        
        $power = 0;
        $number = 2;
        
        $exp = pow($number,$power);
        
        while (($players_no - $exp) > 0) {
        
            $power += 1;
            $exp = pow($number,$power);
        }
        
        $participants_no = pow($number,$power - 1);
        
        // get the first XX players with the highest score
        
        $curent_seasonleague = ScmData::get_current_scm_competition_of_type('season-league');
        $season_league = new SeasonLeagueCompetition( $curent_seasonleague );
        $players = $season_league->get_players_shorted_by_score();

        $participants = array_slice($players,0,$participants_no);

        $this->participants = $participants;

    }

   
}