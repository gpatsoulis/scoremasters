let btns = document.querySelectorAll('.activate-prediction-popup');

[...btns].map( x => x.addEventListener('click',activatePopup));

function activatePopup(event){
    elementorFrontend.documentsManager.documents[872].showModal();
}