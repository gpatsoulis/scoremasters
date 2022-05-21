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

            //todo: use array1+array2 or array_merge
            $old_meta_value = get_user_meta((int) $player_id, 'score_points_seasonID_' . $season_id);

            if (!empty($old_meta_value)) {
                $old_meta_value = $old_meta_value[0];
            } else {
                $old_meta_value = array();
            }

            if (isset($old_meta_value['fixture_id_' . $fixture_id])) {
                $merged_matches = array_merge($old_meta_value['fixture_id_' . $fixture_id], array('match_id_' . $match_id => $score));
                $old_meta_value['fixture_id_' . $fixture_id] = $merged_matches;
            } else {
                $old_meta_value['fixture_id_' . $fixture_id] = array('match_id_' . $match_id => $score);
            }

            $success = update_user_meta($player_id, 'score_points_seasonID_' . $season_id, $old_meta_value);

            if (!$success) {
                error_log(__METHOD__ . ' error updating score metadata for user: ' . $player_id);
            }

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

    public function send_predictions_by_email($email = 'patsoulis.george@gmail.com')
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
