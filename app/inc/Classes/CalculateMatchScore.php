<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\FootballMatch;
use Scoremasters\Inc\Base\CalculateScore;

class CalculateMatchScore
{
    public $match;
    public $predictions;
    public $data_to_insert_in_db;

    public $current_season;
    public $current_fixture;

    public $full_path;

    public function __construct(int $match_id)

    {
        $this->match = (new FootballMatch($match_id))->setup_data();
        $this->current_season = ScmData::get_current_season();
        $this->current_fixture = ScmData::get_current_fixture();
    }

    public function get_predictions()
    {

        $this->predictions = ScmData::get_players_predictions_for_match($this->match->post_data);

        return $this;
    }

    public function calculate_points()
    {

        $data_to_insert_in_db = array();

        foreach ($this->predictions as $prediction) {
            $points = CalculateScore::calculate_points_after_prediction_submit($prediction, $this->match);

            if(SCM_DEBUG){
                error_log( __METHOD__ . ' when match is finished - points calculated: ' . $points . ' for prediction: ' . $prediction->ID);
            }

            $data_to_insert_in_db[] = array(
                'player_id' => $prediction->post_author,
                'season_id' => $this->current_season->ID,
                'fixture_id' => $this->current_fixture->ID,
                'match_id' => $this->match->match_id,
                'score' => $points,
            );
        }

        $this->data_to_insert_in_db = $data_to_insert_in_db;

        return $this;
    }

    public function save_points()
    {

        foreach ($this->data_to_insert_in_db as $player_score_data) {

            $player_id = strval($player_score_data['player_id']);
            $match_id = strval($player_score_data['match_id']);
            $fixture_id = strval($player_score_data['fixture_id']);
            $season_id = strval($player_score_data['season_id']);
            $score = $player_score_data['score'];

            $players_score = get_user_meta((int) $player_id, 'score_points_seasonID_' . $season_id);

            //initialize
            if (!empty($players_score)) {
                $players_score = $players_score[0];
            } else {
                $players_score = array();
            }

            //points already in db
            if(isset($players_score['fixture_id_' . $fixture_id]['match_id_' . $match_id]['season-league']['points'])){
                continue;
            }
            
            //match points for season-league competition
            $players_score['fixture_id_' . $fixture_id]['match_id_' . $match_id]['season-league']['points'] = $score;

            // total points
             if(!isset($players_score['total_points']['season-league'])){
                $players_score['total_points']['season-league'] = 0;
            }

            $players_score['total_points']['season-league'] = intval($score) + intval($players_score['total_points']['season-league']);

            //weekly score for weekly championship competition
            if(!isset( $players_score['fixture_id_' . $fixture_id]['weekly-championship']['points'])){
                $players_score['fixture_id_' . $fixture_id]['weekly-championship']['points'] = 0;
            }
            $players_score['fixture_id_' . $fixture_id]['weekly-championship']['points'] += intval($score);


            // save player score
            $success = update_user_meta($player_id, 'score_points_seasonID_' . $season_id, $players_score);

            if (!$success) {
                error_log(__METHOD__ . ' error updating score metadata for user: ' . $player_id);
            }

            //update_user_meta($player_id, 'total_points', $old_meta_value['total_points']);
            //$find_key = preg_replace("/[^0-9.]/", "", 'fixture_id_850');

        }

        return $this;
    }

    public function export_csv_predictions()
    {
        $export_string = '';
        $export_string .= $this->current_fixture->post_title . ',,,,,,' . "\n";
        $export_string .= 'player-name,SHMEIO,Under/Over,Score,Scorer,Double points,match name' . "\n";

        foreach ($this->predictions as $prediction) {
            $player_name = (get_user_by('id', $prediction->post_author))->display_name;
            $predictions = unserialize($prediction->post_content);

            $shmeio = $predictions['SHMEIO'];
            $uo = $predictions['Under / Over'];
            $score = $predictions['score'];

            $scorer = '';
            $tmp_scorer = get_post(intval($predictions['Scorer']));

            if ($tmp_scorer) {
                $scorer = $tmp_scorer->post_title;
            }

            $double = $predictions['Double Points'];

            $match_name = $this->match->post_data->post_title;

            //$export_string .= $match_name . ',,,,,,'."\n";
            $export_string .= '"' . $player_name . '","' . $shmeio . '","' . $uo . '","' . $score . '","' . $scorer . '","' . $double . '","' . $match_name . '"' . "\n";
        }

        $current_date = new \DateTime();
        $current_date->setTimezone(new \DateTimeZone('Europe/Athens'));

        $full_path = EXPORT_PATH . '/export_' . $this->match->match_id . '_' . $current_date->format('Y-m-d H:i') . '.csv';

        $this->full_path = $full_path;

        file_put_contents($full_path, $export_string);

        return $this;
    }

    public function send_predictions_by_email($email = array('patsoulis.george@gmail.com','kyrkag1@gmail.com','tmountakis@gmail.com'))
    {
        //tmountakis@gmail.com

        $to = $email;

        $subject = 'predictions';
        $message = 'match predictions ' . $this->match->post_data->post_title;
        $headers = 'From: info@scoremasters.gr';

        $attachment = $this->full_path;

        $sent = wp_mail($to, $subject, $message, $headers, $attachment);

        if(!$sent){
            error_log(__METHOD__ . ' error mail not sent!!!');
        }
    }
}
