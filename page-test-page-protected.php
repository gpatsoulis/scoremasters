<?php
require_once dirname(__FILE__) . '/app/tools/calculate_matchups_for_fixture.php';
require_once dirname(__FILE__) . '/app/tools/calculate_weekly_score_for_fixture.php';

use Scoremasters\Inc\Base\ScmData;

echo 'hello';
//local_id:        2574
//production_id:   3709
//$week1 = get_post(3709);

echo '<pre>';
//var_dump($week1 );

//add_weekly_championship_players_matchups($week1);

//$week_2_id = 3935;
//scm_match_trigger_players_weekly_point_calculation(3935);

echo '</pre>';
/*
$cup_phase = get_post(2399);

$fixtures = get_field('scm-related-week',2399);

$pairs = get_field('groups_headsup',2399);

$headsup = array();
foreach($pairs as $pair){

$player_1_id = $pair['group__headsup'][0]['scm-group-player'];
$player_2_id = $pair['group__headsup'][1]['scm-group-player'];
$headsup[] = array( $player_1_id, $player_2_id);

}

$competition = get_field('scm-related-competition', 2399);

echo '<pre>';
var_dump($fixtures);

var_dump($pairs);

var_dump($headsup);

var_dump($competition);
echo '</pre>';
 */

//$meta = get_post_meta( 3708, 'scm-season-competition', true  );

//var_dump( $meta[0] );

$competition = ScmData::get_current_scm_competition_of_type('score-masters-cup');

$prev_fixture = ScmData::get_previous_fixture();
//var_dump($prev_fixture);

$args = array(
    'post_status' => 'any',
    'post_type' => 'scm-competition-roun',
    'post_per_page' => 2,
    'meta_query' => array(
        array(
            'key' => 'scm-related-week',
            'value' => serialize((string) $prev_fixture->ID),
            'compare' => 'LIKE',
        ),
    ),
);

$phase = get_posts($args)[0];

var_dump($phase);

//var_dump(get_field('scm-related-competition',$phase->ID));
//var_dump(serialize(get_post_meta(5378, 'scm-related-week',true)));
//var_dump(get_post_meta(5378,'scm-related-competition'));
//var_dump(get_field('scm-related-week', 5378));
//var_dump(get_field('scm-related-competition', 5378));

//var_dump(get_field('groups__headsup',5378));

$acf_matchups = get_field('groups__headsup', 5378);

$acf_fixtures = get_field('scm-related-week', 5378);
var_dump($acf_fixtures);
//scm-related-competition
$competition = get_field('scm-related-competition', 5378);
var_dump($competition);



$matchups = [];
foreach ($acf_matchups as $group) {
    $players = [];
    foreach ($group['group__headsup'] as $acf_player) {
        $players[] = $acf_player['scm-group-player'];
    }

    $matchups[] = $players;
}

var_dump($matchups);

$score = get_user_meta(30,'score_points_seasonID_3701');
//var_dump( $score[0]['fixture_id_3709'] );
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
exit;
