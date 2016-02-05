(function($) {
    $(document).ready(function(){
       $("#delivery_date").parent().append('<div style="display:none;" class="y2ywsm-datepicker-holder"></div>');
        
        $('#delivery_date').attr('data-field', 'datetime');
        $( ".y2ywsm-timepicker-holder" ).DateTimePicker({
            mode: "time",
            language: options.lang,
            timeFormat: 'HH:mm',
            minuteInterval: 15,
            roundOffMinutes: true
        });
        $(".y2ywsm-datepicker-holder").DateTimePicker({
            mode: "datetime",
            dateTimeFormat: 'dd/MM/yyyy HH:mm',
            minDateTime: options.dateTimePicker.minDateTime,
            language: options.lang,
            minuteInterval: 15,
            roundOffMinutes: true,
            defaultDate: options.dateTimePicker.defaultValue
        });
        
        if($("input[name='shipping_method[0]'][value=You2You]").length > 0){
            if($("input[name='shipping_method[0]'][value=You2You]").is(':checked')){
                $('#delivery_date_field').show();
            }
            else
            {
                $('#delivery_date_field').hide();
            };
        }else{
            $('#delivery_date_field').hide();
        }
        
        
        
        $(document).on('click', "input[name='shipping_method[0]']", function(){
            if($(this).val() === 'You2You')
            {
                $('#delivery_date_field').show();
                
                $('html, body').animate({
                    scrollTop: $("#delivery_date").offset().top-100
                }, 1000);
            }
            else
            {
                $('#delivery_date_field').hide();
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
