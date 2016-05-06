(function($) {
    $(document).ready(function(){
        //$("#delivery_date").parent().append('<div style="display:none;" class="y2ywsm-datepicker-holder"></div>');

        //$('#delivery_date').attr('data-field', 'datetime');
        $("#hidden_date_field").css('display','none');
        $("#hidden_time_field").css('display','none');
        $("#delivery_date_field").css('display','none');
        $("#delivery_date").parent().parent().append('<button class="call-modal">Choisir la date de livraison</button>');
        $("#delivery_date").parent().parent().append('<div id="modal" style="display:none">'
                                                    +'<div id="calendar" style="display: inline; float: left; width:50%;"></div>'
                                                    +'<div class="time" style="display: inline; float: right; width:45%;"></div>'
                                                    +'<div style="width:100%;display:table; padding:4px; float: right;">'
                                                        +'<input type="button" value="Choisir" onclick="select_time()">'
                                                    +'</div>'
                                                +'</div>');
        $('.call-modal').parent().append('<div id="sentence"></div>');
        
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
        
        var cal = $('#calendar').datepicker({
            minDate: minDate,
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
            },
            onSelect: function(date) {
                $("#hidden_date").trigger("change");
            }
        });
        $('.call-modal').on('click', function(event) {
            event.preventDefault();
            $("#hidden_date").trigger("change");
            $("#modal .ui-dialog-titlebar").css('display','none');
            $('#modal').dialog({ width: '45%' });
            $(".ui-dialog-titlebar").css('display','none');
        });
        /*
        $('#delivery_date').datepicker({
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
        });*/
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
        
        $("#hidden_date").change(function() {
            val = $("#hidden_date").val();
            choosen_date = val.split('-');
            monthpos = choosen_date[1].replace(/^0+/, '');
            choosen_day = choosen_date[2].replace(/^0+/, '');
            time_sent = '';
            rawtime = $("#hidden_time").val();
            time = rawtime;
            if(time!==''){
                time = time.split('-');
                time_sent = time[0].toString().replace('h',':')+":00";
                time = 'Veuillez vous rendre disponible de '+time[0]+' à '+time[1]+'.';
            }
            $("#delivery_date").val(val+" "+time_sent);
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
            var week = [
                'dimanche',
                'lundi',
                'mardi',
                'mercredi',
                'jeudi',
                'vendredi',
                'samedi'
            ];
            
            var year = choosen_date[0];
            var dayofthemonth = choosen_day;
            choosen_day = moment(val).day();
            var dayoftheweek = week[choosen_day];
            var month = months[monthpos-1];
            
            //$("#delivery_date").val("Vous avez choisi de recevoir l'article "+choosen_day+" "+months[monthpos-1]+""+time);
            $("#sentence").html("Vous avez choisit le "+dayoftheweek+" "+dayofthemonth+" "+month+" "+year+". "+time);
            
            var times = [];
            
            beg_hour = options.hours.openning_hours_beginning[choosen_day];
            end_hour = options.hours.openning_hours_endding[choosen_day];
            lunch_beg = options.hours.lunch_time_beginning[choosen_day];
            lunch_end = options.hours.lunch_time_endding[choosen_day];
            
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
                        times.push(moment(now,'HH:mm').format('HH:mm').replace(':','h')+" - "+moment(now,'HH:mm').add(1,'hour').format('HH:mm').replace(':','h'));
                        add = 1;
                    }
                }
                
                //afternnoon
                add = timeout;
                if(moment(now,'HH:mm') < moment(lunch_end,'HH:mm')){
                    now = lunch_end;
                }
                while(moment(now,'HH:mm').add(add,'hour') < moment(end_hour,'HH:mm')){
                    if(moment(now,'HH:mm').add(add,'hour') < moment(end_hour,'HH:mm')){
                        now = moment(now,'HH:mm').add(add,'hour');
                        times.push(moment(now,'HH:mm').format('HH:mm').replace(':','h')+" - "+moment(now,'HH:mm').add(1,'hour').format('HH:mm').replace(':','h'));
                        add = 1;
                    }
                }
            }
            else
            {
                var add = timeout;
                //morning
                while(moment(beg_hour,'HH:mm').add(1,'hour') < moment(lunch_beg,'HH:mm')){
                    if(moment(beg_hour,'HH:mm').add(1,'hour') < moment(lunch_beg,'HH:mm')){
                        beg_hour = moment(beg_hour,'HH:mm').add(add,'hour');
                        times.push(moment(beg_hour,'HH:mm').format('HH:mm').replace(':','h')+" - "+moment(beg_hour,'HH:mm').add(1,'hour').format('HH:mm').replace(':','h'));
                        add = 1;
                    }
                }
                
                var add = timeout;
                //afeternoon
                while(moment(lunch_end,'HH:mm').add(1,'hour') < moment(end_hour,'HH:mm')){
                    if(moment(lunch_end,'HH:mm').add(1,'hour') < moment(end_hour,'HH:mm')){
                        lunch_end = moment(lunch_end,'HH:mm').add(add,'hour');
                        times.push(moment(lunch_end,'HH:mm').format('HH:mm').replace(':','h')+" - "+moment(lunch_end,'HH:mm').add(1,'hour').format('HH:mm').replace(':','h'));
                        add = 1;
                    }
                }
                
            }
            
            if($( ".radio-buttons" ).length === 0){
                $("#modal .time").append('<div class="radio-buttons"></div>');
            }
            
            var radiobtns = '';
            for (i = 0; i < times.length; i++){
                span = times[i].split(' - ');
                if(rawtime === (span[0]+'-'+span[1]) || i===0){
                    checked='checked="checked"';
                }else{
                    checked='';
                }
                
                radiobtns += '<div class="buttonsetv" name="radio-group-'+i+'">'
                                    +'<input type="radio" id="time'+i+'" name="time" '+checked+' value="'+span[0]+'-'+span[1]+'">'+'\
                                    <label for="time'+i+'">'+times[i]+'</label>'
                            +'</div>';
            }
            $(".radio-buttons").html(radiobtns);
            $('.buttonsetv').buttonsetv();
        });
        
        checkShippingMethod();
        

        $(document).on('click', "input[name='shipping_method[0]']", function(){
            
            /*if($(this).val() === 'You2You')
            {
                $('#delivery_date_field').show();
                
                $('html, body').animate({
                    scrollTop: $("#delivery_date").offset().top-100
                }, 1000);
            }
            else
            {
                $('#delivery_date_field').hide();
            }*/
        });
            
        $(document.body).on('updated_checkout', checkShippingMethod);
        
        function checkShippingMethod(e){
            if($("input[name='shipping_method[0]'][value=You2You]").length > 0){
                if($("input[name='shipping_method[0]']").length > 1){
                    if($("input[name='shipping_method[0]'][value=You2You]").is(':checked')){
                        //$('#delivery_date_field').show();
                    }
                    else
                    {
                        //$('#delivery_date_field').hide();
                    };
                }else{
                    //$('#delivery_date_field').show();
                }


            }else{
                $('#delivery_date_field').hide();
            }
        }
    });
})(jQuery);

function select_time()
{
    time_sel = jQuery('.radio-buttons input[name=time]:checked').val();
    jQuery('#hidden_time').val(time_sel);
    jQuery("#hidden_date").trigger("change");
    jQuery("#modal").dialog('close');
}