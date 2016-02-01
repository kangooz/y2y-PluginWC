/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


(function($) {
    $(document).ready(function(){/*
        $(".shipping_method").click(function(){
            alert("ola");
        });*/
        $("#shipping_method").parent().append(
                        '<input type="text" style="display:none;" name="calendar_y2y" id="calendar_y2y" data-field="datetime" readonly>\n\
                         <div style="display:none;" id="dtBox"></div>' );
        $("#dtBox").DateTimePicker({
            dateFormat: 'dd/mm/yy'
        });
        if($('#shipping_method_0_you2you').is(':checked')) { alert("it's checked"); }
        if($('#shipping_method_0_free_shipping').is(':checked')) { alert("it's checked2"); }
        if($('#shipping_method_0_flat_rate').is(':checked')) { alert("it's checked3"); }
        
        $("#shipping_method_0_you2you, #shipping_method_0_free_shipping, #shipping_method_0_flat_rate").click(function(){
            console.debug("ola");
            if($('#shipping_method').val() === 'You2You')
            {
                $("#calendar_y2y").css("display","block");
                $("#dtBox").css("display","block");
            }
            else
            {
                $("#calendar_y2y").css("display","none");
                $("#dtBox").css("display","none");
            }
        });
    });
})(jQuery);