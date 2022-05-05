<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Base;

use Scoremasters\Inc\Shortcodes\FixtureShortcode;
use Scoremasters\Inc\Classes\FootballMatch;

class MatchSetup {
    public static function init(){
        add_filter('acf/update_value/name=match-date', array(static::class,'scm_match_update_post_date'), 10, 4);
        add_filter('acf/update_value/name=scm-match-end-time', array(static::class,'scm_match_trigger_players_point_calculation'), 99, 4);
    }

    /**
     * Updates scm-match post date with the date of the scheduled match
     * when user set's match date.
     * 
     * @param mixed        $value     The field value
     * @param (int|string) $post_id   The post ID where the value is saved
     * @param array        $field     The field array containing all settings.
     * @param mixed        $original  The original value before modification
     */
    public static function scm_match_update_post_date( $value, $post_id, array $field, $original  ){
        
        if(get_post_type($post_id) !== 'scm-match'){
            return $value;
        }
       
        //(string)$value format: 20220406 todo: change acf time format to 'Y-m-d H:i:s'
        $start_date = \DateTime::createFromFormat('Y-m-d H:i:s',$value);
        //wp post_date format: 0000-00-00 00:00:00

        $wp_formated_date = $start_date->format('Y-m-d H:i:s');
        
        $updated = wp_update_post(array('ID' => $post_id,'post_date' =>  $wp_formated_date));

        if(is_wp_error( $updated )){
            error_log($updated->get_error_messages());
        }
        
        return $value;
    }


    public static function scm_match_trigger_players_point_calculation( $value, $post_id, array $field, $original  ){

        if(get_post_type($post_id) !== 'scm-match'){
            return $value;
        }

        // scm-full-time-score 
        //$target_fields = ['scm-half-time-score','scm-full-time-score'];
        $target_fields = ['scm-match-end-time'];
        if(!in_array($field['name'],$target_fields)) return $value;

        self::calculate_player_points( intval($post_id) );
        //calculate score
        return $value;
    }

    public static function calculate_player_points(int $macth_id ){

        $match = (new FootballMatch( $macth_id ))->setup_data();

        $match_date = new \DateTime($match->post_data->post_date);

        $args = array(
            'post_type' => 'scm-prediction',
            'post_status' => 'any',
            'date_query' => array(
                'year'  => (int) $match_date->format('Y'),
                'month' => (int) $match_date->format('n'),
                'day'   => (int) $match_date->format('j'),
                'hour'   => (int) $match_date->format('G'),
                'minute'   => (int) $match_date->format('i'),
                'second'   => (int) $match_date->format('s'),
            )
        );

        $predictions = get_posts($args);
        //----------------------------------------
        
        $points_table = get_option('points_table');

        //todo: get current ficture
        $fixcture = get_posts(array('post_type' => 'scm-fixture','post_status' => 'publish','posts_per_page' => 1,));

        
        foreach($predictions as $prediction){
            self::calculate_points_after_prediction_submit($prediction, $match, $fixcture[0]->ID);
        }
    }

    public static function calculate_points_after_prediction_submit(\WP_Post $prediction, FootballMatch $match, $fixcture_id){

        
        $prediction_content = unserialize($prediction->post_content);
    
        $player_id = $prediction->post_author;
        
        $dynamikotita_home_team = $match->home_team_dynamikotita;
        $dynamikotita_away_team = $match->away_team_dynamikotita;
        
        $column = strval($dynamikotita_home_team - $dynamikotita_away_team);
            
        $points_table=$match->points_table;
        
        $prediction_points_shmeio=$points_table[$column][$prediction_content["SHMEIO"]];
        $prediction_points_under_over=$points_table[$column][$prediction_content["Under / Over"]];
        $prediction_points_score=$points_table[$column][$prediction_content["score"]];

        $points['shmeio'] = $points_table[$column][$prediction_content["SHMEIO"]];
        $points['under_over'] = $points_table[$column][$prediction_content["Under / Over"]];
        $points['score'] = $points_table[$column][$prediction_content["score"]];
    
        //calculate points for scorer
        //$prediction_points_scorer=$points_table[$column][$prediction_content["Scorer"]];
    
        //return "Διαφορά Δυναμικότητας: ".$column." | Σημείο: ".$prediction_content["SHMEIO"]." | points: ".$prediction_points_shmeio." | player_id: ".$player_id;
        //file_put_contents(__DIR__ . '/score.txt', json_encode($points) . "\n",FILE_APPEND);

        //meta-name = season name
        /*
       $data = array(
          'total_points' => $total,
           $ficture_id => array(
               $match_id => $score
           )
       );
       */
    }

    //When user sets scm-match-end-time, restrict user from editing acf fields, filter by post id
    //default options -> current season, curent fixture ,
    
}