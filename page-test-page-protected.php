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

$week_2_id = 3935;
scm_match_trigger_players_weekly_point_calculation(3935);

echo '</pre>';

exit;