<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Abstracts\Competition;
use Scoremasters\Inc\Base\ScmData;

final class CalculateScoremastersCupPoints  {

    public static function calculate( array $cup_matchups, int $fixture_id ):array {
        // for each pair 
        // get player A week score, get player B week score
        // winner (player with greatest score ) gets 1 point

        // get_users
        foreach( $cup_matchups as $matchups){

            $args = [
                'include' => $matchups,
                'fields' => 'all'
            ];

            $users = get_users($args);
            //$players = array_map( $fn( \WP_user $scm_user ):Player => new Player( $scm_user ), $users);
            $players = array_map( 
                function ( \WP_user $scm_user):Player 
                {
                    return new Player( $scm_user );
                },
                $users
            );

            if( !isset($players[0]->player_points['fixture_id_' . $fixture_id]['weekly-championship']['score'])) 
            {
                error_log( __METHOD__ . ' - error no score for player: ' . $players[0]->player_id);
            }

            if( !isset($players[1]->player_points['fixture_id_' . $fixture_id]['weekly-championship']['score']))
            {
                error_log( __METHOD__ . ' - error no score for player: ' . $players[1]->player_id);
            }

            $player_a_score = $players[0]->player_points['fixture_id_' . $fixture_id]['weekly-championship']['score'];
            $player_b_score = $players[1]->player_points['fixture_id_' . $fixture_id]['weekly-championship']['score'];

            if( $player_a_score > $player_b_score ){
                return [ 
                    ['player_id' => $players[0]->player_id,'cup_points' => 1],
                    ['player_id' => $players[1]->player_id,'cup_points' => 0] 
                ];
            }

            if( $player_a_score < $player_b_score ){
                return [ 
                    ['player_id' => $players[0]->player_id,'cup_points' => 0],
                    ['player_id' => $players[1]->player_id,'cup_points' => 1] 
                ];
            }

            if( $player_a_score == $player_b_score ){
                return [ 
                    ['player_id' => $players[0]->player_id,'cup_points' => 1],
                    ['player_id' => $players[1]->player_id,'cup_points' => 1] 
                ];
            }

        }

        //return 
        // array [ [player_id,score],[] ... ]
    }

    // in case of tie the player with most points in season league, advances
}