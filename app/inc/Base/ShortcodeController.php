<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Base;

use \Scoremasters\Inc\Shortcodes\FixturesShortcode;
use \Scoremasters\Inc\Shortcodes\FixturesWeeklyPlayerPointsShortcode;
use \Scoremasters\Inc\Shortcodes\FixturesSelectWeekShortcode;
use \Scoremasters\Inc\Shortcodes\SelectLeagueShortcode;
use \Scoremasters\Inc\Shortcodes\SeasonLeagueShortcode;
use \Scoremasters\Inc\Shortcodes\CategoryChampionshipShortcode;
use \Scoremasters\Inc\Shortcodes\WeeklyChampionshipShortcode;
use \Scoremasters\Inc\Shortcodes\CurrentPlayerMatchupShortcode;
use \Scoremasters\Inc\Shortcodes\ShowPlayerScoreShortcode;
use \Scoremasters\Inc\Shortcodes\PlayerProfileLeagueShortcode;
use \Scoremasters\Inc\Shortcodes\LeagueWeeklyMatchupsShortcode;
use \Scoremasters\Inc\Shortcodes\CupShortcode;
use \Scoremasters\Inc\Shortcodes\GetPlayerPredictionFormShortcode;
use \Scoremasters\Inc\Shortcodes\CurrentFixtureTotalPlayerPointsShortcode;
use \Scoremasters\Inc\Shortcodes\LeaguesCupShortcode;
use \Scoremasters\Inc\Shortcodes\LeaguesCupStandingsShortcode;

class ShortcodeController {

    /**
	 * Store all the shortcode classes inside an array
	 * @return array Full list of classes
	 */
    public static function get_shortcodes(){
        return array(
            \Scoremasters\Inc\Shortcodes\FixturesShortcode::class,
			\Scoremasters\Inc\Shortcodes\FixturesWeeklyPlayerPointsShortcode::class,
			\Scoremasters\Inc\Shortcodes\FixturesSelectWeekShortcode::class,
			\Scoremasters\Inc\Shortcodes\SelectLeagueShortcode::class,
			\Scoremasters\Inc\Shortcodes\SeasonLeagueShortcode::class,
			\Scoremasters\Inc\Shortcodes\CategoryChampionshipShortcode::class,
			\Scoremasters\Inc\Shortcodes\WeeklyChampionshipShortcode::class,
			\Scoremasters\Inc\Shortcodes\CurrentPlayerMatchupShortcode::class,
			\Scoremasters\Inc\Shortcodes\ShowPlayerScoreShortcode::class,
			\Scoremasters\Inc\Shortcodes\PlayerProfileLeagueShortcode::class,
			\Scoremasters\Inc\Shortcodes\LeagueWeeklyMatchupsShortcode::class,
			\Scoremasters\Inc\Shortcodes\CupShortcode::class,
			\Scoremasters\Inc\Shortcodes\GetPlayerPredictionFormShortcode::class,
			\Scoremasters\Inc\Shortcodes\CurrentFixtureTotalPlayerPointsShortcode::class,
			\Scoremasters\Inc\Shortcodes\LeaguesCupShortcode::class,
			\Scoremasters\Inc\Shortcodes\LeaguesCupStandingsShortcode::class,
        );
    }

    /**
	 * Loop through the classes, initialize them,
	 * and call the register() method if it exists
	 * @return
	 */
    public static function register_shortcodes(){

        foreach (self::get_shortcodes() as $class) {
			$service = self::instantiate($class);
			if (method_exists($service, 'register_shortcode')) {
				$service->register_shortcode();
			}
		}
    }


	/**
	 * Initialize the class
	 * @param  class $class    class from the shortcodes array
	 * @return class instance  new instance of the class
	 */
	private static function instantiate($class)
	{
		$service = new $class();

		return $service;
	}
}