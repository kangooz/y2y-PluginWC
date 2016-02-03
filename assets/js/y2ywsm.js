/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


(function($) {
    $(document).ready(function(){
        
        
       $("#delivery_date").parent().append('<div style="display:none;" id="dtBox"></div>');
        
        $('#delivery_date').attr('data-field', 'datetime');
        $( ".dtBox" ).DateTimePicker();
        $("#dtBox").DateTimePicker({
            dateFormat: 'dd/mm/yy',
            minDate: 0
        });
        //$( "#delivery_date" ).DateTimePicker();
        
        /*if($('#shipping_method_0_you2you').is(':checked')){
            $("#delivery_date").css("display","block");
        };
        
        $(document).on('click', "input[name='shipping_method[0]']", function(){
            if($(this).val() === 'You2You')
            {
                $("#delivery_date").css("display","block");
                $('html, body').animate({
                    scrollTop: $("#delivery_date").offset().top-70
                }, 1000);
            }
            else
            {
                $("#delivery_date").css("display","none");
            }
        });*/
    });
})(jQuery);