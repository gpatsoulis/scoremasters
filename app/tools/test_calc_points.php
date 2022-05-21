<?php

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Base\DataQuery;

$points = get_option('points_table');

//var_dump($points);
var_dump((new DataQuery())->get_fixture(1758));

var_dump(ScmData::get_current_fixture());



exit;