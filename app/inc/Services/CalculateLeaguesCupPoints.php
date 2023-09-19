<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Services;

use Scoremasters\Inc\Classes\League;
use Scoremasters\Inc\Base\ScmData;

class CalculateLeaguesCupPoints
{
    public int $fixture_id;
    public array $matchUps;
    public array $totalScore;

    public function __construct(array $matchUps, int $fixture_id)
    {

        $this->fixture_id = $fixture_id;
        $this->matchUps = $matchUps;
    }

    public function calculate(): array
    {

        error_log(__METHOD__ . 'league cup fixture id ' . $this->fixture_id);
        error_log(__METHOD__ . 'league cup matchups ' . json_encode($this->matchUps));

        $totalScore = [];

        for ($i = 0; $i < count($this->matchUps); $i += 2) {

            $leagueAplayers = (new League(get_post($this->matchUps[$i])))->short_players_by_fixture_points($this->fixture_id);
            $leagueBplayers = (new League(get_post($this->matchUps[$i + 1])))->short_players_by_fixture_points($this->fixture_id);

            error_log(__METHOD__ . 'Calculating league cup points - leagueAplayers: ' . json_encode($leagueAplayers));
            error_log(__METHOD__ . 'Calculating league cup points - leagueBplayers: ' . json_encode($leagueBplayers));

            $leagueApoints = $this->totalPoints($leagueAplayers);
            $leagueBpoints = $this->totalPoints($leagueBplayers);

            if ($leagueApoints === $leagueBpoints) {
                $totalScore[] = ['league_id' => $this->matchUps[0], 'points' => $leagueApoints, 'score' => 1, 'opponent_id' => $this->matchUps[1]];
                $totalScore[] = ['league_id' => $this->matchUps[1], 'points' => $leagueBpoints, 'score' => 1, 'opponent_id' => $this->matchUps[0]];
            } elseif ($leagueApoints > $leagueBpoints) {
                $totalScore[] = ['league_id' => $this->matchUps[0], 'points' => $leagueApoints, 'score' => 3, 'opponent_id' => $this->matchUps[1]];
                $totalScore[] = ['league_id' => $this->matchUps[1], 'points' => $leagueBpoints, 'score' => 0, 'opponent_id' => $this->matchUps[0]];
            } else {
                $totalScore[] = ['league_id' => $this->matchUps[0], 'points' => $leagueApoints, 'score' => 0, 'opponent_id' => $this->matchUps[1]];
              
                $totalScore[] = ['league_id' => $this->matchUps[1], 'points' => $leagueBpoints, 'score' => 3, 'opponent_id' => $this->matchUps[0]];
            }
            error_log(__METHOD__ . 'Calculating league cup points - leagueA: ' . $leagueApoints . ' leagueB: ' . $leagueBpoints);
            error_log(__METHOD__ . 'Calculating league cup points - total score: ' . json_encode($totalScore[]));

        }

        $this->totalScore = $totalScore;

        
        
        error_log(__METHOD__ . 'Calculating league cup points - total Score: ' . json_encode($totalScore));
        return $totalScore;

    }

    public function totalPoints(array $players): int
    {
        $sum = 0;
        $players = array_slice($players,0,4);

        foreach ($players as $player) {
            $sum += $player->current_fixture_points($this->fixture_id);
        }

        return $sum;
    }

    public function save()
    {

        if(empty($this->totalScore)){
            error_log(__METHOD__ . ' empty array : totalScore' . json_encode($this->totalScore));
            return;
        } 

        $current_season_id = (ScmData::get_current_season())->ID;

        foreach ($this->totalScore as $scoreArray){

            $league_id = $scoreArray['league_id'];

            $score_meta = get_post_meta($league_id,'score_points_seasonID_'.$current_season_id,true);
            if(!$score_meta){
                error_log(__METHOD__ . ' INIT LEAGUE SCORE -- no previus score for league: '. $league_id);
                $score_meta = [];
            }

            array_push($score_meta,$scoreArray);

            $id = update_post_meta($league_id, 'score_points_seasonID_' . $current_season_id, $score_meta);
            if ($id === false) {
                error_log(__METHOD__ . ' false when updating score_points array for league: ' . $league_id);
            }

        }

       /**
        * score_points_seasonID_xx:[fixture_id: [score array],]
        */
    }

}
