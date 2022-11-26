<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Base;

final class DataQuery{

    public $fixture;
    public $season;
    public $matches;

    public function __construct(){

    }

    public function get_season($season_id = null)//:DataQuery
    {
        $args = array(
            'post_type' => 'scm-season',
            'post_status' => 'publish',
            'p' => $season_id,
            'posts_per_page' => 1,
        );

        //get active week
        $posts = get_posts($args);

        if (empty($posts)) {
            error_log(static::class . ' exporter---- no active season');
            throw new \Exception(static::class . ' no active season');
        }

        $curent_season = $posts[0];

        $this->season = $curent_season;

        //return $this;

        return $curent_season;

    }

    public function get_fixture($fixture_id = null)//:DataQuery
    {

        $args = array(
            'post_type' => 'scm-fixture',
            'post_status' => 'publish',
            'p' => $fixture_id,
            'posts_per_page' => 1,
        );

        //get active week
        $posts = get_posts($args);

        if (empty($posts)) {
            error_log('exporter---- no active fixture');
            throw new \Exception(static::class . ' no active fixture');
        }

        $current_fixture = $posts[0];

        $this->fixture = $current_fixture;

        //return $this;

        return $current_fixture;
    }

    public function get_predictions_by_match_id($match_id, $player_id = null){

        $args = array(
            'post_type' => 'scm-prediction',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'author' => $player_id,
            's' => $match_id . '-',
        );

        $predictions = get_posts($args);

        return $predictions;
    }

    public function get_matches(){

        // wrong use of repeater field 'week-matches' !!!!
        $matches = get_field('week-matches',$this->fixture->ID)[0]['week-match'];

        if(!$matches){
            throw new \Exception(static::class . ' get_field("week-matches") error');
        }

        $this->matches = $matches;

        return $matches;
    }

}