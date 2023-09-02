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
    public string $type = 'league-cup';
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

    public static function get_league_score(int $league_id): array
    {
        $current_season_id = (ScmData::get_current_season())->ID;
        $score_meta = get_post_meta($league_id,'score_points_seasonID_'.$current_season_id,true);

        if(!$score_meta){
            $score_meta = [];
        }
        return $score_meta;
    }

    public static function total_score(int $league_id): array
    {
        $scoreArray = self::get_league_score($league_id);
        //['league_id' => $this->matchUps[0], 'points' => $leagueApoints, 'score' => 0, 'opponent_id' => $this->matchUps[1]]

        $score = 0;
        $points = 0;
        $win = 0;
        $loss = 0;
        $draw = 0;

        foreach ($scoreArray as $singleFixtureScore) {
            $score += $singleFixtureScore['score'];
            $points += $singleFixtureScore['points'];

            if($singleFixtureScore['score'] == 0) {
                $loss += 1;
            }elseif($singleFixtureScore['score'] == 1){
                $draw += 1;
            }else{
                $win += 1;
            }
        }

        return ['league_id' => $league_id, 'score' => $score, 'total_points' => $points, 'win' => $win, 'loss' => $loss, 'draw' => $draw];
    }

    public static function get_league_form(int $league_id)
    {
        $scoreArray = self::get_league_score($league_id);

        $result = [ 0 => 'H', 1 => 'I', 3 => 'N'];

        $form = [];
        foreach ($scoreArray as $singleFixtureScore){
            $form[] = $result[$singleFixtureScore['score']];
        }

        return array_reverse($form);
    }

}