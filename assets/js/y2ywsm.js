/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


(function($) {
    $(document).ready(function(){
        $( ".dtBox" ).DateTimePicker();
        $("#dtBox").DateTimePicker({
            dateFormat: 'dd/mm/yy',
            minDate: 0
        });
        
        if($('#shipping_method_0_you2you').is(':checked')){
            $("#calendar_y2y").css("display","block");
        };
        
        $(document).on('click', "input[name='shipping_method[0]']", function(){
            if($(this).val() === 'You2You')
            {
                $("#calendar_y2y").css("display","block");
                $('html, body').animate({
                    scrollTop: $("#calendar_y2y").offset().top-70
                }, 1000);
            }
            else
            {
                $("#calendar_y2y").css("display","none");
            }
        });
    });
})(jQuery);