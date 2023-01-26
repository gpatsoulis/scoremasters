<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

//use Scoremasters\Inc\Base\ScmData;
//use Scoremasters\Inc\Classes\FootballMatch;
//use Scoremasters\Inc\Base\CalculateScore;
use Scoremasters\Inc\Classes\CalculateMatchScore;

/*
 How to use
 $calc_score = new CalculateMatchScore(intval($post_id));

$calc_score->get_predictions()
    ->calculate_points()
    ->save_points();

echo $calc_score->showDiff();
 */

class RecalculateMatchScore extends CalculateMatchScore {

    public $score_diff;

    public function save_points(){

        foreach ($this->data_to_insert_in_db as $player_score_data) {

            $player_id = strval($player_score_data['player_id']);
            $match_id = strval($player_score_data['match_id']);
            $fixture_id = strval($player_score_data['fixture_id']);
            $season_id = strval($player_score_data['season_id']);
            $score = $player_score_data['score'];

            //current player score
            $players_score = get_user_meta((int) $player_id, 'score_points_seasonID_' . $season_id);

            //initialize
            if (!empty($players_score)) {
                $players_score = $players_score[0];
            } else {
                $players_score = array();
            }

            //log diffs before reseting score
            $this->logRecalculationDifferences( $players_score, $score, $fixture_id, $match_id, $player_id );

            //important - reset totals before adding new score and after $player_score initialization
            $players_score = $this->resetTotalPlayerPoints($players_score, $fixture_id, $match_id, $player_id);

            // recalculate changes 
            //points already in db
            /*
            if(isset($players_score['fixture_id_' . $fixture_id]['match_id_' . $match_id]['season-league']['points'])){
                continue;
            }
            */
            
            //match points for season-league competition
            $players_score['fixture_id_' . $fixture_id]['match_id_' . $match_id]['season-league']['points'] = $score;

            // total points
             if(!isset($players_score['total_points']['season-league'])){
                $players_score['total_points']['season-league'] = 0;
            }

            //this changes total score for recalculation
            $players_score['total_points']['season-league'] = intval($score) + intval($players_score['total_points']['season-league']);

            //weekly score for weekly championship competition
            if(!isset( $players_score['fixture_id_' . $fixture_id]['weekly-championship']['points'])){
                $players_score['fixture_id_' . $fixture_id]['weekly-championship']['points'] = 0;
            }
            $players_score['fixture_id_' . $fixture_id]['weekly-championship']['points'] += intval($score);


            // save player score
            $success = update_user_meta($player_id, 'score_points_seasonID_' . $season_id, $players_score);

            
            if (!$success) {
                error_log(__METHOD__ . ' error updating score metadata for user: ' . $player_id);
            }
            

        }

        return $this;
    }

    protected function resetTotalPlayerPoints( array $players_score, int $fixture_id, int $match_id, int $player_id ):array {

        if(!isset($players_score['fixture_id_' . $fixture_id]['match_id_' . $match_id]['season-league']['points'])){
            error_log( __METHOD__ . ' --error-- not a valid recalculation prev score doesn\'t exist player_id:'.$player_id . ' match_id:' . $match_id);
            //return false;
            return $players_score;
        }

        $prev_points = $players_score['fixture_id_' . $fixture_id]['match_id_' . $match_id]['season-league']['points'];

        $players_score['fixture_id_' . $fixture_id]['weekly-championship']['points'] -= $prev_points;
        $players_score['total_points']['season-league'] -= $prev_points;
        
        return $players_score;
    }

    protected function logRecalculationDifferences( array $players_score, $score, int $fixture_id, int $match_id, int $player_id){

        $prev_points = $players_score['fixture_id_' . $fixture_id]['match_id_' . $match_id]['season-league']['points'];

        $diff = floatval($score) - floatval($prev_points);
     
        if( $diff != 0 ){
            $this->score_diff[] = array('player_id' => $player_id, 'match_id' => $match_id, 'score_diff' => $diff);
        }
    }

    public function showDiff():string {

        if(empty($this->score_diff)){
            return '<h3> no score diffs </h3>';
        }

        $output = '<ul>';
        foreach( $this->score_diff as $diff){
            $user_name = get_user_by('ID', $diff['player_id'])->display_name;
            $match_title = get_post($diff['match_id'])->post_title;
            $output .= '<li> player name: ' .$user_name. ' match: ' .$match_title. 'points diff: ' . $diff['score_diff'];
        };
        $output .= '</ul>';

        return $output;
    }
}