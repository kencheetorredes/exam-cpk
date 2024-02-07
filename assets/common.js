/*
Author: kenchee torredes
Email: kencheetorredes@gmail.com
File: js
*/

onloadpage();
$(document).on('click', '.uploadBtn',function(e) {

    var $this   = $(this),
        form    = typeof $this.data('form')  === 'undefined' ? $this.closest('form') : $('#' + $this.data('form') + ''),
        action  = form.attr('action'),
        method  = form.attr('method').toLowerCase(),
        data    = form.serialize();
        spinner_load($this);
        $('#loader_modal').modal({backdrop: 'static', keyboard: false});
        var responce = DataWithIMageHadler(form, method, action, data); 

        responce.done(function (response, textStatus, jqXHR) {
            onloadpage();
            spinner_out($this);
            clearForm(form);
        }).fail(function(xhr, textStatus, errorThrown) {
            alert(xhr.responseText);
            onloadpage();
            spinner_out($this);
        });

    e.preventDefault();
});


function onloadpage(){
    let  onloadpage  = $('.onloadpage');
    if(typeof onloadpage  !== 'undefined'){
        onloadpage.each(function(e){
            $(this).html('<center> <i class="fa fa-spinner fa-3x fa-spin"></i> <br>loading  please wait</center>');
            $(this).load($(this).data('url'));
        });
    }
}



/**
 * handle ajax with images
 * @param {*} form 
 * @param {*} method 
 * @param {*} action 
 * @param {*} data 
 * return array
 */
function DataWithIMageHadler(form, method, action, data){

    var formData = new FormData(form[0]);
    var responses = '';
  
    return $.ajax({
        type: method, 
        url: action, 
        data: formData,
        contentType: false, 
        processData: false
    });

    
}

function spinner_load($this){
    $this.prepend('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    $this.attr('disabled','disabled');
}

function spinner_out($this){
    $this.find('.spinner-border').remove();
    $this.removeAttr('disabled');
}

function clearForm(form){
    form.find('input,select,textarea,file').each(function (e) {
        var $thiss = $(this);
        $thiss.val('');
    });
}