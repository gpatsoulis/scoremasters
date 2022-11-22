<?php

echo 'test score';



// get all players 

$args = array(
    'role' => 'scm-user',
);

$players = get_users( $args );
//var_dump($players);

$data = array();
foreach($players as $player){
    $score = get_user_meta( $player->ID,'score_points_seasonID_3701',true);
    if(!$score){
        continue;
    }

    var_dump(($score));

    $first_week_matches = current($score);

    $match_data = array($player->display_name);
    foreach ( $first_week_matches as $key => $match ){
        if(!isset($match['season-league'])){
            continue;
        }

        $matches = array();
        preg_match('/match_id_([0-9]+)/',$key,$matches);

        $match_game = get_post((int) $matches[1]);

        //var_dump($match_game);

        $match_data = array_merge($match_data,[ $match_game->post_title, $match['season-league']['points']]);
    }


    $data[] = [ 'match_data' => $match_data];

    //var_dump($data);
    
}

$csv = '';
foreach ($data as $line ){
    $csv .= implode(',' , $line['match_data']) . "\n";
  
}

//var_dump($csv);
//file_put_contents(__DIR__  . '/results.csv',$csv);




// ger each player score for each match

// save to csv

die;