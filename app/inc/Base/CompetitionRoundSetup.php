<?php
/**
 * @package scoremasters
 *
 * When new competition is created set publish date
 * same as the fixture date.
 */

namespace Scoremasters\Inc\Base;

use Scoremasters\Inc\Base\ScmData;

class CompetitionRoundSetup
{

    public static function init()
    {
        add_action('transition_post_status', array(static::class, 'add_actions_on_create_post'), 10, 3);
        add_action('new_fixture_published_event', array(static::class, 'actions_on_new_fixture_published'), 15, 3);
    }

    public static function add_actions_on_create_post(string $new_status, string $old_status, \WP_Post $competion_round)
    {

        if (get_post_type($competion_round) !== 'scm-competition-roun') {
            return;
        }

        // use transition_post_status hook
        if ($old_status === $new_status) {
            return;
        }

        if ($old_status !== 'future') {
            return;
        }

        if ($new_status !== 'publish') {
            return;
        }

        error_log(__METHOD__ . '  new_status: ' . $new_status . ' --- ' . 'old_status: ' . $old_status);

        self::setup_date($competion_round);

        self::create_cup_fixture_meta($competion_round);

    }

    /**
     * Setup competition_round post date same as the fixture date where
     * matches will take place.
     *
     * @param WP_Post  $competion_round   The competition post of type scm-competition-roun
     *
     */
    public static function setup_date(\WP_Post $competion_round)
    {

        $fixture_object = (get_field('scm-related-week', $competion_round->ID))[0];

        $wp_formated_date = get_the_date('Y-m-d H:i:s', $fixture_object);
        $wp_formated_date_gmt = get_gmt_from_date($wp_formated_date);

        $post_status = 'future';

        $now = new \DateTimeImmutable('', new \DateTimeZone('Europe/Athens'));

        if ($wp_formated_date < $now) {
            $post_status = 'publish';
        }

        if (SCM_DEBUG) {
            error_log(static::class . ' setup fixture title ' . $fixture_object->post_title);
            error_log(static::class . ' setup competition round date ' . $wp_formated_date);
        }

        $updated = wp_update_post(array(
            'ID' => $competion_round->ID,
            'post_date' => $wp_formated_date,
            'post_date_gmt' => $wp_formated_date_gmt,
            'post_status' => $post_status)
        );

        if (is_wp_error($updated)) {
            error_log($updated->get_error_messages());
        }
    }

    public static function actions_on_new_fixture_published(string $new_status, string $old_status, \WP_Post $fixture_post)
    {

        // When new fixture is published
        // check previous fixture for cup phase-round
        // if exists -> calculate score

        $prev_fixture = ScmData::get_previous_fixture();

        if ($prev_fixture->post_title = 'default') {
            error_log(__METHOD__ . ' no previus fixture for fixture_id: ' . $fixture_post->ID);
            return;
        }

        $competition_phase_array = get_post_meta( $fixture_post->ID, 'competition_phase', true);

        if (!isset($competition_phase_array['score-masters-cup'])) {
            error_log(__METHOD__ . ' no cup round for fixture_id: ' . $fixture_post->ID);
            return;
        }

        $cup_phase_id = $competition_phase_array['score-masters-cup'];

        // calculate cup score - points

        //get matchups
        //[[],[]]

        $acf_matchups = get_field('groups__headsup', $cup_phase_id);
        // $matchups array of wp_users
        $matchups = [];
        foreach ($acf_matchups as $group) {
            $players = [];
            foreach ($group['group__headsup'] as $acf_player) {
                $players[] = $acf_player['scm-group-player'];
            }

            $matchups[] = $players;
        }

        // $score_array = array [ [0:[player_id,cup_points],1:[player_id,cup_points]],[] ... ]
        $score_array = CalculateScoremastersCupPoints::calculate($matchups, $fixture_post->ID);

        /*
        [ 'total_points' => ['season-league' => int,'weekly-championship' => int]
          'fixture_id_3709' => [ 
            'match_id_3631' => ['season-league' => ['points' => int ]], 
            'match_id_3637' => ...,
            'weekly-championship' => [ 'points' => int,'score' => int,'opponent_id' => int,'home_field_advantage' => boolean],
            'score-masters-cup' => [ 'score' => int ,'opponent_id' => int, 'phase_id'=> int]
            ]
        ]
        */
        $current_season = ScmData::get_current_season();

        foreach( $score_array as $matchup){


                $cup_score_0 = $matchup[0]['cup_points'];
                $player_id_0 = $matchup[0]['player_id'];

                $cup_score_1 = $matchup[1]['cup_points'];
                $player_id_1 = $matchup[1]['player_id'];

                $meta_key = 'score_points_seasonID_' . $current_season->ID;
                $score_0 = get_user_meta($player_id_0,$meta_key, true);
                $score_0['fixture_id_' . $prev_fixture->ID]['score-masters-cup'] = ['score' => $cup_score ,'phase_id'=> $cup_phase_id,'opponent_id' => $player_id_1 ];

                $success_0 = update_user_meta($player_id_0, $meta_key, $score_0);

                $score_1 = get_user_meta($player_id_1,$meta_key, true);
                $score_1['fixture_id_' . $prev_fixture->ID]['score-masters-cup'] = ['score' => $cup_score ,'phase_id'=> $cup_phase_id,'opponent_id' => $player_id_0 ];
            
                $success_1 = update_user_meta($player_id_1, $meta_key, $score_1);

        }

    }

    /**
     * Set new custom meta with name competition_round_for_season_id_XX
     * array('score-masters-cup' => array('round_id' => XX))
     *
     * @param WP_Post   $competion_round  The competition post
     *
     */
    public static function create_cup_fixture_meta(\WP_Post $competion_round)
    {

        //there is only one related-competition
        $competition = (get_field('scm-related-competition', $competion_round->ID))[0];
        $fixtures_array = (get_field('scm-related-week', $competion_round->ID));

        // competition_phase [ score-masters-cup: int (post_id), score-champions: int (post_id)]
        foreach ($fixtures_array as $fixture) {

            $data = array('score-masters-cup' => $competion_round->ID);
            $prev_data = get_post_meta($fixture->ID, 'competition_phase', true);

            if (array_key_exists('score-masters-cup', $prev_data)) {
                error_log(__METHOD__ . ' cup id already exixsts in fixture_id: ' . $fixture->ID);
                continue;
            }

            $current_data = array_merge($prev_data, $data);

            $success = update_post_meta($fixture->ID, 'competition_phase', $current_data);

            if ($success === false) {
                error_log(__METHOD__ . ' FAILED updating meta competition_phase for fixture id : ' . $fixture->ID);
            }

            error_log(__METHOD__ . ' updating meta competition_phase for fixture id : ' . $fixture->ID);

        }

    }
}
