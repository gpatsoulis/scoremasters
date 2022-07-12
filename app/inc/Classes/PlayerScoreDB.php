<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Base\ScmData;

class PlayerScoreDB
{
    public $player_id;
    public $total_points;
    public $score_array;

    protected $query_action;

    public function __construct($player_id){

        $this->player_id = $player_id;
        $season = ScmData::get_current_season();
        $this->score_array = get_user_meta( intval($user->ID), 'score_points_seasonID_' . $season->ID);
        $total_points = get_user_meta( intval($user->ID), 'total_points');
        $this->total_points = $total_points;
    }

    public function save_score(){
        //$player_id,$match_id,$fixture_id,$season_id,$competition_name,$score 

        $score_array['fixture_id' . $fixture_id]
            ['match_id' . $match_id]
            [$competition_name] = ['score' => $score,'place' => $place];

    }

}