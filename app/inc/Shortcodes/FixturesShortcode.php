<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\Player;
use Scoremasters\Inc\Classes\FootballMatch;
use Scoremasters\Inc\Classes\PlayerPrediction;
use Scoremasters\Inc\Base\CalculateScore;
use Scoremasters\Inc\Services\CalculatePossiblePlayerPoints;

//[Scoremasters\Inc\Shortcodes\FixturesShortcode]
class FixturesShortcode
{
    public $template;
    public $name;

    public function __construct()
    {
        $this->name = static::class;

        $this->get_template();
    }

    public function register_shortcode()
    {
        add_shortcode($this->name, array($this, 'output'));
    }

    public function output()
    {

        //todo: check for valid match date
        // if date has passed disable "play button" from shortcode

        //var_dump((ScmData::get_current_season())->post_title);
        $current_season = ScmData::get_current_season();
        echo '<p class="test">Season: ' . $current_season->post_title . '</p>';
        
        $post_value = null;
        // 'scm_fixture_setup' -> name of nonce field
        //var_dump($_POST);

        if (isset($_POST['fixture_id'])
            && isset($_POST['scm_fixture_setup'])
            && wp_verify_nonce($_POST['scm_fixture_setup'], 'submit_form')) {

            $post_value = filter_var($_POST['fixture_id'], FILTER_VALIDATE_INT);
        }

        $fixture_id = ($post_value) ? $post_value : null;

        if(!$fixture_id){
            $post = ScmData::get_current_fixture($fixture_id);
            $fixture_id = $post->ID;

            $current_season_start_date = get_field('scm-season-start-date',$current_season->ID);
            $curent_season_date = \DateTime::createFromFormat('Y-m-d H:i:s',$current_season_start_date, new \DateTimeZone('Europe/Athens'));
            $curent_fixture_start_date = get_field('week-start-date',$fixture_id);
            $curent_fixture_date = \DateTime::createFromFormat('Y-m-d H:i:s',$curent_fixture_start_date, new \DateTimeZone('Europe/Athens'));

            if( $curent_fixture_date < $curent_season_date){
                return '<!-- No Current Fixture for Season-->'; 
            }
        }

        $fixture = get_post($fixture_id);
        if(!$fixture){
            $fixture = ScmData::get_default_WP_Post();
        }

        echo '<p class="test">Fixture: ' . $fixture->post_title . '</p>';

        $output = '';
        $data = array();

        //player data
        $player = new Player(wp_get_current_user());

        
       
        //------- output start -------//
        $output .= $this->template->container_start;
        $matches = ScmData::get_all_matches_for_current_fixture($fixture_id);

        // total weekly points
        if(isset($player->player_points['fixture_id_' . $fixture_id]['weekly-championship']['points'])){
            $week_total_points = $player->player_points['fixture_id_' . $fixture_id]['weekly-championship']['points'];
            $output .= "<div class='week-points' >Πόντοι Εβδομάδας: {$week_total_points}</div>";
        }
        

        $current_date = new \DateTime();
        $current_date->setTimezone(new \DateTimeZone('Europe/Athens'));

        if ($matches) {
            foreach ($matches as $match) {
                
                $data['openForPredictions'] = true;

                //when creating new datetime always set timezone
                $match_date = new \DateTime($match->post_date, new \DateTimeZone('Europe/Athens'));

                //scm-full-time-score
                //scm-full-time-home-score
                //scm-full-time-away-score
                $prediction_post = ScmData::get_players_predictions_for_match( $match,$player->player_id);
                $current_match = (new FootballMatch($match->ID))->setup_data();

                unset($data['match-points']);
                unset($data['live-score']);

                if ($current_date > $match_date) {
                    $data['openForPredictions'] = false;

                    $score_acf_group = get_field('scm-full-time-score', $match->ID);
                    $score_home = $score_acf_group['scm-full-time-home-score'];
                    $score_away = $score_acf_group['scm-full-time-away-score'];

                    if(!$score_acf_group['scm-full-time-home-score'] && !$score_acf_group['scm-full-time-away-score']){
                        $half_time_score = get_field('scm-half-time-score', $match->ID);
                        $score_home = $half_time_score['scm-half-time-home-score'];
                        $score_away = $half_time_score['scm-half-time-away-score'];
                    }

                    $data["match-score"] = $score_home . ' - ' . $score_away;

                    if(isset($player->player_points['fixture_id_' . $fixture_id]['match_id_' . $match->ID]['season-league']['points'])){
                        $points_gained = $player->player_points['fixture_id_' . $fixture_id]['match_id_' . $match->ID]['season-league']['points'];
                        $data['match-points'] = $points_gained;
                    }

                    //if the game has finished no need to calculate score here
                    if(!empty($prediction_post) && isset($prediction_post[0])){
                        $total_points = CalculateScore::calculate_points_after_prediction_submit($prediction_post[0],$current_match);
                        $data['live-score'] = $total_points;
                    }
                }

                $data["player-id"] = get_current_user_id();
                $data['match-id'] = $match->ID;

                // add match pointables
                //$current_match = new FootballMatch($match->ID);

                $points_table = json_encode($current_match->points_table,  JSON_UNESCAPED_UNICODE);
                $output .= "<div id='match_{$match->ID}_pointstable' data-pointstable='{$points_table}'></div>";

                //$prediction_post = ScmData::get_players_predictions_for_match( $match,$player->player_id);

                // show prediction for current match 
                $data['prediction-string'] = 'Δεν υπάρχει πρόβλεψη!';
                $prediction_string = '';
                if(!empty($prediction_post)){
                     //test
                    $possible_points = new CalculatePossiblePlayerPoints($prediction_post[0]);
                    $possible_points->get_points($current_match);

                    //$player_prediction = new PlayerPrediction($prediction_post[0]);
                    //$prediction_string .= (string) $player_prediction;
                    $prediction_string .= (string) $possible_points;
                    $data['prediction-string'] = $prediction_string; 
                } 

                //error
                $data['match-date'] = $match_date->getTimestamp();

                $repeater_teams = get_field("match-teams", $match->ID);
                //var_dump(get_field('scm-team-capabilityrange', $repeater_teams[0]["away-team"][0]->ID));

                $data["home-team-name"] = $repeater_teams[0]["home-team"][0]->post_title;
                $data["home-team-id"] = $repeater_teams[0]["home-team"][0]->ID;
                $data["home-team-image"] = get_the_post_thumbnail($repeater_teams[0]["home-team"][0]->ID);
                $data['home-team-capability'] = get_field('scm-team-capabilityrange', $repeater_teams[0]["home-team"][0]->ID);

                if(!$data['home-team-capability']){
                    $data['home-team-capability'] = 0;
                }

                //var_dump(get_field('scm-team-capabilityrange', $repeater_teams[0]["home-team"][0]->ID));
                //var_dump(get_post_meta($repeater_teams[0]["home-team"][0]->ID,'scm-team-capabilityrange',true));

                $data["stadium"] = $repeater_teams[0]["home-team"][0]->post_content;
                $data["match-date-string"] = $match->post_date;

                $data["away-team-name"] = $repeater_teams[0]["away-team"][0]->post_title;
                $data["away-team-id"] = $repeater_teams[0]["away-team"][0]->ID;
                $data["away-team-image"] = get_the_post_thumbnail($repeater_teams[0]["away-team"][0]->ID);
                $data['away-team-capability'] = get_field('scm-team-capabilityrange', $repeater_teams[0]["away-team"][0]->ID);

                if(!$data['away-team-capability']){
                    $data['away-team-capability'] = 0;
                }

                $output .= $this->template->get_html($data);
            }
        }

        $output .= $this->template->container_end;

        $output .= $this->template->get_css();

        return $output;
    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\FixtureTemplate('div', 'scm-fixture-games-list', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }

}
