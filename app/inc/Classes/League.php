<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Base\ScmData;
use WP_Post;

class League
{

    public array $league_participants;
    public $post_data;
    public $status;
    public $scoreArray;

    public function __construct(\WP_Post $scm_league)
    {

        if (!($scm_league instanceof WP_Post)) {
            error_log(__METHOD__ . ' $scm_league must be instance of WP_POST');
            throw new \Exception(__METHOD__ . ' $scm_league must be instance of WP_POST');
        }

        $season = ScmData::get_current_season();

        $this->post_data = $scm_league;
        $this->league_participants = ScmData::get_league_participants($scm_league);
        $this->scoreArray = get_post_meta($scm_league->ID, 'score_points_seasonID_' . $season->ID, true);
    }

    /* the league has even number of playeres and the number is greater than 4
     */
    public function headsup_ready(): bool
    {

        //check no of participants greater than 4
        $participants_no = count($this->league_participants);
        if ($participants_no < 4) {
            return false;
        }
        //check no of participants is even
        if ($participants_no % 2 !== 0) {
            return false;
        }

        return true;
    }

    public function short_players_by_fixture_points(int $fixture_id): array
    {

        $players = $this->league_participants;
        usort($players, function ($player_1, $player_2) use ($fixture_id) {
            return $player_2->current_fixture_points($fixture_id) <=> $player_1->current_fixture_points($fixture_id);
        });

        return $players;
    }

    public function short_players_by_total_points(): array
    {
        $players = $this->league_participants;
        usort($players, function ($player_1, $player_2) {
            return $player_2->current_season_points <=> $player_1->current_season_points;
        });

        return $players;
    }

    public function get_leagues_cup_total_points_for_fixture(int $fixture_id): int
    {
        $total_players = $this->short_players_by_fixture_points($fixture_id);
        $league_players = array_slice($total_players, 0, 4);

        $sum = 0;

        foreach ($league_players as $player) {
            $sum += $player->current_fixture_points($fixture_id);
        }

        return $sum;

    }

    public function best_leagues_cup_player_for_fixture(int $fixture_id): Player
    {
        $total_players = $this->short_players_by_fixture_points($fixture_id);

        return $total_players[0];
    }

    public function get_form(int $no_of_fixtures)
    {
        $last_x_games = array_slice($this->scoreArray, -$no_of_fixtures);
        $form = [];
        foreach ($last_x_games as $result) {
            switch ($result['score']) {
                case 1:
                    $form[] = 'I';
                    break;
                case 3:
                    $form[] = 'N';
                    break;
                case 0:
                    $form[] = 'H';
                    break;
            }
        }

        return array_reverse($form);
    }

    public function get_no_of_results()
    {
        $total_games = count($this->scoreArray);
        $form = $this->get_form($total_games);
        $res = array_count_values($form);

        return $res;
    }

    public function get_top_scorer()
    {

        $players = $this->short_players_by_total_points();

        return $players[0];

    }

}
