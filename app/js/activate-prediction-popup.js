let btns = document.querySelectorAll('.activate-prediction-popup');

[...btns].map( x => {
    //safari fix
    x.addEventListener('click', setProductURLData);
    x.addEventListener('click',activatePopup);
});

function activatePopup(event){
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
}

/*
function safariFix() {
    let buttons = document.querySelectorAll('.activate-prediction-popup');
    [...buttons].map(x => x.addEventListener('click', setProductURLData));
}
*/

function get_submited_predictions(popup){

    //let popup = instance["$element"][0];

    let player_id = popup.querySelector('input[name="player_id"]').value;
    
    let match_id = popup.querySelector('input[name="match_id"]').value;

    let prediction_title = match_id + '-' + player_id;

    //https://scoremasters.gr
    //let url = 'http://scoremasters.test/wp-json/scm/v1/scm_prediction_title/'+prediction_title;
    let url = 'https://scoremasters.gr/wp-json/scm/v1/scm_prediction_title/'+prediction_title;
    console.log(url);

    let responce =  fetch(url,{
        method: 'GET',
        //mode: 'cors',
        //credentials: 'same-origin',
        //headers: {
        //    'Content-Type': 'application/json'
         // }
    })
    .then( data => data.json())
    .then( data => {
        console.log(data);
    
    
        render_submited_prediction_data(popup,data);
    
    }
        
        );

    //let data = await responce.json();


}

function render_submited_prediction_data(popup,data){
    
    //find select elements by text content
    let array_of_labels = popup.querySelectorAll('label');
    //console.log(data);

    for (const [key,value] of Object.entries(data)){
        
        let select_id = find_element_by_text_content(key,[...array_of_labels]);

        if(select_id == ''){
            continue;
        }

        select = popup.querySelector('select#'+select_id);

        console.log(select);
        console.log('option[value="'+value+'"]');

        if(value == ''){
            continue;
        }

        let option = select.querySelector('option[value="'+value+'"');
        console.log(option);
        option.selected = true;
    }
    
    
}

function find_element_by_text_content(prediction_text,array_of_labels){
    //console.log(prediction_text);
    //x.textContet.includes(prediction_text
    let label =  array_of_labels.filter( x => {
        //console.log(x.textContent);
        if(x.textContent.includes(prediction_text)){
             return x;
        }
    });

    //console.log(label);

    if(label.length === 0){
        return '';
    }

    select_id = label[0].htmlFor;
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

    const data = [
        {name:'player_id',value:player_id},
        {name:'match_id',value:match_id},
        {name:'homeTeam_id',value:homeTeam_id},
        {name:'homeTeam_name',value:homeTeam_name},
        {name:'awayTeam_id',value:awayTeam_id},
        {name:'awayTeam_name',value:awayTeam_name},
        {name:'match_date',value:match_date},
    ];
        

    return data;
}

function setModalData(data, popup) {

    //<input type="hidden" name="post_id" value="872">

    let placeholder = popup.querySelector('form.elementor-form');

    data.forEach( data => {
        let data_name = data.name;
        let data_value = data.value;
        let template = `<input type="hidden" name="${data_name}" value="${data_value}">`;
        placeholder.innerHTML += template;
    });

}

//https://scoremasters.gr
//http://scoremasters.test/wp-json/wp/v2/scm-pro-player?
//meta_key=scm-player-team&meta_value=109&per_page=30&_fields=id,status,type,featured_media,acf,title


async function getPlayersList(team_id){

    //let url = 'http://scoremasters.test/wp-json/wp/v2/scm-pro-player?';
    let url = 'https://scoremasters.gr/wp-json/wp/v2/scm-pro-player?';
    let params = 'meta_key=scm-player-team&meta_value=' + team_id + '&per_page=30&_fields=id,status,type,featured_media,acf,title';

    let responce = await fetch(url+params,{
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

function setUpPlayersList(data, popup){

    let playersPlaceholder = popup.querySelector('select#form-field-scm_scorer');
    console.log(playersPlaceholder);
    let documentFragPlayers = new DocumentFragment();
    


    let homeTeam_id = data.filter( x => x.name == 'homeTeam_id')[0].value;
    let homeTeam_name = data.filter( x => x.name == 'homeTeam_name')[0].value;
    
    let awayTeam_id = data.filter( x => x.name == 'awayTeam_id')[0].value;
    let awayTeam_name = data.filter( x => x.name == 'awayTeam_name')[0].value;
   

    let awayTeamPlayers = getPlayersList(awayTeam_id);
    //console.log(awayTeamPlayers);
    awayTeamPlayers.then( pdata => {
        let optionData = pdata.map(
            x => {
                let playerID = x.id;
                let playerName = x.title.rendered;
        
                let option = new Option(awayTeam_name +' - '+ playerName,playerID);
            
                return option;
            }
        );

        optionData.map( x => documentFragPlayers.appendChild( x ));
        playersPlaceholder.appendChild(documentFragPlayers);

        get_submited_predictions(popup);

    });

    let homeTeamsPlayers = getPlayersList(homeTeam_id);

    homeTeamsPlayers.then( pdata => {
        let optionData = pdata.map(
            x => {
                let playerID = x.id;
                let playerName = x.title.rendered;
        
                let option = new Option(homeTeam_name + ' - ' + playerName,playerID);
            
                return option;
            }
        );

        optionData.map( x => documentFragPlayers.appendChild( x ));
        playersPlaceholder.appendChild(documentFragPlayers);

    });

 }

 function setUpTeamsNames(data, popup){

    let teamsNamesPlaceholder = popup.querySelector('div.elementor-element-d9d5193');
    if(!teamsNamesPlaceholder) return;

    let documentFragTeams = new DocumentFragment();

    let homeTeam_name = data.filter( x => x.name == 'homeTeam_name')[0].value;
    let awayTeam_name = data.filter( x => x.name == 'awayTeam_name')[0].value;

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

 function restrictions(event, id, instance){

    let popup = instance["$element"][0];
    console.log(popup);

    let shmeioSelect = popup.querySelector('#form-field-field_b324dff');

    let underOverSelect = popup.querySelector('#form-field-field_eba581d');

    let scoreSelect = popup.querySelector('#form-field-field_4879a1e');

    let scorerSelect = popup.querySelector('#form-field-scm_scorer');

    //let doubleSelect = popup.querySelector('#form-field-field_c61b597');


    //[5/5, 13:50] Tassos Mountakis: Δε μπορούν να βάλουν σημείο άσσο και σκορ 0-1
    //[5/5, 13:50] Tassos Mountakis: Δε μπορούν να επιλέξουν σκορ και Άντερ/οβερ
    //[5/5, 13:51] Tassos Mountakis: Δε μπορούν να επιλέξουν σκόρερ της φιλοξενούμενης ομάδας αν πχ έχουν επιλέξει σκορ 2-0
    //[5/5, 13:52] Tassos Mountakis: Αν επιλέξουν σκορ 0-0, δεν μπορούν να επιλέξουν σκόρερ. Και φυσικά να απενεργοποιείται η επιλογή διπλασιασμό στο σκόρερ


    shmeioSelect.addEventListener( 'change', shmeioSelect_restrictions );

    function shmeioSelect_restrictions(event){
        set_reset_first_to_first_option(scoreSelect);

        if(event.target.value !== '-'){
            //disable under/over select
            set_reset_first_to_first_option(underOverSelect);
            underOverSelect.disabled = true;
        }

        if(event.target.value === '-'){
            //disable under/over select
            
            set_reset_first_to_first_option(underOverSelect);
            underOverSelect.disabled = false;
            let optionsDisble = scoreSelect.querySelectorAll('option');
            [...optionsDisble].map( x => x.disabled = false);
        }

        console.log(event.target.value);
        if(['-/1','1/1','X/1','2/1'].includes(event.target.value) ){
            //shmeioSelect.disabled = false;
            let optionsDisble = scoreSelect.querySelectorAll('option');
            [...optionsDisble].map( x => {
                x.disabled = false;

                if(['0-1','0-2','0-3','0-4','1-2','1-3','1-4','2-3','2-4','3-4'].includes(x.value)){
                    x.disabled = true;
                }
                if(['0-0','1-1','2-2','3-3'].includes(x.value)){
                    x.disabled = true;
                }
                
            });
        }

        if(['-/2','2/2','X/2','1/2'].includes(event.target.value) ){
            //shmeioSelect.disabled = false;
            let optionsDisble = scoreSelect.querySelectorAll('option');
            [...optionsDisble].map( x => {
                x.disabled = true;

                if(['0-1','0-2','0-3','0-4','1-2','1-3','1-4','2-3','2-4','3-4'].includes(x.value)){
                    x.disabled = false;
                }
                if(['0-0','1-1','2-2','3-3'].includes(x.value)){
                    x.disabled = true;
                }
                
            });
        }

        if(['-/X','1/X','X/X','2/X'].includes(event.target.value) ){
            //shmeioSelect.disabled = false;
            let optionsDisble = scoreSelect.querySelectorAll('option');
            [...optionsDisble].map( x => {

                x.disabled = true;

                if(['0-0','1-1','2-2','3-3'].includes(x.value)){
                    x.disabled = false;
                }
                
            });
        }
    }

    underOverSelect.addEventListener( 'change', underOverSelect_restrictions );

    function underOverSelect_restrictions(event) {
        if(event.target.value !== '-'){
            //disable shmeio select
            set_reset_first_to_first_option(shmeioSelect);
            shmeioSelect.disabled = true;
        }

        if(event.target.value === '-'){
            //disable shmeio select
            shmeioSelect.disabled = false;
        }
    }

    scoreSelect.addEventListener( 'change', scoreSelect_restrictions );

    function scoreSelect_restrictions(event){
        if(event.target.value === '0-0'){
            //disable scorer select
            set_reset_first_to_first_option(scoreSelect);
            scorerSelect.disabled = true;

            //let optionScorer = doubleSelect.querySelector('option[value="SCORER"]');
            //optionScorer.disabled = true;
        }

        if(event.target.value !== '0-0'){
            scorerSelect.disabled = false;

            //let optionScorer = doubleSelect.querySelector('option[value="SCORER"]');
            //optionScorer.disabled = false;
            
        }
    }

 }

 function set_reset_first_to_first_option(select){
    options = select.querySelectorAll('option');
    [...options].map( x => {
        if(x.selected == true){
            x.selected = false;
        }
    });

    options[0].selected = true;
 }


 function disable_form_on_date(event, id, instance){
    let popup = instance["$element"][0];

    let button = popup.querySelector('button[type="submit"]');

    button.addEventListener('click',disableBtn);

    function disableBtn(event){

        let current = new Date();
        //current = current.toUTCString();
        console.log(current);
    
        let matchDateTimestamp = popup.querySelector('input[name="match_date"]');
        console.log(matchDateTimestamp.value);
    
        let matchDate = new Date(parseInt(matchDateTimestamp.value * 1000));
        matchDate.setTime(matchDate.getTime() + matchDate.getTimezoneOffset()*60*1000 );
        //matchDate.setTime(matchDate.getTime() + 180*60*1000 );
    
        console.log(matchDate);
    
        if(current.getTime() >= matchDate.getTime()){
            console.log('match time');
            button.disabled = true;
        }
     }

 }