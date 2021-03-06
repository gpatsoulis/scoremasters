<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Services;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\WeeklyMatchUps;

// 'weekly_matchups'
// get_post_meta($competition_id,'weekly_matchups,false);

// constrtuct "WeeklyMatchUps" as a service object tha constructor argument
// should be an object like LeagueChampionship (league,championship)

class CalculateWeeklyMatchups
{

    public $competition_id;
    public $league_id;

    public $matchups;
    public $next_matchups;
    public $previous_matchups;

    private $meta_key = 'weekly_matchups';

    public $fixture_no;

    public function __construct(WeeklyMatchUps $matchups, $league_id)
    {

        //how to initialize matchups
        $this->competition_id = $matchups->competition_id;
        $this->league_id = $league_id;
        $this->matchups = $matchups;

        $this->previous_matchups = $matchups->get_all_matchups();
        $this->fixture_no = count($this->previous_matchups);

    }

    public function for_league_id($league_id)
    {

        $this->league_id = $league_id;

        return $this;
    }

    public function matchups_exists_for_fixture($new_fixture_id): bool
    {

        $all_matchups = $this->previous_matchups;
        
        if (isset($all_matchups['fixture_id_' . $new_fixture_id]['league_id_' . $this->league_id])) {
            return true;
        }

        return false;
        

    }

    public function previous_matchups_exists( $new_fixture_id ){

        $previous = end($this->previous_matchups);
        if(isset($this->previous_matchups['fixture_id_' . $new_fixture_id])){
            $previous = prev($this->previous_matchups);
        }
        
        if( !isset($previous['league_id_' . $this->league_id]) ) {
            return false;
        }

        return true;
    }

    public function get_previous_matchups( $new_fixture_id ){

        $previous = end($this->previous_matchups);
        if(isset($this->previous_matchups['fixture_id_' . $new_fixture_id])){
            $previous = prev($this->previous_matchups);
        }

        return $previous;
    }

    public function for_fixture_id($new_fixture_id)
    {

        //$participants

        if($this->matchups_exists_for_fixture($new_fixture_id)){
            return $this;
        }

        if( !$this->previous_matchups_exists( $new_fixture_id ) ){
            $this->initialize_matchups($new_fixture_id);
            return $this;
        }

        $previous_all_leagues = $this->get_previous_matchups($new_fixture_id);
        $previous_matchups = $previous_all_leagues['league_id_' . $this->league_id];

        //count how many fixtures
        $fixture_no = $this->fixture_no;

        $no_of_participants = count($previous_matchups);

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
                $next[$key] = $previous_matchups[$key - $new_position];
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
                $next[$key] = $previous_matchups[$key - $new_position];
            }

        }

        $next_matchups = array(
            'fixture_id_' . $new_fixture_id => array(
                'league_id_' . $this->league_id => $next));

        $this->next_matchups = $next_matchups;

        var_dump($next_matchups);


        return $this;

    }

    protected function initialize_matchups($fixture_id)
    {

        //hiden dependency
        //todo: remove ScmData from function
        $participants_ids = ScmData::get_league_participants_ids($this->league_id);

        var_dump('initialize');
        var_dump( $participants_ids );

        if( count($participants_ids) < 4 ){
            $next_matchups = 'not enough players';
            $this->$next_matchups = $next_matchups;
            return;
        }

        $next_matchups = array(
            'fixture_id_' . $fixture_id => array(
                'league_id_' . $this->league_id => $participants_ids));

        //$this->next_matchups = array_merge($this->matchups->for_league_id($this->league_id), $next_matchups);
        $this->next_matchups =  $next_matchups;

    }

    public function save()
    {
        
        //todo: use a service object for writing to the database

        //var_dump($this->matchups->get_all_matchups());
        //var_dump($this->next_matchups);
        if(is_null($this->next_matchups) || $this->next_matchups === 'not enough players'){
            return;
        }

        $previous_matchups = $this->previous_matchups;

        $data = array_merge_recursive($previous_matchups, $this->next_matchups);
        
        $id = update_post_meta($this->competition_id, $this->meta_key, $data);

        if ($id === false) {
            throw new \Exception(__METHOD__ . ' failure or same value for meta "competition_matchups", id: ' . $this->competition_id);
        }

    }

}
