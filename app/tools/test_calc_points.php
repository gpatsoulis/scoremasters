<?php

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Base\DataQuery;
use Scoremasters\Inc\Classes\WeeklyChampionshipCompetition;
//$points = get_option('points_table');

//var_dump($points);
//var_dump((new DataQuery())->get_fixture(1758));
//var_dump(ScmData::get_current_fixture());

//$current_player_list = get_field('scm-user-players-list',843 );
//$current_player_list = get_post_meta(843, 'scm-user-players-list');


//var_dump($current_player_list );
//add_action('init', function(){var_dump(taxonomy_exists('scm_competition_type'));});
//add_action('init', function(){var_dump(get_terms('scm_competition_type'));});


//$players = ScmData::get_league_participants($league);

add_action('init', function(){
    
    $league = get_post(843);
    $competition = get_post(121);
    $test = new WeeklyChampionshipCompetition($competition,$league);
    var_dump($test->get_players_shorted_by_score());

});
//$test = new WeeklyChampionshipCompetition($competition,$league);
//var_dump($test);



//exit;