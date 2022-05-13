<?php

/**

 * Plugin Name: Elementor Form Create New User

 * Description: Create a new user using elementor pro form

 * Author:      Kyriakos Kagialoglou

 * Author URI:  https://alfatahnesab.com

 * License URI: https://www.gnu.org/licenses/gpl-2.0.html

 * Version:     1.0.0

 */

add_action('elementor_pro/forms/new_record', 'alfa_elementor_form_create_new_user', 10, 2);

function alfa_elementor_form_create_new_user($record, $ajax_handler) // creating function

{

    $form_name = $record->get_form_settings('form_name');

    //Check that the form is the "Sign Up" if not - stop and return;

    if ('Signup' !== $form_name) {

        return;

    }

    $form_data = $record->get_formatted_data();

    $username = $form_data['Ψευδώνυμο'];

    $email = $form_data['Email'];

    $password = $form_data['Επιθυμητός Κωδικός Πρόσβασης'];

    $user = wp_create_user($username, $password, $email);

    if (is_wp_error($user)) {

        $ajax_handler->add_error_message("Αποτυχία δημιουργίας χρήστη: " . $user->get_error_message());

        $ajax_handler->is_success = false;

        return;

    }

    // Assign Primary field value in the created user profile

    $first_name = $form_data["Όνομα"];

    $last_name = $form_data["Επώνυμο"];

    wp_update_user(array("ID" => $user, "first_name" => $first_name, "last_name" => $last_name));

    // Assign Additional added field value in the created user profile

    /*$user_phone   =$form_data["First Name"];

    $user_bio     =$form_data["Last Name"];

    update_user_meta($user, 'user_phone', $user_phone);

    update_user_meta($user, 'user_bio', $user_bio);

    /* Automatically log in the user and redirect the user to the home page */

    $creds = array(

        "user_login" => $username,

        "user_password" => $password,

        "remember" => true,

    );

    $signon = wp_signon($creds);

    if ($signon) {

        $ajax_handler->add_response_data('redirect_url', get_home_url());

    }

}

function import_points_table()
{

    $file_name = 'points_table.csv';

    $csvdata = file_get_contents(__DIR__ . '/' . $file_name);

    $lines = explode("\n", $csvdata); // split data by new lines

    $points_table = array("0" => array(), "1" => array(), "2" => array(), "3" => array(), "-1" => array(), "-2" => array(), "-3" => array());

    foreach ($lines as $i => $line) {

        $values = explode(',', $line); // split lines by commas

        // set values removing them as we ago

        $key = $values[0];

        $points_table["0"][$key] = trim($values[1]);unset($values[1]);

        $points_table["1"][$key] = trim($values[2]);unset($values[2]);

        $points_table["2"][$key] = trim($values[3]);unset($values[3]);

        $points_table["3"][$key] = trim($values[4]);unset($values[4]);

        $points_table["-1"][$key] = trim($values[5]);unset($values[5]);

        $points_table["-2"][$key] = trim($values[6]);unset($values[6]);

        $points_table["-3"][$key] = trim($values[7]);unset($values[7]);

    }

    update_option('points_table', $points_table);

}

function calculate_points_after_prediction_submit()
{

    $prediction = get_post(900);

    $prediction_content = unserialize($prediction->post_content);

    $player_id = $prediction->post_author;

    //boolean for double points
    $double_shmeio = $double_uo = $double_score = $double_scorer = false;

    $dynamikotita_home_team = intval(get_post_meta($prediction_content["homeTeam_id"], 'scm-team-capabilityrange')[0]);

    $dynamikotita_away_team = intval(get_post_meta($prediction_content["awayTeam_id"], 'scm-team-capabilityrange')[0]);

    $column = strval($dynamikotita_home_team - $dynamikotita_away_team);

    $points_table = get_option('points_table');

    $prediction_points_shmeio = $points_table[$column][$prediction_content["SHMEIO"]];

    $prediction_points_under_over = $points_table[$column][$prediction_content["Under / Over"]];

    $prediction_points_score = $points_table[$column][$prediction_content["score"]];

    $teams = get_field('match-teams', get_post(850));

    $home_team = $teams[0]['home-team'][0];

    $away_team = $teams[0]['away-team'][0];

    $acf_scorers = get_field('scm-scorers', 850);

    $scorers = [];

    foreach ($acf_scorers as $acf_score) {

        //$match_scorer[] = array('scm-scorers' => $acf_score['scm-scorers'],'scm-goal-minute'=>$acf_score['scm-goal-minute']);

        $scorers[] = $acf_score['scm-scorers'][0]->ID;

    }

    $actual_scorers = $scorers;

    /*?><pre><?var_dump($actual_scorers);?><pre><?*/

    $Half_time_score = get_field('scm-half-time-score', 850);

    $final_score = get_field('scm-full-time-score', 850);

    $total_points = 0;

    //check if prediction matches shmeio result

    if (intval($Half_time_score["scm-half-time-home-score"]) > intval($Half_time_score["scm-half-time-away-score"])) {

        if ((intval($final_score["scm-full-time-home-score"]) > intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "1/1") {

            $total_points = $total_points + intval($prediction_points_shmeio);
            $double_shmeio = true;
        } elseif ((intval($final_score["scm-full-time-home-score"]) == intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "1/X") {

            $total_points = $total_points + intval($prediction_points_shmeio);
            $double_shmeio = true;
        } elseif ((intval($final_score["scm-full-time-home-score"]) < intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "1/2") {

            $total_points = $total_points + intval($prediction_points_shmeio);
            $double_shmeio = true;
        }

    } elseif (intval($Half_time_score["scm-half-time-home-score"]) == intval($Half_time_score["scm-half-time-away-score"])) {

        if ((intval($final_score["scm-full-time-home-score"]) > intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "X/1") {

            $total_points = $total_points + intval($prediction_points_shmeio);
            $double_shmeio = true;
        } elseif ((intval($final_score["scm-full-time-home-score"]) == intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "X/X") {

            $total_points = $total_points + intval($prediction_points_shmeio);
            $double_shmeio = true;
        } elseif ((intval($final_score["scm-full-time-home-score"]) < intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "X/2") {

            $total_points = $total_points + intval($prediction_points_shmeio);
            $double_shmeio = true;
        }

    } elseif (intval($Half_time_score["scm-half-time-home-score"]) < intval($Half_time_score["scm-half-time-away-score"])) {

        if ((intval($final_score["scm-full-time-home-score"]) > intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "2/1") {

            $total_points = $total_points + intval($prediction_points_shmeio);
            $double_shmeio = true;
        } elseif ((intval($final_score["scm-full-time-home-score"]) == intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "2/X") {

            $total_points = $total_points + intval($prediction_points_shmeio);
            $double_shmeio = true;
        } elseif ((intval($final_score["scm-full-time-home-score"]) < intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "2/2") {

            $total_points = $total_points + intval($prediction_points_shmeio);
            $double_shmeio = true;
        }

    } elseif ((intval($final_score["scm-full-time-home-score"]) > intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "-/1") {

        $total_points = $total_points + intval($prediction_points_shmeio);
        $double_shmeio = true;
    } elseif ((intval($final_score["scm-full-time-home-score"]) == intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "-/X") {

        $total_points = $total_points + intval($prediction_points_shmeio);
        $double_shmeio = true;
    } elseif ((intval($final_score["scm-full-time-home-score"]) < intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "-/2") {

        $total_points = $total_points + intval($prediction_points_shmeio);
        $double_shmeio = true;
    }

    //check if prediction matches u/o result

    $total_goals = intval($final_score["scm-full-time-home-score"]) + intval($final_score["scm-full-time-away-score"]);

    if ($total_goals < 1.5 && $prediction_content["Under / Over"] == "Under 1.5") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($total_goals < 2.5 && $prediction_content["Under / Over"] == "Under 2.5") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($total_goals < 3.5 && $prediction_content["Under / Over"] == "Under 3.5") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($total_goals < 4.5 && $prediction_content["Under / Over"] == "Under 4.5") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($total_goals > 0.5 && $prediction_content["Under / Over"] == "Over 0.5") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($total_goals > 1.5 && $prediction_content["Under / Over"] == "Over 1.5") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($total_goals > 2.5 && $prediction_content["Under / Over"] == "Over 2.5") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($total_goals > 3.5 && $prediction_content["Under / Over"] == "Over 3.5") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($total_goals > 4.5 && $prediction_content["Under / Over"] == "Over 4.5") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    }

    //These option are not in select option yet, because they are in Greek--- Start //

    elseif ($total_goals < 3 && $prediction_content["Under / Over"] == "1 ή 2") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($total_goals > 1 && $total_goals < 4 && $prediction_content["Under / Over"] == "2 ή 3") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($total_goals > 2 && $total_goals < 5 && $prediction_content["Under / Over"] == "3 ή 4") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($total_goals > 3 && $total_goals < 6 && $prediction_content["Under / Over"] == "4 ή 5") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($total_goals == 1 && $prediction_content["Under / Over"] == "Ακριβώς 1") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($total_goals == 2 && $prediction_content["Under / Over"] == "Ακριβώς 2") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($total_goals == 3 && $prediction_content["Under / Over"] == "Ακριβώς 3") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($total_goals == 4 && $prediction_content["Under / Over"] == "Ακριβώς 4") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($total_goals == 5 && $prediction_content["Under / Over"] == "Ακριβώς 5") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif ($final_score["scm-full-time-home-score"] > 0 && $final_score["scm-full-time-away-score"] > 0 && $prediction_content["Under / Over"] == "goal goal – ναι") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    } elseif (($final_score["scm-full-time-home-score"] == 0 || $final_score["scm-full-time-away-score"] == 0) && $prediction_content["Under / Over"] == "goal goal – οχι") {

        $total_points = $total_points + intval($prediction_points_under_over);
        $double_uo = true;
    }
    //These option are not in select option yet, because they are in Greek--- End //

    //check if prediction matches u/o result

    if (($final_score["scm-full-time-home-score"] . "-" . $final_score["scm-full-time-away-score"]) == $prediction_content["score"]) {

        $total_points = $total_points + intval($prediction_points_score);
        $double_score = true;
    }

    //check if prediction matches scorers result

    $p_scorer = (intval($prediction_content["Scorer"]));

    if (in_array($p_scorer, $actual_scorers)) {

        /*if (scm-player-position == "Επιθετικός"){

        $total_points=$total_points + 1;

        }

        elseif (scm-player-position == "Επιθετικός") {

        $total_points=$total_points + 3;

        }

        elseif (scm-player-position == "Επιθετικός") {

        $total_points=$total_points + 3;

        }*/

        $total_points = $total_points + 3;
        $double_scorer = true;
    }

    /*?><pre><?php
    var_dump($prediction_content["homeTeam_id"]);
    var_dump(get_post_meta($prediction_content["homeTeam_id"],'scm-team-capabilityrange')[0]);
    var_dump($prediction_content["awayTeam_id"]);
    var_dump($dynamikotita_away_team);
    var_dump($column);
    var_dump($double_shmeio);
    var_dump($prediction_points_shmeio);
    var_dump($double_uo);
    var_dump($prediction_points_under_over);
    var_dump($double_score);
    var_dump($prediction_points_score);
    var_dump($double_scorer);
    echo ("3\n");
    var_dump($total_points);

    ?>
    </pre><?php*/

    //check for double points
    if ($prediction_content["Double Points"] == "SHMEIO" && $double_shmeio) {
        $total_points = $total_points + intval($prediction_points_shmeio);
    } elseif ($prediction_content["Double Points"] == "UNDER / OVER" && $double_uo) {
        $total_points = $total_points + intval($prediction_points_under_over);
    } elseif ($prediction_content["Double Points"] == "SCORE" && $double_score) {
        $total_points = $total_points + intval($prediction_points_score);
    } elseif ($prediction_content["Double Points"] == "SCORER" && $double_scorer) {
        $total_points = $total_points + 3;
    }

    return ("Total Points: " . $total_points);

    //return "Διαφορά Δυναμικότητας: ".$column." | Σημείο: ".$prediction_content["SHMEIO"]." | points: ".$prediction_points_shmeio." | player_id: ".$player_id;

}

add_shortcode('scm-calculate_prediction', 'calculate_points_after_prediction_submit');
