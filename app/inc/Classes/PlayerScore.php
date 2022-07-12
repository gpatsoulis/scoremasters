<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Base\ScmData;

class PlayerScore
{
    public $player_id;

    public $season_id;
    public $total_points;
    public $data;

    protected $query;

    public function __construct( $player_id ){

        $this->player_id = $player_id;

    }

    public function set_query( $action = 'get'){

        $this->query['action'] = $action ;
        return $this;
    }

    public function for_season_id( $season_id ){

        $this->season_id = $season_id;

        $data = get_user_meta((int) $this->player_id, 'score_points_seasonID_' . $season_id, false );

        if($data === false){
            throw new \Exception(__METHOD__ . ' invalid id for player: ' . $this->player_id );
        }

        if( $data === '' ){
            throw new \Exception(__METHOD__ . ' there is no  player with id: ' . $this->player_id );
        }

        if(empty($data)){
            //initialize data
            $this->data = array();
            return $this;
        }

        $this->data = $data[0];
        return $this;

    }

    public function for_fixture_id ( $fixture_id ){

        if( !isset( $this->data['fixture_id_' . $fixture_id ] ) ){
            $this->query['for_fixture'] = false;
            return $this;
        }

        $this->query['for_fixture']['data'] = $this->data['fixture_id_' . $fixture_id ];
        $this->query['for_fixture']['fixture_id'] = $fixture_id;

        return $this;

    }

    public function for_match_id( $match_id ){

        if( !isset( $this->query['for_fixture']['data']['match_id_' . $match_id] ) ){
            $this->query['for_match'] = false;
            return $this;
        }

        $this->query['for_match']['data'] = $this->query['for_fixture']['data']['match_id_' . $match_id];
        $this->query['for_match']['match_id'] = $match_id;

        return $this;
    }

    public function for_competition_name( $competition_name ){

        if( !isset( $this->query['for_fixture']['data']['for_match']['data'][$competition_name] ) ){
            $this->query['for_competition'] = false;
            return $this;
        }


        $this->query['for_competition']['data'] = $this->query['for_fixture']['data']['for_match']['data'][$competition_name];
        $this->query['for_competition']['competition_name'] = $competition_name;

        return $this;
    }

    public function parse_query(){

    }

    public function for_last_game ( ) {

    }

    public function save_score( ){

    }

    
}