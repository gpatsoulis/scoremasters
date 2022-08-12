<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Base\ScmData;

class PlayerScore
{
    public $player_id;

    
    public $total_points;
    public $data;

    public $current_season;

    protected $query;

    public function __construct( $player_id ){

        $this->player_id = $player_id;
        $this->current_season = ScmData::get_current_season();

    }

    public function set_query( $action = 'get'){

        $this->query['action'] = $action ;
        return $this;
    }

    public function for_season_id( $season_id = '' ){

        if( $season_id === ''){
            $season_id = $this->current_season->ID;
        }

        $data = get_user_meta((int) $this->player_id, 'score_points_seasonID_' . $season_id, true );

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

        $this->data = $data;
        return $this;
    }

    public function for_fixture_id( $fixture_id = '' ):array
    {
        if( $fixture_id === ''){
            $data = $this->data;
            unset($data['total_points']);
            return current($data);
        }

        return $this->data['fixture_id_' . $fixture_id];
    }

    

    
}