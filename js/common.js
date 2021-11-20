/*
(немного не разобрался с инициализацией по событию выше, поэтому сделал как умею)
add_event(document, 'DOMContentLoaded', common.init);

// PAGE

var common = {

    init: function() {
        console.log('you code here ...');
        // you code here ...
        
    }

}
*/

// загрузка елементов асинхронно через SetTimeout
document.addEventListener('DOMContentLoaded', commonCastom);

function commonCastom() {
    const DOC_ELEM_ARR = Array.from(document.querySelectorAll('.js-pre-animate'));
    const TIMER = (millsec) => new Promise(res => setTimeout(res, millsec));

    async function loadElemPage() { 
        for (let i = 0; i < DOC_ELEM_ARR.length; i++) {
            DOC_ELEM_ARR[i].classList.remove('js-pre-animate');
            DOC_ELEM_ARR[i].classList.add('js-load-animate');
            await TIMER(350);
        }
    }
    
    loadElemPage();
}

