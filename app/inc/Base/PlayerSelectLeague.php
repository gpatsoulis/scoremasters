<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Base;

class PlayerSelectLeague
{

    public static function init()
    {
        add_action('elementor/query/privatescmleagues', array(static::class, 'filter_private_scm_league'));
        add_action('elementor/query/publicscmleagues', array(static::class, 'filter_public_scm_league'));
    }

   
    public static function filter_public_scm_league()
    {
        // Get current meta Query
        $meta_query = $query->get('meta_query');

        // If there is no meta query when this filter runs, it should be initialized as an empty array.
        if (!$meta_query) {
            $meta_query = [];
        }

        // Append our meta query
        $meta_query[] = [
            'key' => 'scm-league-status',
            'value' => 'public',
        ];

        $query->set('meta_query', $meta_query);
    }

    public static function filter_private_scm_league()
    {
        // Get current meta Query
        $meta_query = $query->get('meta_query');

        // If there is no meta query when this filter runs, it should be initialized as an empty array.
        if (!$meta_query) {
            $meta_query = [];
        }

        // Append our meta query
        $meta_query[] = [
            'key' => 'scm-league-status',
            'value' => 'private',
        ];

        $query->set('meta_query', $meta_query);
    }

}
