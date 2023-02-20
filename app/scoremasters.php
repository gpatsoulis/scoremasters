<?php

defined('ABSPATH') or die;

use Scoremasters\Inc\Base\FixtureSetup;
use Scoremasters\Inc\Base\MatchSetup;
use Scoremasters\Inc\Base\PlayerSetup;
use Scoremasters\Inc\Base\ProPlayerSetup;
use Scoremasters\Inc\Base\CompetitionSetup;
use Scoremasters\Inc\Base\ShortcodeController;
use Scoremasters\Inc\Base\PlayerSelectLeague;
use Scoremasters\Inc\Base\CompetitionRoundSetup;
use Scoremasters\Inc\Base\ThemeSetup;
use Scoremasters\Inc\Base\WeeklyChampionshipSetup;
use Scoremasters\Inc\Base\PredictionSetup;

define('EXPORT_PATH', __DIR__ . '/export_predictions');

define('SCM_DEBUG', true);
define('SCM_DEBUG_PATH', __DIR__ . '/debug');

define('SCM_STAGING', false);


FixtureSetup::init();
PlayerSetup::init();
CompetitionSetup::init(); 
MatchSetup::init();
ProPlayerSetup::init();
PlayerSelectLeague::init();

//-- debug --
PredictionSetup::init();
WeeklyChampionshipSetup::init();
CompetitionRoundSetup::init();

ThemeSetup::init();

ShortcodeController::register_shortcodes();