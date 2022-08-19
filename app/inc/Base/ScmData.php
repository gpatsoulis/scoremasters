<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Base;

use Scoremasters\Inc\Classes\Player;

final class ScmData
{

    public static function get_current_season($season_id = null): \WP_Post
    {
        $args = array(
            'post_type' => 'scm-season',
            'post_status' => 'publish',
            'p' => $season_id,
            'posts_per_page' => 1,
        );

        //get active season
        $posts = get_posts($args);

        if (empty($posts)) {
            error_log(static::class . ' exporter ---- no active season');
            throw new Exception(static::class . ' no active season');
        }

        if (!isset($posts[0])) {
            return null;
        }

        $curent_season = $posts[0];

        return $curent_season;

    }

    public static function get_current_fixture($fixture_id = null): \WP_Post
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
            throw new Exception(__METHOD__ . ' no active fixture');
        }

        if (!isset($posts[0])) {
            return null;
        }

        $current_fixture = $posts[0];

        return $current_fixture;
    }

    public static function get_previous_fixture(): \WP_Post
    {

        $current_season = self::get_current_season();
        $curent_season_date = new \DateTime($current_season->post_date, new \DateTimeZone('Europe/Athens'));

        $args = array(
            'post_type' => 'scm-fixture',
            'post_status' => 'publish',
            'posts_per_page' => 2,
        );

        //get active week
        $posts = get_posts($args);
        if(empty($posts)){
            return self::get_default_WP_Post();
        }

        $prev_fixture = end($posts);
        $prev_fixture_date = new \DateTime($prev_fixture->post_date, new \DateTimeZone('Europe/Athens'));

        //todo: fix return argument, return default WP_Post object
        if ($prev_fixture_date < $curent_season_date) {
            return self::get_default_WP_Post();
        }

        return $prev_fixture;
    }

    public static function get_finished_matches_for_fixture(\WP_Post $current_fixture): array
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

    //give's false results if match date changed by user
    public static function get_player_predictions_for_finished_matches(array $matches, $player_id = ''): array
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

    public static function get_players_predictions_for_match(\WP_Post $match, $player_id = null): array
    {

        $args = array(
            'post_type' => 'scm-prediction',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'author' => $player_id,
            's' => $match->ID . '-',
        );

        $predictions = get_posts($args);

        return $predictions;

    }

    public static function get_all_matches_for_current_fixture($fixture_id = null)
    {

        $current_fixture = self::get_current_fixture($fixture_id);

        // wrong use of repeater field 'week-matches' !!!!
        $matches = get_field('week-matches', $current_fixture->ID)[0]['week-match'];

        if (!$matches) {
            error_log(__METHOD__ . ' get_field("week-matches") error ' . $fixture_id);
            //throw new Exception(static::class . ' get_field("week-matches") error');
        }

        return $matches;
    }

    public static function get_all_matches_for_fixture($fixture)
    {

        $current_fixture = $fixture;

        // wrong use of repeater field 'week-matches' !!!!
        $matches = get_field('week-matches', $current_fixture->ID)[0]['week-match'];

        if (!$matches) {
            throw new Exception(static::class . ' get_field("week-matches") error');
        }

        return $matches;
    }

    public static function get_all_player_prediction_for_fixture_by_title(array $matches, $player_id = ''): array
    {

        $all_predictions = array();

        foreach ($matches as $match) {

            $predictions = self::get_players_predictions_for_match($match, $player_id);

            foreach ($predictions as $prediction) {
                $all_predictions[] = $prediction;
            }
        }

        return $all_predictions;
    }

    public static function get_all_player_predictions_for_fixture(\WP_Post $fixture, $player_id = ''): array
    {

        $fixture_start_date_str = get_field('week-start-date', $fixture->ID);

        if (!$fixture_start_date_str) {
            error_log('ScmData::get_all_player_predictions_for_fixture\ error value in acf field week-start-date');
            throw new Exception(static::class . ' no active fixture');
        }
        $fixture_start_date = new \DateTime($fixture_start_date_str, new \DateTimeZone('Europe/Athens'));

        $fixture_end_date_str = get_field('week-end-date', $fixture->ID);
        if (!$fixture_end_date_str) {
            error_log('ScmData::get_all_player_predictions_for_fixture\ error value in acf field week-end-date');
            throw new Exception(static::class . ' no fixture_end_date_str');
        }
        $fixture_end_date = new \DateTime($fixture_end_date_str, new \DateTimeZone('Europe/Athens'));

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
            'inclusive' => true,
        );

        $predictions = get_posts($args);

        return $predictions;
    }

    /**
     * @return WP_Post[]
     */

    public static function get_all_fixtures_for_season($season_id = null): array
    {

        $current_season = self::get_current_season($season_id);

        $season_start_date_str = get_field('scm-season-start-date', $current_season->ID);
        if (!$season_start_date_str) {
            error_log( __METHOD__ . ' scm-season-start-date error');
        }
        $season_start_date = new \DateTime($season_start_date_str, new \DateTimeZone('Europe/Athens'));

        $season_end_date_str = get_field('scm-season-end-date', $current_season->ID);
        if (!$season_end_date_str) {
            error_log(__METHOD__ . ' scm-season-end-date error');
        }
        $season_end_date = new \DateTime($season_end_date_str, new \DateTimeZone('Europe/Athens'));
        
        $args = array(
            'post_type' => 'scm-fixture',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'date_query' => array(
                'after' => array(
                    'year' => (int) $season_start_date->format('Y'),
                    'month' => (int) $season_start_date->format('n'),
                    'day' => (int) $season_start_date->format('j'),
                ),
                'before' => array(
                    'year' => (int) $season_end_date->format('Y'),
                    'month' => (int) $season_end_date->format('n'),
                    'day' => (int) $season_end_date->format('j'),
                ),
            ),
            'inclusive' => true,
        );

        $fixtures = get_posts($args);

        return $fixtures;
    }

    public static function get_all_scm_users(): array
    {
        $args = array('role' => 'scm-user', 'fields' => 'all');
        $all_scm_users = get_users($args);

        return $all_scm_users;
    }

    public static function get_all_players(): array
    {
        $args = array('role' => 'scm-user', 'fields' => 'all');
        $all_scm_users = get_users($args);

        if (empty($all_scm_users)) {
            return $all_scm_users;
        }
        
        $players = [];
        foreach ($all_scm_users as $user) {
            $players[] = new Player($user);
        }

        return $players;
    }

    public static function get_all_participants($all_scm_users): array
    {

        if (empty($all_scm_users)) {
            return $all_scm_users;
        }

        $players = [];
        foreach ($all_scm_users as $user) {
            $players[] = new Player($user);
        }

        return $players;

    }

    public static function competition_is_active(\WP_Post $competition): bool
    {

        // get competiton season acf-field
        // compare with current season
        // find if current season is active ( compare dates )

        $current_season = self::get_current_season();

        $competition_in_season = get_field('scm-season-competition', $competition->ID);

        if ($current_season->ID === $competition_in_season[0]->ID) {
            return true;
        }

        return false;
        //todo: set acf fields
        //$fields = get_fields($scp_season->ID);
        // check if current season
        //todo: set dates
        /*if($start_date <= $today && $today <= $end_date){
    $this->$is_active = True;
    return;
    }  */

    }

    public static function get_player_points(int $player_id, int $season_id = null): array
    {
        if (is_null($season_id)) {
            $season = self::get_current_season();
        }

        $points = get_user_meta(intval($player_id), 'score_points_seasonID_' . $season->ID);

        return $points[0];
    }

    public static function get_public_leagues(): array
    {
        $args = array(
            'post_type' => 'scm_league',
            'post_status' => 'publish',
            'meta_key' => 'scm-league-status',
            'meta_value' => 'public',
            'order' => 'ASC',
            'posts_per_page' => -1,
        );

        $posts = get_posts($args);

        return $posts;
    }

    // todo: replace old name with new "get_current_scm_competition_of_type"
    public static function get_current_scm_competition_of_type(string $scm_competition_taxonomy): \WP_Post
    {

        $args = array(
            'post_type' => 'scm-competition',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'scm_competition_type',
                    'field' => 'slug',
                    'terms' => $scm_competition_taxonomy,
                ),
            ),
        );

        $posts = get_posts($args);

        return $posts[0];
    }

    public static function get_all_leagues(string $post_type = 'scm_league')
    {
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            /*'tax_query' => array(
        array(
        'taxonomy' => $taxonomy,
        'field'    => 'slug',
        'terms'     => $taxonomy_term
        )
        ),
        'meta_query' => array(
        array(
        'key'     => $meta_key,
        'value'   => $meta_value,
        'compare' => 'LIKE',
        ),
        ),*/
        );

        $posts = get_posts($args);

        return $posts;
    }

    /**
     * args WP_Post
     * return array of Players
     */
    public static function get_league_participants(\WP_Post $scm_league): array
    {
        //repeater field scm-user-players-list -> scm-user-player
        $repeater_array = get_field('scm-user-players-list', $scm_league->ID);
        if(is_null($repeater_array)){
            return array();
        }
        
        //$participants = array_map(function ($field){return $field['scm-user-player'];} , $repeater_array);
        $participants = array_map(fn($field) => $field['scm-user-player'], $repeater_array);
        $wp_users = get_users(array('include' => $participants));

        $players = [];
        foreach ($wp_users as $user) {
            $players[] = new Player($user);
        }

        return $players;
    }

    public static function get_league_participants_ids($scm_league_id): array
    {
        //repeater field scm-user-players-list -> scm-user-player
        $repeater_array = get_field('scm-user-players-list', $scm_league_id);

        if(!$repeater_array){
            return array();
        }

        //$participants = array_map(function ($field){return $field['scm-user-player'];} , $repeater_array);
        $participants = array_map(fn($field) => $field['scm-user-player'], $repeater_array);

        return $participants;
    }

    public static function get_players_by_ids()
    {

    }

    public static function get_fixture_id_for_match_id($match_id)
    {

    }

    public static function get_next_matchup()
    {

    }

    public static function get_current_matchup()
    {
        // player id
        // get current league
        // get current fixture
        // get matchup
    }

    public static function get_next_future_fixture(): \WP_Post
    {
        $args = array(
            'post_status' => 'future',
            'post_type' => 'scm-fixture',
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'ASC',
        );

        $next_future = get_posts($args);

        if( empty($next_future) ){
            return self::get_default_WP_Post();
        }

        return $next_future[0];
    }

    // return default wp_post of type 'default' instead of null
    public static function get_default_WP_Post(): \WP_Post
    {
        $post_id = -99; // negative ID, to avoid clash with a valid post
        $post = new \stdClass();
        $post->ID = $post_id;
        $post->post_author = 1;
        $post->post_date = current_time('mysql');
        $post->post_date_gmt = current_time('mysql', 1);
        $post->post_title = 'default';
        $post->post_content = 'default';
        $post->post_status = 'publish';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->post_name = 'fake-page-' . rand(1, 99999); // append random number to avoid clash
        $post->post_type = 'default';
        $post->filter = 'raw'; // important!

        // Convert to WP_Post object
        $wp_post = new \WP_Post( $post );
        // Add the fake post to the cache
        wp_cache_add( $post_id, $wp_post, 'posts' );

        return $wp_post;
    }

}

/*
getPredictions->by(FootballMatch $match)
getPredictions->by(Player $player)
getPredictions->by(Fixture $fixture)
getPredictions->by(\DateTime $after_date,\DateTime $before_date)
getPredictions->by(string $title)
getPredictions->by($player_id)
 */
