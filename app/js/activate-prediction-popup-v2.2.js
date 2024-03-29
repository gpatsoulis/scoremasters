import {
    restrictions
} from '/wp-content/themes/scoremasters/app/js/restrictions-v2.2.js';

import {
    disable_form_on_date
} from '/wp-content/themes/scoremasters/app/js/dateRestrictions-v2.1.js';

// todo: delete;
const player_points_table = {
    "Επιθετικός": 3,
    "Μέσος": 5,
    "Αμυντικός": 10,
};

let btns = document.querySelectorAll('.activate-prediction-popup');

let testDomain = 'http://scoremasters.test';
let productionDomain = 'https://scoremasters.gr';
let stagingDomain = 'https://scoremasters.gr/staging/';

//let currentDomain = testDomain;
let currentDomain = window.location.origin;


[...btns].map(x => {
    //safari fix
    x.addEventListener('click', setProductURLData);
    x.addEventListener('click', activatePopup);
});

function activatePopup(event) {
    elementorFrontend.documentsManager.documents[872].showModal();
}

jQuery(document).ready(function ($) {

    //safariFix();

    $(document).on('elementor/popup/show', {
        data: "ns4u data"
    }, onPopupEvent);

});

function onPopupEvent(event, id, instance) {
    //console.log(event, id);
    // populate popup with match data
    editPopupContent(event, id, instance);

    restrictions(event, id, instance);

    //get_submited_predictions(event, id, instance);

    disable_form_on_date(event, id, instance);
    //addPlayersList(event, id, instance);
    possible_player_points(event, id, instance);

    in_option_render_points();
}

/*
function safariFix() {
    let buttons = document.querySelectorAll('.activate-prediction-popup');
    [...buttons].map(x => x.addEventListener('click', setProductURLData));
}
*/

function get_submited_predictions(popup) {

    //let popup = instance["$element"][0];

    let player_id = popup.querySelector('input[name="player_id"]').value;

    let match_id = popup.querySelector('input[name="match_id"]').value;

    let prediction_title = match_id + '-' + player_id;

    //https://scoremasters.gr
    //let url = 'http://scoremasters.test/wp-json/scm/v1/scm_prediction_title/'+prediction_title;
    let url = currentDomain + '/wp-json/scm/v1/scm_prediction_title/pre_title=' + prediction_title + '&pre_author=' + player_id;
    console.log(url);

    let responce = fetch(url, {
            method: 'GET',
            mode: 'cors',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(
            data => {
                    console.log(data.status,data.statusText);
                    //todo: handle 404 response

                    if(data.status !== 200){
                    return JSON.stringify([]);
                }

                return data.json();
                }
            )
        .then(data => {
                //console.log(data);
               
                

                render_submited_prediction_data(popup, data);

            }

        );

    //let data = await responce.json();
}

function render_submited_prediction_data(popup, data) {

    //find select elements by text content
    let array_of_labels = popup.querySelectorAll('label');
    //console.log(data);

    //translate data 
    data['ΣΗΜΕΙΟ']              = data['SHMEIO'];
    delete data['SHMEIO'];

    data['ΣΚΟΡ']                = data['score'];
    delete data['score'];

    data['ΣΚΟΡΕΡ']              = data['Scorer'];
    delete data['Scorer'];
    
    data['ΔΙΠΛΑΣΙΑΣΜΟΣ ΠΟΝΤΩΝ'] = data['Double Points'];
    delete data['Double Points'];


    for (const [key, value] of Object.entries(data)) {

        let select_id = find_element_by_text_content(key, [...array_of_labels]);

        if (select_id == '') {
            continue;
        }

        let select = popup.querySelector('select#' + select_id);

        //console.log(select);
        //console.log('option[value="'+value+'"]');

        if (value == '') {
            continue;
        }

        let option = select.querySelector('option[value="' + value + '"');
        //console.log(option);
        option.selected = true;


        /*
        if (value == '-') {
            continue;
        }
        */
        let event = {};
        event.target = select;
        calc_possible_points(event);
    }


}

function find_element_by_text_content(prediction_text, array_of_labels) {
    //console.log(prediction_text);
    //x.textContet.includes(prediction_text
    let label = array_of_labels.filter(x => {
        //console.log(x.textContent);
        if (x.textContent.includes(prediction_text)) {
            return x;
        }
    });

    //console.log(label);

    if (label.length === 0) {
        return '';
    }

    let select_id = label[0].htmlFor;
    //console.log(select_id);

    return select_id


}

function setProductURLData(event) {

    //todo: check if product class exists
    if (!event.target) {
        return;
    }

    let target = event.target;
    let parentDataEl = target.closest('.scm-fixture-list');

    if (!parentDataEl) {
        return;
    }

    let player_id = parentDataEl.dataset.player_id;


    let homeTeam = parentDataEl.querySelector('.scm-home-team');

    if (!homeTeam) {
        return;
    }

    let homeTeam_id = homeTeam.dataset.team_id;
    let homeTeam_name = homeTeam.innerText;

    let awayTeam = parentDataEl.querySelector('.scm-away-team');

    if (!awayTeam) {
        return;
    }

    let awayTeam_id = awayTeam.dataset.team_id;
    let awayTeam_name = awayTeam.innerText;

    let match = parentDataEl.querySelector('.scm-fixture-list-row');

    if (!match) {
        return;
    }

    let match_id = match.dataset.match_id;
    let match_date = match.dataset.match_date;

    const url = new URL(window.location);

    url.searchParams.set('player_id', player_id);
    url.searchParams.set('match_id', match_id);
    url.searchParams.set('homeTeam_id', homeTeam_id);
    url.searchParams.set('homeTeam_name', homeTeam_name);
    url.searchParams.set('awayTeam_id', awayTeam_id);
    url.searchParams.set('awayTeam_name', awayTeam_name);
    url.searchParams.set('match_date', match_date);

    window.history.replaceState({}, window.location, "/πρόγραμμα-εβδομάδας/");
    window.history.pushState({}, '', url);

}


function editPopupContent(event, id, instance) {

    if (id !== 872) return;

    let data = readURLSearchParams();

    let popup = instance["$element"][0];

    setModalData(data, popup);

    setUpTeamsNames(data, popup);

    setUpPlayersList(data, popup);

}

function readURLSearchParams() {

    let params = new URLSearchParams(document.location.search);


    let player_id = params.get('player_id');
    let match_id = params.get('match_id');
    let homeTeam_id = params.get('homeTeam_id');
    let homeTeam_name = params.get('homeTeam_name');
    let awayTeam_id = params.get('awayTeam_id');
    let awayTeam_name = params.get('awayTeam_name');
    let match_date = params.get('match_date');

    const data = [{
            name: 'player_id',
            value: player_id
        },
        {
            name: 'match_id',
            value: match_id
        },
        {
            name: 'homeTeam_id',
            value: homeTeam_id
        },
        {
            name: 'homeTeam_name',
            value: homeTeam_name
        },
        {
            name: 'awayTeam_id',
            value: awayTeam_id
        },
        {
            name: 'awayTeam_name',
            value: awayTeam_name
        },
        {
            name: 'match_date',
            value: match_date
        },
    ];


    return data;
}

function setModalData(data, popup) {

    //<input type="hidden" name="post_id" value="872">

    let placeholder = popup.querySelector('form.elementor-form');

    data.forEach(data => {
        let data_name = data.name;
        let data_value = data.value;
        let template = `<input type="hidden" name="${data_name}" value="${data_value}">`;
        placeholder.innerHTML += template;
    });

}

//https://scoremasters.gr
//http://scoremasters.test/wp-json/wp/v2/scm-pro-player?
//meta_key=scm-player-team&meta_value=109&per_page=30&_fields=id,status,type,featured_media,acf,title
//window.location


async function getPlayersList(team_id) {

    //let url = 'http://scoremasters.test/wp-json/wp/v2/scm-pro-player?';
    let url = currentDomain + '/wp-json/wp/v2/scm-pro-player?';
    let params = 'meta_key=scm-player-team&meta_value=' + team_id + '&per_page=30&_fields=id,status,type,featured_media,acf,title,position,points';

    let responce = await fetch(url + params, {
        method: 'GET',
        mode: 'cors',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json'
        }
    });

    let data = await responce.json();

    //console.log(data);

    return data;
}

function setUpPlayersList(data, popup) {

    let playersPlaceholder = popup.querySelector('select#form-field-scm_scorer');
    //console.log(playersPlaceholder);
    let documentFragPlayers = new DocumentFragment();

    // todo: delete
    /*
    let player_points_table = {
        "Επιθετικός": 3,
        "Μέσος": 4,
        "Αμυντικός": 8,
    };*/


    let homeTeam_id = data.filter(x => x.name == 'homeTeam_id')[0].value;
    let homeTeam_name = data.filter(x => x.name == 'homeTeam_name')[0].value;

    let awayTeam_id = data.filter(x => x.name == 'awayTeam_id')[0].value;
    let awayTeam_name = data.filter(x => x.name == 'awayTeam_name')[0].value;


    let awayTeamPlayers = getPlayersList(awayTeam_id);

    awayTeamPlayers.then(pdata => {
        console.log( pdata );

        let optionData = pdata.map(
            x => {
                let playerID = x.id;
                let playerName = x.title.rendered;

                // todo: delete player_points_table
                //let option = new Option(awayTeam_name + ' - ' + playerName + '   πόντοι: ' + player_points_table[x.position], playerID);
                let points = x.points;
                if(!points){
                    points = player_points_table[x.position];
                }

                let option = new Option(awayTeam_name + ' - ' + playerName + '   πόντοι: ' + points, playerID);
                option.dataset.position = x.position;
                option.dataset.points = x.points;
                return option;
            }
        );

        optionData.map(x => documentFragPlayers.appendChild(x));
        playersPlaceholder.appendChild(documentFragPlayers);

        get_submited_predictions(popup);

    });

    let homeTeamsPlayers = getPlayersList(homeTeam_id);

    console.log(homeTeamsPlayers);

    homeTeamsPlayers.then(pdata => {
        let optionData = pdata.map(
            x => {
                let playerID = x.id;
                let playerName = x.title.rendered;

                // todo: delete player_points_table
                //let option = new Option(homeTeam_name + ' - ' + playerName + '   πόντοι: ' + player_points_table[x.position], playerID);
                let points = x.points;
                if(!points){
                    points = player_points_table[x.position];
                }
                let option = new Option(homeTeam_name + ' - ' + playerName + '   πόντοι: ' + points, playerID);
                option.dataset.position = x.position;
                option.dataset.points = x.points;

                return option;
            }
        );

        optionData.map(x => documentFragPlayers.appendChild(x));
        playersPlaceholder.appendChild(documentFragPlayers);

    });

}

function setUpTeamsNames(data, popup) {

    let teamsNamesPlaceholder = popup.querySelector('div.elementor-element-d9d5193');
    if (!teamsNamesPlaceholder) return;

    let documentFragTeams = new DocumentFragment();

    let homeTeam_name = data.filter(x => x.name == 'homeTeam_name')[0].value;
    let awayTeam_name = data.filter(x => x.name == 'awayTeam_name')[0].value;

    let homeH3 = document.createElement('h3');
    homeH3.innerText = homeTeam_name;
    homeH3.className = 'home-team-name';

    let awayH3 = document.createElement('h3');
    awayH3.innerText = awayTeam_name;
    awayH3.className = 'away-team-name';

    let vs = document.createElement('h3');
    vs.innerText = 'VS';

    let teamsNames = document.createElement('div');
    teamsNames.className = 'team-names-container';
    teamsNames.appendChild(homeH3);
    teamsNames.appendChild(vs);
    teamsNames.appendChild(awayH3);


    documentFragTeams.appendChild(teamsNames);

    teamsNamesPlaceholder.prepend(documentFragTeams);
}


function possible_player_points(event, id, instance) {
    let popup = instance["$element"][0];

    let form = popup.querySelector('form');

    let shmeioSelect = popup.querySelector('#form-field-field_b324dff');
    shmeioSelect.addEventListener('change',calc_possible_points);
    let underOverSelect = popup.querySelector('#form-field-field_eba581d');
    underOverSelect.addEventListener('change',calc_possible_points);
    let scoreSelect = popup.querySelector('#form-field-field_4879a1e');
    scoreSelect.addEventListener('change',calc_possible_points);
    let scorerSelect = popup.querySelector('#form-field-scm_scorer');
    scorerSelect.addEventListener('change',calc_possible_points);

}

function calc_possible_points(event){

    let params = new URLSearchParams(document.location.search);
    let match_id = params.get('match_id');

    let match_el = document.querySelector('[data-match_id="'+ match_id +'"]'); //2417

    //selectedItem,match element
    let scoreTable = JSON.parse(document.getElementById('match_'+ match_id +'_pointstable').dataset.pointstable);
   
    // todo: delete;
    /*let player_points_table = {
        "Επιθετικός": 3,
        "Μέσος": 4,
        "Αμυντικός": 8,
    };*/

    let home_team_capability = match_el.querySelector('h4.scm-home-team').dataset.home_team_capability;
    let away_team_capability = match_el.querySelector('h4.scm-away-team').dataset.away_team_capability;

    
    let capabilityDiff = parseInt(home_team_capability) - parseInt(away_team_capability);

    let possible_points = scoreTable[capabilityDiff.toString()][event.target.value];

    //console.log(event);

    let parent = event.target.closest('div.elementor-field-type-select');
    let points_text = parent.querySelector('.scm-possible-points');

    if(event.target.id == 'form-field-scm_scorer'){
        let playerPosition = event.target.querySelector('[value="'+event.target.value+'"]');
        if(playerPosition.dataset.points !== ''){
            possible_points = playerPosition.dataset.points;
        }else{
            // todo: delete;
            possible_points = player_points_table[playerPosition.dataset.position];
        }
        
    }

    if(!points_text){
        points_text = document.createElement('span');
        points_text.classList.add('scm-possible-points');
    }

    
    if(!possible_points){
        possible_points = ' - ';
    }

    points_text.textContent = ' Πόντοι: ' + possible_points;

    parent.appendChild(points_text);

}

function getMatchDataFromURL(){
    let params = new URLSearchParams(document.location.search);
    let match_id = params.get('match_id');

    let match_el = document.querySelector('[data-match_id="'+ match_id +'"]'); //2417

    //selectedItem,match element
    let scoreTable = JSON.parse(document.getElementById('match_'+ match_id +'_pointstable').dataset.pointstable);

    let home_team_capability = match_el.querySelector('h4.scm-home-team').dataset.home_team_capability;
    let away_team_capability = match_el.querySelector('h4.scm-away-team').dataset.away_team_capability;

    
    let capabilityDiff = parseInt(home_team_capability) - parseInt(away_team_capability);

    return {
        'capabilityDiff': capabilityDiff,
        'scoreTable': scoreTable,
    } 
}


function in_option_render_points(){

   
    let data = getMatchDataFromURL();

    let simeioOptions = document.querySelectorAll('#form-field-field_b324dff option');
    let underoverOptions = document.querySelectorAll('#form-field-field_eba581d option');
    let scoreOptions = document.querySelectorAll('#form-field-field_4879a1e option');
    let playerOptions = document.querySelectorAll('#form-field-scm_scorer option');

    let fromScoreTable = [simeioOptions,underoverOptions,scoreOptions];

    console.log(playerOptions);

    fromScoreTable.map( formElement => [...formElement].map( option => showPossiblePointsInOption(option) ));

    function showPossiblePointsInOption ( option ){
        let points = data.scoreTable[data.capabilityDiff.toString()][option.value];

        if(!points){
            option.innerText += '';
            return;
        }

        option.innerText += '  |  πόντοι: ' + points;
    }

}