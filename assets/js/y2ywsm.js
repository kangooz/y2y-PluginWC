(function($) {
    $(document).ready(function(){
        //$("#delivery_date").parent().append('<div style="display:none;" class="y2ywsm-datepicker-holder"></div>');

        //$('#delivery_date').attr('data-field', 'datetime');
        $("#hidden_date_field").css('display','none');
        $("#hidden_time_field").css('display','none');
        $("#delivery_date").css('display','none');
        var div = $('<div/>', {id: "y2y_module"});
        var hidden_date_field = $("#hidden_date_field");
        var hidden_time_field = $("#hidden_time_field");
        var delivery_date = $("#delivery_date");
        $("#hidden_date_field").remove();
        $("#hidden_time_field").remove();
        $("#delivery_date").remove();
        var button = $('<button class="call-modal">'+options.trans.chose_delivery_date+'</button>');
        var modal = $('<div id="modal-y2y" style="display:none">'
                                                    +'<div id="calendar" style="display: inline; float: left; width:50%;"></div>'
                                                    +'<div class="time" style="display: inline; float: right; width:45%;"></div>'
                                                    +'<div style="width:100%;display:table; padding:4px; float: right;">'
                                                        +'<input type="button" value="'+options.trans.chose+'" onclick="select_time()">'
                                                    +'</div>'
                                                +'</div>');
        div.append(delivery_date);
        div.append(hidden_date_field);
        div.append(hidden_time_field);
        div.append(button);
        div.append(modal);
        $('.woocommerce-billing-fields').append(div);
        $('.call-modal').parent().append('<div id="sentence"></div>');
        
        closed_days = '';
        cd = '';
        if(options.hours.closed_day!==undefined)
        {
            cd = $.map(options.hours.closed_day, function(value, index) {
                return [Number(index)];
            });
            for(i=0;i<cd.length;i++){
                if(closed_days===''){
                    closed_days+='day != '+cd[i];
                }else{
                    closed_days+= ' && day != '+cd[i];
                }
            }
        }
        
        today = moment(options.now);
        var today_week = moment(today).format('e');
        var now = moment(today).format('HH:mm');
        var timeout = Number(options.hours.timeout);
        var nowtimeout = moment(today,'HH:mm').add(timeout,'hour').format('HH:mm');
        
        if(options.hours.openning_hours_endding[today_week]>nowtimeout){
            minDate = 0;
        }else{
            minDate = 1;
        }
        
        var cal = $('#y2y_module #calendar').datepicker({
            minDate: minDate,
            altField: "#hidden_date",
            altFormat: "yy-mm-dd",
            setDate: minDate,
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
                $("#y2y_module #hidden_date").trigger("change");
            }
        });
        $('#y2y_module .call-modal').on('click', function(event) {
            event.preventDefault();
            $("#y2y_module #hidden_date").trigger("change");
            $('#modal-y2y').dialog({
                width: '45%',
                close: function(event, ui){
                    select_time();
                }
            });
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
        
        $("#y2y_module #hidden_date").change(function() {
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
                time = options.trans.please_be_available_at+" "+time[0]+" "+options.trans.until+" "+time[1]+'.';
            }
            $("#y2y_module #delivery_date").val(val+" "+time_sent);
            var months = [
                options.month.january,
                options.month.february,
                options.month.march,
                options.month.april,
                options.month.may,
                options.month.june,
                options.month.july,
                options.month.august,
                options.month.september,
                options.month.october,
                options.month.november,
                options.month.december
            ];
            var week = [
                options.week.sunday,
                options.week.monday,
                options.week.tuesday,
                options.week.wednesday,
                options.week.thursday,
                options.week.friday,
                options.week.saturday
            ];
            
            var year = choosen_date[0];
            var dayofthemonth = choosen_day;
            choosen_day = moment(val).day();
            var dayoftheweek = week[choosen_day];
            var month = months[monthpos-1];
            
            $("#y2y_module #sentence").html(options.trans.you_chose+" "+dayoftheweek+" "+dayofthemonth+" "+month+" "+year+". "+time);
            
            var times = [];
            
            beg_hour = options.hours.openning_hours_beginning[choosen_day];
            end_hour = options.hours.openning_hours_endding[choosen_day];
            lunch_beg = options.hours.lunch_time_beginning[choosen_day];
            lunch_end = options.hours.lunch_time_endding[choosen_day];
            
            var add = timeout;
            var now = moment(today).format('HH:mm');
            now_m = moment(now,'HH:mm').format('mm');
            while(now_m!=='00' && now_m!=='15' && now_m!=='30' && now_m!=='45'){
                now = moment(now,'HH:mm').add(1,'minute');
                now_m = moment(now,'HH:mm').format('mm');
            }
            if(moment(today).format('YYYY-MM-DD') === val){
                //today
                if(lunch_beg!=='' || lunch_end!=='')
                {
                    //morning
                    if(moment(now,'HH:mm') < moment(beg_hour,'HH:mm')){
                        now = beg_hour;
                    }

                    while(moment(now,'HH:mm').add(add,'hour') < moment(lunch_beg,'HH:mm').add(1,'hour')){
                        if(moment(now,'HH:mm').add(add,'hour') < moment(lunch_beg,'HH:mm').add(1,'hour')){
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
                    while(moment(now,'HH:mm').add(add,'hour') < moment(end_hour,'HH:mm').add(1,'hour')){
                        if(moment(now,'HH:mm').add(add,'hour') < moment(end_hour,'HH:mm').add(1,'hour')){
                            now = moment(now,'HH:mm').add(add,'hour');
                            times.push(moment(now,'HH:mm').format('HH:mm').replace(':','h')+" - "+moment(now,'HH:mm').add(1,'hour').format('HH:mm').replace(':','h'));
                            add = 1;
                        }
                    }
                }
                else
                {
                    if(moment(now,'HH:mm') < moment(beg_hour,'HH:mm')){
                        now = beg_hour;
                    }
                    while(moment(now,'HH:mm').add(add,'hour') < moment(end_hour,'HH:mm').add(1,'hour')){
                        if(moment(now,'HH:mm').add(add,'hour') < moment(end_hour,'HH:mm').add(1,'hour')){
                            now = moment(now,'HH:mm').add(add,'hour');
                            times.push(moment(now,'HH:mm').format('HH:mm').replace(':','h')+" - "+moment(now,'HH:mm').add(1,'hour').format('HH:mm').replace(':','h'));
                            add = 1;
                        }
                    }
                }
            }
            else
            {
                //not today
                if(lunch_beg!=='' || lunch_end!=='')
                {
                    var add = 1;
                    //morning
                    while(moment(beg_hour,'HH:mm').add(1,'hour') < moment(lunch_beg,'HH:mm').add(1,'hour')){
                        if(moment(beg_hour,'HH:mm').add(1,'hour') < moment(lunch_beg,'HH:mm').add(1,'hour')){
                            beg_hour = moment(beg_hour,'HH:mm').add(add,'hour');
                            times.push(moment(beg_hour,'HH:mm').format('HH:mm').replace(':','h')+" - "+moment(beg_hour,'HH:mm').add(1,'hour').format('HH:mm').replace(':','h'));
                        }
                    }

                    //afeternoon
                    while(moment(lunch_end,'HH:mm').add(1,'hour') < moment(end_hour,'HH:mm').add(1,'hour')){
                        if(moment(lunch_end,'HH:mm').add(1,'hour') < moment(end_hour,'HH:mm').add(1,'hour')){
                            lunch_end = moment(lunch_end,'HH:mm').add(add,'hour');
                            times.push(moment(lunch_end,'HH:mm').format('HH:mm').replace(':','h')+" - "+moment(lunch_end,'HH:mm').add(1,'hour').format('HH:mm').replace(':','h'));
                        }
                    }
                }
                else
                {
                    var add = 1;
                    //morning
                    while(moment(beg_hour,'HH:mm').add(1,'hour') < moment(end_hour,'HH:mm').add(1,'hour')){
                        if(moment(beg_hour,'HH:mm').add(1,'hour') < moment(end_hour,'HH:mm').add(1,'hour')){
                            beg_hour = moment(beg_hour,'HH:mm').add(add,'hour');
                            times.push(moment(beg_hour,'HH:mm').format('HH:mm').replace(':','h')+" - "+moment(beg_hour,'HH:mm').add(1,'hour').format('HH:mm').replace(':','h'));
                        }
                    }
                }
            }
            
            if($( "#y2y_module .radio-buttons" ).length === 0){
                $("#y2y_module #modal-y2y .time").append('<div class="radio-buttons"></div>');
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
            if(radiobtns===''){
                radiobtns = '<p style="algin-text:center">Il n\'y a plus de livraisons en ce jour. S\'il vous plaît choisir un autre jour.</p>';
            }
            $("#modal-y2y .radio-buttons").html(radiobtns);
            $('#modal-y2y .buttonsetv').buttonsetv();
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
                //$('#delivery_date_field').hide();
            }
        }
    });
})(jQuery);

function select_time()
{
    time_sel = jQuery('#modal-y2y .radio-buttons input[name=time]:checked').val();
    jQuery('#y2y_module #hidden_time').val(time_sel);
    jQuery("#y2y_module #hidden_date").trigger("change");
    jQuery("#modal-y2y").dialog('close');
}