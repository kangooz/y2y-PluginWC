
(function($) {
    $(document).ready(function(){
        //$("#delivery_date").parent().append('<div style="display:none;" class="y2ywsm-datepicker-holder"></div>');

        $('#delivery_date').attr('data-field', 'datetime');
        $("#y2y_hidden_date_field").css('display','none');
        $("#y2y_hidden_time_field").css('display','none');
        $("#y2y_delivery_date").css('display','none');
        var div = $('<div/>', {id: "y2y_module"});
        var y2y_hidden_date_field = $("#y2y_hidden_date_field");
        var y2y_hidden_time_field = $("#y2y_hidden_time_field");
        var y2y_delivery_date = $("#y2y_delivery_date");
        $("#y2y_hidden_date_field").remove();
        $("#y2y_hidden_time_field").remove();
        $("#y2y_delivery_date").remove();
        var button = $('<button class="call-modal">'+options.messages.choose_delivery_date+'</button>');
        
        if(options.hours.inline_calendar=='0' || options.hours.inline_calendar=='undefined')
        {
            var modal = $('<div id="modal-y2y" class="col-1" style="display:none">'
                                                    +'<div id="calendar" class="col-1" style="float: left;"></div>'
                                                    +'<div class="y2y_time" style=" float: right;">'
                                                        +'<div class="radio-buttons"></div>'
                                                    +'</div>'
                                                    +'<div style="width:100%;display:table; padding:4px;">'
                                                        +'<input type="button" value="'+options.messages.choose+'" onclick="select_time()">'
                                                    +'</div>'
                                                +'</div>');
            div.append(modal);
            div.append(button);
            
        }else{
            button = '<div class="y2y_time">'
                        +'<div class="radio-buttons"></div>'
                    +'</div>';
            div.append('<div id="calendar" class="inline-calendar"></div><br>');
            div.append(button);
            
        }
        
        div.append(y2y_delivery_date);
        div.append(y2y_hidden_date_field);
        div.append(y2y_hidden_time_field);
        div.append('<div id="y2y-sentence"></div>');
        $('.woocommerce-billing-fields').append(div);
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
        var now = moment(today).format('HH[h]mm');
        var timeout = Number(options.hours.timeout);
        var nowtimeout = moment(today,'HH[h]mm').add(timeout+1,'hour').format('H[h]mm');
        if(moment(options.hours.openning_hours_endding[today_week],'HH[h]mm')>moment(nowtimeout,'HH[h]mm')){
            minDate = 0;
        }else{
            minDate = 1;
        }
        var cal = $('#y2y_module #calendar').datepicker({
            minDate: minDate,
            altField: "#y2y_hidden_date",
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
                $("#y2y_module #y2y_hidden_date").trigger("change");
            }
        });
        
        $('#y2y_module .call-modal').on('click', function(event) {
            event.preventDefault();
            $("#y2y_module #y2y_hidden_date").trigger("change");
            $('#modal-y2y').dialog({
                width: '45%',
                close: function(event, ui){
                    select_time();
                }
            });
        });
        
        
        $( ".y2ywsm-timepicker-holder" ).DateTimePicker({
            mode: "time",
            language: options.lang,
            timeFormat: 'HH[h]mm',
            minuteInterval: 15,
            roundOffMinutes: true
        });
        $(".y2ywsm-datepicker-holder").DateTimePicker({
            mode: "datetime",
            dateTimeFormat: 'dd/MM/yyyy HH[h]mm',
            minDateTime: options.dateTimePicker.minDateTime,
            language: options.lang,
            minuteInterval: 15,
            roundOffMinutes: true,
            defaultDate: options.dateTimePicker.defaultValue
        });
        
        $("#y2y_module #y2y_hidden_date").change(function() {
            var timeout = Number(options.hours.timeout);
            val = $("#y2y_hidden_date").val();
            choosen_day = moment(val).day();
            var times = [];
            
            choosen_day = moment(val).day();
            beg_hour = options.hours.openning_hours_beginning[choosen_day];
            end_hour = options.hours.openning_hours_endding[choosen_day];
            lunch_beg = options.hours.lunch_time_beginning[choosen_day];
            lunch_end = options.hours.lunch_time_endding[choosen_day];


            var now = moment(today).format('HH[h]mm');
            now_m = moment(now,'HH[h]mm').format('mm');
            while(now_m!=='00' && now_m!=='15' && now_m!=='30' && now_m!=='45'){
                now = moment(now,'HH[h]mm').add(1,'minute');
                now_m = moment(now,'HH[h]mm').format('mm');
            }
            if(moment(today).format('YYYY-MM-DD') === val){
                //today
                add = timeout+1;
                if(lunch_beg!=='' || lunch_end!=='')
                {
                    //morning
                    if(moment(now,'HH[h]mm') < moment(beg_hour,'HH[h]mm')){
                        now = beg_hour;
                    }
                    while(moment(now,'HH[h]mm').add(add,'hour') < moment(lunch_beg,'HH[h]mm').add(1,'hour')){
                        now = moment(now,'HH[h]mm').add(add,'hour');
                        times.push(moment(now,'HH[h]mm').format('HH[h]mm')+" - "+moment(now,'HH[h]mm').add(1,'hour').format('HH[h]mm'));
                        add = 1;
                    }

                    //afternnoon
                    add = timeout+1;
                    if(moment(now,'HH[h]mm') < moment(lunch_end,'HH[h]mm')){
                        now = lunch_end;
                    }
                    while(moment(now,'HH[h]mm').add(add,'hour') < moment(end_hour,'HH[h]mm').add(1,'hour')){
                        now = moment(now,'HH[h]mm').add(add,'hour');
                        times.push(moment(now,'HH[h]mm').format('HH[h]mm')+" - "+moment(now,'HH[h]mm').add(1,'hour').format('HH[h]mm'));
                        add = 1;
                    }
                }
                else
                {
                    if(moment(now,'HH[h]mm') < moment(beg_hour,'HH[h]mm')){
                        now = beg_hour;
                    }
                    while(moment(now,'HH[h]mm').add(add,'hour') < moment(end_hour,'HH[h]mm').add(1,'hour')){
                        now = moment(now,'HH[h]mm').add(add,'hour');
                        times.push(moment(now,'HH[h]mm').format('HH[h]mm')+" - "+moment(now,'HH[h]mm').add(1,'hour').format('HH[h]mm'));
                        add = 1;
                    }
                }
            }
            else
            {
                //not today
                if(lunch_beg!=='' || lunch_end!=='')
                {
                    //morning
                    while(moment(beg_hour,'HH[h]mm').add(1,'hour') < moment(lunch_beg,'HH[h]mm').add(1,'hour')){
                        beg_hour = moment(beg_hour,'HH[h]mm').add(add,'hour');
                        times.push(moment(beg_hour,'HH[h]mm').format('HH[h]mm')+" - "+moment(beg_hour,'HH[h]mm').add(1,'hour').format('HH[h]mm'));
                    }

                    //afeternoon
                    add = 1;
                    while(moment(lunch_end,'HH[h]mm').add(1,'hour') < moment(end_hour,'HH[h]mm').add(1,'hour')){
                        lunch_end = moment(lunch_end,'HH[h]mm').add(add,'hour');
                        times.push(moment(lunch_end,'HH[h]mm').format('HH[h]mm')+" - "+moment(lunch_end,'HH[h]mm').add(1,'hour').format('HH[h]mm'));
                        add = 1;
                    }
                }
                else
                {
                    //morning
                    while(moment(beg_hour,'HH[h]mm').add(1,'hour') < moment(end_hour,'HH[h]mm').add(1,'hour')){
                        beg_hour = moment(beg_hour,'HH[h]mm').add(add,'hour');
                        times.push(moment(beg_hour,'HH[h]mm').format('HH[h]mm')+" - "+moment(beg_hour,'HH[h]mm').add(1,'hour').format('HH[h]mm'));
                        add = 1;
                    }
                }
            }
            
            rawtime = $("#y2y_hidden_time").val();

            var radiobtns = '';
            for (i = 0; i < times.length; i++){
                span = times[i].split(' - ');
                if(rawtime === (span[0]+'-'+span[1]) || i===0){
                    checked='checked="checked"';
                }else{
                    checked='';
                }

                radiobtns += '<div class="buttonsetv" onchange="javascript:generate_sentence();" name="radio-group-'+i+'">'
                                    +'<input type="radio" id="time'+i+'" name="time" '+checked+' value="'+span[0]+'-'+span[1]+'">'+'\
                                    <label for="time'+i+'">'+times[i]+'</label>'
                            +'</div>';
            }
            if(radiobtns===''){
                radiobtns = '<p style="algin-text:center">'+options.messages.no_deliveries+'</p>';
            }
            
            $(".y2y_time .radio-buttons").html(radiobtns);
            $('.y2y_time .buttonsetv').buttonsetv();
            generate_sentence();
        });
        
        checkShippingMethod();
        

        $(document).on('click', "input[name='shipping_method[0]']", function(){
            if($(this).val() === 'You2You')
            {
                $('#y2y_module').show();
                
                $('html, body').animate({
                    scrollTop: $("#y2y_module").offset().top-100
                }, 1000);
            }
            else
            {
                $('#y2y_module').hide();
            }
        });
            
        $(document.body).on('updated_checkout', checkShippingMethod);
        
        function checkShippingMethod(e){
            if($("input[name='shipping_method[0]'][value=You2You]").length > 0){
                if($("input[name='shipping_method[0]']").length > 1){
                    if($("input[name='shipping_method[0]'][value=You2You]").is(':checked')){
                        $('#y2y_module').show();
                    }
                    else
                    {
                        $('#y2y_module').hide();
                    };
                }else{
                    $('#y2y_module').show();
                }
            }else{
                $('#y2y_module').hide();
            }
        }
    });
})(jQuery);


function select_time()
{
    generate_sentence();
    jQuery("#modal-y2y").dialog('close');
}


function generate_sentence()
{
    time_sel = jQuery('.y2y_time .radio-buttons input[name=time]:checked').val();
    jQuery('#y2y_module #y2y_hidden_time').val(time_sel);
    val = jQuery("#y2y_hidden_date").val();
    choosen_date = val.split('-');
    monthpos = choosen_date[1].replace(/^0+/, '');
    choosen_day = choosen_date[2].replace(/^0+/, '');
    time_sent = '';
    rawtime = jQuery("#y2y_hidden_time").val();
    time = rawtime;
    if(time!==''){
        time = time.split('-');
        time_sent = time[0].toString().replace('h',':')+":00";
        time = options.messages.please_be_available_at+" "+time[0]+" "+options.messages.until+" "+time[1];
    }
    jQuery("#y2y_module #y2y_delivery_date").val(val+" "+time_sent);
    var months = [
        options.months.january,
        options.months.february,
        options.months.march,
        options.months.april,
        options.months.may,
        options.months.june,
        options.months.july,
        options.months.august,
        options.months.september,
        options.months.october,
        options.months.november,
        options.months.december
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
    jQuery("#y2y_module #y2y-sentence").html(options.messages.you_chose+" "+dayoftheweek+" "+dayofthemonth+" "+month+" "+year+". "+time+" "+options.messages.final+".");
}