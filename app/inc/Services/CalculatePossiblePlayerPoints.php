<?php
/**
 * @package scoremasters
 *
 *  todo: this is a service, move to Service directory
 */

namespace Scoremasters\Inc\Services;


use Scoremasters\Inc\Classes\FootballMatch;
use Scoremasters\Inc\Classes\PlayerPrediction;;

class CalculatePossiblePlayerPoints
{
    public $prediction;

    public function __construct(\WP_Post $prediction_post)
    {
        $this->prediction = new PlayerPrediction($prediction_post);
    }

    public function get_points(FootballMatch $match)
    {
        $prediction_data = $this->prediction->get_prediction_data();
        $match_points_table = $match->points_table;

        $capabilityDiff = $match->home_team_dynamikotita - $match->away_team_dynamikotita;

        $player_points_table = array(
            "Επιθετικός" => 3,
            "Μέσος" => 5,
            "Αμυντικός" => 10,
        );

        $data = array();
        $tmp = array();
        foreach ($prediction_data as $key => $prediction_string) {
            unset($tmp);

            if ($key === 'Scorer') {
                $scorer_points = get_field('scm-player-points', $prediction_string);
                if (!$scorer_points) {
                    $player_position = get_field('scm-player-position', $prediction_string);
                    $scorer_points = $player_points_table[$player_position];
                }

                //$tmp['prediction_key'] = $key;
                $tmp['prediction_key'] = 'Σκόρερ';
              
                $tmp['prediction_string'] = (get_post($prediction_string))->post_title;
                $tmp['possible_points'] = $scorer_points;
                $data[] = $tmp;
                continue;

            }

            if($key === 'Double Points') {
                $tmp['prediction_key'] = 'Διπλασιασμός Πόντων';

                if($prediction_string === 'SHMEIO'){
                    $prediction_string = 'Σημείο';
                }

                $tmp['prediction_string'] = $prediction_string;
                $tmp['possible_points'] = '';
                $data[] = $tmp;
                continue;
            }

            $possible_points = $match_points_table[$capabilityDiff][$prediction_string];

            $tmp['prediction_key'] = $key;
            $tmp['prediction_string'] = $prediction_string;
            $tmp['possible_points'] = $possible_points;

            // todo: test
            if ($key === 'SHMEIO'){
                $tmp['prediction_key'] = 'Σημείο';
            }

            $data[] = $tmp;

        }

        $this->data = $data;
    }


    public function __toString(): string {
        $str = '';
        foreach( $this->data as $prediction){
            if( $prediction['possible_points'] === ''){
                $str .= $prediction['prediction_key'] . ': ' . $prediction['prediction_string'] . " | ";
                continue;
            }
            $str .= $prediction['prediction_key'] . ': ' . $prediction['prediction_string'] . ' Πόντοι: ' . $prediction['possible_points'] . " | ";
        }

        return $str;
    }

}
