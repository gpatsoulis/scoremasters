<?php
/**
 * @package scoremasters
 * 
 */

namespace Scoremasters\Inc\Interfaces;

interface  ShortcodeInterface {

    public function register_shortcode();

    public function output():string;

    public function get_template();

}