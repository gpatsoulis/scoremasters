<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Base\ScmData;

// 'weekly_score'
// deprecated class

class WeeklyPoints {

    public $competition_id;
    public $league_id;

    public $current_score;
    //public $current_players_points;
    public $new_score;
    public $new_players_points;

    public $meta_key = 'weekly_score'; 

    public function __construct( $competition_id, $league_id ){

        $this->competition_id = $competition_id;
        $this->league_id = $league_id;

        $current_score = get_post_meta($this->competition_id, $this->meta_key, false);

        if ($current_score === false) {
            throw new \Exception(__METHOD__ . ' invalid post->ID for meta "competition_matchups", id: ' . $this->competition_id);
        }

        if ( $current_score === '' || empty($current_score)){
            $this->current_score = array();
        }else{
            $this->current_score = $current_score[0];
        }
        
    }

    public function get_current_score( $match_id = '' ){

        if($fixture !== ''){

            if(isset($this->current_score['match_id_' . $match_id]['league_id_' . $this->league_id])){
                return $this->current_score['match_id_' . $match_id]['league_id_' . $this->league_id];
            }

            // if invalid fixture id return empry array -- no matchups for this fixtures
            return array();
        }

        $current_score_for_match_id = end($this->current_matchups);

        if( !isset($current_score_for_match_id['league_id_' . $this->league_id])){
            return array();
        }

        $current_score = $current_score_for_match_id['league_id_' . $this->league_id];

         // return matchups for last fixture
        return $current_score;
    }

    public function calculate_players_weekly_points( $match_id ){

        // get fixture id for current match id
        // this function must run after points are calculated for every player
        // this function must run after new matchups are calculated

        //get next matchups
        $matchups = new WeeklyMatchUps($this->competition_id, $this->league_id);
        $current_matchups = $matchups->get_current_matchups();

        $current_season = ScmData::get_current_season();
        $current_fixture = ScmData::get_current_fixture();

        

        //get players

        //get player points

        $new_score_array = array('fixture_id' => $current_fixture->ID,'season_id' => $current_season->ID, 'match_id' => $match_id,'matchups_score' => array());

        foreach ($current_matchups as $player_id){
            $points = get_user_meta( intval($player_id), 'score_points_seasonID_' . $current_season->ID);
            
            if ( !isset($points['fixture_id_' . $current_fixture->ID]['match_id_' . $match_id]) ) {
                throw new \Exception(__METHOD__ . ' there is no score for player: ' . $player_id . ' and match: '. $match_id);
            }

            $match_points = $points['fixture_id_' . $current_fixture->ID]['match_id_' . $match_id];
            //$new_score_array['player_id_'. $player_id] = array('match_points' => $match_points);
            $new_score_array['matchups_score'][] = array('player_id' => $player_id, 'match_points' => $match_points);
        }


        //compare
        for( $i = 0; $i <= count($new_score_array['matchups_score']); $i += 2){

            $score_diff = intval($new_score_array['matchups_score'][$i]['match_points']) - intval($new_score_array[$i+1]['match_points']);

            switch ( $score_diff ) {
                case ($score_diff > 0):
                    $new_score_array['matchups_score'][$i]['weekly_champion_points'] =  3;
                    $new_score_array['matchups_score'][$i+1]['weekly_champion_points'] = 0 ;
                    break;
                case ($score_diff < 0):
                    $new_score_array['matchups_score'][$i]['weekly_champion_points'] = 0 ;
                    $new_score_array['matchups_score'][$i+1]['weekly_champion_points'] = 3 ;
                    break;
                case ($score_diff == 0):
                    $new_score_array['matchups_score'][$i]['weekly_champion_points'] = 1 ;
                    $new_score_array['matchups_score'][$i+1]['weekly_champion_points'] = 1 ;
                    break;
            }
            $new_score_array['matchups_score'][$i]['opponent_id']   = $new_score_array['matchups_score'][$i+1]['player_id'];
            $new_score_array['matchups_score'][$i+1]['opponent_id'] = $new_score_array['matchups_score'][$i]['player_id'];
        }

        $this->new_players_points = $new_score_array;
        // to be fixed !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

        //$new_score = array( 'match_id_' . $match_id => array( 'league_id_' . $this->league_id => $new_score_array ) );
        
        //save score

    }

    public function save_player_weekly_score(){
        
        // todo save points for each player
        $current_season_id =  $this->new_players_points['season_id'];
        $current_fixture_id = $this->new_players_points['fixture_id'];

        // [fixture_id => match_id => [ match_up, points ]

        foreach($this->new_players_points['matchups_score'] as $player_data){

            $current_players_points_array = get_user_meta((int) $player_id, 'weekly_points_seasonID_' . $current_season_id);
            $current_players_points = $current_players_points_array[0];

            $points = $player_data['weekly_champion_points'];
            $opponent = $player_data['opponent_id'];
            $match_id = $this->new_players_points['match_id'];

            $curent_players_points[ 'fixture_id_' . $current_fixture_id ]['match_id_' . $match_id] = ['points' => $points,'oponnent_id' => $opponent];
            $id = update_user_meta();
          
        }

    }

    public function save_championsip_score(){
        //save total score for league
    }


}