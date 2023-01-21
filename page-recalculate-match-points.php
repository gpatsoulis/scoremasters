<?php 

use Scoremasters\Inc\Base\ScmData;
use Scoremasters\Inc\Classes\RecalculateMatchScore;


if (isset($_POST['match_id'])
&& wp_verify_nonce($_POST['scm_match_id_'], 'submit_recalculate_form')) {
    $match_id = filter_var($_POST['match_id'], FILTER_VALIDATE_INT);
}

if ( isset($match_id) ) {
    $recalculate = new RecalculateMatchScore(intval($match_id));
    $recalculate->get_predictions()->calculate_points()->save_points();

    echo $recalculate->showDiff();
}




$action = htmlspecialchars($_SERVER['REQUEST_URI']);

$matches = ScmData::get_finished_matches_for_fixture(ScmData::get_current_fixture());

$options = '';
foreach ($matches as $match){
    $options .= '<option value="'. $match->ID .'" >'. $match->post_title .' ' . $match->post_date .'</option>';
}

$nonce = wp_nonce_field( 'submit_recalculate_form', 'scm_match_id_' );

?>
<form action="<?php echo $action ?>" method="post">
    <label for="scm-select-match">Choose a match from current week:</label>
    <select name="match_id" id="scm-select-match">
        <?php echo $options; ?>
    </select>
    <?php echo $nonce ?>
    <div class="submit"><input type="submit" value='recalculate selected match'></div>
</form>