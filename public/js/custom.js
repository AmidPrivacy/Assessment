/************************* Wavesurfer start **********************/
var wavesurfer = WaveSurfer.create({
  container: '.waveform, #waveform',
  waveColor: 'violet',
  progressColor: 'purple',
});

var showTime = function () {
  $('.audio-loading').hide();
  $('.audio-player').css('visibility', 'initial');
  document.querySelector('#timer').textContent = wavesurfer
    .getCurrentTime()
    .toFixed(2);
};

wavesurfer.on('ready', showTime);
wavesurfer.on('audioprocess', showTime);
wavesurfer.on('seek', showTime);

wavesurfer.on('ready', updateTimer);
wavesurfer.on('audioprocess', updateTimer);
wavesurfer.on('seek', updateTimer);

function updateTimer() {
  var formattedTime = secondsToTimestamp(wavesurfer.getCurrentTime());
  $('#waveform-time-indicator .time').text(formattedTime);
}

function secondsToTimestamp(seconds) {
  seconds = Math.floor(seconds);
  var h = Math.floor(seconds / 3600);
  var m = Math.floor((seconds - h * 3600) / 60);
  var s = seconds - h * 3600 - m * 60;

  h = h < 10 ? '0' + h : h;
  m = m < 10 ? '0' + m : m;
  s = s < 10 ? '0' + s : s;
  return h + ':' + m + ':' + s;
}
/************************ wavesurfer end ***********************/

/********** Assessment steps variables and audio timer function ********/
var current_fs, next_fs, previous_fs;
var left, opacity, scale;
var animating;

var commonAssessment = [];

var raf = null;
var start = 0;
var end = 0;
var counter = 1 / 50;
var countdown = null;
function interval() {
  countdown = setInterval(function () {
    start++;

    end = start * counter;
    $('#audio-time').text(start);
    $('#timer').val(end);
    if (end === 1) {
      start = 0;
      $('.fa-pause-circle').attr('class', 'far fa-play');
      clearInterval(countdown);
    }
  }, 1000);
}
/********** Assessment steps variables and audio timer function *********/

/******************** create timer function start ***********************/
function setTime(secondsLabel, minutesLabel, totalSeconds) {
  secondsLabel.innerHTML = pad(totalSeconds % 60);
  minutesLabel.innerHTML = pad(parseInt(totalSeconds / 60));
}

function pad(val) {
  var valString = val + '';
  if (valString.length < 2) {
    return '0' + valString;
  } else {
    return valString;
  }
}
/******************** create timer function end *************************/

/****************** assessment date and filter start ********************/
$(function () {
  $('.input-daterange input').each(function () {
    $(this).datepicker({
      autoclose: true,
      todayHighlight: true,
      language: 'az',
      Default: true,
    });
  });

  $('#name-filter').keyup(function () {
    let key = $(this).val().toUpperCase();
    $('.ul .child').hide();
    $('.ul .child').next().hide();

    if (key.length > 0) {
      $('.ul .child').each(function () {
        if ($(this).find('.operator-fullname').text().search(key) > 0) {
          $(this).show();
          $(this).next().show();
        }
      });
    } else {
      $('.ul .child').show();
      $('.ul .child').next().show();
    }
  });
});

/******************** assessment date and filter end **********************/

/************************** Assessment steps start ************************/
$(document).on('click', '.next', function () {
  if (animating) return false;

  current_fs = $(this).parent();
  next_fs = $(this).parent().next();

  var beginDate = current_fs.find('.begin-date').val();
  var endDate = current_fs.find('.end-date').val();
  var operatorId = $('.operator-id').val();
  var checkedCount = $('#checked-count').val();

  if (
    current_fs.attr('class') === 'assessment-dates' &&
    beginDate !== '' &&
    endDate !== ''
  ) {
    $('.services-list').html();
    $('.response-loading').css('display', 'inherit');
    $.ajax({
      type: 'get',
      url: `/calls/${operatorId}/${beginDate}/${endDate}`,
      dataType: 'json',
      success: function (data) {
        $('.response-loading').fadeOut();
        if (data.services.length > 0) {
          animating = true;

          $('#progressbar li')
            .eq($('fieldset').index(next_fs))
            .addClass('active');

          nexted(next_fs, current_fs);

          let callCount = 0;
          data.services.forEach((item, index) => {
            if (item.call.length > 0) {
              callCount += item.call.length;
              $('.services-list').append(
                '<li class="list-group-item d-flex justify-content-between align-items-center"> <span class="services-counter">' +
                  (index + 1) +
                  '. </span> ' +
                  item.name +
                  ' <span class="badge badge-primary badge-pill">' +
                  item.call.length +
                  '</span> </li>'
              );
            } else {
              $('.services-list').text('Seçilən tarixlərdə zəng tapılmadı');
            }
          });

          if (callCount > 0) {
            $('.selected-services').text(
              data.services.length + '(' + callCount + ')'
            );

            $('.selected-services-count').attr('value', data.services.length);
            $('.selected-call-count').attr('value', callCount);
          }
        } else {
          $('.modal-description').html(
            'Seçilən tarix aralığında heç bir xidmətə uyğun zəng tapılmadı'
          );
          document.querySelector('#confirm-modal').showModal();
        }
      },
      error: function (e) {
        console.log(e);
        $('.response-loading').fadeOut();
      },
    });
  } else if (current_fs.attr('class') === 'assestment-services') {
    var call = $('.services-list .list-group-item');
    var callCount = 0;
    call.each(function () {
      callCount += Number($(this).find('.badge-pill').text());
    });
    if (checkedCount !== '' && call.length > 0) {
      let servicesCount = $('.selected-services-count').val();
      let allCallCount = $('.selected-call-count').val();
      if (callCount >= checkedCount) {
        $('.response-loading').css('display', 'inherit');
        $.ajax({
          type: 'get',
          url: `/calls/${servicesCount}/${allCallCount}/${operatorId}/${$(
            '.begin-date'
          ).val()}/${$('.end-date').val()}/${checkedCount}`,
          dataType: 'json',
          success: function (data) {
            $('.supervisor-counter').fadeIn();
            // setInterval(setTime, 1000);
            animating = true;

            $('#progressbar li')
              .eq($('fieldset').index(next_fs))
              .addClass('active');

            nexted(next_fs, current_fs);
            var parent = document.querySelector('#accordionExample');

            parent.setAttribute('data-id', data.id);

            parent.innerHTML = '';
            $('.response-loading').fadeOut();
            data.calls.forEach((item, index) => {
              var callAssessment = document
                .querySelector('#copycall .calls-assessment-details')
                .cloneNode(true);
              callAssessment.querySelector(
                '.btn-link'
              ).innerHTML = ` <span> ${item.citizen_number} </span> / ${item.organName} / ${item.serviceName}`;

              callAssessment
                .querySelector('.btn-link')
                .setAttribute('data-target', '#collapse' + index);

              callAssessment
                .querySelector('.selected-items')
                .setAttribute('data-call', item.id);

              callAssessment
                .querySelector('.card-header')
                .setAttribute('id', 'heading' + index);

              callAssessment
                .querySelector('.card-header')
                .setAttribute('data-number', item.citizen_number);

              callAssessment
                .querySelector('.card-header')
                .setAttribute('data-collapse', '0');

              callAssessment
                .querySelector('.card-header')
                .setAttribute('data-start-date', item.callStart);

              callAssessment
                .querySelector('.card-header')
                .setAttribute('data-end-date', item.callEnd);

              callAssessment
                .querySelector('.body-detail')
                .setAttribute('id', 'collapse' + index);

              callAssessment
                .querySelector('.body-detail')
                .setAttribute('aria-labelledby', 'heading' + index);

              parent.appendChild(callAssessment);
            });
          },
          error: function (e) {
            console.log(e);
            $('.response-loading').fadeOut();
            $('.modal-description').html('Sistem xətası');
            document.querySelector('#confirm-modal').showModal();
          },
        });
      } else {
        $('.modal-description').html(
          'Daxil etdiyiniz rəqəm zəng sayından çoxdur'
        );
        document.querySelector('#confirm-modal').showModal();
      }
    } else {
      $('.modal-description').html('Zəng və ya say daxil edilməyib');
      document.querySelector('#confirm-modal').showModal();
    }
  } else {
    $('.modal-description').html('Zəhmət olmasa tarix seçin');
    document.querySelector('#confirm-modal').showModal();
  }
});

function nexted(next_fs, current_fs) {
  //show the next fieldset
  next_fs.show();
  //hide the current fieldset with style
  current_fs.animate(
    { opacity: 0 },
    {
      step: function (now, mx) {
        //as the opacity of current_fs reduces to 0 - stored in "now"
        //1. scale current_fs down to 80%
        scale = 1 - (1 - now) * 0.2;
        //2. bring next_fs from the right(50%)
        left = now * 50 + '%';
        //3. increase opacity of next_fs to 1 as it moves in
        opacity = 1 - now;
        current_fs.css({
          transform: 'scale(' + scale + ')',
          position: 'relative',
        });
        next_fs.css({ left: left, opacity: opacity });
      },
      duration: 800,
      complete: function () {
        current_fs.hide();
        animating = false;
      },
      //this comes from the custom easing plugin
      easing: 'easeInOutBack',
    }
  );
}
/*************************** Assessment steps end *************************/

/**************************** Calls according  start **********************/
var timerInterval = null;

$(document).on(
  'touchend click',
  '#accordionExample .card-header, .operator-assessment .card-header',
  function () {
    var number = $(this).attr('data-number');
    $('#accordionExample .card-header, .operator-assessment .card-header')
      .not(this)
      .attr('data-collapse', '0');
    var collapse = $(this).attr('data-collapse');

    var beginDate = new Date(
      $(this).attr('data-start-date').substring(0, 16)
    ).getTime();

    if ($(this).attr('data-end-date') == 'null') {
      var endDate = new Date($(this).attr('data-start-date').substring(0, 16));
      endDate.setMinutes(endDate.getMinutes() + 20);
      endDate = endDate.getTime();
    } else {
      var endDate = new Date(
        $(this).attr('data-end-date').substring(0, 16)
      ).getTime();
    }

    let totalSeconds = Number(
      $(this).find('.supervisor-counter').attr('data-second')
    );
    let secondsLabel = this.querySelector('.seconds');
    minutesLabel = this.querySelector('.minutes');
    let context = this;
    clearInterval(timerInterval);
    if (collapse == 0) {
      timerInterval = setInterval(function () {
        ++totalSeconds;
        setTime(secondsLabel, minutesLabel, totalSeconds);
        $(context)
          .find('.supervisor-counter')
          .attr('data-second', totalSeconds.toString());
      }, 1000);

      // $(".audio-loading").fadeIn();
      $(this).attr('data-collapse', '1');
      var assessmentId = $(this).attr('data-assessment');
      $.ajax({
        type: 'get',
        url: `/get-audio-checker/${number}/${beginDate}/${endDate}/${assessmentId}`,
        dataType: 'json',
        success: function (data) {
          if (data.code == 200) {
            $('.step-section').animate(
              {
                scrollTop: 0,
              },
              3000
            );

            $('.audio-player').fadeIn();
            $('.audio-loading').fadeIn();
            $('.audio-player').css('visibility', 'hidden');
            wavesurfer.load(
              `/get-mediasense/${number}/${beginDate}/${endDate}/${assessmentId}`
            );
          } else {
            $('.audio-loading').fadeOut();
            $('.modal-description').html(data.responseMessage);
            document.querySelector('#confirm-modal').showModal();
          }
        },
        error: function (err) {
          console.log(err);
          $('.audio-loading').fadeOut();
          $('.modal-description').html('Sistem xətası');
          document.querySelector('#confirm-modal').showModal();
        },
      });
    } else {
      wavesurfer.pause();
      $('.audio-player').fadeOut();
      $(this).attr('data-collapse', '0');
    }
  }
);
/**************************** Calls according  end *****************************/

/**************************** Audio player  start *****************************/
$(document).on(
  'click',
  '.audio-play, .audio-play .fa-pause-circle, .audio-play .fa-play',
  function () {
    var play = $(this).attr('data-play');
    if (play == '0') {
      wavesurfer.play();
      $(this).attr('data-play', '1');
      $('.audio-play .fa-play').attr('class', 'far fa-pause-circle');
    } else {
      wavesurfer.pause();
      $(this).attr('data-play', '0');
      $('.audio-play .fa-pause-circle').attr('class', 'far fa-play');
    }
  }
);
/**************************** Audio player end *****************************/

/*************************** Assessment start *****************************/
$(document).on('click', '.button--add', function () {
  var val = $(this).prev().find('.operator-fullname').text();

  var selectedDate = $(this).prev().find('.assestment-date').text();

  $('html, body').animate(
    {
      scrollTop:
        document.body.scrollHeight || document.documentElement.scrollHeight,
    },
    3000
  );

  selectedDate = selectedDate.split(' ')[2];

  selectedDate = new Date(selectedDate);

  selectedDate.setDate(selectedDate.getDate() + 2);

  if (selectedDate != 'Invalid Date') {
    $('.begin-date, .end-date').datepicker('setStartDate', selectedDate);
  }

  $('.assessment-dates').fadeIn();
  $('.assessment-dates').css('position', 'relative');
  $('.assessment-dates').attr('style', 'position: relative; opacity: 1');
  setTimeout(function () {
    $('.assessment-dates').attr('style', 'position: relative; opacity: 1');
  }, 900);

  $('.assestment-services, .audio-cutter').fadeOut();

  $('.assestment-user').text(val);
  $('.step-section').fadeIn();
  $('.operator-id').val($(this).attr('data-id'));
});
/*************************** Assessment end *****************************/

/*************************** Critery add start *************************/
$(document).on('click', '.add-critery', function () {
  var parent = $(this).parent().parent().parent().parent();
  var parentTime = $('#waveform').parent().find('.time');

  var decrement = $(this).parent().find('.badge-pill').text();

  var score = parent.find('.common-score span').text();

  score = Number(score) - Number(decrement);

  parent.find('.common-score span').text(score);

  var totalTime =
    Number(parentTime.text().split(':')[1]) * 60 +
    Number(parentTime.text().split(':')[2]);

  var currentTime = totalTime;

  if (totalTime > 5) {
    var time =
      parentTime.text().split(':')[1] +
      ':' +
      (Number(parentTime.text().split(':')[2]) - 5);
    currentTime -= 5;
  } else {
    currentTime = 0;
    var time = parentTime.text().split(':')[1] + ':' + '00';
  }

  var critery = $(this).parent().find('.critery-name').text();

  parent
    .find('.selected-items')
    .append(
      '<li class="list-group-item d-flex justify-content-between align-items-center"><span class="selected-critery-name">' +
        critery +
        '</span> <span class="badge badge-primary selected-time">' +
        time +
        '</span> <span class="badge badge-primary badge-pill">' +
        $(this).parent().find('.badge-pill').text() +
        '</span> <i class="far fa-trash-alt" data-id=' +
        $(this).attr('data-id') +
        "></i><i class='far fa-play' data-second=" +
        currentTime +
        '></i></li>'
    );
});
/*************************** Critery add end *****************************/

/************************* Critery player start **************************/
$(document).on(
  'click',
  '.selected-items .fa-play, .calls-assessment-details .list-group .fa-play',
  function () {
    var second = $(this).attr('data-second');

    var formattedTime = secondsToTimestamp(Number(second));

    wavesurfer.setCurrentTime(Number(second));

    $('#waveform-time-indicator .time').text(formattedTime);
  }
);
/************************* Critery player end **************************/

/************************* Critery delete start ************************/
$(document).on('click', '.selected-items .fa-trash-alt', function () {
  var score = $(this.parentNode)
    .parent()
    .parent()
    .find('.common-score span')
    .text();

  var decrement = $(this).parent().find('.badge-pill').text();

  score = Number(score) + Number(decrement);
  $(this).parent().remove();

  $('.common-score span').text(score);
});
/************************* Critery delete end ************************/

/************************* Delete all critery start ******************/
$(document).on('click', '.clear-common-score', function () {
  let next = Number($(this).prev().find('span').text());
  var parent = $(this).next().find('li');

  parent.each(function () {
    next += Number($(this).find('.badge-pill').text());
  });
  $(this).prev().find('span').text(next);
  $(this).next().html('');
});
/************************* Delete all critery end ********************/

/*********************** Call assessment start ***********************/
$(document).on('click', '.call-assessment', function () {
  var criterias = $(this).parent().find('.selected-items .list-group-item');

  var criteraList = [];
  criterias.each(function () {
    criteraList.push({
      id: $(this).find('.fa-trash-alt').attr('data-id'),
      name: $(this).find('.selected-critery-name').text(),
      time: $(this).find('.selected-time').text(),
      count: $(this).find('.selected-time').next().text(),
      second: $(this).find('.fa-play').attr('data-second'),
    });
  });

  let score = $(this).parent().parent().find('.common-score span').text();

  let callInfo = {
    call: $(this).parent().find('.selected-items').attr('data-call'),
    count: score,
    comment: $(this).next().val(),
    criterias: criteraList,
    time: Number(
      $(this).parent().parent().find('.supervisor-counter').attr('data-second')
    ),
    wrongSelection: $(this)
      .parent()
      .find('.checked-critery input')
      .is(':checked')
      ? 1
      : 0,
  };
  commonAssessment.push(callInfo);

  callInfo._token = $('input[name="_token"]').val();

  let content = this;
  if (criteraList.length > 0) {
    $.ajax({
      type: 'POST',
      url: '/update-call',
      dataType: 'json',
      data: callInfo,
      success: function (res) {
        if (res.status == 200) {
          $(content).parent().removeClass('show');

          $(content)
            .parent()
            .parent()
            .find('.card-header span')
            .html('<i class="fas fa-check"></i> ' + score);
        }
      },
    });
  }
});
/*********************** Call assessment end ***********************/

/*********************** Wrong selection start *********************/
$(document).on('change', '.checked-critery input', function () {
  if ($(this).is(':checked')) {
    let secodChecked = $('#waveform-time-indicator .time').text().substring(3);
    $(this).val(secodChecked);
  } else {
    $(this).val('');
  }
});
/*********************** Wrong selection end **********************/

/******************** Completed assessment start ******************/
$('.assesment-completed').click(function () {
  let beginDate = $('.begin-date').val();
  let endDate = $('.end-date').val();
  let _token = $('input[name="_token"]').val();
  let id = $('#accordionExample').attr('data-id');
  let operatorId = $('.operator-id').val();
  let comment = $('.call-comment').val();
  let supervisorTimer = $('#minutes').text() + ':' + $('#seconds').text();

  let data = {
    beginDate,
    _token,
    id: Number(id),
    operatorId: Number(operatorId),
    endDate,
    commonAssessment,
    supervisorTimer,
    comment,
  };

  if (commonAssessment.length > 0) {
    $('.response-loading').css('display', 'inherit');
    $.ajax({
      type: 'POST',
      url: '/new-assessment',
      dataType: 'json',
      data,
      success: function (res) {
        if (res.status === 200) {
          $('.response-loading').fadeOut();
          $('.modal-description').html('Uğurlu əməliyyat');
          document.querySelector('#confirm-modal').showModal();
          setTimeout(function () {
            location.reload();
          }, 2500);
        }
      },
    });
  } else {
    $('.modal-description').html('Zehmet olmasa qiymetlendirme edin');
    document.querySelector('#confirm-modal').showModal();
  }
});
/******************** Completed assessment end ******************/

/******************** Assessment continue start *****************/
$(document).on('click', '.continue-assestment', function () {
  $('.step-section, .audio-cutter, .assessment-loading').fadeIn();
  $('.assessment-dates, .assestment-services').fadeOut();
  let id = $(this).attr('data-id');
  let fullname = $(this).parent().find('.operator-fullname').text();

  $('.assestment-user').text(fullname);
  $('html, body').animate(
    {
      scrollTop:
        document.body.scrollHeight || document.documentElement.scrollHeight,
    },
    3000
  );
  $.ajax({
    type: 'get',
    url: `/get-assessment/${id}`,
    dataType: 'json',
    success: function (data) {
      var parent = document.querySelector('#accordionExample');

      parent.setAttribute('data-id', id);

      parent.innerHTML = '';
      $('.assessment-loading').fadeOut();
      data.calls.forEach((item, index) => {
        var callAssessment = document
          .querySelector('#copycall .calls-assessment-details')
          .cloneNode(true);
        callAssessment.querySelector(
          '.btn-link'
        ).innerHTML = ` <span> ${item.citizen_number} </span> / ${item.organName} / ${item.serviceName}`;

        callAssessment
          .querySelector('.btn-link')
          .setAttribute('data-target', '#collapse' + index);

        callAssessment
          .querySelector('.selected-items')
          .setAttribute('data-call', item.callId);

        callAssessment
          .querySelector('.card-header')
          .setAttribute('id', 'heading' + index);

        callAssessment
          .querySelector('.card-header')
          .setAttribute('data-number', item.citizen_number);

        callAssessment
          .querySelector('.card-header')
          .setAttribute('data-collapse', '0');

        callAssessment
          .querySelector('.card-header')
          .setAttribute('data-start-date', item.callStart);

        callAssessment
          .querySelector('.card-header')
          .setAttribute('data-end-date', item.callEnd);

        callAssessment
          .querySelector('.body-detail')
          .setAttribute('id', 'collapse' + index);

        callAssessment
          .querySelector('.body-detail')
          .setAttribute('aria-labelledby', 'heading' + index);

        if (item.count > 0) {
          callAssessment.querySelector(
            '.card-header .mb-0 .score-count'
          ).innerText = item.count;
        }
        if (item.comment != null) {
          callAssessment.querySelector('.call-comment').value = item.comment;
        }

        if (item.criterias != null && item.criterias != 'null') {
          let str = '';
          JSON.parse(item.criterias).forEach(function (criteryItem) {
            str +=
              '<li class="list-group-item d-flex justify-content-between align-items-center"><span class="selected-critery-name">' +
              criteryItem.name +
              '</span> <span class="badge badge-primary selected-time">' +
              criteryItem.time +
              '</span> <span class="badge badge-primary badge-pill">' +
              criteryItem.count +
              '</span> <i class="far fa-trash-alt" data-id=' +
              criteryItem.id +
              "></i><i class='far fa-play' data-second=" +
              criteryItem.second +
              '></i></li>';
          });

          callAssessment.querySelector('.selected-items').innerHTML = str;

          callAssessment.querySelector('.checked-critery input').checked =
            item.wrongSelection === 0 ? false : true;
        }

        parent.appendChild(callAssessment);
      });
    },
  });
});
/******************** Assessment continue end *****************/

/**************** Assessment stepprevious start **************/
$('.previous').click(function () {
  if (animating) return false;
  animating = true;

  current_fs = $(this).parent();
  previous_fs = $(this).parent().prev();

  //de-activate current step on progressbar
  $('#progressbar li')
    .eq($('fieldset').index(current_fs))
    .removeClass('active');

  //show the previous fieldset
  previous_fs.show();
  //hide the current fieldset with style
  current_fs.animate(
    { opacity: 0 },
    {
      step: function (now, mx) {
        //as the opacity of current_fs reduces to 0 - stored in "now"
        //1. scale previous_fs from 80% to 100%
        scale = 0.8 + (1 - now) * 0.2;
        //2. take current_fs to the right(50%) - from 0%
        left = (1 - now) * 50 + '%';
        //3. increase opacity of previous_fs to 1 as it moves in
        opacity = 1 - now;
        current_fs.css({ left: left });
        previous_fs.css({
          transform: 'scale(' + scale + ')',
          opacity: opacity,
        });
      },
      duration: 800,
      complete: function () {
        current_fs.hide();
        animating = false;
      },
      //this comes from the custom easing plugin
      easing: 'easeInOutBack',
    }
  );
});
/**************** Assessment stepprevious end **************/

$('.submit').click(function () {
  return false;
});

/******************* Get assessment by id start *******************/
$(document).on('click', '.assessment-detail-page', function () {
  let id = $('.assessment-detail-page').attr('data-id');
  $('.assessment-response-loading').fadeIn();
  $.ajax({
    type: 'get',
    url: `/get-assessment/${id}`,
    dataType: 'json',
    success: function (data) {
      $('.assessment-response-loading').fadeOut();
      var parent = $('.operator-assessment .list-group');

      parent.html('');
      $('.operator-assessment .fa-arrow-left').fadeIn();
      data.calls.forEach((item, index) => {
        callAssessment = [];

        var callAssessment = document
          .querySelector('#copycall .calls-assessment-details')
          .cloneNode(true);
        callAssessment.querySelector(
          '.btn-link'
        ).innerHTML = `<span> ${item.citizen_number} </span> / ${item.organName} / ${item.serviceName}`;

        callAssessment
          .querySelector('.btn-link')
          .setAttribute('data-target', '#collapse' + index);

        callAssessment.querySelector('.col-md-6').className = 'col-md-12';

        if (item.criterias !== null) {
          var asessmentCriteriaList = '';
          JSON.parse(item.criterias).forEach((values) => {
            asessmentCriteriaList += `<li class="list-group-item d-flex justify-content-between align-items-center"> 
                            ${values.name}   <span class="badge badge-primary badge-pill selected-time"> 
                            ${values.time}   </span> <span class="badge badge-primary badge-pill">
                            ${values.count} </span>  <i class="far fa-play" data-second= 
                            ${values.second}></i></li>`;
          });
          callAssessment.querySelector('.list-group').innerHTML =
            asessmentCriteriaList;
        }

        callAssessment.querySelector('.call-assessment').remove();

        callAssessment
          .querySelector('.selected-items')
          .setAttribute('data-call', item.id);

        callAssessment
          .querySelector('.card-header')
          .setAttribute('id', 'heading' + index);

        callAssessment.querySelector(
          '.card-header .mb-0 .common-score-count'
        ).innerText = item.count;

        callAssessment
          .querySelector('.card-header')
          .setAttribute('data-number', item.citizen_number);

        callAssessment
          .querySelector('.card-header')
          .setAttribute('data-start-date', item.callStart);

        callAssessment
          .querySelector('.card-header')
          .setAttribute('data-end-date', item.callEnd);

        callAssessment
          .querySelector('.card-header')
          .setAttribute('data-collapse', '0');

        callAssessment
          .querySelector('.body-detail')
          .setAttribute('id', 'collapse' + index);

        callAssessment
          .querySelector('.body-detail')
          .setAttribute('aria-labelledby', 'heading' + index);
        parent.append(callAssessment);
      });
    },
    error: function (e) {
      console.log(e);
      $('.assessment-response-loading').fadeOut();
    },
  });
});
/******************* Get assessment by id end *******************/

/****************** Wrong selection checked start ***************/
$('.op-assessment-checked').click(function (e) {
  e.stopPropagation();
  let val = 0;
  $('.op-assessment-checked').each(function () {
    if (this.checked) {
      val += $(this).val();
    }
  });

  if (val > 0) {
    $('.completed-assessment').fadeIn();
  } else {
    $('.completed-assessment').fadeOut();
  }
});
/****************** Wrong selection checked end ****************/

/**************** Print assessment package start ***************/
$(document).on('click', '.package-assessment-detail .fa-print', function (e) {
  let id = $(this).attr('data-id');

  $.ajax({
    type: 'get',
    url: `/get-package/${id}`,
    dataType: 'json',
    success: function (data) {
      let str = '';
      data.package.assessment.forEach(function (item, index) {
        str +=
          '<tr><td data-label="Account"> Q ' +
          (index + 1) +
          ' </td><td data-label="Account"> ' +
          item.begin_date.split(' ')[0] +
          ' - ' +
          item.end_date.split(' ')[0] +
          ' </td><td data-label="Account">' +
          item.services_count +
          '</td><td data-label="Account">' +
          item.calls_count +
          '</td><td data-label="Account"> ' +
          item.isAssessment +
          ' </td><td data-label="Account"> ' +
          item.wrong_selection +
          ' </td><td data-label="Account"> ' +
          item.assessmentTime +
          ' </td><td data-label="Account"> ' +
          item.score_count +
          ' </td></</tr>';
      });

      $('.print-modal table tbody').html(str);
      $('.completed-package-score').html(data.package.completedScore);

      // console.log(data);
    },
  });

  e.preventDefault();
});
/**************** Print assessment package end ****************/

/*************** Create package assessment start **************/
$('.completed-assessment').click(function () {
  let assesmentList = $('.assessment-detail-page');
  let score = 0;
  let assesment = [];
  assesmentList.each(function () {
    if ($(this).find('.op-assessment-checked')[0].checked) {
      score += Number($(this).find('.assessment-completed-score span').text());

      assesment.push(Number($(this).find('.op-assessment-checked').val()));
    }
  });

  let _token = $('input[name="_token"]').val();
  let userId = $('.op-assessment-checked').attr('data-operator');

  let data = {
    score: Math.round(score / assesmentList.length),
    assesment: JSON.stringify(assesment),
    userId,
    _token,
  };

  $('.package-response-loading').fadeIn();

  $.ajax({
    type: 'POST',
    url: '/package-assessment',
    dataType: 'json',
    data,
    success: function (res) {
      if (res.status === 200) {
        $('.package-response-loading').fadeOut();
        $('.modal-description').html(res.response);
        document.querySelector('#confirm-modal').showModal();
        setTimeout(function () {
          window.location.reload();
        }, 2500);
      }
    },
  });
});
/*************** Create package assessment end **************/

/****************** Back operator assessment start **********/
$(document).on('click', '.operator-assessment .fa-arrow-left', function () {
  window.location.reload();
});
/****************** Back operator assessment end ************/

/************************ Close modal start *****************/
$(document).on('click', '.assessment-modal-close', function () {
  $('#modal-container').addClass('out');
  $('body').removeClass('modal-active');
});
/********************* Close modal start ********************/

// function countdown() {
//     var now = new Date().getTime();
//     var pct = (now - start) / end;
//     pct = 0.3;
//     console.log(now);
//     console.log(start);
//     console.log(end);
//     console.log(pct);

//     setTimeout(function() {
//         // pct = 1;
//         $("#timer").val(pct);
//     }, 0);
//     console.log(pct);
//     // if (pct < 1) {
//     //     $("#timer").val(pct);
//     //     raf = requestAnimationFrame(countdown);
//     // }

//     // setTimeout(function() {
//     //     raf = requestAnimationFrame(countdown);
//     // }, 6000);
// }

// $("#go").on("click", function(e) {
//     start = new Date().getTime();
//     end = $("#sec").val() * 1000;

//     cancelAnimationFrame(raf);
//     raf = requestAnimationFrame(countdown);
//     return false;
// });

// $(document).on("click", ".assessment-visible", function() {
//     var buttonId = $(this).attr("data-modal");
//     var id = $(this).attr("data-id");
//     $("#modal-container")
//         .removeAttr("class")
//         .addClass(buttonId);
//     $("body").addClass("modal-active");

//     var fullname = $(this)
//         .parent()
//         .find(".operator-fullname")
//         .text();

//     $.ajax({
//         type: "get",
//         url: `/assessment/${id}`,
//         dataType: "json",
//         success: function(data) {
//             $("#modal-container .modal .list-group").html("");
//             if (data.assessment.length > 0) {
//                 data.assessment.forEach(item => {
//                     $("#modal-container .modal .list-group").append(
//                         '<a href="#" class="list-group-item list-group-item-action assessment-detail-page" data-id=' +
//                             item.id +
//                             '> <div class="d-flex w-100 justify-content-between"> <h5 class="mb-1">' +
//                             fullname +
//                             "</h5><small>" +
//                             '</small></div><p class="mb-1"> Yekun qiymətləndirmə balı: ' +
//                             item.score_count +
//                             " </p><small> " +
//                             item.begin_date +
//                             " - " +
//                             item.end_date +
//                             " </small></a>"
//                     );
//                 });
//             }
//         },
//         error: function(e) {
//             console.log(e);
//         }
//     });
// });
