<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Abstracts\Competition;
use Scoremasters\Inc\Base\ScmData;

class CalculateScoreMastersCupParticipants  {

    public array $participants = array();
    public int $participants_number = 0;
    public array $players_shorted_by_score = array();


    public function __constructor(){

        //check its the 3 weekly fixture of current season
        // todo:

        $this->participants = ScmData::get_all_players();

    }

    public  function set_number_of_participants(){

        $players_no = count($this->participants);
        
        $power = 0;
        $number = 2;
        
        $exp = pow($number,$power);
        
        while (($players_no - $exp) > 0) {
        
            $power += 1;
            $exp = pow($number,$power);
        }
        
        $participants_no = pow($number,$power - 1);
        $this->participants_number = $participants_no;

        return $this;

    }


    public function set_players_shorted_by_score(){
       
        $players_array = $this->participants;

        usort($players_array, array($this,'score_comparator'));

        $this->players_shorted_by_score = $players_array;

        return $this;

    }

    public function score_comparator($player_1,$player_2){
        return $player_1->current_season_points < $player_2->current_season_points;
    }

    public function set_cup_players(){

        $shorted_array = $this->players_shorted_by_score;
        $cup_participants = array_slice($shorted_array, 0, $this->participants_number);

        $this->cup_participants = $cup_participants;

        return $this;
    }

    public function save_cup_participants(){
        //save participants as meta field to scoremasterscup post
    }
}