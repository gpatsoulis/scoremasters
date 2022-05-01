let btns = document.querySelectorAll('.activate-prediction-popup');

[...btns].map( x => x.addEventListener('click',activatePopup));

function activatePopup(event){
    elementorFrontend.documentsManager.documents[872].showModal();
}

jQuery(document).ready(function ($) {

    safariFix();

    $(document).on('elementor/popup/show', {
        data: "ns4u data"
    }, onPopupEvent);
   
});

function onPopupEvent(event, id, instance) {
    //console.log(event, id);
    // populate popup with match data
    editPopupContent(event, id, instance);

}

function safariFix() {
    let buttons = document.querySelectorAll('.activate-prediction-popup');
    [...buttons].map(x => x.addEventListener('click', setProductURLData));
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

    let awayTeam = parentDataEl.querySelector('.scm-away-team');

    if (!awayTeam) {
        return;
    }

    let awayTeam_id = awayTeam.dataset.team_id;

    let match = parentDataEl.querySelector('.scm-fixture-list-row');

    if (!match) {
        return;
    }

    let match_id = match.dataset.match_id;
    let match_date_gmt = match.dataset.match_date_gmt;

    const url = new URL(window.location);

    url.searchParams.set('player_id', player_id);
    url.searchParams.set('match_id', match_id);
    url.searchParams.set('homeTeam_id', homeTeam_id);
    url.searchParams.set('awayTeam_id', awayTeam_id);
    url.searchParams.set('match_date_gmt', match_date_gmt);

    window.history.pushState({}, '', url);

}


function editPopupContent(event, id, instance) {

    if (id !== 872) return;

        let data = readURLSearchParams();

        let popup = instance["$element"][0];

        setModalData(data, popup);

}

function readURLSearchParams() {

    let params = new URLSearchParams(document.location.search);


    let player_id = params.get('player_id');
    let match_id = params.get('match_id');
    let homeTeam_id = params.get('homeTeam_id');
    let awayTeam_id = params.get('awayTeam_id');
    let match_date_gmt = params.get('match_date_gmt');

    const data = [
        {name:'player_id',value:player_id},
        {name:'match_id',value:match_id},
        {name:'homeTeam_id',value:homeTeam_id},
        {name:'awayTeam_id',value:awayTeam_id},
        {name:'match_date_gmt',value:match_date_gmt},
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