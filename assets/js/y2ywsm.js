(function($) {
    $(document).ready(function(){
        //$("#delivery_date").parent().append('<div style="display:none;" class="y2ywsm-datepicker-holder"></div>');

        //$('#delivery_date').attr('data-field', 'datetime');
        $("#hidden_date_field").css('display','none');
        $("#hidden_time_field").css('display','none');
        
        closed_days = '';
        cd = $.map(options.hours.closed_day, function(value, index) {
            return [Number(index)];
        });
        for(i=0;i<cd.length;i++)
        {
            if(closed_days==='')
            {
                closed_days+='day != '+cd[i];
            }
            else{
                closed_days+= ' && day != '+cd[i];
            }
        }
        /*
        console.debug(options.hours.closed_day);
        for (i = 0; i < options.hours.closed_day.length; i++) {
            closed_days += options.hours.closed_day[i];
        }*/
        //console.debug(cd);
        //console.debug(options.hours.openning_hours_endding[day]);
        
        today = moment();
        var today_week = moment(today).format('e');
        var timeout = Number(options.hours.timeout);
        var nowtimeout = moment(today,'HH:mm').add(timeout,'hour').format('HH:mm');
        var now = moment(today).format('HH:mm');
        
        if(options.hours.openning_hours_endding[today_week]>nowtimeout){
            minDate = 0;
        }
        else{
            minDate = 1;
        }
        
        $('#sentence').datepicker({
            showOn: "button",
            minDate: minDate,
            buttonImage: options.calendar_img,
            buttonImageOnly: true,
            altField: "#hidden_date",
            altFormat: "yy-mm-dd",
            buttonText: "Select date",
            beforeShowDay: function(date) {
                var day = date.getDay();
                if ($.inArray(day, cd) === -1) {
                  return [true, "","Available"];
                } else {
                  return [false,"","unAvailable"];
                }
            }
        });
        /*
        $( "#delivery_date" ).focus(function() {
            //$('.ui-datepicker-trigger').click();
        });*/
        
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
        
        $("#sentence, #hidden_date").change(function() {
            val = $("#hidden_date").val();
            choosen_date = val.split('-');
            monthpos = choosen_date[1].replace(/^0+/, '');
            dayoftheweek = choosen_date[2].replace(/^0+/, '');
            
            time = $("#hidden_time").val();
            console.debug(time);
            if(time!=='')
            {
                time = ' à '+time;
            }
            var months = [
                'Janvier',
                'Février',
                'Mars',
                'Avril',
                'Mai',
                'Juin',
                'Juillet',
                'Août',
                'Septembre',
                'Octobre',
                'Novembre',
                'Décembre'
            ];
            //$("#sentence").val("Vous avez choisi de recevoir l'article "+dayoftheweek+" "+months[monthpos-1]+""+time);
            $("#sentence").val("Vous avez choisi de recevoir l'article "+dayoftheweek+" "+months[monthpos-1]+""+time);
            var times = [];
            
            dayoftheweek = moment(val).day();
            console.log('dia da semana: '+dayoftheweek);
            
            //minDate = 1;
            beg_hour = options.hours.openning_hours_beginning[dayoftheweek];
            end_hour = options.hours.openning_hours_endding[dayoftheweek];
            lunch_beg = options.hours.lunch_time_beginning[dayoftheweek];
            lunch_end = options.hours.lunch_time_endding[dayoftheweek];
            
            var add = timeout;
            var now = moment(today).format('HH:mm');
            
            if(moment(today).format('YYYY-MM-DD') === val){
                //today
                
                //morning
                if(moment(now,'HH:mm') < moment(beg_hour,'HH:mm')){
                    now = beg_hour;
                }
                
                while(moment(now,'HH:mm').add(add,'hour') < moment(lunch_beg,'HH:mm')){
                    if(moment(now,'HH:mm').add(add,'hour') < moment(lunch_beg,'HH:mm')){
                        now = moment(now,'HH:mm').add(add,'hour');
                        times.push(moment(now,'HH:mm').format('HH:mm'));
                        add = 1;
                    }
                }
                
                //afternnoon
                addt = timeout;
                if(moment(now,'HH:mm') < moment(lunch_end,'HH:mm')){
                    now = lunch_end;
                }
                while(moment(now,'HH:mm').add(add,'hour') < moment(end_hour,'HH:mm')){
                    if(moment(now,'HH:mm').add(add,'hour') < moment(end_hour,'HH:mm')){
                        now = moment(now,'HH:mm').add(add,'hour');
                        times.push(moment(now,'HH:mm').format('HH:mm'));
                        add = 1;
                    }
                }
            }
            else
            {
                var add = timeout;
                //morning
                console.debug('1');
                while(moment(beg_hour,'HH:mm').add(1,'hour') < moment(lunch_beg,'HH:mm')){
                    if(moment(beg_hour,'HH:mm').add(1,'hour') < moment(lunch_beg,'HH:mm')){
                        beg_hour = moment(beg_hour,'HH:mm').add(1,'hour');
                        times.push(moment(beg_hour,'HH:mm').format('HH:mm'));
                        add = 1;
                    }
                }
                
                var add = timeout;
                //afeternoon
                while(moment(lunch_end,'HH:mm').add(1,'hour') < moment(end_hour,'HH:mm')){
                    if(moment(lunch_end,'HH:mm').add(1,'hour') < moment(end_hour,'HH:mm')){
                        lunch_end = moment(lunch_end,'HH:mm').add(1,'hour');
                        times.push(moment(lunch_end,'HH:mm').format('HH:mm'));
                        add = 1;
                    }
                }
                
            }
            
            if($( ".radio-buttons" ).length === 0){
                $("#sentence").parent().append('<div class="radio-buttons" style="display:none" title="Time"></div>');
            }
            
            var radiobtns = '';
            for (i = 0; i < times.length; i++) {
                radiobtns += '<input type="radio" id="time" name="time" value="'+times[i]+'">'+times[i]+'<br>';
            }
            radiobtns += '<input type="button" value="Choose" onclick="select_time()">';
            $(".radio-buttons").html(radiobtns).dialog();
        });
        
        checkShippingMethod();
        

        $(document).on('click', "input[name='shipping_method[0]']", function(){
            
            if($(this).val() === 'You2You')
            {
                $('#sentence_field').show();
                
                $('html, body').animate({
                    scrollTop: $("#sentence").offset().top-100
                }, 1000);
            }
            else
            {
                $('#sentence_field').hide();
            }
        });
            
        $(document.body).on('updated_checkout', checkShippingMethod);
        
        function checkShippingMethod(e){
            if($("input[name='shipping_method[0]'][value=You2You]").length > 0){
                if($("input[name='shipping_method[0]']").length > 1){
                    if($("input[name='shipping_method[0]'][value=You2You]").is(':checked')){
                        $('#sentence_field').show();
                    }
                    else
                    {
                        $('#sentence_field').hide();
                    };
                }else{
                    $('#sentence_field').show();
                }


            }else{
                $('#sentence_field').hide();
            }
        }
    });
})(jQuery);

function select_time()
{
    time_sel = jQuery('.radio-buttons input[name=time]:checked').val();
    jQuery('#hidden_time').val(time_sel);
    jQuery("#hidden_date").trigger("change");
    jQuery(".radio-buttons").dialog('close');
}