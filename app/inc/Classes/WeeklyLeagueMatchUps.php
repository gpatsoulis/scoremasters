<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

class WeeklyLeagueMatchUps
{

    public $competition_id;
    private $meta_key = 'weekly_league_matchups';
    public $matchups = array();

    public function __construct($competition_id)
    {

        //how to initialize matchups
        $this->competition_id = $competition_id;
        //$this->matchups = $this->get_all_matchups();

        //todo: use a service object for getting data from the database

    }

    public function get_all_matchups(): array
    {

        $current_matchups = get_post_meta($this->competition_id, $this->meta_key, false);

        if ($current_matchups === false) {
            throw new \Exception(__METHOD__ . ' invalid post->ID for meta "competition_matchups", id: ' . $this->competition_id);
        }

        if ($current_matchups === '' || empty($current_matchups)) {
            $this->matchups = array();
        } else {
            $this->matchups = $current_matchups[0];
        }

        return $this->matchups;
    }

    //remove default false should be int
    public function for_fixture_id($fixture_id = 0): array
    {

        if ($fixture_id === 0) {
            $current_fixture_matchups = end($this->get_all_matchups());
            return $current_fixture_matchups;
        }

        if (isset($this->matchups['fixture_id_' . $fixture_id])) {
            return $this->matchups['fixture_id_' . $fixture_id];
        }

        return array();
    }

    //new functions for calculatescore
    public function get_matchups(): WeeklyLeagueMatchUps
    {
        $current_matchups = get_post_meta($this->competition_id, $this->meta_key, false);

        if ($current_matchups === false) {
            throw new \Exception(__METHOD__ . ' invalid post->ID for meta "competition_matchups", id: ' . $this->competition_id);
        }

        if ($current_matchups === '' || empty($current_matchups)) {
            $this->matchups = array();
        } else {
            $this->matchups = $current_matchups[0];
        }

        return $this;
    }

    //matchups data

    /*
[
fixture_id_xxx: [xx,xx,xx,xx,xx,xx],
fixture_id_xxx: [xx,xx,xx,xx,xx,xx],
...
],

 */

}
