export {disable_form_on_date};

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