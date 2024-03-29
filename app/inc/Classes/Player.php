<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Base\ScmData;

class Player
{

    public $wp_player;
    private $scm_league;
    public $can_play_double;

    public $user_email;
    public $player_id;
    public $player_points;

    public $current_season_points;
    public $weekly_competition_points;

    public function __construct(\WP_User $user)
    {

        $this->wp_player = $user;
        $this->user_email = $user->user_email;
        $this->player_id = intval($user->ID);

        $season = ScmData::get_current_season();
        //add to factory class for player or to db class
        $scm_league_array = get_user_meta((int) $this->player_id, 'scm_league_status', true);

        if (!empty($scm_league_array)) {
            $scm_league = end($scm_league_array);
            $this->scm_league = $scm_league['league_id'];

            if (isset($scm_league_array['season_id:' . $season->ID])) {
                $scm_league = $scm_league_array['season_id:' . $season->ID];
                $this->scm_league = $scm_league['league_id'];
            }
        }

        $this->player_points = get_user_meta(intval($user->ID), 'score_points_seasonID_' . $season->ID, true);

        if (!isset($this->player_points['total_points']['season-league'])) {
            $total_points = 0;
        } else {
            $total_points = intval($this->player_points['total_points']['season-league']);
        }

        $this->current_season_points = $total_points;

        if (!isset($this->player_points['total_points']['weekly-championship'])) {
            $this->weekly_competition_points = 0;
        } else {
            $this->weekly_competition_points = intval($this->player_points['total_points']['weekly-championship']);
        }

    }

    public function set_scm_league($league_id): bool
    {
        // -------------------- debug -------------------------
        if (SCM_DEBUG) {

            if (SCM_DEBUG && ($this->player_id !== 1 || $this->player_id !== 2 || $this->player_id !== 3)) {
                //return false;
            }

            if (!is_null($this->scm_league)) {
                //error_log( __METHOD__ . ' player ' . $this->player_id . ' is already in league: ' . $this->get_league() );
                error_log(__METHOD__ . ' player ' . $this->player_id . ' is already in league: ' . $this->get_league());
            }

        }

        if (!is_null($this->scm_league)) {
            error_log(__METHOD__ . ' removing from league ');
            $this->remove_from_current_league();
        }

        $season = ScmData::get_current_season();

        $data = get_user_meta($this->player_id, 'scm_league_status', true);

        if (!$data) {
            $data = array();
        }

        $data['season_id:' . $season->ID] = array('league_id' => $league_id);

        $success = update_user_meta((int) $this->player_id, 'scm_league_status', $data);

        //scm_league_status[ season_id:id => [league_id => id] ]

        if (!$success) {
            error_log(__METHOD__ . ' error setting player scm-league: ' . $this->player_id);
        }

        $this->scm_league = $league_id;

        //$data = get_field('scm-user-players-list',$league_id);

        $args = array('scm-user-player' => $this->wp_player->ID);

        add_row('scm-user-players-list', $args, $league_id);

        return $success;
    }

    public function remove_from_current_league(): bool
    {

        if (!empty(ScmData::get_all_fixtures_for_season())) {
            error_log(__METHOD__ . ' only remove players at start of season player:' . $this->player_id);
            //return false;
        }
        // scm_league_status = array(season_id => array('league_id' => XX))

        // -------------------- debug -------------------------
        if (SCM_DEBUG && ($this->player_id !== 1 || $this->player_id !== 2 || $this->player_id !== 3)) {
            //return false;
        }
        // -------------------- debug -------------------------

        $current_league_id = $this->scm_league;
        $success = $this->remove_from_league_acf($current_league_id, $this->player_id);
        if (!$success) {
            error_log(__METHOD__ . ' can\'t remove player: ' . $this->wp_player->display_name . ' from league: ' . $current_league_id);
        }

        $old_data = get_user_meta((int) $this->player_id, 'scm_league_status', true);

        if (end($old_data)['league_id'] === (int) $current_league_id) {
            $val = array_pop($old_data);
        }

        $success = update_user_meta((int) $this->player_id, 'scm_league_status', $old_data);

        if (!$success) {
            error_log(__METHOD__ . ' can\'t update player league_status meta: ' . $this->wp_player->display_name . ' from league: ' . $current_league_id);
        }

        return $success;
    }

    private function remove_from_league_acf($league_id, $player_out_id): bool
    {

        $league_players = get_field('scm-user-players-list', $league_id);

        if (!$league_players) {
            return false;
        }

        $league_players_left = array_filter($league_players,
            //fn($player) => $player['scm-user-player'] !== $player_out_id
            function ($player) use ($player_out_id) {return $player['scm-user-player'] !== $player_out_id;}
        );

        $new_array = array_values($league_players_left);

        $success = update_field('scm-user-players-list', $new_array, $league_id);
        return $success;
    }

    public function save_prediction(PlayerPrediction $prediction)
    {

    }

    public function can_make_predictions(): bool
    {

        if ($this->scm_league) {
            return true;
        }

        return false;
    }

    public function get_current_week_predictions(): array
    {

        //$current_fixture = ScmData::get_current_fixture();
        //$player_predictions = ScmData::get_all_player_predictions_for_fixture($current_fixture, $this->wp_player->ID);
        $matches = ScmData::get_all_matches_for_current_fixture();
        $player_predictions = ScmData::get_all_player_prediction_for_fixture_by_title($matches, $this->wp_player->ID);

        return $player_predictions;
    }

    public function can_play_double(): bool
    {

        $predictions = $this->get_current_week_predictions();

        if (empty($predictions)) {
            error_log(static::class . ' there are no predictions');
            return true;
        }

        $double_counter = 0;

        foreach ($predictions as $prediction) {
            $match_prediction = unserialize($prediction->post_content);

            $double = $match_prediction['Double Points'];

            if ($match_prediction['Double Points'] !== '') {
                $double_counter += 1;
            }

            if ($double_counter == 2) {
                return false;
            }
        }

        return true;
    }

    public function send_message(string $title, string $message)
    {
        $to = $this->user_email;
        $subject = $title;
        $body = $message;
        $headers = array('Content-Type: text/html; charset=UTF-8');

        $success = wp_mail($to, $subject, $body, $headers);

        if (!$success) {
            error_log(__METHOD__ . ' error sending mail to ' . $to . ' title: ' . $title);
        }
    }

    public function get_league()
    {
        return $this->scm_league;
    }

    public function current_fixture_points($fixture_id = null): int
    {
        
        if (is_null($fixture_id)) {
            $current_fixture = ScmData::get_current_fixture();
        }else{
            $current_fixture = get_post($fixture_id);
        }

        $current_fixture_points = 0;
        if (is_array($this->player_points) && isset($this->player_points['fixture_id_' . strval($current_fixture->ID)])) {
            $current_fixture_points = $this->player_points['fixture_id_' . strval($current_fixture->ID)]['weekly-championship']['points'];
        }

        return intval($current_fixture_points);
    }

    public function get_league_cup_total_points()
    {
        // fixture_id_xxx
        $leagues_cup = ScmData::get_current_scm_competition_of_type('leagues-cup');

        $matchups = get_post_meta( $leagues_cup->ID, 'weekly_league_matchups', true );
        $fixtures = array_keys($matchups);

        $fixture_ids = [];
        foreach ($fixtures as  $fixture) {
            preg_match('/[0-9]+/',$fixture,$matches);
            if (isset($matches[0])) {
                $fixture_ids[] = $matches[0];
            }
        }

        $total_league_cup_points = 0;
        foreach ($fixture_ids as $id) {
            $points = $this->current_fixture_points($id);
            $total_league_cup_points += (int) $points;
        }

        return $total_league_cup_points;
    }

}

/*
prediction schema

{
match_id: (int),
winner: (string) 1,2,X
scorrer: (array) [ (int) FootballPlayer->id]
under-over: ???,
Double: (bool) true/false //use twice per week
}

 */

/*
player_points

['fixture_id_3935'=> [
match_id_3631 => [ 'season-league' => ['score' => int ]],
match_id_3637 => [ 'season-league' => ['score' => int ]],
weekly-championship => [ points => int,score => int,opponent_id => int ,home_field_advantage => bool]
],
'total_points' => [
'season-league' => int,
'weekly-championship' => int
]

]

 */
