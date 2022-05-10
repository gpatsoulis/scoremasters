<?php


//find curretn fixture
//find all finished matches
//get all predictions for those matches
//get palyer names
// get predictions 
// get calculated points

//export as csv 
/**
 * fixture
 * player-name;player-id; prediction-id;predictions; points; match-name;match-id
 */


$args = array(
    'post_type' => 'scm-fixture',
    'post_status' => 'publish',
    'posts_per_page' => 1,
);

//get active week
$posts = get_posts($args);

if(empty($posts)){
    error_log('exporter---- no active fixture');
}

$current_fixture = $posts[0];

$current_date = new \DateTime();
$current_date->setTimezone(new \DateTimeZone('Europe/Athens'));

$fixture_start_date_string = get_field('week-start-date',$current_fixture->ID);
$fixture_start_date = new \DateTime($fixture_start_date_string, new \DateTimeZone('Europe/Athens'));

$fixture_end_date_string = get_field('week-end-date',$current_fixture->ID);
$fixture_end_date = new \DateTime($fixture_end_date_string, new \DateTimeZone('Europe/Athens'));



$args = array(
    'post_type' => 'scm-match',
    'post_status' => 'publish',
    'date_query' => array(
        'after' => array(
            'year' => (int) $fixture_start_date->format('Y'),
            'month' => (int) $fixture_start_date->format('n'),
            'day' => (int) $fixture_start_date->format('j'),
        ),
        'before' => array(
            'year' => (int) $current_date->format('Y'),
            'month' => (int) $current_date->format('n'),
            'day' => (int) $current_date->format('j'),
        ),
    )
);

$matches = get_posts($args);


/*$args = array(
    'post_type' => 'scm-prediction',
    'post_status' => 'any',
    'date_query' => array(
        'after' => array(
            'year' => (int) $fixture_start_date->format('Y'),
            'month' => (int) $fixture_start_date->format('n'),
            'day' => (int) $fixture_start_date->format('j'),
        ),
        'before' => array(
            'year' => (int) $current_date->format('Y'),
            'month' => (int) $current_date->format('n'),
            'day' => (int) $current_date->format('j'),
        ),
    )
);

$predictions = get_posts($args);

*/

$all_predictions = array();

foreach($matches as $match){

    $match_date = new \DateTime($match->post_date, new \DateTimeZone('Europe/Athens'));

    $args = array(
        'post_type' => 'scm-prediction',
        'post_status' => 'any',
        'posts_per_page'=> -1,
        'date_query' => array(
            'year' => (int) $match_date->format('Y'),
            'month' => (int) $match_date->format('n'),
            'day' => (int) $match_date->format('j'),
            'hour' => (int) $match_date->format('G'),
            'minute' => (int) $match_date->format('i'),
            'second' => (int) $match_date->format('s'),
        ),
    );

    $predictions = get_posts($args);

    
    
    foreach ($predictions as $prediction){
        $all_predictions[] = $prediction;
    }

}


$export_string = '';

$se = '"';
$export_string .= $current_fixture->post_title  . ',,,,,,'."\n";
$export_string .= 'player-name,SHMEIO,Under/Over,Score,Scorer,Double points,match name'."\n";

foreach ($all_predictions as $prediction){
    $player_name = (get_user_by('id', $prediction->post_author))->display_name;
    $predictions = unserialize($prediction->post_content);

    $shmeio = $predictions['SHMEIO'];
    $uo = $predictions['Under / Over'];
    $score = $predictions['score'];
   
    $scorer = '';
    $tmp_scorer = get_post(intval($predictions['Scorer']));

    if($tmp_scorer){
        $scorer = $tmp_scorer->post_title;
    }


    $double = $predictions['Double Points'];

    $match_name = get_post(intval(explode('-',$prediction->post_title)[0]))->post_title;

    //$export_string .= $match_name . ',,,,,,'."\n";
    $export_string .= '"'.$player_name . '","' . $shmeio . '","' . $uo . '","' . $score . '","' . $scorer . '","' . $double . '","'.$match_name.'"'."\n";
}

//file_put_contents(__DIR__ . '/export_'.$current_date->format('Y-m-d H:i').'.csv',$export_string);
var_dump($export_string);

exit;