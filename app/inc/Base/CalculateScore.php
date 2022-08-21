<?php
/**
 * @package scoremasters
 * 
 *  todo: this is a service, move to Service directory
 */

namespace Scoremasters\Inc\Base;

use Scoremasters\Inc\Classes\FootballMatch;

class CalculateScore
{

    public static function calculate_points_after_prediction_submit(\WP_Post $prediction_post, FootballMatch $match)
    {
        $prediction = $prediction_post;

        $prediction_content = unserialize($prediction->post_content);

        $player_id = $prediction->post_author;

        //boolean for double points
        $double_shmeio = $double_uo = $double_score = $double_scorer = false;

        $dynamikotita_home_team = $match->home_team_dynamikotita;
        $dynamikotita_away_team = $match->away_team_dynamikotita;

        $column = strval($dynamikotita_home_team - $dynamikotita_away_team);

        $points_table = $match->points_table;

        $prediction_points_shmeio = $prediction_points_under_over = $prediction_points_score = 0;
        $double_shmeio = $double_uo = $double_score = $double_scorer = false;

        if($prediction_content["SHMEIO"] && $prediction_content["SHMEIO"] != '-' ){
            $prediction_points_shmeio = $points_table[$column][$prediction_content["SHMEIO"]]; 
        }
        
        if($prediction_content["Under / Over"] != '-'){
            $prediction_points_under_over = $points_table[$column][$prediction_content["Under / Over"]];
        }
        
        if($prediction_content["score"] != '-'){
            $prediction_points_score = $points_table[$column][$prediction_content["score"]];
        }
       

        $home_team = $match->home_team;
        $away_team = $match->away_team;

        $actual_scorers = $match->scorers;

        $Half_time_score = $match->half_time_score;
        $final_score = $match->final_score;

        $total_points = 0;

        //check if prediction matches shmeio result

        if (intval($Half_time_score["scm-half-time-home-score"]) > intval($Half_time_score["scm-half-time-away-score"])) {

            if ((intval($final_score["scm-full-time-home-score"]) > intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "1/1") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            } elseif ((intval($final_score["scm-full-time-home-score"]) == intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "1/X") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            } elseif ((intval($final_score["scm-full-time-home-score"]) < intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "1/2") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            }

        } elseif (intval($Half_time_score["scm-half-time-home-score"]) == intval($Half_time_score["scm-half-time-away-score"])) {

            if ((intval($final_score["scm-full-time-home-score"]) > intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "X/1") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            } elseif ((intval($final_score["scm-full-time-home-score"]) == intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "X/X") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            } elseif ((intval($final_score["scm-full-time-home-score"]) < intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "X/2") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            }

        } elseif (intval($Half_time_score["scm-half-time-home-score"]) < intval($Half_time_score["scm-half-time-away-score"])) {

            if ((intval($final_score["scm-full-time-home-score"]) > intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "2/1") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            } elseif ((intval($final_score["scm-full-time-home-score"]) == intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "2/X") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            } elseif ((intval($final_score["scm-full-time-home-score"]) < intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "2/2") {

                $total_points = $total_points + intval($prediction_points_shmeio);
                $double_shmeio = true;
            }

        }

        if ((intval($final_score["scm-full-time-home-score"]) > intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "-/1") {

            $total_points = $total_points + intval($prediction_points_shmeio);
            $double_shmeio = true;
        }

        if ((intval($final_score["scm-full-time-home-score"]) == intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "-/X") {

            $total_points = $total_points + intval($prediction_points_shmeio);
            $double_shmeio = true;
        }

        if ((intval($final_score["scm-full-time-home-score"]) < intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "-/2") {

            $total_points = $total_points + intval($prediction_points_shmeio);
            $double_shmeio = true;
        }

        //check if prediction matches u/o result

        $total_goals = intval($final_score["scm-full-time-home-score"]) + intval($final_score["scm-full-time-away-score"]);

        if ($total_goals < 1.5 && $prediction_content["Under / Over"] == "Under 1.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals < 2.5 && $prediction_content["Under / Over"] == "Under 2.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals < 3.5 && $prediction_content["Under / Over"] == "Under 3.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals < 4.5 && $prediction_content["Under / Over"] == "Under 4.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals > 0.5 && $prediction_content["Under / Over"] == "Over 0.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals > 1.5 && $prediction_content["Under / Over"] == "Over 1.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals > 2.5 && $prediction_content["Under / Over"] == "Over 2.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals > 3.5 && $prediction_content["Under / Over"] == "Over 3.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals > 4.5 && $prediction_content["Under / Over"] == "Over 4.5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        }

        //These option are not in select option yet, because they are in Greek--- Start //

        elseif ($total_goals < 3 && $prediction_content["Under / Over"] == "1 ή 2") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals > 1 && $total_goals < 4 && $prediction_content["Under / Over"] == "2 ή 3") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals > 2 && $total_goals < 5 && $prediction_content["Under / Over"] == "3 ή 4") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals > 3 && $total_goals < 6 && $prediction_content["Under / Over"] == "4 ή 5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals == 1 && $prediction_content["Under / Over"] == "Ακριβώς 1") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals == 2 && $prediction_content["Under / Over"] == "Ακριβώς 2") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals == 3 && $prediction_content["Under / Over"] == "Ακριβώς 3") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals == 4 && $prediction_content["Under / Over"] == "Ακριβώς 4") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($total_goals == 5 && $prediction_content["Under / Over"] == "Ακριβώς 5") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif ($final_score["scm-full-time-home-score"] > 0 && $final_score["scm-full-time-away-score"] > 0 && $prediction_content["Under / Over"] == "goal goal – ναι") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        } elseif (($final_score["scm-full-time-home-score"] == 0 || $final_score["scm-full-time-away-score"] == 0) && $prediction_content["Under / Over"] == "goal goal – οχι") {

            $total_points = $total_points + intval($prediction_points_under_over);
            $double_uo = true;
        }
        //These option are not in select option yet, because they are in Greek--- End //

        //check if prediction matches u/o result

        if (($final_score["scm-full-time-home-score"] . "-" . $final_score["scm-full-time-away-score"]) == $prediction_content["score"]) {

            $total_points = $total_points + intval($prediction_points_score);
            $double_score = true;
        }

        //check if prediction matches scorers result

        $p_scorer = (intval($prediction_content["Scorer"]));
        
        

        if (in_array($p_scorer, $actual_scorers)) {
            $prediction_points_scorer = 0;

            //get custom point per scm-pro-player
            $p_scorer_points = get_field('scm-player-points',(get_post($p_scorer))->ID);

            //if pro-player has custom points
            if($p_scorer_points){
                $prediction_points_scorer = intval($p_scorer_points);
            }

            //if pro-player has not custom points use defaults
            if(!$p_scorer_points){

                if (get_field("scm-player-position", $p_scorer) == "Επιθετικός") {
                    $prediction_points_scorer = 4;
    
                } elseif (get_field("scm-player-position", $p_scorer) == "Μέσος") {
    
                    $prediction_points_scorer = 8;
    
                } elseif (get_field("scm-player-position", $p_scorer) == "Αμυντικός") {
    
                    $prediction_points_scorer = 9;
    
                }
            }

            $total_points = $total_points + $prediction_points_scorer;
            $double_scorer = true;
        }

        //check for double points
        if ($prediction_content["Double Points"] == "SHMEIO" && $double_shmeio) {
            $total_points = $total_points + intval($prediction_points_shmeio);
        }

        if ($prediction_content["Double Points"] == "UNDER / OVER" && $double_uo) {
            $total_points = $total_points + intval($prediction_points_under_over);
        }

        if ($prediction_content["Double Points"] == "SCORER" && $double_scorer) {
            $total_points = $total_points + $prediction_points_scorer;
        }

        //return ("Total Points: " . $total_points);

        if(SCM_DEBUG){
            error_log(__METHOD__ . ' column: ' .  json_encode($column));
            error_log(__METHOD__ . ' dynamikotita_home_team: ' .  json_encode($dynamikotita_home_team));
            error_log(__METHOD__ . ' dynamikotita_away_team: ' .  json_encode($dynamikotita_away_team));
        }
        return $total_points;

    }


    public static function calculate_match_shmeio(){

        //check if prediction matches shmeio result
        $match_shmeio_symbol_half = '';

        $diff_half = intval($final_score["scm-half-time-home-score"]) - intval($final_score["scm-half-time-away-score"]);

        switch (true) {
            case ($diff == 0):
                $match_shmeio_symbol = 'X';
                break;
            case ($diff > 0):
                $match_shmeio_symbol = '1';
                break;
            case ($diff < 0):
                $match_shmeio_symbol = '2';
                break;
        }


        $match_shmeio_symbol_full = '';

        $diff_full = intval($final_score["scm-full-time-home-score"]) - intval($final_score["scm-full-time-away-score"]);

        switch (true) {
            case ($diff_full == 0):
                $match_shmeio_symbol = 'X';
                break;
            case ($diff_full > 0):
                $match_shmeio_symbol = '1';
                break;
            case ($diff_full < 0):
                $match_shmeio_symbol = '2';
                break;
        }

        $match_shmeio = $match_shmeio_symbol_half + '/' + $match_shmeio_symbol_full;

        return $match_shmeio;
    }

    public static function compare_shmeio($player_shmeio,$match_shmeio){
        $match_shmeio_array = explode('/',$match_shmeio);
        $player_shmeio_array = explode('/',$player_shmeio);

        $success = (($player_shmeio_array[0] == '-') && ($player_shmeio_array[1] == $match_shmeio_array[1])) ||
        (($player_shmeio_array[0] == match_shmeio_array[0]) && ($player_shmeio_array[1] == $match_shmeio_array[1]));

        return $success;
    }

}
