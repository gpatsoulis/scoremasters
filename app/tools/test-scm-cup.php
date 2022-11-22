<?php

$cup_round = get_post(2399);

// scm-related-competition scm-related-week groups_headsup competition_round_number

$competition = get_field('scm-related-competition',2399);
$fixture = get_field('scm-related-week',2399);
$group  = get_field('groups_headsup',2399);
$round_no = get_field('competition_round_number',2399);

var_dump($competition);
var_dump($fixture);

$groups_array = array();
foreach($group as $pairs){
    //var_dump($pairs['group__headsup']);

    $groups_array[] = array(
         'home' => $pairs['group__headsup'][0]['scm-group-player']->ID,
         'away' => $pairs['group__headsup'][1]['scm-group-player']->ID,
    );
}
var_dump($groups_array);
exit;