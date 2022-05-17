<?php

defined('ABSPATH') or die;

use Scoremasters\Inc\Base\FixtureSetup;
use Scoremasters\Inc\Base\MatchSetup;
use Scoremasters\Inc\Base\PlayerSetup;
use Scoremasters\Inc\Base\ProPlayerSetup;
use Scoremasters\Inc\Base\CompetitionSetup;
use Scoremasters\Inc\Base\ShortcodeController;



FixtureSetup::init();
PlayerSetup::init();
CompetitionSetup::init(); 
MatchSetup::init();
ProPlayerSetup::init();

ShortcodeController::register_shortcodes();
