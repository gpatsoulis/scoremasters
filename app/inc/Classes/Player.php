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
        $scm_league_array = get_user_meta((int) $this->player_id, 'scm_league_status',true);
        if (!empty($scm_league_array)) {
            if(isset($scm_league_array['season_id:' . $season->ID])){
                $scm_league = $scm_league_array['season_id:' . $season->ID];
                $this->scm_league = $scm_league['league_id'];
            }
        }

        $this->player_points = get_user_meta( intval($user->ID), 'score_points_seasonID_' . $season->ID, true);

        
        if(!isset($this->player_points['total_points']['season-league'])){
            $total_points = 0;
        }else{
            $total_points = intval($this->player_points['total_points']['season-league']);
        }
        
        $this->current_season_points = $total_points;


        if(!isset($this->player_points['total_points']['weekly-championship'])){
            $this->weekly_competition_points =  0;
        }else{
            $this->weekly_competition_points =  intval($this->player_points['total_points']['weekly-championship']);
        }
        

    }

    public function set_scm_league($league_id):bool
    {
        // -------------------- debug -------------------------
        if( SCM_DEBUG && ($this->player_id !==  1 || $this->player_id !== 2 || $this->player_id !== 3) ){
            //return false;
        }
        // -------------------- debug -------------------------


        if(!is_null($this->scm_league)){
            //error_log( __METHOD__ . ' player ' . $this->player_id . ' is already in league: ' . $this->get_league() );
            error_log( __METHOD__ . ' player ' . $this->player_id . ' is already in league: ' . $this->get_league());
        }

        $season = ScmData::get_current_season();

        $data = get_user_meta($this->player_id,'scm_league_status',true);

        if(!$data){
            $data = array();
        }

        $data['season_id:' . $season->ID] = array('league_id' =>  $league_id);
        
        $success = update_user_meta((int) $this->player_id, 'scm_league_status', $data);

        //scm_league_status[ season_id:id => [league_id => id] ]

        if (!$success) {
            error_log(__METHOD__ . ' error setting player scm-league: ' . $this->player_id);
        }

        $this->scm_league = $league_id;

        //$data = get_field('scm-user-players-list',$league_id);
        //var_dump($data);

        $args = array('scm-user-player' => $this->wp_player->ID); 

        add_row('scm-user-players-list', $args, $league_id);

        return $success;
    }

    public function remove_from_current_league():bool {

        // -------------------- debug -------------------------
        if( SCM_DEBUG && ($this->player_id !==  1 || $this->player_id !== 2 || $this->player_id !== 3) ){
            //return false;
        }
        // -------------------- debug -------------------------

        $this->scm_league = null;
        
        $season = ScmData::get_current_season();
        $old_data = get_user_meta((int) $this->player_id, 'scm_league_status',true);

        if (empty($old_data) || $old_data === ''){
            return false;
        }

        $old_data_array = $old_data;

        if(!isset($old_data_array['season_id:' . $season->ID])){
            return false;
        }

        unset($old_data_array['season_id:' . $season->ID]);

        $success = update_user_meta((int) $this->player_id, 'scm_league_status', $old_data_array);

        if (!$success) {
            error_log(__METHOD__ . ' error unsetting player scm-league: ' . $this->player_id);
        }

        unset($this->scm_league);
        
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

        if(!$success){
            error_log(__METHOD__ . ' error sending mail to ' . $to . ' title: ' . $title);
        }
    }

    public function get_league()
    {
        return $this->scm_league;
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
