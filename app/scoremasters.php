<?php

defined('ABSPATH') or die;

use Scoremasters\Inc\Base\FixtureSetup;
use Scoremasters\Inc\Base\PlayerSetup;
use Scoremasters\Inc\Base\CompetitionSetup;
use Scoremasters\Inc\Base\ShortcodeController;



FixtureSetup::init();
PlayerSetup::init();
CompetitionSetup::init(); 

ShortcodeController::register_shortcodes();
