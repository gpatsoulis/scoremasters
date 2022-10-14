<?php
require_once dirname(__FILE__) . '/app/tools/calculate_matchups_for_fixture.php';
require_once dirname(__FILE__) . '/app/tools/calculate_weekly_score_for_fixture.php';

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

$meta = get_post_meta( 3708, 'scm-season-competition', true  );

var_dump( $meta[0] );
exit;