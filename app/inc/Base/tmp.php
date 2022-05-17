<?php

/**

 * Plugin Name: Elementor Form Create New User

 * Description: Create a new user using elementor pro form

 * Author:      Kyriakos Kagialoglou

 * Author URI:  https://alfatahnesab.com

 * License URI: https://www.gnu.org/licenses/gpl-2.0.html

 * Version:     1.0.0

 */



add_action( 'elementor_pro/forms/new_record',  'alfa_elementor_form_create_new_user' , 10, 2 );



function alfa_elementor_form_create_new_user($record,$ajax_handler) // creating function 

{

    $form_name = $record->get_form_settings('form_name');

    

    //Check that the form is the "Sign Up" if not - stop and return;

    if ('Signup' !== $form_name) {

        return;

    }

    

    $form_data  = $record->get_formatted_data();

    $username   = $form_data['Ψευδώνυμο'];

    $email      = $form_data['Email']; 

    $password   = $form_data['Επιθυμητός Κωδικός Πρόσβασης']; 



    

    $user = wp_create_user($username,$password,$email); 



    if (is_wp_error($user)){ 

        $ajax_handler->add_error_message("Αποτυχία δημιουργίας χρήστη: ".$user->get_error_message());

        $ajax_handler->is_success = false;

        return;

    }



    // Assign Primary field value in the created user profile

    $first_name   =$form_data["Όνομα"]; 

    $last_name    =$form_data["Επώνυμο"];

    wp_update_user(array("ID"=>$user,"first_name"=>$first_name,"last_name"=>$last_name)); 



    // Assign Additional added field value in the created user profile

    /*$user_phone   =$form_data["First Name"]; 

    $user_bio     =$form_data["Last Name"];

    update_user_meta($user, 'user_phone', $user_phone);    

    update_user_meta($user, 'user_bio', $user_bio); 



    /* Automatically log in the user and redirect the user to the home page */

    $creds= array(

        "user_login"=>$username,

        "user_password"=>$password,

        "remember"=>true

    );

    

    $signon = wp_signon($creds); 

    

    if ($signon) {

        $ajax_handler->add_response_data( 'redirect_url', get_home_url() );

    }

} 



function import_points_table() {



    $file_name = 'points_table.csv';

    

    $csvdata = file_get_contents(__DIR__ . '/' . $file_name);

    $lines = explode("\n", $csvdata); // split data by new lines

    $points_table=array("0"=>array(),"1"=>array(),"2"=>array(),"3"=>array(),"-1"=>array(),"-2"=>array(),"-3"=>array());

    

    foreach ($lines as $i => $line) {

        $values = explode(',', $line); // split lines by commas

        // set values removing them as we ago

        $key=$values[0];

        $points_table["0"][$key]= trim($values[1]); unset($values[1]);

        $points_table["1"][$key]= trim($values[2]); unset($values[2]);

        $points_table["2"][$key]= trim($values[3]); unset($values[3]);

        $points_table["3"][$key]= trim($values[4]); unset($values[4]);

        $points_table["-1"][$key]= trim($values[5]); unset($values[5]);

        $points_table["-2"][$key]= trim($values[6]); unset($values[6]);

        $points_table["-3"][$key]= trim($values[7]); unset($values[7]);

    }

    update_option('points_table',$points_table);

}

    

    

function calculate_points_after_prediction_submit(){

    $prediction=get_post(1392);

    $prediction_content=unserialize($prediction->post_content);

    $player_id=$prediction->post_author;

    //boolean for double points
    $double_shmeio=$double_uo=$double_score=$double_scorer=false;
    

    $dynamikotita_home_team=intval(get_post_meta($prediction_content["homeTeam_id"],'scm-team-capabilityrange')[0]);

    $dynamikotita_away_team=intval(get_post_meta($prediction_content["awayTeam_id"],'scm-team-capabilityrange')[0]);

    

    $column=strval($dynamikotita_home_team - $dynamikotita_away_team);

        

    $points_table=get_option('points_table');


    $prediction_points_shmeio=$prediction_points_under_over=$prediction_points_score=0;
    $double_shmeio=$double_uo=$double_score=$double_scorer=false;


    $prediction_points_shmeio=$points_table[$column][$prediction_content["SHMEIO"]];

    $prediction_points_under_over=$points_table[$column][$prediction_content["Under / Over"]];

    $prediction_points_score=$points_table[$column][$prediction_content["score"]];



    $teams = get_field('match-teams',get_post(1119));



        $home_team = $teams[0]['home-team'][0];

        $away_team = $teams[0]['away-team'][0];



    $acf_scorers = get_field('scm-scorers',1119);

        $scorers = [];

        foreach($acf_scorers as $acf_score){

            //$match_scorer[] = array('scm-scorers' => $acf_score['scm-scorers'],'scm-goal-minute'=>$acf_score['scm-goal-minute']);

            $scorers[] = $acf_score['scm-scorers'][0]->ID;

        }



        $actual_scorers = $scorers;

        /*?><pre><?var_dump($actual_scorers);?><pre><?*/

    

    $Half_time_score = get_field('scm-half-time-score',1119);

    $final_score = get_field('scm-full-time-score',1119);

    $total_points=0;

    



    //check if prediction matches shmeio result

    if (intval($Half_time_score["scm-half-time-home-score"]) > intval($Half_time_score["scm-half-time-away-score"])){

        if ((intval($final_score["scm-full-time-home-score"]) > intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "1/1"){

            $total_points=$total_points+intval($prediction_points_shmeio);
            $double_shmeio=true;
        }



        elseif ((intval($final_score["scm-full-time-home-score"]) == intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "1/X"){

            $total_points=$total_points+intval($prediction_points_shmeio);
            $double_shmeio=true;
        }



        elseif ((intval($final_score["scm-full-time-home-score"]) < intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "1/2"){

            $total_points=$total_points+intval($prediction_points_shmeio);
            $double_shmeio=true;
        }

    }



    elseif (intval($Half_time_score["scm-half-time-home-score"]) == intval($Half_time_score["scm-half-time-away-score"])){

        if ((intval($final_score["scm-full-time-home-score"]) > intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "X/1"){

            $total_points=$total_points+intval($prediction_points_shmeio);
            $double_shmeio=true;
        }



        elseif ((intval($final_score["scm-full-time-home-score"]) == intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "X/X"){

            $total_points=$total_points+intval($prediction_points_shmeio);
            $double_shmeio=true;
        }



        elseif ((intval($final_score["scm-full-time-home-score"]) < intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "X/2"){

            $total_points=$total_points+intval($prediction_points_shmeio);
            $double_shmeio=true;
        }

    }



    elseif (intval($Half_time_score["scm-half-time-home-score"]) < intval($Half_time_score["scm-half-time-away-score"])){

        if ((intval($final_score["scm-full-time-home-score"]) > intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "2/1"){

            $total_points=$total_points+intval($prediction_points_shmeio);
            $double_shmeio=true;
        }



        elseif ((intval($final_score["scm-full-time-home-score"]) == intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "2/X"){

            $total_points=$total_points+intval($prediction_points_shmeio);
            $double_shmeio=true;
        }



        elseif ((intval($final_score["scm-full-time-home-score"]) < intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "2/2"){

            $total_points=$total_points+intval($prediction_points_shmeio);
            $double_shmeio=true;
        }

    }



    if ((intval($final_score["scm-full-time-home-score"]) > intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "-/1"){

        $total_points=$total_points+intval($prediction_points_shmeio);
        $double_shmeio=true;
    }



    if ((intval($final_score["scm-full-time-home-score"]) == intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "-/X"){

        $total_points=$total_points+intval($prediction_points_shmeio);
        $double_shmeio=true;
    }



    if ((intval($final_score["scm-full-time-home-score"]) < intval($final_score["scm-full-time-away-score"])) && $prediction_content["SHMEIO"] == "-/2"){

        $total_points=$total_points+intval($prediction_points_shmeio);
        $double_shmeio=true;
    }





    //check if prediction matches u/o result

    $total_goals=intval($final_score["scm-full-time-home-score"]) + intval($final_score["scm-full-time-away-score"]);

    if ($total_goals < 1.5 && $prediction_content["Under / Over"] == "Under 1.5"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }



    elseif ($total_goals < 2.5 && $prediction_content["Under / Over"] == "Under 2.5"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }



    elseif ($total_goals < 3.5 && $prediction_content["Under / Over"] == "Under 3.5"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }



    elseif ($total_goals < 4.5 && $prediction_content["Under / Over"] == "Under 4.5"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }



    elseif ($total_goals > 0.5 && $prediction_content["Under / Over"] == "Over 0.5"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }



    elseif ($total_goals > 1.5 && $prediction_content["Under / Over"] == "Over 1.5"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }



    elseif ($total_goals > 2.5 && $prediction_content["Under / Over"] == "Over 2.5"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }



    elseif ($total_goals > 3.5 && $prediction_content["Under / Over"] == "Over 3.5"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }



    elseif ($total_goals > 4.5 && $prediction_content["Under / Over"] == "Over 4.5"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }

    //These option are not in select option yet, because they are in Greek--- Start //

    elseif ($total_goals < 3 && $prediction_content["Under / Over"] == "1 ή 2"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }

    elseif ($total_goals > 1 && $total_goals < 4 && $prediction_content["Under / Over"] == "2 ή 3"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }

    elseif ($total_goals > 2 && $total_goals < 5 && $prediction_content["Under / Over"] == "3 ή 4"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }

    elseif ($total_goals > 3 && $total_goals < 6 && $prediction_content["Under / Over"] == "4 ή 5"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }

    elseif ($total_goals == 1 && $prediction_content["Under / Over"] == "Ακριβώς 1"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }

    elseif ($total_goals == 2 && $prediction_content["Under / Over"] == "Ακριβώς 2"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }

    elseif ($total_goals == 3 && $prediction_content["Under / Over"] == "Ακριβώς 3"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }

    elseif ($total_goals == 4 && $prediction_content["Under / Over"] == "Ακριβώς 4"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }

    elseif ($total_goals == 5 && $prediction_content["Under / Over"] == "Ακριβώς 5"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }

    elseif ($final_score["scm-full-time-home-score"] > 0 && $final_score["scm-full-time-away-score"]>0 && $prediction_content["Under / Over"] == "goal goal – ναι"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }

    elseif (($final_score["scm-full-time-home-score"] == 0 || $final_score["scm-full-time-away-score"] == 0) && $prediction_content["Under / Over"] == "goal goal – οχι"){

        $total_points=$total_points+intval($prediction_points_under_over);
        $double_uo=true;
    }
    //These option are not in select option yet, because they are in Greek--- End //


    //check if prediction matches u/o result

    if(($final_score["scm-full-time-home-score"]."-".$final_score["scm-full-time-away-score"])==$prediction_content["score"]){

        $total_points=$total_points+intval($prediction_points_score);
        $double_score=true;
    }

    

    //check if prediction matches scorers result

    $p_scorer=(intval($prediction_content["Scorer"]));

    if (in_array($p_scorer,$actual_scorers)){
        $prediction_points_scorer=0;
        if (get_field("scm-player-position",$p_scorer) == "Επιθετικός"){

            $prediction_points_scorer=4;

        }

        elseif (get_field("scm-player-position",$p_scorer) == "Μέσος") {

            $prediction_points_scorer=8;

        }

        elseif (get_field("scm-player-position",$p_scorer) == "Αμυντικός") {

            $prediction_points_scorer=9;

        }

        $total_points=$total_points + $prediction_points_scorer;
        $double_scorer=true;
    }

    ?><pre><?php 
    echo ("Διαφορά Δυναμικότητας: ".$column."\nΣημείο: ".$prediction_content["SHMEIO"]." | points: ".$prediction_points_shmeio." | Διπλασιασμός: ".$double_shmeio);
    echo ("\nUnder / Over: ".$prediction_content["Under / Over"]." | points: ".$prediction_points_under_over." | Διπλασιασμός: ".$double_uo);
    echo ("\nScore: ".$prediction_content["score"]." | points: ".$prediction_points_score." | Διπλασιασμός: ".$double_score);
    echo ("\nScorer: ".$prediction_content["Scorer"]." | points: ".$p_scorer." | Διπλασιασμός: ".$double_scorer);
    ?>
    </pre><?php

    //check for double points
    if ($prediction_content["Double Points"]=="SHMEIO" && $double_shmeio){
        $total_points=$total_points+intval($prediction_points_shmeio);
    }

    if ($prediction_content["Double Points"]=="UNDER / OVER" && $double_uo){
        $total_points=$total_points+intval($prediction_points_under_over);
    }

    if ($prediction_content["Double Points"]=="SCORER" && $double_scorer){
        $total_points=$total_points+$prediction_points_scorer;
    }

    return ("Total Points: ".$total_points);

}

    

    add_shortcode('scm-calculate_prediction','calculate_points_after_prediction_submit');

/** Shortcode for week selection in SCM-Fixture*/
function fill_select_week_fixture()
{
    $args = array(
        'post_type' => 'scm-fixture',
        'post_status' => 'publish',
        'order' => 'ASC',
        'numberposts' => -1,
    );

    $posts = get_posts($args);
    ?>
	<form action="/staging/?page_id=692" method="post">
			<select name="scm-fixtures-selection" id="scm-fixtures-selection-week">
			<?php foreach ($posts as $post): setup_postdata($post);
        /*$end_date_string=get_post_meta( $post->ID, 'week-end-date', true );
        $end_date=DateTime::createFromFormat('Ymd', $end_date_string);
        $start_date_string=get_post_meta( $post->ID, 'week-start-date', true );
        $start_date=DateTime::createFromFormat('Ymd', $start_date_string);
         */
        echo '<option value="' . $post->ID . '">' . $post->post_title . ' ( ' . date("Y-m-d",strtotime(get_post_meta($post->ID, 'week-start-date', true))) . ' - ' . date("Y-m-d",strtotime(get_post_meta($post->ID, 'week-end-date', true))) . ' )</option>';
    endforeach;?>
			</select>
		<input type="submit" name="submit" value="Προβολή" />
	</form>
	<?php
wp_reset_postdata();


}

add_shortcode('scm-select-week', 'fill_select_week_fixture');
