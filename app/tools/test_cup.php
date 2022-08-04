<?php
use Scoremasters\Inc\Base\ScmData;

$matches = get_field('week-matches',1899)[0]['week-match'];
//var_dump($matches);

usort($matches, 'date_compare');

function date_compare($match1,$match2){
    $datetime1 = new \DateTime($match1->post_date, new \DateTimeZone('Europe/Athens'));
    $datetime2 = new \DateTime($match2->post_date, new \DateTimeZone('Europe/Athens'));
    return $datetime1 < $datetime2;
}

var_dump($matches);

exit;


$competition = (get_field('scm-related-competition', 2399))[0];
$matchups = (get_field('groups_headsup', 2399));

$pairs = [];
$i = 0;
foreach($matchups as $group){
    
    foreach($group['group__headsup'] as $player){
        $pairs[$i][] = $player['scm-group-player']->ID;
    }
    $i += 1; 

}

var_dump($pairs);
var_dump($competition);
var_dump(get_the_terms($competition,'scm_competition_type'));
var_dump($matchups);
exit;
