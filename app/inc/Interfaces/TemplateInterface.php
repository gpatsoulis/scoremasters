<?php
/**
 * @package scoremasters
 * 
 */

namespace Scoremasters\Inc\Interfaces;

interface  TemplateInterface {

    public function get_html(array $data ):string;
    public function get_css( array $data = array() ):string;

}