<?php

$args = array(
    'post_type' => 'scm-pro-player',
    'post_status' => 'publish',
    'posts_per_page' => -1
);

$players = get_posts($args);

$player_team_array = [];
foreach( $players as $player ) {
    $player_team_array[] = get_team($player);
}


usort($player_team_array,'short_teams');

$list = '';
foreach($player_team_array as $data){

$list .= '"' . $data['player'] . '","' . $data['team'] . '"' . "\n";

}

file_put_contents(__DIR__ . '/players-list.csv',$list);

function short_teams($arr1,$arr2){

    return $arr1['team'] > $arr2['team'];
}

function get_team( $player ){
    $team_array = get_field('scm-player-team',$player->ID);
    $team = urldecode($team_array[0]->post_title);

    return ['player' => $player->post_title,'team' => $team];
}