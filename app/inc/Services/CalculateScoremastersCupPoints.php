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
                $player_a_score = 0;
            } else {
                $player_a_score = (int) $players[0]->player_points['fixture_id_' . $fixture_id]['weekly-championship']['score'];
            }

            if (!isset($players[1]->player_points['fixture_id_' . $fixture_id]['weekly-championship']['score'])) {
                error_log(__METHOD__ . ' - error no score for player: ' . $players[1]->player_id);
                $player_b_score = 0;
            } else {
                $player_b_score = (int) $players[1]->player_points['fixture_id_' . $fixture_id]['weekly-championship']['score'];
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

            if ($player_a_score == $player_b_score) {
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

        }

        //returns
        // array [ [0:[player_id,cup_points],1:[player_id,cup_points]],[] ... ]
        //

        return $score;
    }

    // in case of tie the player with most points in season league, advances
}
