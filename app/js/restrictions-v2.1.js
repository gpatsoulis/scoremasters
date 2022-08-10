export {
    restrictions,
    set_reset_first_to_first_option
};



function restrictions(event, id, instance) {

    let popup = instance["$element"][0];
    //console.log(popup);

    let shmeioSelect = popup.querySelector('#form-field-field_b324dff');

    let underOverSelect = popup.querySelector('#form-field-field_eba581d');

    let scoreSelect = popup.querySelector('#form-field-field_4879a1e');

    let scorerSelect = popup.querySelector('#form-field-scm_scorer');

    let doublePoints = popup.querySelector('#form-field-field_c61b597');



    //let doubleSelect = popup.querySelector('#form-field-field_c61b597');


    //[5/5, 13:50] Tassos Mountakis: Δε μπορούν να βάλουν σημείο άσσο και σκορ 0-1
    //[5/5, 13:50] Tassos Mountakis: Δε μπορούν να επιλέξουν σκορ και Άντερ/οβερ
    //[5/5, 13:51] Tassos Mountakis: Δε μπορούν να επιλέξουν σκόρερ της φιλοξενούμενης ομάδας αν πχ έχουν επιλέξει σκορ 2-0
    //[5/5, 13:52] Tassos Mountakis: Αν επιλέξουν σκορ 0-0, δεν μπορούν να επιλέξουν σκόρερ. Και φυσικά να απενεργοποιείται η επιλογή διπλασιασμό στο σκόρερ

    //initial restriction
    scoreSelect.addEventListener('click', runScoreRestrictions);

    function runScoreRestrictions(event) {

        if (shmeioSelect.value != '-') {
            let ev = {
                target: shmeioSelect
            };
            shmeioSelect_restrictions(ev);
        }
    }

    shmeioSelect.addEventListener('change', shmeioSelect_restrictions);

    function shmeioSelect_restrictions(event) {

        //let possible_points = get_possible_points(event.target.value)
        //render_possible_points(event.target,possible_points,);

        if (event.target.value !== '-') {

        }

        if (event.target.value === '-') {

            set_reset_first_to_first_option(scoreSelect);
            set_reset_first_to_first_option(underOverSelect);
            set_reset_first_to_first_option(scorerSelect);

        }

        if (['-/1', '1/1', 'X/1', '2/1'].includes(event.target.value)) {

            let optionsDisble = scoreSelect.querySelectorAll('option');
            [...optionsDisble].map(x => {
                x.disabled = false;

                if (['0-1', '0-2', '0-3', '0-4', '1-2', '1-3', '1-4', '2-3', '2-4', '3-4'].includes(x.value)) {
                    x.disabled = true;
                }
                if (['0-0', '1-1', '2-2', '3-3'].includes(x.value)) {
                    x.disabled = true;
                }

                if (x.value == '-') {
                    x.disabled = false;
                }

            });

            if (['0-1', '0-2', '0-3', '0-4', '1-2', '1-3', '1-4', '2-3', '2-4', '3-4'].includes(scoreSelect.value)) {
                set_reset_first_to_first_option(scoreSelect);
            }
            if (['0-0', '1-1', '2-2', '3-3'].includes(scoreSelect.value)) {
                set_reset_first_to_first_option(scoreSelect);
            }

        }


        if (['-/2', '2/2', 'X/2', '1/2'].includes(event.target.value)) {
            //shmeioSelect.disabled = false;
            let optionsDisble = scoreSelect.querySelectorAll('option');
            [...optionsDisble].map(x => {
                x.disabled = true;

                if (['0-1', '0-2', '0-3', '0-4', '1-2', '1-3', '1-4', '2-3', '2-4', '3-4'].includes(x.value)) {
                    x.disabled = false;
                }
                if (['0-0', '1-1', '2-2', '3-3'].includes(x.value)) {
                    x.disabled = true;
                }
                if (x.value == '-') {
                    x.disabled = false;
                }

            });

            if (['0-0', '1-1', '2-2', '3-3'].includes(scoreSelect.value)) {
                set_reset_first_to_first_option(scoreSelect);
            }
            if (!['0-1', '0-2', '0-3', '0-4', '1-2', '1-3', '1-4', '2-3', '2-4', '3-4'].includes(scoreSelect.value)) {
                set_reset_first_to_first_option(scoreSelect);
            }
        }

        if (['-/X', '1/X', 'X/X', '2/X'].includes(event.target.value)) {
            //shmeioSelect.disabled = false;
            let optionsDisble = scoreSelect.querySelectorAll('option');
            [...optionsDisble].map(x => {

                x.disabled = true;

                if (['0-0', '1-1', '2-2', '3-3'].includes(x.value)) {
                    x.disabled = false;
                }

                if (x.value == '-') {
                    x.disabled = false;
                }

            });

            if (!['0-0', '1-1', '2-2', '3-3'].includes(scoreSelect.value)) {
                set_reset_first_to_first_option(scoreSelect);
            }


        }
    }

    underOverSelect.addEventListener('change', underOverSelect_restrictions);

    function underOverSelect_restrictions(event) {
        console.log(event);
        if (event.target.value !== '-') {
            //disable shmeio select
            set_reset_first_to_first_option(scoreSelect);
            //scoreSelect.disabled = true;
            scoreSelect.value = '-';
        }

        if (event.target.value === '-') {
            //disable shmeio select
            //scoreSelect.disabled = false;
        }
    }

    scoreSelect.addEventListener('change', scoreSelect_restrictions);

    function scoreSelect_restrictions(event) {

        if (event.target.value === '0-0') {
            //disable scorer select
            set_reset_first_to_first_option(scorerSelect);
            scorerSelect.disabled = true;
            //scorerSelect.value = '-';

            //let optionScorer = doubleSelect.querySelector('option[value="SCORER"]');
            //optionScorer.disabled = true;
        }

        if (event.target.value !== '0-0') {
            scorerSelect.disabled = false;

            //let optionScorer = doubleSelect.querySelector('option[value="SCORER"]');
            //optionScorer.disabled = false;

        }

        if (event.target.value !== '-') {
            //disable under/over select

            //last-minute
            set_reset_first_to_first_option(underOverSelect);
            //underOverSelect.disabled = true;
            underOverSelect.value = '-';

        }

        if (event.target.value === '-') {
            //disable under/over select

            //last-minute
            set_reset_first_to_first_option(underOverSelect);
            //underOverSelect.disabled = false;

            let optionsDisble = scoreSelect.querySelectorAll('option');
            [...optionsDisble].map(x => x.disabled = false);
        }


    }


}


function set_reset_first_to_first_option(select) {

    let options = select.querySelectorAll('option');
    [...options].map(x => {
        if (x.selected == true) {
            x.selected = false;
        }
    });

    options[0].selected = true;
}


function get_possible_points(prediction_value){

    console.log(prediction_value);

    let points = 5;

    return points;

 }

function render_possible_points(select, points){

    let pointsPlaceholder = select.parentElement.nextElementSibling;

    if(!pointsPlaceholder) return;

    pointsPlaceholder.textContent =  'Πιθανοί Πόντοι: ' + points;
}