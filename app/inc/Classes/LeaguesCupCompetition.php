<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\WeeklyLeagueMatchUps;

class LeaguesCupCompetition
{
    public \WP_post $post_object;
    public int $competition_id;
    public string $description;
    public string $type = 'league-competition';
    public WeeklyLeagueMatchUps $matchups;
    public array $participants;
    public $standings;

    public bool $is_active;

    public function __construct(\WP_Post $competition)//\WP_Post $post
    {
        /*
        if ('scm-season-competition' !== get_post_type($post)) {
            throw new Exception('Scoremasters\Inc\Abstracts\Competition invalid post type post id: ' . $post->ID . ' post type: get_post_type($post)');
        }*/

        //$this->post_object = $post;
        $this->competition_id = $competition->ID;
        $this->post_object = $competition;
        $this->set_participants();
    }

    // get all scm_league with 4 players or more => as participant scm_leagues

    public function set_participants()
    {

        $leagues = ScmData::get_all_leagues();

        $scm_leagues = [];
        foreach ($leagues as $league) {

            $tmp_league = new League($league);
            if ($tmp_league->headsup_ready()) {
                $scm_leagues[] = $tmp_league;
            }

        }

        if (count($scm_leagues) % 2 !== 0) {
            usort($scm_leagues, array($this,'sort_by_participants'));
            array_pop($scm_leagues);
        }

        $this->participants =  array_map(function($league){ return $league->post_data->ID;},$scm_leagues);

    }


    public function sort_by_participants($a, $b)
    {
        $a_p = count($a->league_participants);
        $b_p = count($b->league_participants);

        if ($a_p === $b_p) {
            return 0;
        }

        if ($a_p < $b_p) {
            return 1;
        }

        if ($a_p > $b_p) {
            return -1;
        }

    }

    // calculate matchups
    // calculate point for each player - scm-league

}
