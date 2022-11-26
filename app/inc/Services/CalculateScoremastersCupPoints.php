<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Services;

use Scoremasters\Inc\Classes\Player;

final class CalculateScoremastersCupPoints
{

    public static function calculate(array $cup_matchups, int $fixture_id): array
    {
        // for each pair
        // get player A week score, get player B week score
        // winner (player with greatest score ) gets 1 point

        // get_users
        $score = [];
        foreach ($cup_matchups as $matchups) {

            //$players = array_map( $fn( \WP_user $scm_user ):Player => new Player( $scm_user ), $users);
            $players = array_map(
                function (\WP_user $scm_user): Player {
                    return new Player($scm_user);
                },
                $matchups
            );

            

            if (!isset($players[0]->player_points['fixture_id_' . $fixture_id]['weekly-championship']['score'])) {
                error_log(__METHOD__ . ' - error no score for player: ' . $players[0]->player_id);
                $player_a_score = self::get_player_points_for_week($players[0],$fixture_id);
                error_log(__METHOD__ . ' - new score: ' . $player_a_score);
            } else {
                $player_a_score = (int) $players[0]->player_points['fixture_id_' . $fixture_id]['weekly-championship']['points'];
            }

            if (!isset($players[1]->player_points['fixture_id_' . $fixture_id]['weekly-championship']['score'])) {
                error_log(__METHOD__ . ' - error no score for player: ' . $players[1]->player_id);
                $player_b_score = self::get_player_points_for_week($players[1],$fixture_id);
                error_log(__METHOD__ . ' - new score: ' . $player_b_score);
            } else {
                $player_b_score = (int) $players[1]->player_points['fixture_id_' . $fixture_id]['weekly-championship']['points'];
            }

            if ($player_a_score > $player_b_score) {
                $score[] = [
                    ['player_id' => $players[0]->player_id, 'cup_points' => 1],
                    ['player_id' => $players[1]->player_id, 'cup_points' => 0],
                ];
            }

            if ($player_a_score < $player_b_score) {
                $score[] = [
                    ['player_id' => $players[0]->player_id, 'cup_points' => 0],
                    ['player_id' => $players[1]->player_id, 'cup_points' => 1],
                ];
            }

            if ($player_a_score === $player_b_score) {
                // get players scm-league score, in case of draw player with most season-league points advances
                $p1_points = $players[0]->player_points['total_points']['season-league'];
                $p2_points = $players[1]->player_points['total_points']['season-league'];

                if ($p1_points > $p2_points) {
                    $score[] = [
                        ['player_id' => $players[0]->player_id, 'cup_points' => 1],
                        ['player_id' => $players[1]->player_id, 'cup_points' => 0],
                    ];
                } else {
                    $score[] = [
                        ['player_id' => $players[0]->player_id, 'cup_points' => 0],
                        ['player_id' => $players[1]->player_id, 'cup_points' => 1],
                    ];
                }

            }


            if(in_array($players[0]->player_id,[58,60,156,32,33,34]) || in_array($players[1]->player_id,[58,60,156,32,33,34])){
                error_log('name: ' . $players[0]->wp_player->display_name . ' score: ' . $player_a_score);
                error_log(json_encode($players[0]->player_points['fixture_id_' . $fixture_id]));
                error_log('name: ' . $players[1]->wp_player->display_name . ' score: ' . $player_b_score); 
                error_log(json_encode($players[1]->player_points['fixture_id_' . $fixture_id]));  
                error_log($player_a_score > $player_b_score); 
                error_log($player_a_score < $player_b_score);  

            }

        }

        //returns
        // array [ [0:[player_id,cup_points],1:[player_id,cup_points]],[] ... ]
        //

        return $score;
    }

    public static function get_player_points_for_week(Player $player,int $fixture_id){

        if(!isset($player->player_points['fixture_id_' . $fixture_id])){
            error_log(__METHOD__ . ' - error no score for player: ' . $player->player_id . ' in fixture: ' . $fixture_id );
            return 0;
        }

        $week_data = $player->player_points['fixture_id_' . $fixture_id];

        $week_points = 0;
        foreach( $week_data as $key => $data ){
            if( preg_match('/match_id_\d+/',$key)){
                $week_points += $data['season-league']['points'];
            }
        }

        return $week_points;
    }

     /*
        [ 'total_points' => ['season-league' => int,'weekly-championship' => int]
          'fixture_id_3709' => [ 
            'match_id_3631' => ['season-league' => ['points' => int ]], 
            'match_id_3637' => ...,
            'weekly-championship' => [ 'points' => int,'score' => int,'opponent_id' => int,'home_field_advantage' => boolean],
            'score-masters-cup' => [ 'score' => int ,'opponent_id' => int, 'phase_id'=> int]
            ]
        ]
        */

    // in case of tie the player with most points in season league, advances
}
