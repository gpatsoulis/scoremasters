<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Classes;

class ScoremestersCupCompetitionRound

{

    public \WP_Post $post_data;

    public int $competition_id;

    public array $player_pairs;

    public array $rounds;

    public function __construct(\WP_Post $post)
    {
        $this->post_data = $post;
        $this->player_pairs = static::set_player_pairs($post->ID);
        $this->rounds = get_field('scm-related-week',$post->ID);
        $this->competition_id = get_field('scm-related-competition', $post->ID);

    }

    public static function set_player_pairs(int $id): array
    {

        $pairs = get_field('groups_headsup', $id);

        $player_pairs = array();

        foreach ($pairs as $pair) {
            $player_1_id = $pair['group__headsup'][0]['scm-group-player'];
            $player_2_id = $pair['group__headsup'][1]['scm-group-player'];
            $player_pairs[] = array($player_1_id, $player_2_id);
        }

        return $player_pairs;
    }
}
