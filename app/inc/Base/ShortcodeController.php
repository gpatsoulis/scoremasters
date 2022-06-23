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
use \Scoremasters\Inc\Shortcodes\WeekleChampionshipShortcode;

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
			\Scoremasters\Inc\Shortcodes\WeekleChampionshipShortcode::class,
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