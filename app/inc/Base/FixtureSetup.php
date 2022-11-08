<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Base;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\Player;
use Scoremasters\Inc\Classes\WeeklyMatchUps;
use Scoremasters\Inc\Services\CalculateWeeklyMatchups;
use Scoremasters\Inc\Services\CalculateWeeklyPoints;

class FixtureSetup
{

    public static function init()
    {
        add_filter('acf/update_value/name=week-start-date', array(static::class, 'scm_fixture_update_post_date'), 10, 4);
        add_filter('wp_insert_post_data',array(static::class,'set_fixture_status_to_future'),99,4);
        add_action('elementor_pro/forms/new_record', array(static::class, 'scm_player_prediction'), 10, 2);
        
        // actions run on fixture end
        // todo: change action from 'transition_post_status' to custom cron job 
        add_action('transition_post_status', array(static::class, 'add_weekly_championship_players_matchups'), 10, 3);
        add_action('transition_post_status', array(static::class, 'scm_match_trigger_players_weekly_point_calculation'), 15, 3);

        //todo: remove trigger_players_cup_point_calculation action
        add_action('transition_post_status', array(static::class, 'trigger_players_cup_point_calculation'), 15, 3);

        //todo: put add_weekly_championship_players_matchups and 
        // scm_match_trigger_players_weekly_point_calculation actions 
        // under new_fixture_published action
        add_action('transition_post_status', array(static::class, 'new_fixture_published'), 20, 3);
        
        
        //add_action('publish_scm-fixture', array(static::class, 'add_weekly_championship_players_matchups'), 10, 2);
        //add_action('wp_after_insert_post',array(static::class,'set_fixture_status_to_future2'),99,4);
    }

    public static function new_fixture_published( string $new_status, string $old_status, \WP_Post $fixture_post ){

        $post_type = 'scm-fixture';

        if( $fixture_post->post_type !== $post_type){
            return;
        }

        // use transition_post_status hook
        if ($old_status === $new_status) {
            return;
        }

        if($old_status !== 'future'){
            return;
        }

        if ($new_status !== 'publish') {
            return;
        }
        
        if( SCM_DEBUG ){
            error_log( __METHOD__ . ' ---- new fixture published ---EVENT--- ! id: ' .  $fixture_post->ID );
        }

        do_action('new_fixture_published_event', $new_status, $old_status, $fixture_post);
        
        /*
        if (!array_key_exists('new_fixture_published_event', $GLOBALS['wp_filter'])) {
            do_action('new_fixture_published_event', $new_status, $old_status, $fixture_post);
        }
        */

    }


     /**
     *  Set new post of post type 'scm-fixture' to post_status = future
     * 
     *  @param array   $data                 An array of slashed, sanitized, and processed post data.
     *  @param array   $postarr              An array of sanitized (and slashed) but otherwise unmodified post data.
     *  @param array   $unsanitized_postarr  An array of slashed yet *unsanitized* and unprocessed post data as originally passed to wp_insert_post().
     *  @param bool    $update               Whether this is an existing post being updated.
     */

    public static function set_fixture_status_to_future( $data,$postarr,$unsanitized_postarr,$update ){

        $post_type = "scm-fixture"; 
        
        if( $data['post_type'] !== $post_type ){
            return $data;
        }

        if ($update) {
            return $data;
        }

        

        $post_date = new \DateTime($data['post_date'], new \DateTimeZone('Europe/Athens'));
        $current_date = new \DateTime('',new \DateTimeZone('Europe/Athens'));

        //add +hour day to newly created post
        $new_date = $current_date->modify('+1 hour');
        $data['post_date'] = $new_date->format('Y-m-d H:i:s');
        $data['post_date_gmt'] = get_gmt_from_date($new_date->format('Y-m-d H:i:s'));

        if( SCM_DEBUG ){
            error_log( __METHOD__ . ' ---- set_fixture_status_to_future! ' .  $data['post_title']);
            error_log(__METHOD__ . ' ---- post_date: ' . $post_date->format('Y-m-d H:i:s'));
            error_log(__METHOD__ . ' ---- current_date: ' . $current_date->format('Y-m-d H:i:s'));
        }
  
         return $data;
    }

    /**
     *  Set fixture date same as scm-fixture-start-date
     * 
     *  @param mixed        $value            The field value
     *  @param int|string   $fixture_id       The post ID where the value is saved.
     *  @param array        $field            The field array containing all settings.
     *  @param mixed        $original         The original value before modification.
     */

    public static function scm_fixture_update_post_date($value, $fixture_id, array $field, $original)
    {
        //todo: set post status to future
        if (get_post_type($fixture_id) !== 'scm-fixture') {
            return $value;
        }

        //$start_date = \DateTime::createFromFormat('Y-m-d H:i:s', $value, new \DateTimeZone('Europe/Athens'))->setTime(0, 0);

        $start_date = \DateTime::createFromFormat('Y-m-d H:i:s', $value, new \DateTimeZone('Europe/Athens'));
        $start_date->modify('+1 hour');

        if(SCM_STAGING){
            //$start_date->modify('-1 hour');
        }
        //wp post_date format: 0000-00-00 00:00:00

        $wp_formated_date = $start_date->format('Y-m-d H:i:s');
        $wp_formated_date_gmt =  get_gmt_from_date( $wp_formated_date );

        $now = new \DateTimeImmutable('', new \DateTimeZone('Europe/Athens'));

        $post_status = 'future';

        if ($start_date < $now) {
            $post_status = 'publish';
        }

        $updated = wp_update_post(array('ID' => $fixture_id, 'post_date' => $wp_formated_date, 'post_date_gmt' => $wp_formated_date_gmt, 'post_status' => $post_status));

        if(SCM_DEBUG){
            error_log(__METHOD__ . ' original value: '. $original);
            error_log(__METHOD__ . ' if post status != future -> egine malakia --- post status:' .  $post_status);
        }

        if (is_wp_error($updated)) {
            error_log($updated->get_error_messages());
        }

        return $value;
    }

    // todo: use match object instead of match_data array
    public static function scm_match_trigger_players_weekly_point_calculation(string $new_status, string $old_status, \WP_Post $fixture_post)
    {
        
        if( $fixture_post->post_type !== 'scm-fixture'){
            return;
        }

        // use transition_post_status hook
        if ($old_status === $new_status) {
            return;
        }

        if($old_status !== 'future'){
            return;
        }

        if ($new_status !== 'publish') {
            return;
        }

        $prev_fixture = ScmData::get_previous_fixture();
        if($prev_fixture->post_title === 'default'){
            return;
        }


       if(SCM_DEBUG){
        error_log( __METHOD__ . ' ---- calculating weekly points for fixture: ' . $prev_fixture . ' current_fixture: ' . $fixture_post->ID);
       }

        $match_data = array(
            'fixture_id' => $prev_fixture->ID,
            'season_id' => (ScmData::get_current_season())->ID,
        );

        $matches = get_field('week-matches', $prev_fixture->ID)[0]['week-match'];
        //usort($matches, self::date_compare);

        $all_leagues = ScmData::get_all_leagues();
        $weekly_competition_post = ScmData::get_current_scm_competition_of_type('weekly-championship');
        $weekly_matchups = (new WeeklyMatchUps($weekly_competition_post->ID))->get_matchups();
        //$weekly_competition = new WeeklyChampionshipCompetition( $weekly_competition_post, $weekly_matchups );

        foreach ($all_leagues as $league) {

            $matchups = $weekly_matchups->by_fixture_id($prev_fixture->ID)->by_league_id($league->ID);
            $calculate_weekly_points = new CalculateWeeklyPoints($match_data, $matchups);
            $calculate_weekly_points->calculate()->save();

        }

    }

    public static function add_weekly_championship_players_matchups(string $new_status, string $old_status, \WP_Post $fixture_post)
    {

        if( $fixture_post->post_type !== 'scm-fixture'){
            return;
        }

        // use transition_post_status hook
        if ($old_status === $new_status) {
            return;
        }

        if ($new_status !== 'future') {
            return;
        }

       

        if(SCM_DEBUG){
            error_log( __METHOD__ . ' calculating weekly matchups for fixture: ' .  $fixture_post->ID);
        }
        //weekly-championship

        // get competition WP_Post
        // todo: check competition is in current season 
        $weekly_competition = ScmData::get_current_scm_competition_of_type('weekly-championship');

        $matchups = new WeeklyMatchUps($weekly_competition->ID);

        // get all active leagues WP_Post[]
        $leagues_array = ScmData::get_all_leagues();

        foreach ($leagues_array as $league) {

            $calculate_matchups = (new CalculateWeeklyMatchups($matchups, $league->ID))
                ->for_league_id($league->ID)
                ->for_fixture_id($fixture_post->ID)
                ->save();
        }

        // setup matchups for each championship
        // save matchups in custom meta for each championship

        // seasonid_XXX [ 'fid_XXX' => ['leagueid_XXX' => ['pairs'],'leagueid_XXX' => ['pairs']]];

    }

    public static function trigger_players_cup_point_calculation(string $new_status, string $old_status, \WP_Post $fixture_post){
        

        if( $fixture_post->post_type !== 'scm-fixture'){
            return;
        }

        // use transition_post_status hook
         if ($old_status === $new_status) {
            return;
        }

        if($old_status !== 'future'){
            return;
        }

        if ($new_status !== 'publish') {
            return;
        }


    }

    public static function scm_player_prediction($record, $ajax_handler)
    {

        $form_name = $record->get_form_settings('form_name');

        if ($form_name !== 'scm-prediction-form') {
            error_log(static::class . ' - invalid form name - ' . $form_name);
            return;
        }

        $form_data = $record->get_formatted_data();

        //error_log( json_encode($form_data,  JSON_UNESCAPED_UNICODE) );
        /*
{"SHMEIO":"1\/1","Under \/ Over":"Under 4.5","score":"-","Scorer":"1051","Double Points":"SHMEIO"}
{"SHMEIO":"1\/1","Under \/ Over":"-","score":"2-0","Scorer":"1051","Double Points":"UNDER \/ OVER"}
{"SHMEIO":"1\/1","Under \/ Over":"-","score":"2-0","Scorer":"1051","Double Points":"SCORER"}

        */

        $translations = array(
             'ΣΗΜΕΙΟ'              => 'SHMEIO',
             'ΣΚΟΡΕΡ'              => 'Scorer',
             'ΔΙΠΛΑΣΙΑΣΜΟΣ ΠΟΝΤΩΝ' => 'Double Points',
             'ΣΚΟΡ'                => 'score'
        );

        foreach( $form_data as $key => $value){
            if(isset($translations[$key])){
                $form_data[$translations[$key]] = $form_data[$key];
                unset($form_data[$key]);
            }
        }

        error_log( json_encode($form_data,  JSON_UNESCAPED_UNICODE) );
        
        // todo: filter $key values from greek to english defaults px "SHMEIO" -> "Σημείο"
        
        $form_meta = $record->get_form_meta(array('page_url'));

        $raw_req_url = $form_meta['page_url']['value'];

        if (!filter_var($raw_req_url, FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED)) {
            error_log(static::class . ' - invalid form url');
            //send error message to ajax handler
            return;
        }

        $req_url = parse_url($raw_req_url);

        parse_str($req_url['query'], $url_query_params);
        $filtered_url_query_params = array();

        //{"page_id":"692","player_id":"2","match_id":"850","homeTeam_id":"133","awayTeam_id":"138"}

        $valid_keys = array('page_id', 'player_id', 'match_id', 'homeTeam_id', 'awayTeam_id', 'match_date');

        foreach ($url_query_params as $param_key => $param_value) {

            if (!in_array($param_key, $valid_keys, true)) {
                continue;
            }

            if (!filter_var($param_value, FILTER_VALIDATE_INT)) {
                continue;
            }

            $filtered_url_query_params[$param_key] = $param_value;
        }

        //if $req_url === false log error, stop action, return error message to fron end

        //todo: check if player can make predictions
        //todo: check if is for active week
        //todo: check for valid data
        //save or update data
        //$post_date = gmdate('Y-m-d H:i:s' ,$filtered_url_query_params['match_date']);

        $post_date = new \DateTime();
        $post_date->setTimezone(new \DateTimeZone('Europe/Athens'));
        $post_date->setTimestamp($filtered_url_query_params['match_date']);

        //$match_date = new \DateTime($match->post_date, new \DateTimeZone('Europe/Athens'));

        //file_put_contents(__DIR__ . '/date.txt', json_encode($filtered_url_query_params['match_date']) . "\n",FILE_APPEND);
        //file_put_contents(__DIR__ . '/date.txt', json_encode($post_date->format('Y-m-d H:i:s')) . "\n",FILE_APPEND);
        //$post_date = get_date_from_gmt( $post_date_gmt );

        $form_data['homeTeam_id'] = $filtered_url_query_params['homeTeam_id'];
        $form_data['awayTeam_id'] = $filtered_url_query_params['awayTeam_id'];

        $player_prediction_post = array(
            'post_author' => $filtered_url_query_params['player_id'],
            'post_date' => $post_date->format('Y-m-d H:i:s'),
            'post_content' => serialize($form_data),
            'post_title' => $filtered_url_query_params['match_id'] . '-' . $filtered_url_query_params['player_id'],
            'post_type' => 'scm-prediction',
        );

        $existing_player_prediction = get_page_by_title($player_prediction_post['post_title'], OBJECT, 'scm-prediction');

        if ($existing_player_prediction) {
            $player_prediction_post['ID'] = $existing_player_prediction->ID;
        }

        //check if player can play for Double Points
        if (is_array($existing_player_prediction)) {
            error_log(static::class . ' - too many posts with type: "scm-prediction", should be only one');
            throw new Exception(static::class . ' many existing_player_prediction');
        }

        if (is_null($existing_player_prediction)) {
            $is_new_prediction = true;
        } else {
            $is_new_prediction = false;
        }

        $double_points = $form_data['Double Points'];

        //if player has selected "double points"
        if ($double_points) {

            //if this is new prediction
            if ($is_new_prediction) {

                $player_id = $filtered_url_query_params['player_id'];
                $player = new Player(get_user_by('id', $player_id));

                //if player can't make predictions then
                if (!$player->can_play_double()) {

                    $msg = 'Η επιλογή δηπλασιασμού επιτρέπεται μέχρι δύο φορές.';
                    $ajax_handler->add_error_message($msg);
                    $ajax_handler->is_success = false;
                    return;
                }
            }

            //if this is old prediction but with no double
            if (!$is_new_prediction && unserialize($existing_player_prediction->post_content)['Double Points'] == '') {

                $player_id = $filtered_url_query_params['player_id'];
                $player = new Player(get_user_by('id', $player_id));

                //if player can't make predictions then
                if (!$player->can_play_double()) {

                    $msg = 'Η επιλογή δηπλασιασμού επιτρέπεται μέχρι δύο φορές.';
                    $ajax_handler->add_error_message($msg);
                    $ajax_handler->is_success = false;
                    return;
                }
            }
        }

        $current_dateTime = new \DateTime();
        $current_dateTime->setTimezone(new \DateTimeZone('Europe/Athens'));

        if ($current_dateTime > $post_date) {
            $msg = 'Δεν επιτρέπεται η αλλάγη της πρόβλεψης μετά την έναρξη του αγώνα';
            $ajax_handler->add_error_message($msg);
            $ajax_handler->is_success = false;
            return;
        }

        // save user prediction
        $player_prediction = wp_insert_post($player_prediction_post);
        $ajax_handler->is_success = true;

    }

   

    public static function set_fixture_status_to_future2(  int $post_id,\WP_Post $post ,bool $update, $post_before ){

        $post_type = "scm-fixture"; 

        if($post->post_type !== $post_type){
            return;
        }

        if( $update ){
            return;
        }

        $post_date = new \DateTime($post->post_date, new \DateTimeZone('Europe/Athens'));
        $current_date = new \DateTime('',new \DateTimeZone('Europe/Athens'));

        $fixture_start_date_acf = get_field('week-start-date',$post_id);
        $fixture_start_date_meta = get_post_meta($post_id, 'week-start-date');
        $fixture_start_date = new \DateTime($fixture_start_date_acf, new \DateTimeZone('Europe/Athens'));


        if(false && SCM_DEBUG){
            file_put_contents(SCM_DEBUG_PATH . '/test_fixture_status.json', json_encode($fixture_start_date_acf) . "\n",FILE_APPEND);
            file_put_contents(SCM_DEBUG_PATH . '/test_fixture_status.json', json_encode($post) . "\n",FILE_APPEND);
            file_put_contents(SCM_DEBUG_PATH . '/test_fixture_status.json','Update: ' .  json_encode($update). "\n",FILE_APPEND);

            file_put_contents(SCM_DEBUG_PATH . '/test_fixture_status.json', json_encode('post date: ' . $post_date->format('Y-m-d H:i:s')). "\n",FILE_APPEND);
            file_put_contents(SCM_DEBUG_PATH . '/test_fixture_status.json', json_encode('current date: ' . $current_date->format('Y-m-d H:i:s')). "\n",FILE_APPEND);
            file_put_contents(SCM_DEBUG_PATH . '/test_fixture_status.json', json_encode($fixture_start_date > $current_date). "\n",FILE_APPEND);

            file_put_contents(SCM_DEBUG_PATH . '/test_fixture_status.json', json_encode($_POST). "\n",FILE_APPEND);
        }

        return;

    }

    public static function check_fixture_enddate(){
        // add custom cron job 
        // create custom hook
        // run custom hook
    }


}