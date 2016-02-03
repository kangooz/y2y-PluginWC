(function($) {
    $(document).ready(function(){
       $("#delivery_date").parent().append('<div style="display:none;" class="y2ywsm-datepicker-holder"></div>');
        
        
        $('#delivery_date').attr('data-field', 'datetime');
        $( ".y2ywsm-timepicker-holder" ).DateTimePicker({
            mode: "time",
            language: options.lang,
            timeFormat: 'HH:mm',
        });
        $(".y2ywsm-datepicker-holder").DateTimePicker({
            mode: "date",
            dateFormat: 'dd/mm/yy',
            minDate: 0,
            language: options.lang,
        });
        
        if($('#shipping_method_0_you2you').is(':checked')){
            $("#delivery_date").css("display","block");
            $("#delivery_date").parent().find('label[for=delivery_date]').css("display","block");
        }
        else
        {
            $("#delivery_date").css("display","none");
            $("#delivery_date").parent().find('label[for=delivery_date]').css("display","none");
        };
        
        $(document).on('click', "input[name='shipping_method[0]']", function(){
            if($(this).val() === 'You2You')
            {
                $("#delivery_date").parent().find('label[for=delivery_date]').css("display","block");
                $("#delivery_date").css("display","block");
                $('html, body').animate({
                    scrollTop: $("#delivery_date").offset().top-100
                }, 1000);
            }
            else
            {
                $("#delivery_date").css("display","none");
                $("#delivery_date").parent().find('label[for=delivery_date]').css("display","none");
            }
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
