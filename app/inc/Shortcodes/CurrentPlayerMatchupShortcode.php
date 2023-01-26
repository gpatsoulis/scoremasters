<?php
/**
 * @package scoremasters
 */

namespace Scoremasters\Inc\Shortcodes;

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\Player;
use Scoremasters\Inc\Classes\WeeklyMatchUps;

//[Scoremasters\Inc\Shortcodes\CurrentPlayerMatchupShortcode]
class CurrentPlayerMatchupShortcode
{
    public $template;
    public $name;

    public function __construct()
    {
        $this->name = static::class;

        $this->get_template();
    }

    public function register_shortcode()
    {
        add_shortcode($this->name, array($this, 'output'));
    }

    public function output()
    {

        $player = new Player(wp_get_current_user());
        
        $player_league = $player->get_league();
        $current_fixture = ScmData::get_current_fixture();

       

        $current_weekly_competition = ScmData::get_current_scm_competition_of_type('weekly-championship');

        if($current_weekly_competition->ID < 0){
            error_log( __METHOD__ . ' error output');
            
            $output = $this->template->container_start;
            $data = array();
            $output .= $this->template->get_html($data);
            $output .= $this->template->container_end;
            $output .= $this->template->get_css();
        
            return $output;
        }

        $weekly_matchups = new WeeklyMatchUps($current_weekly_competition->ID);
        //$weekly_matchups->get_all_matchups();

        $pairs = $weekly_matchups->get_matchups()->by_fixture_id($current_fixture->ID)->by_league_id($player_league);

        if(empty( $pairs )){
            $output = $this->template->container_start;
            $data = array();
            $output .= $this->template->get_html($data);
            $output .= $this->template->container_end;
            $output .= $this->template->get_css();
        
            return $output;
        }

        $new_pairs = [];
        for ($i = 0; $i < (count($pairs) / 2); $i++) {

            $z = 2 * $i;
            $new_pairs[] = array($pairs[$z], $pairs[$z + 1]);

        }

        
        //$my_pair = current(array_filter($new_pairs, fn($pair_array) => in_array($player->player_id, $pair_array)));
        //error with arrow function in production server
        $my_pair = current(array_filter($new_pairs, function($pair_array) use ($player) {return in_array($player->player_id, $pair_array);}));

        if( $my_pair === false ){
            error_log( __METHOD__ . ' error no pairs for player with id: '. $player->player_id );

            $output = $this->template->container_start;
            $data = array();
            $output .= $this->template->get_html($data);
            $output .= $this->template->container_end;
            $output .= $this->template->get_css();
        
            return $output;
        }
        
        //$data['home'] = ($my_pair[0] === $player->player_id) ? $player : ( get_user_by( 'id', $my_pair[0] ) )->display_name;
        //$data['away'] = ($my_pair[1]=== $player->player_id) ? $player : ( get_user_by( 'id', $my_pair[1] ) )->display_name;

        $home_user = get_user_by('id', $my_pair[0]);
        if($home_user){
            $home_user_name = $home_user->display_name;
        }

        if(!$home_user){
            error_log(__METHOD__ . ' error user not found id: ' . $my_pair[0] . ' current user:' . $player->wp_player->display_name . ' id:' . $player->wp_player->ID);
            $home_user_name = '';
        }

        $away_user = get_user_by('id', $my_pair[1]);
        //todo : can't find user
        
        if($away_user){
            $away_user_name = $away_user->display_name;
        }
        
        if(!$away_user){
            error_log(__METHOD__ . ' error user not found id: ' . $my_pair[1] . ' current user:' . $player->wp_player->display_name . ' id:' . $player->wp_player->ID);
            $away_user_name = '';
        }

        $data['home'] = $home_user_name;
        //$data['home_score'] = (new Player($home_user))->player_points['fixture_id_'.$current_fixture->ID]['weekly-championship']['points'];
        $data['away'] = $away_user_name;
        //$data['away_score'] = (new Player($away_user))->player_points['fixture_id_'.$current_fixture->ID]['weekly-championship']['points'];
        $data['fixture'] = urldecode($current_fixture->post_name);

        //get next weeks pairs
        $next_feature = ScmData::get_next_future_fixture();

        $future_data = array();
        if ($next_feature->post_type !== 'default') {
            $future_pairs = $weekly_matchups->get_matchups()->by_fixture_id($next_feature->ID)->by_league_id($player_league);
            $new_future_pairs = [];
            for ($i = 0; $i < (count($future_pairs) / 2); $i++) {

                $z = 2 * $i;
                $new_future_pairs[] = array($future_pairs[$z], $future_pairs[$z + 1]);

            }

            //$my_future_pair = current(array_filter($new_future_pairs, fn($pair_array) => in_array($player->player_id, $pair_array)));
            $my_future_pair = current(array_filter($new_future_pairs, function($pair_array) use ($player) {return in_array($player->player_id, $pair_array);}));

            $future_data['home'] = (get_user_by('id', $my_future_pair[0]))->display_name;
            $future_data['away'] = (get_user_by('id', $my_future_pair[1]))->display_name;
            $future_data['fixture'] = urldecode($next_feature->post_name);
        }

        if (SCM_DEBUG) {
            //echo '<pre>';
            //var_dump($pairs);
            //var_dump($new_pairs);
            //var_dump($future_pairs);
            //var_dump($future_data);
            //var_dump($current_fixture);
            //var_dump($weekly_matchups->get_all_matchups());
            //var_dump($data);
            //var_dump($my_pair);
            //var_dump($player->player_id);
            //echo '</pre>';
        }

        $output = $this->template->container_start;
        $output .= $this->template->get_html($data);

        $output .= $this->template->get_html($future_data);
        $output .= $this->template->container_end;

        $output .= $this->template->get_css();

        return $output;

    }

    public function get_template()
    {
        $this->template = new \Scoremasters\Inc\Templates\CurrentPlayerMatchupTemplate('div', 'scm-player-matchups', '', array('name' => 'player_id', 'value' => get_current_user_id()));
    }

}
