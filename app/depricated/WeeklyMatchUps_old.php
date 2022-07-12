<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Base\ScmData;

// 'weekly_matchups'
// get_post_meta($competition_id,'weekly_matchups,false);

// constrtuct "WeeklyMatchUps" as a service object tha constructor argument
// should be an object like LeagueChampionship (league,championship)

class WeeklyMatchUps_old
{

    public $competition_id;
    public $league_id;

    public $current_matchups;
    public $next_matchups;
    
    private $meta_key = 'weekly_matchups';

    public function __construct( $competition_id, $league_id )
    {

        //how to initialize matchups
        $this->competition_id = $competition_id;
        $this->league_id = $league_id;
        
        //todo: use a service object for getting data from the database
        $current_matchups = get_post_meta($this->competition_id, $this->meta_key, false);
        var_dump($current_matchups);
      

        if ($current_matchups === false) {
            throw new \Exception(__METHOD__ . ' invalid post->ID for meta "competition_matchups", id: ' . $this->competition_id);
        }

        if ( $current_matchups === '' || empty($current_matchups)){
            $this->current_matchups = array();
        }else{
            $this->current_matchups = $current_matchups[0];
        }


       
    }

    public function get_current_matchups( $fixture = '' ){

        if($fixture !== ''){

            if(isset($this->current_matchups['fixture_id_' . $fixture]['league_id_' . $this->league_id])){
                return $this->current_matchups['fixture_id_' . $fixture]['league_id_' . $this->league_id];
            }

            // if invalid fixture id return empry array -- no matchups for this fixtures
            return array();
        }

        // return matchups for last fixture
        $current_fixture_matchups = end($this->current_matchups);

        if( !isset($current_fixture_matchups['league_id_' . $this->league_id])){
            return array();
        }

        $matchups = $current_fixture_matchups['league_id_' . $this->league_id];
        return $matchups;
    }

    public function calc_new_matchup_data( $new_fixture_id )
    {

        //$participants

        $prev = $this->get_current_matchups();

        if( empty($prev) || $prev === '' ){
            $this->initialize_matchups($new_fixture_id);
            return $this;
        }

        //count how many fixtures
        $fixture_no = count($this->current_matchups);

        $no_of_participants = count($prev);

        //is odd
        if ($fixture_no % 2 !== 0) {
            $middle_generator = [1, -1];
            $middle = [];

            for ($i = 1; $i <= ($no_of_participants - 4) / 2; $i++) {
                $middle = array_merge($middle, $middle_generator);
            }

            $transformation_matrix = array_merge([0, -1], $middle, [-1, 2]);

            $next = array();
            foreach ($transformation_matrix as $key => $new_position) {
                $next[$key] = $prev[$key - $new_position];
            }
        }

        //is even
        if ($fixture_no % 2 === 0) {
            $middle_generator = [-3, 3];
            $middle = [];

            for ($i = 1; $i <= ($no_of_participants - 4) / 2; $i++) {
                $middle = array_merge($middle, $middle_generator);
            }

            $transformation_matrix = array_merge([-3, 0], $middle, [2, 1]);

            $next = array();
            foreach ($transformation_matrix as $key => $new_position) {
                $next[$key] = $prev[$key - $new_position];
            }

        }

        $next_matchups = array( 'fixture_id_' . (int) $new_fixture_id => $next);

        $this->next_matchups = array_merge( $prev, $next_matchups);
        $this->save_new_matchups();

        return $this;

    }

    public function initialize_matchups($fixture_id)
    {

        //hiden dependency
        //todo: remove ScmData from function

        $participants_ids = ScmData::get_league_participants_ids($this->league_id);

        $next_matchups = array(
                'fixture_id_' . $fixture_id => array(
                    'league_id_' . $this->league_id => $participants_ids));

        $this->next_matchups = array_merge($this->current_matchups, $next_matchups);
        $this->save_new_matchups();
    }

    public function save_new_matchups()
    {
        //todo: use a service object for writing to the database
        $id = update_post_meta($this->competition_id, $this->meta_key, $this->next_matchups);

        if($id === false){
            throw new \Exception(__METHOD__ . ' failure or same value for meta "competition_matchups", id: ' . $this->competition_id);
        }

    }

}
