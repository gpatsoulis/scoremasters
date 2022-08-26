<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Classes\ScmData;

class WeeklyMatchUps
{

    public $competition_id;
    private $meta_key = 'weekly_matchups';
    //public $matchups = array();

    public function __construct(  $competition_id )
    {

        //how to initialize matchups
        $this->competition_id = $competition_id;
        
        //todo: use a service object for getting data from the database

    }

    public function get_all_matchups(){

        $current_matchups = get_post_meta($this->competition_id, $this->meta_key, false);
        

        if ($current_matchups === false) {
            throw new \Exception(__METHOD__ . ' invalid post->ID for meta "competition_matchups", id: ' . $competition_id);
        }

        if ( $current_matchups === '' || empty($current_matchups)){
            $this->matchups = array();
        }else{
            $this->matchups = $current_matchups[0];
        }

        return $this->matchups;
    }

    public function for_fixture_id ( $fixture_id ){

        if( $fixture_id === ''){
            $current_fixture_matchups = end($this->get_all_matchups());
            return $current_fixture_matchups;
        }

        if(isset($this->matchups['fixture_id_' . $fixture_id])){
            return $this->matchups['fixture_id_' . $fixture_id];
        }

        return array(); 
    }


    public function for_league_id ( $league_id ){

        // return matchups for current fixture
        $matchups_array = $this->get_all_matchups();
        $current_fixture_matchups = end($matchups_array);

        if( !isset($current_fixture_matchups['league_id_' . $league_id])){
            return array();
        }

        $matchups = $current_fixture_matchups['league_id_' . $league_id];

        return $matchups;
    }

    //new functions for calculatescore
    public function get_matchups(){
        $current_matchups = get_post_meta($this->competition_id, $this->meta_key, false);

        if ($current_matchups === false) {
            throw new \Exception(__METHOD__ . ' invalid post->ID for meta "competition_matchups", id: ' . $competition_id);
        }

        if ( $current_matchups === '' || empty($current_matchups)){
            $this->matchups = array();
        }else{
            $this->matchups = $current_matchups[0];
        }

        return $this;
    }

    //return a closure with fixture_id in context 
    public function by_fixture_id( $fixture_id ){

        $this->closure = function ( $league_id ) use ($fixture_id) {

            if(!isset($this->matchups['fixture_id_' . $fixture_id]['league_id_' . $league_id])){
                return array();
            }

            $result = $this->matchups['fixture_id_' . $fixture_id]['league_id_' . $league_id];
            return $result;
        };

        return $this;
    }

    public function by_league_id($league_id):array{

        $func = $this->closure;

        return $func( $league_id );
    }


}
