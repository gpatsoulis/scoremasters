<?php
require_once dirname(__FILE__) . '/app/tools/calculate_matchups_for_fixture.php';

echo 'hello';
//local_id:        2574
//production_id:   3709
$week1 = get_post(3709);

var_dump($week1 );

add_weekly_championship_players_matchups($week1);

exit;