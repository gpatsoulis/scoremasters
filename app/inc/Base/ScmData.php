<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Base;

final class ScmData
{

    public static function get_current_fixture()
    {

        $args = array(
            'post_type' => 'scm-fixture',
            'post_status' => 'publish',
            'posts_per_page' => 1,
        );

        //get active week
        $posts = get_posts($args);

        if (empty($posts)) {
            error_log('exporter---- no active fixture');
            $current_fixture = array();
        }

        $current_fixture = $posts[0];

        return $current_fixture;
    }

    public static function get_finished_matches_for_fixture(\WP_Post $current_fixture)
    {
        $current_date = new \DateTime();
        $current_date->setTimezone(new \DateTimeZone('Europe/Athens'));

        $fixture_start_date_string = get_field('week-start-date', $current_fixture->ID);
        $fixture_start_date = new \DateTime($fixture_start_date_string, new \DateTimeZone('Europe/Athens'));

        $fixture_end_date_string = get_field('week-end-date', $current_fixture->ID);
        $fixture_end_date = new \DateTime($fixture_end_date_string, new \DateTimeZone('Europe/Athens'));

        $args = array(
            'post_type' => 'scm-match',
            'post_status' => 'publish',
            'date_query' => array(
                'after' => array(
                    'year' => (int) $fixture_start_date->format('Y'),
                    'month' => (int) $fixture_start_date->format('n'),
                    'day' => (int) $fixture_start_date->format('j'),
                ),
                'before' => array(
                    'year' => (int) $current_date->format('Y'),
                    'month' => (int) $current_date->format('n'),
                    'day' => (int) $current_date->format('j'),
                ),
                'inclusive' => true,
            ),
        );

        $matches = get_posts($args);

        return $matches;
    }

    public static function get_player_predictions_for_finished_matches(array $matches,  $player_id = '')
    {
        $all_predictions = array();

        foreach ($matches as $match) {

            $match_date = new \DateTime($match->post_date, new \DateTimeZone('Europe/Athens'));

            $args = array(
                'post_type' => 'scm-prediction',
                'post_status' => 'any',
                'posts_per_page' => -1,
                'author' => $player_id,
                'date_query' => array(
                    'year' => (int) $match_date->format('Y'),
                    'month' => (int) $match_date->format('n'),
                    'day' => (int) $match_date->format('j'),
                    'hour' => (int) $match_date->format('G'),
                    'minute' => (int) $match_date->format('i'),
                    'second' => (int) $match_date->format('s'),
                ),
            );

            $predictions = get_posts($args);

            foreach ($predictions as $prediction) {
                $all_predictions[] = $prediction;
            }

        }

        return $all_predictions;
    }

    public static function get_all_player_predictions_for_fixture(\WP_Post $fixture,  $player_id = ''){


        $fixture_start_date_str = get_field('week-start-date',$fixture->ID);
        if(!$fixture_start_date_str){
            error_log( 'ScmData::get_all_player_predictions_for_fixture\ error value in acf field week-start-date');
            //return array();
        }
        $fixture_start_date = new \DateTime( $fixture_start_date_str, new \DateTimeZone('Europe/Athens'));

        $fixture_end_date_str = get_field('week-end-date',$fixture->ID);
        if(!$fixture_start_date_str){
            error_log( 'ScmData::get_all_player_predictions_for_fixture\ error value in acf field week-end-date');
            //return array();
        }
        $fixture_end_date = new \DateTime( $fixture_end_date_str, new \DateTimeZone('Europe/Athens'));

        $args = array(
            'aurhor' => $player_id,
            'post_type' => 'scm-prediction',
            'post_status' => 'any',
            'date_query' => array(
                'after' => array(
                    'year' => (int) $fixture_start_date->format('Y'),
                    'month' => (int) $fixture_start_date->format('n'),
                    'day' => (int) $fixture_start_date->format('j'),
                ),
                'before' => array(
                    'year' => (int) $fixture_end_date->format('Y'),
                    'month' => (int) $fixture_end_date->format('n'),
                    'day' => (int) $fixture_end_date->format('j'),
                ),
            ),
        );

        $predictions = get_posts($args);

        return $predictions;
    }

}