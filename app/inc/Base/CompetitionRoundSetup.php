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
        add_action('transition_post_status', array(static::class, 'add_actions'), 10, 3);
        add_filter('wp_insert_post_data',array(static::class,'add_actions2'),99,4);
    }

    public static function add_actions( string $new_status, string $old_status, \WP_Post $competion_round ){

        if (get_post_type($competion_round) !== 'scm-competition-roun') {
            return;
        }

        // use transition_post_status hook
        if ($old_status === $new_status) {
            return;
        }

        if ($new_status !== 'publish' && $old_status === 'publish') {
            return;
        }

        self::setup_date( $competion_round );

        self::create_fixture_meta( $competion_round );


    }

    public static function add_actions2( array $data,array $postarr, array $unsanitized_postarr, bool $update){
        $post_type = 'scm-competition-roun'; 

        if( $data['post_type'] !== $post_type ){
            return $data;
        }

        if ($update) {
            return $data;
        }

        
    }

    public static function setup_date2( array $data ){

    }
    

    /**
     * Setup competition_round post date same as the fixture date where 
     * matches will take place.
     * 
     * @param WP_Post  $competion_round   The competition post of type scm-competition-roun
     * 
     */
    public static function setup_date( \WP_Post $competion_round)
    {

        $fixture_object = (get_field('scm-related-week', $competion_round->ID))[0];

        $fixture_date = get_the_date('Y-m-d H:i:s', $fixture_object);
        $wp_formated_date_gmt =  get_gmt_from_date( $fixture_date );

        if (SCM_DEBUG) {
            error_log(static::class . ' setup fixture title ' . $fixture_object->post_title);
            error_log(static::class . ' setup competition round date ' . $fixture_date);
        }

        $updated = wp_update_post(array(
            'ID' => $competion_round->ID, 
            'post_date' => $fixture_date,
            'post_date_gmt' => $wp_formated_date_gmt,
        ));

    }

    /**
     * Set new custom meta with name competition_round_for_season_id_XX
     * array('score-masters-cup' => array('round_id' => XX))
     * 
     * @param WP_Post   $competion_round  The competition post
     * 
     */
    public static function create_fixture_meta(  \WP_Post $competion_round )
    {
        

        $competition = (get_field('scm-related-competition', $competion_round->ID))[0];
        $matchups = (get_field('groups_headsup', $competion_round->ID))[0];
        $fixture_object = (get_field('scm-related-week', $competion_round->ID))[0];
        $round_no = get_field('competition_round_number',$competion_round->ID);

        // get competition type by term
        $competition_term = get_the_terms($competition,'scm_competition_type')[0];

        $surrent_season = ScmData::get_current_season();

        switch ($competition_term) {
            case 'score-masters-cup':
                $data = array('score-masters-cup' => array('round_id' => $competion_round->ID));
                $id = update_post_meta( $fixture_object->ID, 'competition_round_for_season_id_' . $surrent_season->ID, $data);
                break;
        }
        

        if (SCM_DEBUG) {
            error_log(static::class . ' setup fixture title ' . $competition);
            file_put_contents(SCM_DEBUG_PATH . '/competition_round.json', json_encode($matchups) . "\n", FILE_APPEND);
        }

    }
}
