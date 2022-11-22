<?php

use Scoremasters\Inc\Base\ScmData;

function see_players_predictionForm()
{
    $fixtures = ScmData::get_all_fixtures_for_season();
    $players = ScmData::get_all_scm_users();

    $action = htmlspecialchars($_SERVER['REQUEST_URI']);

    ?>
            <form action="<?php echo $action; ?>" method="post">
            <input list="scm-fixtures-selection-week" name="fixture_name" id="fixture_name_selection">
                <datalist name="fixture_name" id="scm-fixtures-selection-week">
    <?php
foreach ($fixtures as $fixture) {

        $fixturedata = array(
            'fixture_id' => $fixture->ID,
            'fixture_title' => $fixture->post_title,
        );
        ?><option value=<?php echo $fixturedata['fixture_id']; ?>><?php echo $fixturedata['fixture_title']; ?></option><?php
}
    ?>
                </datalist>
            <input list="scm-player-selection" name="player_name" id="player_name_selection">
            <datalist name="player_name" id="scm-player-selection"><?php
foreach ($players as $player) {

        $playerdata = array(
            'player_id' => $player->data->ID,
            'player_name' => $player->data->display_name,
        );
        ?><option value=<? echo $playerdata['player_id'];?>><?php echo $playerdata['player_name']; ?></option><?php
}
    //$nonce = wp_nonce_field( 'submit_form', 'scm_fixture_setup' );

    ?>
            </datalist>
               <?php //{$nonce}?>
                <input type="submit" name="submit" value="Προβολή Προβλέψεων" />
            </form>

    <?php
$fixture_selection = get_post($_POST['fixture_name']);
    $selected_fixture = ($fixture_selection) ? $fixture_selection : get_current_fixture();
    //get_post($_POST['fixture_name']):ScmData::get_current_fixture();
    $scm_player = ($_POST['player_name']) ? $_POST['player_name'] : get_current_user_id();
    echo "<pre>";
    var_dump($selected_fixture);
    var_dump($scm_player);
    var_dump($predictions);
    echo "</pre>\n";

    $predictions[] = see_players_prediction($selected_fixture, $scm_player);

}

add_shortcode('seeplayerspredictionsForm', 'see_players_predictionForm');

function see_players_prediction($selected_fixture, $scm_player)
{
    //$matches[]=ScmData::get_all_matches_for_fixture($selected_fixture);
    $fixture_start_date_str = get_field('week-start-date', $selected_fixture->ID);

    $fixture_start_date = new \DateTime($fixture_start_date_str, new \DateTimeZone('Europe/Athens'));

    $fixture_end_date_str = get_field('week-end-date', $selected_fixture->ID);

    $fixture_end_date = new \DateTime($fixture_end_date_str, new \DateTimeZone('Europe/Athens'));

    $args = array(
        'aurhor' => $scm_player,
        'post_type' => 'scm-prediction',
        'post_status' => 'any',
        'date_query' => array(
            'after' => array(
                'year' => (int) $fixture_start_date->format('Y'),
                'month' => (int) $fixture_start_date->format('n'),
                'day' => (int) $fixture_start_date->format('j'),
            ),
            'before' => array(
                'year' => (int) $fixture_end_date->format('Y'),
                'month' => (int) $fixture_end_date->format('n'),
                'day' => (int) $fixture_end_date->format('j'),
            ),
        ),
        'inclusive' => true,
    );

    $predictions = get_posts($args);

    echo "<pre>";
    var_dump($predictions);
    echo "</pre>\n";

    return $predictions;
}