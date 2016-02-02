/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


(function($) {
    $(document).ready(function(){
        $("#order_comments_field").parent().append(
                        '<input type="text" style="display:none;" name="calendar_y2y" id="calendar_y2y" data-field="datetime" readonly placeholder="Choose date and time">\n\
                         <div style="display:none;" id="dtBox"></div>' );
        $("#dtBox").DateTimePicker({
            dateFormat: 'dd/mm/yy'
        });
        
        if($('#shipping_method_0_you2you').is(':checked')){
            console.log("ola");
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