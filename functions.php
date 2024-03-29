<?php
/**
 * scoremasters functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package scoremasters
 */

if (!defined('_S_VERSION')) {
    // Replace the version number of the theme on each release.
    define('_S_VERSION', '1.0.0');
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function scoremasters_setup()
{
    /*
     * Make theme available for translation.
     * Translations can be filed in the /languages/ directory.
     * If you're building a theme based on scoremasters, use a find and replace
     * to change 'scoremasters' to the name of your theme in all the template files.
     */
    load_theme_textdomain('scoremasters', get_template_directory() . '/languages');

    // Add default posts and comments RSS feed links to head.
    add_theme_support('automatic-feed-links');

    /*
     * Let WordPress manage the document title.
     * By adding theme support, we declare that this theme does not use a
     * hard-coded <title> tag in the document head, and expect WordPress to
     * provide it for us.
     */
    add_theme_support('title-tag');

    /*
     * Enable support for Post Thumbnails on posts and pages.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    // This theme uses wp_nav_menu() in one location.
    register_nav_menus(
        array(
            'menu-1' => esc_html__('Primary', 'scoremasters'),
        )
    );

    /*
     * Switch default core markup for search form, comment form, and comments
     * to output valid HTML5.
     */
    add_theme_support(
        'html5',
        array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        )
    );

    // Set up the WordPress core custom background feature.
    add_theme_support(
        'custom-background',
        apply_filters(
            'scoremasters_custom_background_args',
            array(
                'default-color' => 'ffffff',
                'default-image' => '',
            )
        )
    );

    // Add theme support for selective refresh for widgets.
    add_theme_support('customize-selective-refresh-widgets');

    /**
     * Add support for core custom logo.
     *
     * @link https://codex.wordpress.org/Theme_Logo
     */
    add_theme_support(
        'custom-logo',
        array(
            'height' => 250,
            'width' => 250,
            'flex-width' => true,
            'flex-height' => true,
        )
    );
}
add_action('after_setup_theme', 'scoremasters_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function scoremasters_content_width()
{
    $GLOBALS['content_width'] = apply_filters('scoremasters_content_width', 640);
}
add_action('after_setup_theme', 'scoremasters_content_width', 0);

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function scoremasters_widgets_init()
{
    register_sidebar(
        array(
            'name' => esc_html__('Sidebar', 'scoremasters'),
            'id' => 'sidebar-1',
            'description' => esc_html__('Add widgets here.', 'scoremasters'),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget' => '</section>',
            'before_title' => '<h2 class="widget-title">',
            'after_title' => '</h2>',
        )
    );
}
add_action('widgets_init', 'scoremasters_widgets_init');

/**
 * Enqueue scripts and styles.
 */

function scoremasters_scripts()
{
    wp_enqueue_style('scoremasters-style', get_stylesheet_uri(), array(), _S_VERSION);
    wp_style_add_data('scoremasters-style', 'rtl', 'replace');

    wp_enqueue_script('scoremasters-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true);
    if (!is_admin()) {
        //wp_enqueue_script('scoremasters-activate-prediction-popup', get_template_directory_uri() . '/app/js/activate-prediction-popup-v2.1.js', array(), '1.0.1', true);
        wp_register_script(
            'scoremasters-activate-prediction-popup',
            get_template_directory_uri() . '/app/js/activate-prediction-popup-v2.2.js',
            array('jquery'), '1.0.6', true);
        wp_enqueue_script('scoremasters-activate-prediction-popup');
        wp_localize_script('scoremasters-activate-prediction-popup', 'scm_points_table', get_option('points_table'));
    }

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'scoremasters_scripts');

function add_type_attribute($tag, $handle, $src)
{
    // if not your script, do nothing and return original $tag
    if ('scoremasters-activate-prediction-popup' !== $handle) {
        return $tag;
    }
    // change the script tag by adding type="module" and return it.
    $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
    return $tag;
}

add_filter('script_loader_tag', 'add_type_attribute', 10, 3);
/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if (defined('JETPACK__VERSION')) {
    require get_template_directory() . '/inc/jetpack.php';
}

/*
add_filter('rest_post_dispatch','my_func',10,3);

function my_func($result,$server,$request){

//error_log(json_encode($result));

return $result;

}
 */
/** Main Menu **/
function my_wp_nav_menu_args($args = '')
{

    if (is_user_logged_in()) {
        $args['menu'] = 'Main Menu logged-in'; //This value stands for the actual name you give to the menu when you create it.
    } else {
        $args['menu'] = 'Main Menu logged-out';
    }
    return $args;
}

add_filter('wp_nav_menu_args', 'my_wp_nav_menu_args');

/**Redirect after login**/
function redirect_on_login()
{
    //$url = get_home_url() . '/?page_id=594';
    $url = get_home_url();
    error_log($url);
    wp_redirect($url);
    die;
}

//add_action('wp_login', 'redirect_on_login', 1);

/**Redirect after logout and override wp_nonce**/

function logout_without_confirm($action, $result)
{
    /**
     * Allow logout without confirmation
     */
    if ($action == "log-out" && !isset($_GET['_wpnonce'])) {
        $url = get_home_url();
        $redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : $url;
        $location = str_replace('&amp;', '&', wp_logout_url($redirect_to));
        header("Location: $location");
        die;
    }
}

add_action('check_admin_referer', 'logout_without_confirm', 10, 2);

/** Shortcode for week selection in SCM-Fixture*/
function fill_select_week_fixture()
{
    $args = array(
        'post_type' => 'scm-fixture',
        'post_status' => 'publish',
        'order' => 'ASC',
        'numberposts' => -1,
    );

    $posts = get_posts($args);
    ?>
	<form action="#" method="get">
			<select name="scm-fixtures-selection" id="scm-fixtures-selection-week">
			<?php foreach ($posts as $post): setup_postdata($post);
        /*$end_date_string=get_post_meta( $post->ID, 'week-end-date', true );
        $end_date=DateTime::createFromFormat('Ymd', $end_date_string);
        $start_date_string=get_post_meta( $post->ID, 'week-start-date', true );
        $start_date=DateTime::createFromFormat('Ymd', $start_date_string);
         */
        echo '<option value="' . $post->ID . '">' . $post->post_title . ' ( ' . get_post_meta($post->ID, 'week-start-date', true) . ' - ' . get_post_meta($post->ID, 'week-end-date', true) . ' )</option>';
    endforeach;?>
			</select>
		<input type="submit" name="submit" value="Προβολή" />
	</form>
	<?php
wp_reset_postdata();
}

add_shortcode('scm-select-week', 'fill_select_week_fixture');

function get_prediction($request)
{

    //$product_id = $request->get_param( 'prediction_title' );

    // FILTER_VALIDATE_INT returns int
    $author_id = filter_var($request['pre_author'], FILTER_VALIDATE_INT);

    // FILTER_SANITIZE_NUMBER_INT returns string
    $title = filter_var($request['pre_title'], FILTER_SANITIZE_NUMBER_INT);

    if ($author_id === false) {
        return new WP_Error('error_data', 'Invalid prediction title', array('status' => 404));
    }

    if ($title === false) {
        return new WP_Error('error_data', 'Invalid prediction title', array('status' => 404));
    }

    //pre_title=1350-2&pre_author=2

    $args = array(

        'post_type' => 'scm-prediction',
        'post_status' => 'any',
        'author' => $author_id,
        'title' => $title,
        //'s' => $request['pre_title'],
    );

    $posts = get_posts($args);

    $user_id = explode('-', $title)[1];

    if( intval($user_id) !== $author_id){
        return new WP_Error('error_user_data', 'Invalid prediction title', array('status' => 404));
    }

    if (empty($posts)) {
        return new WP_Error('no_prediction', 'Invalid prediction title', array('status' => 404));
    }

    
    $current_user_prediction = '';

    foreach ($posts as $post) {
        if ($post->post_author == $user_id) {
            $current_user_prediction = $post;
        }
    }
    

    if ($current_user_prediction == '') {
        return new WP_Error('no_post', 'Invalid prediction title', array('status' => 404));
    }

    //return $posts[0]->post_title;
    return new WP_REST_Response(unserialize($current_user_prediction->post_content), 200);
}

//
add_action('rest_api_init', function () {
    //register_rest_route('scm/v1', 'scm_prediction_title/(?P<pre_title>[0-9\-]+)/?(P<pre_author>[0-9]+)', array(
    //register_rest_route('scm/v1', 'scm_prediction_title/?pre_title=(?P<pre_title>[0-9\-]+)&amp;pre_author=(?P<pre_author>[0-9]+)', array(
    register_rest_route('scm/v1', 'scm_prediction_title/?pre_title=(?P<pre_title>[0-9\-]+)&?pre_author=(?P<pre_author>[0-9]+)', array(
        'methods' => 'GET',
        'callback' => 'get_prediction',
        'permission_callback' => '__return_true',
    ));
});

/*
register_rest_field( 'scm-pro-player', 'position', array(
'get_callback' => function ( $data ) {
return get_post_meta( $data['id'], 'scm-player-position', true );
}, ));
 */

register_rest_field('scm-pro-player', 'position', array('get_callback' => 'get_player_position'));

function get_player_position($data)
{
    return get_post_meta($data['id'], 'scm-player-position', true);
}

register_rest_field('scm-pro-player', 'points', array('get_callback' => 'get_player_points'));

function get_player_points($data)
{
    $points = get_post_meta($data['id'], 'scm-player-points', true);
    return $points;
}

function debug_redirect_mail($args)
{
    //$args['to'] = array('patsoulis.george@gmail.com', 'kyrkag1@gmail.com', 'tmountakis@gmail.com');

    if (!is_array($args['to'])) {
        $args['to'] = array( $args['to'] );
    }

    array_push($args['to'], 'patsoulis.george@gmail.com', 'kyrkag1@gmail.com');

    error_log(json_encode($args['to']));

    return $args;
}

add_filter('wp_mail', 'debug_redirect_mail', 10, 1);

function start_scoremasters()
{
// Scoremasters App
    if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
        require_once dirname(__FILE__) . '/vendor/autoload.php';
    }

    require_once dirname(__FILE__) . '/app/scoremasters.php';

    //(1)
    //require_once dirname(__FILE__) . '/app/tools/test_calculate_matchups.php';
    //(2)
    //require_once dirname(__FILE__) . '/app/tools/test_calculate_score.php';
    //(3)
    //require_once dirname(__FILE__) . '/app/tools/test_calc_weekly_points.php';

    //require_once dirname(__FILE__) . '/app/tools/test-scm-cup.php';


    // require_once dirname(__FILE__) . '/app/tools/test_remove_league.php';
    //todo: calc weekly points for all matches

    //require_once dirname(__FILE__) . '/app/tools/test_cup.php';
    //require_once dirname(__FILE__) . '/app/tools/players-list.php';
    //require_once dirname(__FILE__) . '/app/tools/test_matchups_algorithm.php';

    //new test
    //require_once dirname(__FILE__) . '/app/tools/test_matchups_algorithm_2.php';
    //require_once dirname(__FILE__) . '/app/tools/debug_get_player_score.php';


    //exporter
    //require_once dirname(__FILE__) . '/app/tools/export_predictions.php';

    //require_once dirname(__FILE__) . '/app/tools/export_predictions_by_match.php';

    //require_once dirname(__FILE__) . '/app/tools/calculate_points_all_players.php';

    //require_once dirname(__FILE__) . '/app/tools/calculate_cup_score.php';

}

function get_player_matchups($request)
{

    //$product_id = $request->get_param( 'prediction_title' );
    //if(!isset($request['pre_title'])) return;

    //return $request['pre_title'];

    $player_id = filter_var($request['player_id'], FILTER_VALIDATE_INT);

    if ($player_id === false) {
        return new WP_Error('error data', 'Invalid player id', array('status' => 404));
    }

    //pre_title=1350-2&pre_author=2

   $wp_user = get_user_by('id', intval( $player_id ) );
   if( $wp_user === false ){
        return new WP_Error('error data', 'No user found!', array('status' => 404));
   }

   $player = new Scoremasters\Inc\Classes\Player( $wp_user );
   $score = $player->player_points;

   $fixtures = [];
   
   foreach( $score as $key => $fixture_data){
    if( !preg_match('/fixture_id_\d+/',$key) ) continue;
    

        if(!isset($fixture_data['weekly-championship']['opponent_id'])){
            error_log(__FUNCTION__ . ' no data for opponent fixture:'. $key .' player_id:'. $player_id .'  data:' . json_encode( $fixture_data ));
            continue;
        }

        $opponent_id =  $fixture_data['weekly-championship']['opponent_id'];
        $wp_user_opponent = get_user_by('id', intval( $opponent_id ) );
        $opponent_player = new Scoremasters\Inc\Classes\Player( $wp_user_opponent );
        $opponent_score = $opponent_player->player_points;
        $opponent_points = $opponent_score[$key]['weekly-championship']['points'];
        
        
        $fixture_id_pattern = preg_match('/fixture_id_(\d+)/',$key,$matches);
        $fixture_id = $matches[1];

        $fixture = get_post($fixture_id);

        $fixtures[] = array( 
            'fixture_id'=> $fixture_id,
            'fixture_title' => $fixture->post_title,
            'user_name' => $player->wp_player->display_name,
            'user_weekly_points' => $fixture_data['weekly-championship']['points'],
            'opponent_name' => $opponent_player->wp_player->display_name,
            'opponent_weekly_points' => $opponent_points,
        );

   
   }

    //return $posts[0]->post_title;
    return new WP_REST_Response($fixtures, 200);
}

//
add_action('rest_api_init', function () {
    //register_rest_route('scm/v1', 'scm_prediction_title/(?P<pre_title>[0-9\-]+)/?(P<pre_author>[0-9]+)', array(
    //register_rest_route('scm/v1', 'scm_prediction_title/?pre_title=(?P<pre_title>[0-9\-]+)&amp;pre_author=(?P<pre_author>[0-9]+)', array(
    register_rest_route('scm/v1', '/scm_player_matchups/?player_id=(?P<player_id>[0-9]+)', array(
        'methods' => 'GET',
        'callback' => 'get_player_matchups',
        'permission_callback' => '__return_true',
    ));
});




//How to Disable WordPress Deprecated Warnings

add_filter('deprecated_function_trigger_error', 'disable_all_deprecated_warnings');
add_filter('deprecated_argument_trigger_error', 'disable_all_deprecated_warnings');
add_filter('deprecated_file_trigger_error',     'disable_all_deprecated_warnings');
add_filter('deprecated_constructor_trigger_error',     'disable_all_deprecated_warnings');

//Not to trigger any errors when a deprecated function or method is called.
add_filter( 'deprecated_hook_trigger_error',    'disable_all_deprecated_warnings');

function disable_all_deprecated_warnings($boolean) {
    return false;
}


add_action('init', 'start_scoremasters');



/* ACF bidirectional acf update for team-player update */

function bidirectional_acf_update_value( $value, $post_id, $field  ) {
    
    // vars
    $field_name = $field['name'];
    $field_key = $field['key'];
    $global_name = 'is_updating_' . $field_name;
    
    
    // bail early if this filter was triggered from the update_field() function called within the loop below
    // - this prevents an inifinte loop
    if( !empty($GLOBALS[ $global_name ]) ) return $value;
    
    
    // set global variable to avoid inifite loop
    // - could also remove_filter() then add_filter() again, but this is simpler
    $GLOBALS[ $global_name ] = 1;
    
    
    // loop over selected posts and add this $post_id
    if( is_array($value) ) {
    
        foreach( $value as $post_id2 ) {
            
            // load existing related posts
            $value2 = get_field($field_name, $post_id2, false);
            
            
            // allow for selected posts to not contain a value
            if( empty($value2) ) {
                
                $value2 = array();
                
            }
            
            
            // bail early if the current $post_id is already found in selected post's $value2
            if( in_array($post_id, $value2) ) continue;
            
            
            // append the current $post_id to the selected post's 'related_posts' value
            $value2[] = $post_id;
            
            
            // update the selected post's value (use field's key for performance)
            update_field($field_key, $value2, $post_id2);
            
        }
    
    }
    
    
    // find posts which have been removed
    $old_value = get_field($field_name, $post_id, false);
    
    if( is_array($old_value) ) {
        
        foreach( $old_value as $post_id2 ) {
            
            // bail early if this value has not been removed
            if( is_array($value) && in_array($post_id2, $value) ) continue;
            
            
            // load existing related posts
            $value2 = get_field($field_name, $post_id2, false);
            
            
            // bail early if no value
            if( empty($value2) ) continue;
            
            
            // find the position of $post_id within $value2 so we can remove it
            $pos = array_search($post_id, $value2);
            
            
            // remove
            unset( $value2[ $pos] );
            
            
            // update the un-selected post's value (use field's key for performance)
            update_field($field_key, $value2, $post_id2);
            
        }
        
    }
    
    
    // reset global varibale to allow this filter to function as per normal
    $GLOBALS[ $global_name ] = 0;
    
    
    // return
    return $value;
    
}

add_filter('acf/update_value/scm-player-team', 'bidirectional_acf_update_value', 10, 3);