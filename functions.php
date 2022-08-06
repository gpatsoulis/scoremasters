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
            get_template_directory_uri() . '/app/js/activate-prediction-popup-v2.1.js',
            array('jquery'), '1.0.1', true);
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
    $some_url = 'http://scoremasters.gr/?page_id=594';
    wp_redirect($some_url);
    exit;
}

add_action('wp_login', 'redirect_on_login', 1);

/**Redirect after logout and override wp_nonce**/

function logout_without_confirm($action, $result)
{
    /**
     * Allow logout without confirmation
     */
    if ($action == "log-out" && !isset($_GET['_wpnonce'])) {
        $redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : 'http://scoremasters.gr/';
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
    //if(!isset($request['pre_title'])) return;

    //return $request['pre_title'];

    $author = filter_var($request['pre_author'], FILTER_VALIDATE_INT);

    if ($author === false) {
        return new WP_Error('error data', 'Invalid prediction title', array('status' => 404));
    }

    //pre_title=1350-2&pre_author=2

    $args = array(

        'post_type' => 'scm-prediction',
        'post_status' => 'any',
        //'author' => $author,
        's' => $request['pre_title'],
    );

    $posts = get_posts($args);

    $user_id = explode('-', $request['pre_title'])[1];

    if (empty($posts)) {
        return new WP_Error('no_author', 'Invalid prediction title', array('status' => 404));
    }

    $current_user_prediction = '';

    foreach ($posts as $post) {
        if ($post->post_author == $user_id) {
            $current_user_prediction = $post;
        }
    }

    if ($current_user_prediction == '') {
        return new WP_Error('no_author', 'Invalid prediction title', array('status' => 404));
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

function debug_redirect_mail($args)
{
    $args['to'] = array('patsoulis.george@gmail.com', 'kyrgag1@gmail.com', 'tmountakis@gmail.com');

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

    //require_once dirname(__FILE__) . '/app/tools/calculate_score.php';
    //require_once dirname(__FILE__) . '/app/tools/test_calculate_matchups.php'; 
    //todo: calc weekly points for all matches
    //require_once dirname(__FILE__) . '/app/tools/test_calc_weekly_points.php';
    //require_once dirname(__FILE__) . '/app/tools/test_cup.php';
    //require_once dirname(__FILE__) . '/app/tools/players-list.php';
    //require_once dirname(__FILE__) . '/app/tools/test_matchups_algorithm.php';



}
add_action('init', 'start_scoremasters');

//exporter
//require_once dirname(__FILE__) . '/app/tools/export_predictions.php';
//
//require_once dirname(__FILE__) . '/app/tools/export_predictions_by_match.php';
//require_once dirname(__FILE__) . '/app/tools/calculate_points_all_players.php';