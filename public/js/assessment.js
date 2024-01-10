/************************* Wavesurfer start **********************/
var wavesurfer = WaveSurfer.create({
    container: ".waveform, #waveform",
    waveColor: "#ccc",
    progressColor: "purple",
});

wavesurfer.on("ready", updateTimer);
wavesurfer.on("audioprocess", updateTimer);
wavesurfer.on("seek", updateTimer);

function updateTimer() {
    $(".audio-loading").hide();
    $(".body-detail").fadeIn();
    $(".audio-player").css("visibility", "initial");

    var formattedTime = secondsToTimestamp(wavesurfer.getCurrentTime());
    var commonDuration = secondsToTimestamp(wavesurfer.getDuration());
    $("#waveform-time-indicator .completed-time").text(commonDuration);
    $("#waveform-time-indicator .completed-time").attr(
        "data-second",
        wavesurfer.getDuration()
    );
    $("#waveform-time-indicator .time").text(formattedTime);
}

function secondsToTimestamp(seconds) {
    seconds = Math.floor(seconds);
    var h = Math.floor(seconds / 3600);
    var m = Math.floor((seconds - h * 3600) / 60);
    var s = seconds - h * 3600 - m * 60;

    h = h < 10 ? "0" + h : h;
    m = m < 10 ? "0" + m : m;
    s = s < 10 ? "0" + s : s;
    return h + ":" + m + ":" + s;
}
/************************ wavesurfer end ***********************/

/********** Assessment steps variables and audio timer function ********/

var commonAssessment = [];

var start = 0;
var end = 0;
var counter = 1 / 50;
var countdown = null;
function interval() {
    countdown = setInterval(function () {
        start++;

        end = start * counter;
        $("#audio-time").text(start);
        $("#timer").val(end);
        if (end === 1) {
            start = 0;
            $(".fa-pause-circle").attr("class", "far fa-play-circle");
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
    var valString = val + "";
    if (valString.length < 2) {
        return "0" + valString;
    } else {
        return valString;
    }
}
/******************** create timer function end *************************/

$(document).on("click", ".selected-user-info .far", function (e) {
    $(this).parent().parent().fadeOut();
});

/*************************** Assessment start *****************************/
$(document).on("click", ".button--add", function () {
    var val = $(this).parent().parent().find(".operator-fullname").text();

    var selectedDate = $(this)
        .parent()
        .parent()
        .find(".assestment-date")
        .text();

    var userId = $(this).attr("data-id");
    var userName = $(this).attr("data-user");

    $(".selected-user-assessment .continue-assestment").attr("data-id", userId);
    $(".selected-user-assessment .continue-assestment").attr(
        "data-user",
        userName
    );
    $(".selected-user-assessment .continue-assestment").attr(
        "data-operator",
        val
    );

    $(".selected-user-info span").text(val);

    $(".selected-user-show").fadeIn();

    selectedDate = selectedDate.split(" ")[0];

    selectedDate = new Date(selectedDate);

    selectedDate.setDate(selectedDate.getDate() + 1);

    if (selectedDate != "Invalid Date") {
        $(".begin-date, .end-date").datepicker("setStartDate", selectedDate);
    } else {
        $(".begin-date, .end-date").datepicker("setStartDate", null);
    }

    $(".assessment-dates").css("position", "relative");
    $(".assessment-dates").attr("style", "position: relative; opacity: 1");
    setTimeout(function () {
        $(".assessment-dates").attr("style", "position: relative; opacity: 1");
    }, 900);

    $(".assestment-services, .audio-cutter").fadeOut();

    $(".assestment-user").text(val);
    $(".step-section").fadeIn();
    $(".operator-id").val($(this).attr("data-id"));
});
/*************************** Assessment end *****************************/

/******************** Completed assessment start ******************/
$(".assesment-completed").click(function () {
    let beginDate = $(".begin-date").val();
    let endDate = $(".end-date").val();
    let _token = $('input[name="_token"]').val();
    let id = $(".step-container-calls table").attr("data-id");

    let operatorId = $(".operator-id").val();
    let comment = $(".call-comment").val();
    let supervisorTimer = $("#minutes").text() + ":" + $("#seconds").text();

    let criteryList = $(".critery-list .add-critery").map(function () {
        return {
            id: $(this).attr("data-id"),
            count: 0,
            score: Number($(this).parent().attr("data-score")),
        };
    });

    let allCriteryList = criteryList.map(function (index, item) {
        commonAssessment.forEach(function (assessment) {
            if (assessment.criterias !== null) {
                if (
                    assessment.criterias.some(function (critery) {
                        return item.id == critery.id;
                    }) &&
                    item.count + item.score < 3
                ) {
                    item.count += item.score;
                }
            }
        });
        return item;
    });

    assessmentCount = 33;
    allCriteryList.map(function (inds, element) {
        assessmentCount -= element.count > 3 ? 3 : element.count;
    });

    let data = {
        beginDate,
        _token,
        id: Number(id),
        operatorId: Number(operatorId),
        endDate,
        commonAssessment,
        supervisorTimer,
        comment,
        assessmentScore: assessmentCount,
    };

    if (commonAssessment.length > 0) {
        $(".response-loading").css("display", "list-item");
        $.ajax({
            type: "POST",
            url: "/new-assessment",
            dataType: "json",
            data,
            success: function (res) {
                if (res.status === 200) {
                    $(".response-loading").fadeOut();
                    $(".modal-description").html("Uğurlu əməliyyat");
                    document.querySelector("#confirm-modal").showModal();
                    setTimeout(function () {
                        location.reload();
                    }, 2500);
                }
            },
        });
    } else {
        $(".modal-description").html("Zehmet olmasa qiymetlendirme edin");
        document.querySelector("#confirm-modal").showModal();
    }
});
/******************** Completed assessment end ******************/

$(function () {
    $(".input-daterange input").each(function () {
        $(this).datepicker({
            autoclose: true,
            todayHighlight: true,
            language: "az",
            Default: true,
        });
    });

    $(".continue-assestment").click(function () {
        $(".operator-id").val($(this).attr("data-id"));
        $(".step-container-operators").fadeOut();
        let id = $(this).attr("data-id");

        let criteryList = $(".critery-list .add-critery").map(function () {
            // return { id: $(this).attr("data-id"), count: 0 };
            return {
                id: $(this).attr("data-id"),
                count: 0,
                score: Number($(this).parent().attr("data-score")),
            };
        });

        var fullName = "";
        if ($(this).hasClass("next-process")) {
            $(
                ".operator-step .step-icon .fa-user, .date-step .step-icon .fa-business-time, .services-step .step-icon .fa-cog"
            ).attr("class", "fas fa-check active-step");

            $(".step-icon .fa-folder-open").addClass("active-step");
            $(".step-container-calls").fadeIn();
            fullName = $(this).attr("data-operator");
            $(".step-container-calls tbody").html(
                '<img src="img/loading-audio.gif" class="audio-loading table-data-loading" alt="">'
            );
            $.ajax({
                type: "get",
                url: `/get-assessment/${id}`,
                dataType: "json",
                success: function (data) {
                    $(".step-container-calls tbody").html("");
                    var criteryCount = $(".critery-list ul li").length;
                    //all tables row sum
                    let assessmentTimeSum = 0;
                    let playTimeSum = 0;
                    let notListenTime = 0;
                    let specialTimeSum = 0;
                    let audioAllTimes = 0;

                    let assesmentCount = 0;
                    let assesmentScore = 0;

                    data.calls.forEach((item, index) => {
                        let allTimer =
                            " </td><td>" +
                            '<div class="supervisor-counter audio-all-times" data-second="0"><label class="minutes">00</label>:<label class="seconds">00</label></div>' +
                            '<div class="supervisor-counter assest-time" data-second="0"><label class="minutes">00</label>:<label class="seconds">00</label></div>' +
                            '<div class="supervisor-counter play-time" data-second="0"><label class="minutes">00</label>:<label class="seconds">00</label></div>';

                        if (item.assessmentTime !== null) {
                            assessmentTimeSum += item.assessmentTime;
                            playTimeSum += item.playTime;

                            audioAllTimes += item.audioTime;
                            allTimer =
                                " </td><td>" +
                                '<div class="supervisor-counter audio-all-times" data-second="' +
                                item.audioTime +
                                '"><label class="minutes">' +
                                secondsToTimestamp(item.audioTime)
                                    .substring(3)
                                    .split(":")[0] +
                                '</label>:<label class="seconds">' +
                                secondsToTimestamp(item.audioTime)
                                    .substring(3)
                                    .split(":")[1] +
                                "</div>" +
                                '<div class="supervisor-counter assest-time" data-second="' +
                                item.assessmentTime +
                                '"><label class="minutes">' +
                                secondsToTimestamp(item.assessmentTime)
                                    .substring(3)
                                    .split(":")[0] +
                                '</label>:<label class="seconds">' +
                                secondsToTimestamp(item.assessmentTime)
                                    .substring(3)
                                    .split(":")[1] +
                                "</div>" +
                                '<div class="supervisor-counter play-time" data-second="' +
                                item.playTime +
                                '"><label class="minutes">' +
                                secondsToTimestamp(item.playTime)
                                    .substring(3)
                                    .split(":")[0] +
                                '</label>:<label class="seconds">' +
                                secondsToTimestamp(item.playTime)
                                    .substring(3)
                                    .split(":")[1] +
                                "</label></div>";
                        }

                        $(".step-container-calls table").attr("data-id", id);
                        let status = "";

                        let callTypes = JSON.parse(item.wrongSelection);
                        let type = "";
                        if (callTypes ? callTypes.length : 0 > 0) {
                            $(".callTypes option").each(function () {
                                if (
                                    callTypes.some(
                                        (item) => item == $(this).val()
                                    )
                                ) {
                                    type += $(this).text() + "; ";
                                }
                            });
                            status =
                                type.length > 0
                                    ? type.slice(0, type.length - 2)
                                    : type;
                        } else {
                            status =
                                item.criterias !== null || item.comment !== null
                                    ? "Qiymətləndirilib"
                                    : "";
                        }

                        if (item.criterias !== null) {
                            assesmentCount += 1;
                            assesmentScore += item.count;
                        }
                        $(".step-container-calls tbody").append(
                            "<tr data-recording-id='" +
                                item.recording_id +
                                "' data-number='" +
                                item.citizen_number +
                                "' data-assessment=" +
                                item.assessmentId +
                                " data-call=" +
                                item.callId +
                                " data-collapse='0'> <td>" +
                                (index + 1) +
                                "</td> <td class='call-citizen-number'>" +
                                item.citizen_number +
                                "<br/><small>" +
                                (item.beginDate != undefined
                                    ? item.beginDate
                                    : "") +
                                "</small></td><td class='call-organ-name'>" +
                                item.organName +
                                "</td> <td class='call-service-name'> " +
                                item.serviceName +
                                allTimer +
                                " </td>  <td class='call-assessment-status'>" +
                                status +
                                "</td><td class='completed-score'>" +
                                (item.count ? item.count : 33) +
                                "</td></tr>"
                        );

                        if (item.criterias != null) {
                            let callInfo = {
                                call: item.callId,
                                count: item.count,
                                comment: item.comment,
                                criterias: JSON.parse(item.criterias),
                                time: item.assessmentTime,
                                playTime: item.playTime,
                                unPlayTime: item.unPlayTime,
                                specialTime: item.specialTime,
                                audioTime: item.audioTime,
                                wrongSelection: item.wrongSelection,
                            };

                            commonAssessment.push(callInfo);
                        }
                    });

                    $(".assessment-statistics .call-status").text(
                        assesmentCount
                    );

                    $(".selected-call-assessment span").text(data.calls.length);

                    $(".assessment-statistics .calls-completed-score").text(
                        data.score != null
                            ? data.score + " / " + data.percent + "%"
                            : 33 + "/100%"
                    );

                    $(".all-listening-times .supervisor-counter")
                        .eq(1)
                        .text(
                            secondsToTimestamp(assessmentTimeSum).substring(3)
                        );

                    $(".all-listening-times .play-time").text(
                        secondsToTimestamp(playTimeSum).substring(3)
                    );

                    $(".all-listening-times .audio-all-times").text(
                        secondsToTimestamp(audioAllTimes).substring(3)
                    );
                },
            });
        } else {
            $(".operator-step .step-icon .fa-user").attr(
                "class",
                "fas fa-check active-step"
            );
            $(".step-icon .fa-business-time").addClass("active-step");
            $(".step-container-date").fadeIn();
            fullName = $(".selected-user-info span").text();
        }

        $(".selected-operator-info span").text(fullName);
    });

    $(".action-button").click(function () {
        var beginDate = $(".step-container-date").find(".begin-date").val();
        var endDate = $(".step-container-date").find(".end-date").val();
        var operatorId = $(".operator-id").val();
        if (beginDate !== "" && endDate !== "") {
            $(".response-loading").css("display", "block");
            $.ajax({
                type: "get",
                // url: `/calls/${operatorId}/${beginDate}/${endDate}`,
                url: `/service-calls`,
                dataType: "json",
                data: {
                    internal_number: operatorId,
                    start_date: beginDate,
                    end_date: endDate,
                },
                success: function (data) {
                    $(".response-loading").fadeOut();
  
                    if (data.status == 200) {
                        if (data.total_service_count > 0) {
                            let callCount = 0;
                            $(".step-icon .fa-business-time").attr(
                                "class",
                                "fas fa-check active-step"
                            );
                            $(".step-icon .fa-cog").addClass("active-step");
                            $(".step-container-date").fadeOut();
                            $(".step-container-services").fadeIn();
                            var selected = [
                                40, 45, 46, 47, 51, 59, 66, 90, 94, 128,
                            ];
                            data.services.forEach((item, index) => {
                                if (Number(item.call_count) > 0) {
                                    callCount += Number(item.call_count);
                                    $(".step-container-services tbody").append(
                                        "<tr> <td> " +
                                            (index + 1) +
                                            " </td> <td> " +
                                            item.service_name +
                                            " </td><td>" +
                                            item.call_count +
                                            "</td></tr>"
                                    );
                                } else {
                                    $(".services-list").text(
                                        "Seçilən tarixlərdə zəng tapılmadı"
                                    );
                                }
                            });
                            if (callCount > 0) {
                                $(".selected-services span").text(
                                    data.services.length
                                );
                                $(".selected-calls span").text(callCount);
                                $(".selected-services-count").attr(
                                    "value",
                                    data.services.length
                                );
                                $(".selected-call-count").attr(
                                    "value",
                                    callCount
                                );
                                $(".selected-not-days span").html(
                                    data.selected_days_count +
                                        "(<span style='color: red'>" +
                                        data.notWork +
                                        "</span>)"
                                );
                            }
                        } else {
                            $(".modal-description").html(
                                "Seçilən tarix aralığında heç bir xidmətə uyğun zəng tapılmadı"
                            );
                            document
                                .querySelector("#confirm-modal")
                                .showModal();
                        }
                    } else {
                        $(".modal-description").html(
                            "Seçilən tarix aralığında heç bir xidmətə uyğun zəng tapılmadı"
                        );
                        document.querySelector("#confirm-modal").showModal();
                    }
                },
                error: function (e) {
                    console.log(e);
                    $(".response-loading").fadeOut();
                },
            });
        }
    });

    $("#name-filter").keyup(function () {
        let key = $(this).val().toUpperCase();
        $(".table-scrool tbody tr").hide();
        if (key.length > 0) {
            $(".table-scrool tbody tr").each(function () {
                if (
                    $(this)
                        .find(".operator-fullname")
                        .text()
                        .toUpperCase()
                        .search(key) >= 0
                ) {
                    $(this).show();
                }
            });
        } else {
            $(".table-scrool tbody tr").show();
        }
    });

    $(".assessment-filter-box input").change(function () {

        let beginDate = ($(".assessment-filter-box .begin-date").val()).toUpperCase();
        let endDate = ($(".assessment-filter-box .end-date").val()).toUpperCase();
        let score = ($("#assessment-score input").val()).toUpperCase();
        let fullName = ($("#operator-fullname input").val()).toUpperCase();
 
        $(".table-scrool tbody tr").hide();
        if (beginDate.length > 0 || endDate.length > 0 || score.length > 0 || fullName.length > 0) { 
            $(".table-scrool tbody tr").each(function () {
                let startD = $(this).find(".assessment-date").text().split("--")[0];
                let endD = $(this).find(".assessment-date").text().split("--")[1]; 
                if (
                    !(!($(this).find(".operator-fullname").text().toUpperCase().search(fullName) >= 0) || 
                    !(startD.search(beginDate) >= 0) || !(endD.search(endDate) >= 0) || 
                    !($(this).find(".assessment-score").text().search(score) >= 0))
                ) {  
                    $(this).show();
                }
            });
        } else {
            $(".table-scrool tbody tr").show();
        }
    });

    // $(".call-assessment-next").click(function () {

    // })

    $(".call-assessment-next").click(function () {
        var operatorNumber = $(".operator-id").val();
        var operatorId = $(".continue-assestment").attr("data-user");
        var checkedCount = $("#checked-count").val();
        var services = $(".step-container-services tbody tr");
        var callCount = 0;
        services.each(function () {
            callCount += Number($(this).find("td").last().text());
        });

        if (checkedCount !== "" && services.length > 0) {
            let servicesCount = $(".selected-services-count").val();
            let allCallCount = $(".selected-call-count").val();
            if (callCount >= checkedCount) {
                $(".response-loading").css("display", "block");
                $.ajax({
                    type: "get",
                    url: `/calls/${servicesCount}/${allCallCount}/${operatorId}/${operatorNumber}/${$(
                        ".begin-date"
                    ).val()}/${$(".end-date").val()}/${checkedCount}`,
                    dataType: "json",
                    success: function (data) {

                        if(data.status ==200) {
                            $(".step-container-services").fadeOut();
                            $(".step-icon .fa-cog").attr(
                                "class",
                                "fas fa-check active-step"
                            );
                            $(".step-icon .fa-folder-open").addClass("active-step");
                            $(".step-container-calls").fadeIn();
                            $(".response-loading").fadeOut();

                            $(".step-container-calls table").attr(
                                "data-id",
                                data.id
                            );

                            $(".selected-call-assessment span").text(checkedCount);

                            var criteryCount = $(".critery-list ul li").length;
                            data.calls.forEach((item, index) => {
                                $(".step-container-calls tbody").append(
                                    "<tr data-assessment=" +
                                        item.assessmentId +
                                        " data-recording-id='" +
                                        item.recording_id +
                                        "' data-number=" +
                                        item.citizen_number +
                                        " data-call=" +
                                        item.callId +
                                        " data-collapse='0'> <td>" +
                                        (index + 1) +
                                        "</td> <td class='call-citizen-number'>" +
                                        item.citizen_number +
                                        "<br/><small>" +
                                        item.beginDate +
                                        "</small></td><td class='call-organ-name'>" +
                                        item.organName +
                                        "</td> <td class='call-service-name'> " +
                                        item.serviceName +
                                        " </td><td>" +
                                        '<div class="supervisor-counter audio-all-times" data-second="0"><label class="minutes">00</label>:<label class="seconds">00</label></div>' +
                                        '<div class="supervisor-counter assest-time" data-second="0"><label class="minutes">00</label>:<label class="seconds">00</label></div>' +
                                        '<div class="supervisor-counter play-time" data-second="0"><label class="minutes">00</label>:<label class="seconds">00</label></div>' +
                                        " </td> <td class='call-assessment-status'></td><td class='completed-score'>" +
                                        33 +
                                        "</td></tr>"
                                );
                            });
                        } else {
                            $(".response-loading").fadeOut();
                            $(".modal-description").html(data.message);
                            document.querySelector("#confirm-modal").showModal();
                        }
                    },
                    error: function (e) {
                        console.log(e);
                        $(".response-loading").fadeOut();
                        $(".modal-description").html("Sistem xətası");
                        document.querySelector("#confirm-modal").showModal();
                    },
                });
            } else {
                $(".modal-description").html(
                    "Daxil etdiyiniz rəqəm zəng sayından çoxdur"
                );
                document.querySelector("#confirm-modal").showModal();
            }
        } else {
            $(".modal-description").html("Zəng və ya say daxil edilməyib");
            document.querySelector("#confirm-modal").showModal();
        }
    });

    $(".packed-assessment").click(function () {
        $(".operator-assessment tbody tr").hide();
        $(".assessment-packages").fadeIn();
    });

    $(".common-assessment-tab").click(function () {
        $(".operator-assessment tbody tr").fadeIn();
    });

    /**************** Call research *****************/
    $(".call-refresh, #call-refresh").click(function () {
        let conf = confirm("Zəngi yeniləməyə əminsinizmi?");
        let status = $(this).attr("data-status");
        let id = $(this).attr("data-assessment");
        if (conf) {
            $.ajax({
                type: "get",
                url: `/research-call/${id}/${status}`,
                dataType: "json",
                success: function (data) {
                    if (data.status == 200) {
                        document.querySelector("#confirm-modal").close(); 
                        $(".active-line").attr(
                            "data-start-date",
                            data.startDate
                        );
                        $(".active-line").attr(
                            "data-end-date",
                            data.endDate != null ? data.endDate : ""
                        );
                        $(".active-line").attr("data-number", data.number);
                        $(".active-line").attr("data-call", data.callId);
                        if (timerInterval) {
                            clearInterval(timerInterval);
                        }
                        $(".call-refresh").fadeOut();
                        
                        $(".active-line")
                            .find(".call-citizen-number")
                            .html(
                                data.number +
                                    " <br/> <small>" +
                                    data.startDate +
                                    "</small>"
                            );

                        $(".active-line")
                            .find(".call-organ-name")
                            .text(data.organ);
                        $(".active-line")
                            .find(".call-service-name")
                            .text(data.service);
                        $(".active-line").removeClass("active-line");
                        wavesurfer.pause();
                        $(".audio-player, .body-detail").fadeOut();
                        alert(data.message);
                    } else {
                        alert("Zəng tapılmadı");
                    }
                },
            });
        }
    });
});

$(".speed-adjustment>div").click(function(){

    $(".speed-adjustment>div").removeClass("active");
    $(this).addClass("active")
    const speedVal = $(this).attr("data-speed");
    wavesurfer.setPlaybackRate(speedVal); 
})

/*************************** Critery add start *************************/
$(document).on("click", ".critery-list li", function () {
    var parent = $(this).parent().parent().parent();

    let clicked = Number($(this).find(".badge-success").text());

    $(this)
        .find(".badge-success")
        .text(++clicked);

    $(this).find(".badge-success").css("opacity", "1");

    var parentTime = $("#waveform").parent().find(".time");

    var decrement = $(this).find(".badge-pill").text();

    var score = parent.find(".common-score span").text();

    score = Number(score) - Number(decrement);

    if (score >= 0) {
        parent.find(".common-score span").text(score);
    }

    $(this).css("background-color", "#5f9ea04f");

    var totalTime =
        Number(parentTime.text().split(":")[1]) * 60 +
        Number(parentTime.text().split(":")[2]);

    var currentTime = totalTime;

    if (totalTime > 5) {
        var time =
            parentTime.text().split(":")[1] +
            ":" +
            (Number(parentTime.text().split(":")[2]) - 5);
        currentTime -= 5;
    } else {
        currentTime = 0;
        var time = parentTime.text().split(":")[1] + ":" + "00";
    }

    var critery = $(this).find(".critery-name").text();

    parent
        .find(".selected-items")
        .append(
            '<li class="list-group-item d-flex justify-content-between align-items-center"><span class="selected-critery-name">' +
                critery +
                '</span> <span class="badge badge-primary selected-time">' +
                time +
                '</span> <span class="badge badge-primary badge-pill">' +
                $(this).find(".badge-pill").text() +
                '</span> <i class="far fa-trash-alt" data-id=' +
                $(this).attr("data-id") +
                "></i><i class='far fa-play-circle' data-second=" +
                currentTime +
                "></i></li>"
        );
});
/*************************** Critery add end *****************************/

/************************* Critery player start **************************/
$(document).on(
    "click",
    ".selected-items .fa-play-circle, .calls-assessment-details .list-group .fa-play-circle",
    function () {
        var second = $(this).attr("data-second");

        var formattedTime = secondsToTimestamp(Number(second));

        wavesurfer.setCurrentTime(Number(second));

        $("#waveform-time-indicator .time").text(formattedTime);
    }
);
/************************* Critery player end **************************/

/************************* Critery delete start ************************/
$(document).on("click", ".selected-items .fa-trash-alt", function () {
    var score = $(this.parentNode)
        .parent()
        .parent()
        .find(".common-score span")
        .text();

    criteryList = $(".critery-list li .badge-success");

    criteryId = $(this).attr("data-id");

    criteryList.each(function () {
        if ($(this).attr("data-id") == criteryId) {
            let count = Number($(this).text()) - 1;
            $(this).text(count);
            if (count == 0) {
                $(this).parent().css("background-color", "#fff");
                $(this).parent().find(".badge-success").css("opacity", "0");
            } else {
                $(this)
                    .parent()
                    .find(".critery-list .badge-success")
                    .css("opacity", "1");
            }
        }
    });

    var decrement = $(this).parent().find(".badge-pill").text();

    score = Number(score) + Number(decrement);
    $(this).parent().remove();

    $(".common-score span").text(score);
});
/************************* Critery delete end ************************/

/************************* Delete all critery start ******************/
$(document).on("click", ".clear-common-score", function () {
    let allLi = $(".critery-list li");
    let next = Number($(this).prev().find("span").text());

    var parent = $(this).next().find("li");

    parent.each(function () {
        next += Number($(this).find(".badge-pill").text());
    });

    $(".critery-list li").css("background-color", "#fff");

    $(".critery-list .badge-success").css("opacity", "0");

    $(this)
        .prev()
        .find("span")
        .text(allLi.length * 3);

    $(this).next().html("");

    $(".critery-list .badge-success").text("0");
});
/************************* Delete all critery end ********************/

/*********************** Call assessment start ***********************/
$(document).on("click", ".call-assessment, #call-not-assessment", function () {
    var criterias = $(this).parent().find(".selected-items .list-group-item");

    var criteraList = [];
    criterias.each(function () {
        criteraList.push({
            id: $(this).find(".fa-trash-alt").attr("data-id"),
            name: $(this).find(".selected-critery-name").text(),
            time: $(this).find(".selected-time").text(),
            count: $(this).find(".selected-time").next().text(),
            second: $(this).find(".fa-play-circle").attr("data-second"),
        });
    });

    let score = $(this).parent().parent().find(".common-score span").text();

    wavesurfer.pause();
    if (playInterval) {
        clearInterval(playInterval);
    }

    $(".audio-play").attr("data-play", "0");

    let playTime = Number($("#play-time").val());

    let notPlayTime = wavesurfer.getDuration() - playTime;

    let Ctype = $(this).parent().find(".callTypes").val();

    let callTypes = Ctype==null || Ctype.includes("0") ? null : Ctype; 

    let specialTime =
        (Number(
            $(".active-line .supervisor-counter").eq(1).attr("data-second")
        ) -
            wavesurfer.getDuration() -
            playTime -
            notPlayTime) /
            60 +
        5 * criteraList.length;

    // special-calculate-time

    let callInfo = {
        call: $(this).parent().find(".selected-items").attr("data-call"),
        assessmentCriteryId: Number($(".active-line").attr("data-assessment")),
        count: score,
        comment: $(".call-comment").val(),
        criterias: criteraList,
        time: Number(
            $(".active-line .supervisor-counter").eq(1).attr("data-second")
        ),
        playTime: playTime,
        unPlayTime: notPlayTime > 0 ? notPlayTime : 0,
        specialTime: specialTime > 0 ? specialTime : 0,
        audioTime: wavesurfer.getDuration(),
        wrongSelection: callTypes ? JSON.stringify(callTypes) : "",
        status: Number($(this).attr("data-status")),
    };

    let check = commonAssessment.some(
        (element) => element.call == callInfo.call
    );

    assIndex = commonAssessment.findIndex(
        (element) => element.call == callInfo.call
    );
    if (assIndex != -1) {
        commonAssessment[assIndex] = callInfo;
    } else {
        if (!check || commonAssessment.length > 0) {
            commonAssessment.push(callInfo);
        }
    }

    let criteryList = $(".critery-list .add-critery").map(function () {
        return {
            id: $(this).attr("data-id"),
            count: 0,
            score: Number($(this).parent().attr("data-score")),
            maxScore: $(this).attr("data-maxscore"),
        };
    });

    let allCriteryList = criteryList.map(function (index, item) {
        commonAssessment.forEach(function (assessment, inds) {
            if (assessment.criterias !== null) {
                if (
                    assessment.criterias.some(function (critery) {
                        return item.id == critery.id;
                    }) &&
                    item.count < item.maxScore
                ) {
                    item.count += item.score;
                }
            }
        });
        return item;
    });

    assessmentCount = 33;
    let val = 0;
    allCriteryList.map(function (inds, element) {
        assessmentCount -=
            element.count > Number(element.maxScore)
                ? Number(element.maxScore)
                : element.count;
        if (element.count <= Number(element.maxScore)) {
            val += element.count;
        } 
    }); 
    $(".assessment-statistics .calls-completed-score").text(
        assessmentCount.toFixed(2) +
            " / " +
            Math.round((assessmentCount.toFixed(2) / 33) * 100) +
            "%"
    );

    callInfo.assessmentScore = assessmentCount; 
    callInfo._token = $('input[name="_token"]').val();

    let content = this;

    let selected = $(this);

    if (criteraList.length > 0 || $(".call-comment").length > 0) {
        $(this).find(".assessment-call-loading").fadeIn();

        $.ajax({
            type: "POST",
            url: "/update-call",
            dataType: "json",
            data: callInfo,
            success: function (res) {
                if (res.status == 200) {
                    let scoreCount = Number($(".common-score span").text());

                    let tableRow = $(".step-container-calls table tbody tr");

                    tableRow.each(function () {
                        if ($(this).hasClass("active-line")) {
                            $(this).find(".completed-score").text(scoreCount);
                        }
                    });

                    //active row seconds
                    $(".active-line .play-time").text(
                        secondsToTimestamp(playTime).substring(3)
                    );
                    $(".active-line .play-time").attr("data-second", playTime);

                    $(".active-line .audio-all-times").text(
                        secondsToTimestamp(wavesurfer.getDuration()).substring(
                            3
                        )
                    );

                    $(".active-line .audio-all-times").attr(
                        "data-second",
                        wavesurfer.getDuration()
                    );

                    $(".assessment-call-loading").fadeOut();

                    let type = "";
                    if (callTypes ? callTypes.length : 0 > 0) {
                        $(".callTypes option").each(function () {
                            if (
                                callTypes.some((item) => item == $(this).val())
                            ) {
                                type += $(this).text() + "; ";
                            }
                        });
                        type =
                            type.length > 0
                                ? type.slice(0, type.length - 2)
                                : type; 
                        $(".active-line .call-assessment-status").text(type);
                    } else { 
                        $(".active-line .call-assessment-status").text(
                            Number(selected.attr("data-status")) == 0
                                ? "Qimətləndirilməyib"
                                : "Qimətləndirilib"
                        );
                    }

                    $(".step-container-calls table tbody tr").removeClass(
                        "active-line"
                    );
                    $(".audio-player, .body-detail").fadeOut();
                    clearInterval(timerInterval);

                    $(content)
                        .parent()
                        .parent()
                        .find(".card-header span")
                        .html('<i class="fas fa-check"></i> ' + score);

                    //all tables row sum
                    let assessmentTimeSum = 0;
                    let playTimeSum = 0;
                    let notListenTime = 0;
                    let specialTimeSum = 0;
                    let completedAssessment = 0;
                    let completedScore = 0;

                    tableRow.each(function () {
                        assessmentTimeSum += Number(
                            $(this)
                                .find(".supervisor-counter")
                                .eq(1)
                                .attr("data-second")
                        );

                        playTimeSum += Number(
                            $(this).find(".play-time").attr("data-second")
                        );

                        if (
                            $(this).find(".call-assessment-status").text() != ""
                        ) {
                            completedAssessment += 1;
                            completedScore += Number(
                                $(this).find(".completed-score").text()
                            );
                        }
                    });

                    $(".assessment-statistics .call-status").text(
                        completedAssessment
                    );

                    // $(".assessment-statistics .calls-completed-score").text(
                    //     Math.ceil(completedScore / completedAssessment)
                    // );

                    $(".all-listening-times .supervisor-counter")
                        .eq(1)
                        .text(
                            secondsToTimestamp(assessmentTimeSum).substring(3)
                        );

                    $(".all-listening-times .play-time").text(
                        secondsToTimestamp(playTimeSum).substring(3)
                    );

                    $(".all-listening-times .audio-all-times").text(
                        secondsToTimestamp(wavesurfer.getDuration()).substring(
                            3
                        )
                    );
                }
            },
        });
    } else {
        $(".modal-description").html(
            "Zəhmət olmasa qiymətləndirmə edin və ya səbəb daxil edin"
        );
        document.querySelector("#confirm-modal").showModal();
    }
});
/*********************** Call assessment end ***********************/

/*************************** steppers ******************************/

$(document).on("click", ".operator-step i", function () {
    if (!$(".assessment-step i").hasClass("active-step")) {
        $(this).attr("class", "fas fa-user active-step");
        $(".date-step i").attr("class", "fas fa-business-time");
        $(".services-step i").attr("class", "fas fa-cog");
        $(".assessment-step i").attr("class", "far fa-folder-open");
        $(".step-container-operators").fadeIn();
        $(
            ".step-container-date, .step-container-services, .step-container-calls"
        ).fadeOut();
    }
});

$(document).on("click", ".date-step i", function () {
    if (!$(".assessment-step i").hasClass("active-step")) {
        $(".operator-step i").attr("class", "fas fa-check active-step");
        $(this).attr("class", "fas fa-business-time active-step");
        $(".services-step i").attr("class", "fas fa-cog");
        $(".assessment-step i").attr("class", "far fa-folder-open");
        $(".step-container-date").fadeIn();
        $(
            ".step-container-operators, .step-container-services, .step-container-calls"
        ).fadeOut();
    }
});

$(document).on("click", ".services-step i", function () {
    if (!$(".assessment-step i").hasClass("active-step")) {
        $(".services-step i").attr("class", "fas fa-user active-step");
        $(".date-step i").attr("class", "fas fa-check active-step");
        $(this).attr("class", "fas fa-cog active-step");
        $(".assessment-step i").attr("class", "far fa-folder-open");
        $(
            ".step-container-operators, .step-container-date, .step-container-calls"
        ).fadeOut();
        $(".step-container-calls").fadeOut();
        $(".step-container-services").fadeIn();
    }
});

/**************************** Audio player  start *****************************/
var playInterval = null;
$(document).on(
    "click",
    ".audio-play, .audio-play .fa-pause, .audio-play .fa-play",
    function () {
        clearInterval(playInterval);
        var play = $(this).attr("data-play");
        if (play == "0") {
            wavesurfer.play();
            $(this).attr("data-play", "1");
            $(".audio-play .fa-play").attr("class", "fas fa-pause");

            totalSeconds = Number($("#play-time").attr("value"));
            playInterval = setInterval(function () {
                ++totalSeconds;
                $("#play-time").attr("value", totalSeconds);
            }, 1000);
        } else {
            wavesurfer.pause();

            $(this).attr("data-play", "0");
            $(".audio-play .fa-pause").attr("class", "fas fa-play");
        }
    }
);

wavesurfer.on("pause", function () {
    clearInterval(playInterval);
    wavesurfer.pause();

    $(this).attr("data-play", "0");
    // $(".audio-play .fa-pause").attr("class", "fas fa-play");
});
/**************************** Audio player end *****************************/

/********************** call assessment start ****************************/
var timerInterval = null;
$(document).on("click", ".step-container-calls table tbody tr", function () {
    $(".critery-list .badge-success").text("0");
    if (playInterval) {
        clearInterval(playInterval);
    }

    var assessmentId = $(this).attr("data-assessment");
    $(".step-container-calls table tbody tr")
        .not(this)
        .attr("data-collapse", "0");
    var collapse = $(this).attr("data-collapse");

    var audioId = $(this).attr("data-recording-id");

    document.querySelector(".new-critery-list .selected-items").innerHTML = "";

    $(".call-comment").val("");

    let totalSeconds = Number(
        $(this).find(".supervisor-counter").eq(1).attr("data-second")
    );
    let secondsLabel = this.querySelector(".assest-time .seconds");
    let minutesLabel = this.querySelector(".assest-time .minutes");
    let context = this;

    if (
        collapse == 0 &&
        !$(".step-container-calls table tbody tr").hasClass("active-line")
    ) {
        $(this).addClass("active-line");

        $(".selected-items").attr("data-call", $(this).attr("data-call"));

        timerInterval = setInterval(function () {
            ++totalSeconds;
            setTime(secondsLabel, minutesLabel, totalSeconds);
            $(context)
                .find(".supervisor-counter")
                .eq(1)
                .attr("data-second", totalSeconds.toString());
        }, 1000);

        $(this).attr("data-collapse", "1");

        $(".critery-list li").css("background-color", "#fff");
        $(".critery-list .badge-success").css("opacity", "0");

        $.ajax({
            type: "get",
            // url: `/get-audio-checker/${number}/${beginDate}/${endDate}/${assessmentId}`,
            url: `/get-audio-window/${audioId}/${assessmentId}`,
            dataType: "json",
            success: function (data) {
                let totalScore = 33;

                $(".common-score span").text(totalScore);
                if (data.code == 200) {
                    $(".call-refresh").css("display", "none");
                    $("#call-refresh, #call-not-assessment").attr(
                        "data-assessment",
                        assessmentId
                    );
                    $(".callTypes").val(
                        JSON.parse(data.assessmentCritery.wrongSelection)
                    );

                    $("#play-time").val(
                        data.assessmentCritery.playTime == null
                            ? 0
                            : data.assessmentCritery.playTime
                    );

                    $("#play-time").val(
                        data.assessmentCritery.playTime == null
                            ? 0
                            : data.assessmentCritery.playTime
                    );

                    $(".call-comment").val(
                        data.assessmentCritery.comment == null
                            ? ""
                            : data.assessmentCritery.comment
                    );
                    if (
                        data.assessmentCritery &&
                        data.assessmentCritery.criterias != null
                    ) {
                        $(".critery-list li").each(function () {
                            let obj = this;
                            let arr = JSON.parse(
                                data.assessmentCritery.criterias
                            ).filter(function (item) {
                                return (
                                    $(obj)
                                        .find(".add-critery")
                                        .attr("data-id") == item.id
                                );
                            });

                            if (arr.length > 0) {
                                $(this).css(
                                    "background-color",
                                    "rgba(95, 158, 160, 0.31)"
                                );
                                $(this)
                                    .find(".badge-success")
                                    .css("opacity", "1");
                            } else {
                                $(this).css("background-color", "#fff");
                            }

                            $(this).find(".badge-success").text(arr.length);
                        });

                        let str = "";
                        JSON.parse(data.assessmentCritery.criterias).forEach(
                            function (criteryItem) {
                                str +=
                                    '<li class="list-group-item d-flex justify-content-between align-items-center"><span class="selected-critery-name">' +
                                    criteryItem.name +
                                    '</span> <span class="badge badge-primary selected-time">' +
                                    criteryItem.time +
                                    '</span> <span class="badge badge-primary badge-pill">' +
                                    criteryItem.count +
                                    '</span> <i class="far fa-trash-alt" data-id=' +
                                    criteryItem.id +
                                    "></i><i class='far fa-play-circle' data-second=" +
                                    criteryItem.second +
                                    "></i></li>";
                            }
                        );

                        $(".common-score span").text(
                            data.assessmentCritery.count == 0
                                ? 33
                                : data.assessmentCritery.count
                        );

                        document.querySelector(
                            ".new-critery-list .selected-items"
                        ).innerHTML = str;
                    }

                    $(".step-section").animate(
                        {
                            scrollTop: 0,
                        },
                        3000
                    );

                    var xmlhttp = new XMLHttpRequest();

                    xmlhttp.open(
                        "GET",
                        // `/get-mediasense/${number}/${beginDate}/${endDate}/${assessmentId}`,
                        `/get-audio-loader/${audioId}/${assessmentId}`,
                        true
                    );
                    $(".audio-loading").fadeIn();
                    xmlhttp.responseType = "blob";
                    xmlhttp.withCredentials = "true";
                    xmlhttp.onreadystatechange = function (e) {
                        if (xmlhttp.readyState === 4) {
                            var audioFile = xmlhttp.response;

                            if (audioFile && xmlhttp.status == 200) {
                                $(".audio-player").fadeIn();
                                wavesurfer.loadBlob(audioFile);
                                $(".audio-loading").fadeOut();
                            } else {
                                // alert("Səs yüklənmədi");
                                $(".audio-loading").fadeOut();
                                $(".modal-description").html(
                                    "Audio Yüklənmədi"
                                );
                                $(".call-refresh").css("display", "block");
                                $(".call-refresh").attr(
                                    "data-assessment",
                                    assessmentId
                                );
                                document
                                    .querySelector("#confirm-modal")
                                    .showModal();
                            }
                        }
                        if (xmlhttp.readyState === 0) {
                            console.log(new Date().toLocaleString());
                        }
                        if (xmlhttp.readyState === 1) {
                            console.log(new Date().toLocaleString());
                        }
                        if (xmlhttp.readyState === 2) {
                            console.log(new Date().toLocaleString());
                        }
                        if (xmlhttp.readyState === 3) {
                            console.log(new Date().toLocaleString());
                        }
                    };

                    xmlhttp.send();
                } else {
                    $(".audio-loading").fadeOut();
                    $(".modal-description").html(data.responseMessage);
                    $(".call-refresh").css("display", "block");
                    $(".call-refresh").attr("data-assessment", assessmentId);
                    document.querySelector("#confirm-modal").showModal();
                }
            },
            error: function (err) {
                console.log(err);
                $(".audio-loading").fadeOut();
                $(".modal-description").html("Sistem xətası2");
                document.querySelector("#confirm-modal").showModal();
            },
        });
    } else {
        // wavesurfer.pause();
        // $('.audio-player, .body-detail').fadeOut();
        // $(this).attr('data-collapse', '0');
    }
});

/********************** Call close start **********************/

$(document).on("click", "#close-call", function () {
    var assestId = $(".active-line").attr("data-assessment");
    var playeTime = $("#play-time").val();
    var time = $(".active-line .assest-time").attr("data-second");
    let _token = $('input[name="_token"]').val();
    if (playeTime != "0") {
        $.ajax({
            type: "post",
            url: `/call-close`,
            data: {
                assestId,
                playeTime,
                time,
                _token,
            },
            dataType: "json",
            success: function (data) {
                if (data.code !== 200) {
                    $(".modal-description").html("Sistem xətası");
                    document.querySelector("#confirm-modal").showModal();
                }
            },
            error: function (err) {
                console.log(err);
                $(".modal-description").html("Sistem xətası");
                document.querySelector("#confirm-modal").showModal();
            },
        });
    }

    clearInterval(timerInterval);
    $(".active-line").attr("data-collapse", "0");
    $(".active-line").removeClass("active-line");
    wavesurfer.pause();
    $(".audio-player, .body-detail").fadeOut();
    if (playInterval) {
        clearInterval(playInterval);
    }
});

/*********************** Call close end ***********************/

/********************** call assessment end ****************************/

$(".add-text").on("click", function () {
    $(".call-comment").val("Düzgün cavablandırılıb");
});

/********************** call assested start ****************************/
var timerInterval = null;
$(document).on("click", ".assessment-calls-detail", function () {
    var assessmentId = $(this).attr("data-assessment");
    $(".step-container-calls table tbody tr")
        .not(this)
        .attr("data-collapse", "0");
    var collapse = $(this).attr("data-collapse");
    var beginDate = $(this).attr("data-start-date");
    var endDate = $(this).attr("data-start-date");
    var audioId = $(this).attr("data-recording-id");
    document.querySelector(".new-critery-list .selected-items").innerHTML = "";
    $(".assessment-calls-detail").removeClass("active-assessted-line");
    // $(".step-container-calls table tbody tr").removeClass("active-line");
    if (collapse == 0) {
        $(this).addClass("active-assessted-line");

        $(".selected-items").attr("data-call", $(this).attr("data-call"));

        $(this).attr("data-collapse", "1");
        $(".selected-audio-loading").fadeIn();
        $.ajax({
            type: "get",
            // url: `/get-audio-checker/${number}/${beginDate}/${endDate}/${assessmentId}`,
            url: `/get-audio-window/${audioId}/${assessmentId}`,
            dataType: "json",
            success: function (data) {
                if (data.code == 200) {
                    if (
                        data.assessmentCritery &&
                        data.assessmentCritery.criterias != null
                    ) {
                        $(".selected-audio-loading").fadeOut();

                        $(".complaint-done, .selected-call-comment").fadeIn();

                        $(".call-comment").val(data.assessmentCritery.comment);

                        $(".complaint-comment").val(
                            data.assessmentCritery.operatorComment
                        );

                        $(".curator-comment").val(
                            data.assessmentCritery.curatorComment
                        ); 
                        $(".callTypes").val(
                            JSON.parse(data.assessmentCritery.wrongSelection)
                        );

                        $(".leader-comment").val(
                            data.assessmentCritery.leaderComment
                        );

                        $(".common-score span").text(
                            data.assessmentCritery.count == 0
                                ? 33
                                : data.assessmentCritery.count
                        );

                        let str = "";

                        let leader = data.complaints.filter(function (item) {
                            return item.leadingStatus == 1;
                        });

                        let leaderSecond = data.complaints.filter(function (
                            item
                        ) {
                            return item.leadingStatus > 1;
                        });

                        if (data.assessmentCritery.criterias != null) {
                            JSON.parse(
                                data.assessmentCritery.criterias
                            ).forEach(function (criteryItem, index) {
                                let bool = false;
                                let criteryId = 0;
                                let criteryIndex = 0;
                                let reasonableCurator = 0;
                                let leaderStatus = 0;
                                data.complaints.forEach(function (item) {
                                    if (item.critery == index) {
                                        bool = true;
                                        criteryIndex = item.critery;
                                        criteryId = item.id;
                                        leaderStatus = item.leadingStatus;
                                        reasonableCurator = item.curatorStatus;
                                    }
                                });

                                var complaintCheck = "";

                                if (data.role == null || data.role == 0) {
                                    complaintCheck = "";
                                    if (
                                        !(
                                            leader.length > 0 &&
                                            data.complaints.length == 0
                                        ) &&
                                        leaderSecond.length == 0 &&
                                        data.role !== 0 &&
                                        leader.length == 0
                                    ) {
                                        complaintCheck =
                                            '<div class="checked-complaint"> Narazıyam <input type="checkbox" value=' +
                                            index +
                                            " data-status=" +
                                            (leader.length == 0 ? 0 : 1) +
                                            " /> </div> ";
                                    }

                                    if (bool) { 
                                        if (reasonableCurator == 2) {
                                            complaintCheck =
                                                '<div class="checked-complaint"> Əsaslı  </div>';
                                        } else if (reasonableCurator == 1) {
                                            complaintCheck =
                                                '<div class="checked-complaint"> Şikayət gözləmədə  </div>';
                                        } else {
                                            if (
                                                leaderStatus == 1 &&
                                                data.role !== 0
                                            ) {
                                                complaintCheck =
                                                    '<div class="checked-complaint">  <div class="unproven-complaint"> Narazıyam <input type="checkbox" value=' +
                                                    criteryId +
                                                    " data-status='1' /> </div> Əsassız </div>";
                                            } else if (leaderStatus == 2) {
                                                complaintCheck =
                                                    '<div class="checked-complaint"> <div class="unproven-complaint"> Şikayət gözləmədə </div> Əsassız </div>';
                                            } else if (leaderStatus == 3) {
                                                complaintCheck =
                                                    '<div class="checked-complaint">   Əsaslı  -  Əsassız </div>';
                                            } else if (
                                                leaderStatus == 1 &&
                                                data.role == 0
                                            ) {
                                                complaintCheck =
                                                    '<div class="checked-complaint">   Əsassız </div>';
                                            } else {
                                                complaintCheck =
                                                    '<div class="checked-complaint">  Əsassız - Əsassız </div>';
                                            }
                                        }
                                    }
                                } else {
                                    if (bool) {
                                        if (reasonableCurator == 1) {
                                            complaintCheck =
                                                '<div class="checked-complaint reasonable-complaint" data-role="0" data-critery=' +
                                                criteryIndex +
                                                ">" +
                                                '<div><input type="radio" name="reasonable[' +
                                                index +
                                                ']" data-id=' +
                                                criteryId +
                                                ' value="2" />Əsaslı</div> <div><input type="radio" name="reasonable[' +
                                                index +
                                                ']" data-id=' +
                                                criteryId +
                                                ' value="3" />Əsassız</div>' +
                                                "</div> ";
                                        } else if (reasonableCurator == 2) {
                                            complaintCheck =
                                                '<div class="checked-complaint reasonable-complaint">' +
                                                "<div> Əsaslı</div></div>";
                                        } else {
                                            if (leaderStatus == 2) {
                                                if (data.role == 1) {
                                                    complaintCheck =
                                                        '<div class="checked-complaint" style="right: 182px"> Gözləmədə -  Əsassız </div>';
                                                } else {
                                                    complaintCheck =
                                                        '<div class="checked-complaint reasonable-complaint" data-role="1">' +
                                                        '<div class="second-reasonable"> <div><input type="radio" name="reasonable[' +
                                                        index +
                                                        ']" data-id=' +
                                                        criteryId +
                                                        ' value="3" />Əsaslı</div> <div><input type="radio" name="reasonable[' +
                                                        index +
                                                        ']" data-id=' +
                                                        criteryId +
                                                        ' value="4" />Əsassız</div>' +
                                                        "</div>  <span>Əsassız</span> </div> ";
                                                }
                                            } else if (leaderStatus == 3) {
                                                complaintCheck =
                                                    '<div class="checked-complaint" style="right: 220px"> Əsaslı -  Əsassız </div>';
                                            } else {
                                                if (leaderStatus == 1) {
                                                    complaintCheck =
                                                        '<div class="checked-complaint reasonable-complaint">' +
                                                        "<div> Əsassız </div></div>";
                                                } else {
                                                    complaintCheck =
                                                        '<div class="checked-complaint reasonable-complaint">' +
                                                        "<div> Əsassız - Əsassız </div></div>";
                                                }
                                            }
                                        }
                                    }
                                }

                                str +=
                                    '<li class="list-group-item d-flex justify-content-between align-items-center"><span class="selected-critery-name">' +
                                    criteryItem.name +
                                    "</span> " +
                                    complaintCheck +
                                    '<span class="badge badge-primary selected-time">' +
                                    criteryItem.time +
                                    '</span> <span class="badge badge-primary badge-pill">' +
                                    criteryItem.count +
                                    "</span> <i class='far fa-play-circle' data-second=" +
                                    criteryItem.second +
                                    "></i></li>";
                            });
                        }

                        if (data.assessmentCritery.comment != null) {
                            $(".call-comment").val(
                                data.assessmentCritery.comment
                            );
                            $(".selected-call-comment").fadeIn();
                        }

                        if (data.assessmentCritery.wrongSelection === 1) {
                            $(".callTypes").val(
                                JSON.parse(
                                    data.assessmentCritery.wrongSelection
                                )
                            );

                            $(".checked-wrong-selection").fadeIn();
                        }

                        document.querySelector(
                            ".new-critery-list .selected-items"
                        ).innerHTML = str;
                    } else {
                        $(".checked-critery, .selected-call-comment").fadeIn();

                        $(".callTypes").val(
                            JSON.parse(data.assessmentCritery.wrongSelection)
                        );

                        $(".call-comment").val(data.assessmentCritery.comment);
                    }

                    $(".leader-done").attr("data-role", data.role);

                    $(".audio-player").fadeIn();
                    $(".audio-loading").fadeIn();
                    $(".audio-player").css("visibility", "hidden");

                    var xmlhttp = new XMLHttpRequest();

                    xmlhttp.responseType = "blob";
                    xmlhttp.withCredentials = "true";

                    xmlhttp.open(
                        "GET",
                        `/get-audio-loader/${audioId}/${assessmentId}`,
                        true
                    );

                    xmlhttp.onload = function () {
                        var audioFile = xmlhttp.response;
                        if (audioFile && xmlhttp.status == 200) {
                            wavesurfer.loadBlob(audioFile);
                            $(".audio-loading").fadeOut();
                        } else {
                            alert("Səs yüklənmədi");
                            $(".selected-audio-loading").hide();
                        }
                    };
                    xmlhttp.send();
                } else {
                    $(".audio-loading, .selected-call-comment").fadeOut();
                    $(".modal-description").html(data.responseMessage);
                    document.querySelector("#confirm-modal").showModal();
                }
            },
            error: function (err) {
                console.log(err);
                $(".audio-loading").fadeOut();
                $(".modal-description").html("Sistem xətası");
                document.querySelector("#confirm-modal").showModal();
            },
        });
    } else {
        wavesurfer.pause();
        if (playInterval) {
            clearInterval(playInterval);
        }
        $(".selected-audio-loading, .checked-critery").fadeOut();
        $(".audio-player, .body-detail, .selected-call-comment").fadeOut();
        $(this).attr("data-collapse", "0");
    }
});

/********************** call assessment end ****************************/

/********************** call archive start ****************************/
var timerInterval = null;
$(document).on("click", ".assessment-call-archive-detail", function () {
    var number = $(this).attr("data-number");
    var assessmentId = $(this).attr("data-assessment");
    $(".step-container-calls table tbody tr")
        .not(this)
        .attr("data-collapse", "0");
    var collapse = $(this).attr("data-collapse");
    var beginDate = new Date($(this).attr("data-start-date").substring(0, 19));

    beginDate.setSeconds(beginDate.getSeconds() - 90);
    beginDate = beginDate.getTime();

    if ($(this).attr("data-end-date") == "null") {
        var endDate = new Date(
            $(this).attr("data-start-date").substring(0, 19)
        );
        endDate.setMinutes(endDate.getMinutes() + 20);
        endDate = endDate.getTime();
    } else {
        var endDate = new Date(
            $(this).attr("data-end-date").substring(0, 19)
        ).getTime();
    }

    // document.querySelector(".new-critery-list .selected-items").innerHTML = "";
    $(".assessment-call-archive-detail").removeClass("active-assessted-line");

    if (collapse == 0) {
        $(this).addClass("active-assessted-line");

        $(".selected-items").attr("data-call", $(this).attr("data-call"));

        $(this).attr("data-collapse", "1");
        $(".selected-audio-loading").fadeIn();
        $.ajax({
            type: "get",
            url: `/get-audio-checker/${number}/${beginDate}/${endDate}/${assessmentId}`,
            dataType: "json",
            success: function (data) {
                if (data.code == 200) {
                    if (
                        data.assessmentCritery &&
                        data.assessmentCritery.criterias != null
                    ) {
                        $(".selected-audio-loading").fadeOut();

                        $(".complaint-done, .selected-call-comment").fadeIn();

                        $(".call-comment").val(data.assessmentCritery.comment);

                        $(".complaint-comment").val(
                            data.assessmentCritery.operatorComment
                        );

                        $(".curator-comment").val(
                            data.assessmentCritery.curatorComment
                        ); 
                        $(".callTypes").val(
                            JSON.parse(data.assessmentCritery.wrongSelection)
                        );

                        $(".leader-comment").val(
                            data.assessmentCritery.leaderComment
                        );

                        $(".common-score span").text(
                            data.assessmentCritery.count == 0
                                ? 33
                                : data.assessmentCritery.count
                        );

                        let str = "";

                        let leader = data.complaints.filter(function (item) {
                            return item.leadingStatus == 1;
                        });

                        let leaderSecond = data.complaints.filter(function (
                            item
                        ) {
                            return item.leadingStatus > 1;
                        });

                        if (data.assessmentCritery.criterias != null) {
                            JSON.parse(
                                data.assessmentCritery.criterias
                            ).forEach(function (criteryItem, index) {
                                let bool = false;
                                let criteryId = 0;
                                let reasonableCurator = 0;
                                let leaderStatus = 0;
                                data.complaints.forEach(function (item) {
                                    if (item.critery == index) {
                                        bool = true;
                                        criteryId = item.id;
                                        leaderStatus = item.leadingStatus;
                                        reasonableCurator = item.curatorStatus;
                                    }
                                });

                                var complaintCheck = "";

                                if (data.role == null || data.role == 0) {
                                    complaintCheck = "";
                                    if (
                                        !(
                                            leader.length > 0 &&
                                            data.complaints.length == 0
                                        ) &&
                                        leaderSecond.length == 0 &&
                                        data.role !== 0 &&
                                        leader.length == 0
                                    ) {
                                        complaintCheck =
                                            '<div class="checked-complaint"> Narazıyam <input type="checkbox" value=' +
                                            index +
                                            " data-status=" +
                                            (leader.length == 0 ? 0 : 1) +
                                            " /> </div> ";
                                    }

                                    if (bool) { 
                                        if (reasonableCurator == 2) {
                                            complaintCheck =
                                                '<div class="checked-complaint"> Əsaslı  </div>';
                                        } else if (reasonableCurator == 1) {
                                            complaintCheck =
                                                '<div class="checked-complaint"> Şikayət gözləmədə  </div>';
                                        } else {
                                            if (
                                                leaderStatus == 1 &&
                                                data.role !== 0
                                            ) {
                                                complaintCheck =
                                                    '<div class="checked-complaint">  <div class="unproven-complaint"> Narazıyam <input type="checkbox" value=' +
                                                    criteryId +
                                                    " data-status='1' /> </div> Əsassız </div>";
                                            } else if (leaderStatus == 2) {
                                                complaintCheck =
                                                    '<div class="checked-complaint"> <div class="unproven-complaint"> Şikayət gözləmədə </div> Əsassız </div>';
                                            } else if (leaderStatus == 3) {
                                                complaintCheck =
                                                    '<div class="checked-complaint">   Əsaslı  -  Əsassız </div>';
                                            } else if (
                                                leaderStatus == 1 &&
                                                data.role == 0
                                            ) {
                                                complaintCheck =
                                                    '<div class="checked-complaint">   Əsassız </div>';
                                            } else {
                                                complaintCheck =
                                                    '<div class="checked-complaint">  Əsassız - Əsassız </div>';
                                            }
                                        }
                                    }
                                } else {
                                    if (bool) {
                                        if (reasonableCurator == 1) {
                                            complaintCheck =
                                                '<div class="checked-complaint reasonable-complaint" data-role="0">' +
                                                '<div><input type="radio" name="reasonable[' +
                                                index +
                                                ']" data-id=' +
                                                criteryId +
                                                ' value="2" />Əsaslı</div> <div><input type="radio" name="reasonable[' +
                                                index +
                                                ']" data-id=' +
                                                criteryId +
                                                ' value="3" />Əsassız</div>' +
                                                "</div> ";
                                        } else if (reasonableCurator == 2) {
                                            complaintCheck =
                                                '<div class="checked-complaint reasonable-complaint">' +
                                                "<div> Əsaslı</div></div>";
                                        } else {
                                            if (leaderStatus == 2) {
                                                if (data.role == 1) {
                                                    complaintCheck =
                                                        '<div class="checked-complaint" style="right: 182px"> Gözləmədə -  Əsassız </div>';
                                                } else {
                                                    complaintCheck =
                                                        '<div class="checked-complaint reasonable-complaint" data-role="1">' +
                                                        '<div class="second-reasonable"> <div><input type="radio" name="reasonable[' +
                                                        index +
                                                        ']" data-id=' +
                                                        criteryId +
                                                        ' value="3" />Əsaslı</div> <div><input type="radio" name="reasonable[' +
                                                        index +
                                                        ']" data-id=' +
                                                        criteryId +
                                                        ' value="4" />Əsassız</div>' +
                                                        "</div>  <span>Əsassız</span> </div> ";
                                                }
                                            } else if (leaderStatus == 3) {
                                                complaintCheck =
                                                    '<div class="checked-complaint" style="right: 220px"> Əsaslı -  Əsassız </div>';
                                            } else {
                                                if (leaderStatus == 1) {
                                                    complaintCheck =
                                                        '<div class="checked-complaint reasonable-complaint">' +
                                                        "<div> Əsassız </div></div>";
                                                } else {
                                                    complaintCheck =
                                                        '<div class="checked-complaint reasonable-complaint">' +
                                                        "<div> Əsassız - Əsassız </div></div>";
                                                }
                                            }
                                        }
                                    }
                                }

                                str +=
                                    '<li class="list-group-item d-flex justify-content-between align-items-center"><span class="selected-critery-name">' +
                                    criteryItem.name +
                                    "</span> " +
                                    complaintCheck +
                                    '<span class="badge badge-primary selected-time">' +
                                    criteryItem.time +
                                    '</span> <span class="badge badge-primary badge-pill">' +
                                    criteryItem.count +
                                    "</span> <i class='far fa-play-circle' data-second=" +
                                    criteryItem.second +
                                    "></i></li>";
                            });
                        }

                        if (data.assessmentCritery.comment != null) {
                            $(".call-comment").val(
                                data.assessmentCritery.comment
                            );
                            $(".selected-call-comment").fadeIn();
                        }

                        if (data.assessmentCritery.wrongSelection === 1) {
                            $(".callTypes").val(
                                JSON.parse(
                                    data.assessmentCritery.wrongSelection
                                )
                            );

                            $(".checked-wrong-selection").fadeIn();
                        }

                        document.querySelector(
                            ".new-critery-list .selected-items"
                        ).innerHTML = str;
                    } else {
                        $(".checked-critery, .selected-call-comment").fadeIn();

                        $(".callTypes").val(
                            JSON.parse(data.assessmentCritery.wrongSelection)
                        );

                        $(".call-comment").val(data.assessmentCritery.comment);
                    }

                    $(".leader-done").attr("data-role", data.role);

                    $(".audio-player").fadeIn();
                    $(".audio-loading").fadeIn();
                    $(".audio-player").css("visibility", "hidden");

                    var xmlhttp = new XMLHttpRequest();

                    xmlhttp.responseType = "blob";
                    xmlhttp.withCredentials = "true";

                    xmlhttp.open(
                        "GET",
                        `/get-mediasense/${number}/${beginDate}/${endDate}/${assessmentId}`,
                        true
                    );

                    xmlhttp.onload = function () {
                        var audioFile = xmlhttp.response;
                        if (audioFile && xmlhttp.status == 200) {
                            wavesurfer.loadBlob(audioFile);
                        } else {
                            alert("Səs yüklənmədi");
                            $(".selected-audio-loading").hide();
                        }
                    };
                    xmlhttp.send();
                } else {
                    $(".audio-loading, .selected-call-comment").fadeOut();
                    $(".modal-description").html(data.responseMessage);
                    document.querySelector("#confirm-modal").showModal();
                }
            },
            error: function (err) {
                console.log(err);
                $(".audio-loading").fadeOut();
                $(".modal-description").html("Sistem xətası");
                document.querySelector("#confirm-modal").showModal();
            },
        });
    } else {
        wavesurfer.pause();
        if (playInterval) {
            clearInterval(playInterval);
        }
        $(".selected-audio-loading, .checked-critery").fadeOut();
        $(".audio-player, .body-detail, .selected-call-comment").fadeOut();
        $(this).attr("data-collapse", "0");
    }
});

/********************** call archive end ****************************/

$(document).on(
    "change",
    ".checked-complaint input[type='checkbox']",
    function () {
        let checked = false;
        $(".checked-complaint input").each(function () {
            if (this.checked) {
                checked = true;
            }
        }); 
        if (checked) {
            $(".complaint-done").addClass("show-submit");
        } else {
            $(".complaint-done").removeClass("show-submit");
        }
    }
);

$(document).on("change", ".checked-complaint input[type='radio']", function () {
    let checked = false;
    $(".checked-complaint input").each(function () {
        if (this.checked) {
            checked = true;
        }
    });

    if (checked) {
        $(".leader-done").addClass("show-submit");
    } else {
        $(".leader-done").removeClass("show-submit");
    }
});

$(document).on("click", ".complaint-done", function () {
    let criteries = [];
    let status = 0;
    $(".checked-complaint input").each(function () {
        if (this.checked) {
            criteries.push(this.value);
            status = $(this).attr("data-status");
        }
    });

    let callId = $(".active-assessted-line").attr("data-call");
    let assestmentId = $(".active-assessted-line").attr("data-assessment");
    let operatorId = $(this).attr("data-operator");
    let _token = $('input[name="_token"]').val();
    let comment = $(".complaint-comment").val();

    let data = {
        criteries,
        callId,
        assestmentId,
        operatorId,
        comment,
        status,
        _token,
    };

    $(".complaint-done img").fadeIn();
    $.ajax({
        type: "POST",
        url: "/op-insert-complaint",
        dataType: "json",
        data,
        success: function (res) {
            if (res.status === 200) {
                $(".complaint-done img").fadeOut();
                $(".modal-description").html(res.message);
                document.querySelector("#confirm-modal").showModal();

                //active line
                $(".step-container-calls table tbody tr").attr(
                    "data-collapse",
                    "0"
                );
                $(".assessment-calls-detail").removeClass(
                    "active-assessted-line"
                );
                wavesurfer.pause();
                $(".selected-audio-loading").fadeOut();
                $(
                    ".audio-player, .body-detail, .selected-call-comment"
                ).fadeOut();
                $(".complaint-done").removeClass("show-submit");
                $(".selected-items").html("");
            }
        },
    });
});

$(document).on("click", ".leader-done", function () {
    let role = $(this).attr("data-role");
    let assestmentId = $(".active-assessted-line").attr("data-assessment");
    let _token = $('input[name="_token"]').val();
    let curatorComment = $(".curator-comment").val();
    let leaderComment = $(".leader-comment").val();
    let score = $(this).attr("data-score");
    let reasonable = [];

    $(".reasonable-complaint input[type='radio']").each(function () {
        if (this.checked) {
            reasonable.push({
                id: $(this).attr("data-id"),
                index: $(this).parent().parent().attr("data-critery"),
                value: $(this).val(),
            });
        }
    });

    let checkRole = $(".reasonable-complaint").attr("data-role");

    let data = {
        role,
        assestmentId,
        reasonable,
        _token,
        curatorComment,
        leaderComment,
        checkRole,
        score,
    };

    $(".leader-done img").fadeIn();
    $.ajax({
        type: "POST",
        url: "/reasonable",
        dataType: "json",
        data,
        success: function (res) {
            if (res.status == 200) {
                $(".leader-done img").fadeOut();
                $(".modal-description").html(res.message);
                document.querySelector("#confirm-modal").showModal();

                //active line
                $(".step-container-calls table tbody tr").attr(
                    "data-collapse",
                    "0"
                );
                $(".assessment-calls-detail").removeClass(
                    "active-assessted-line"
                );
                wavesurfer.pause();
                $(".selected-audio-loading").fadeOut();
                $(
                    ".audio-player, .body-detail, .selected-call-comment"
                ).fadeOut();
                $(".leader-done").removeClass("show-submit");
                $(".selected-items").html("");

                setTimeout(function () {
                    window.location.reload();
                }, 1500);
            }
        },
    });
});

/****************** Wrong selection checked start ***************/
$(".op-assessment-checked").click(function (e) {
    e.stopPropagation();
    let val = 0;
    $(".op-assessment-checked").each(function () {
        if (this.checked) {
            val += $(this).val();
        }
    });

    if (val > 0) {
        $(".completed-assessment").fadeIn();
    } else {
        $(".completed-assessment").fadeOut();
    }
});
/****************** Wrong selection checked end ****************/

/*************** Create package assessment start **************/
$(".completed-assessment").click(function () {
    let assesmentList = $(".checked-assessment");
    let score = 0;
    let assesment = [];
    assesmentList.each(function () {
        if ($(this).find(".op-assessment-checked")[0].checked) {
            score += Number(
                $(this).find(".assessment-completed-score span").text()
            );

            assesment.push(
                Number($(this).find(".op-assessment-checked").val())
            );
        }
    });

    let _token = $('input[name="_token"]').val();
    let userId = $(".op-assessment-checked").attr("data-operator");

    let data = {
        score: Math.round(score / assesment.length),
        assesment: JSON.stringify(assesment),
        userId,
        _token,
    };

    $(".package-response-loading").fadeIn();

    $.ajax({
        type: "POST",
        url: "/package-assessment",
        dataType: "json",
        data,
        success: function (res) {
            if (res.status === 200) {
                $(".package-response-loading").fadeOut();
                $(".modal-description").html(res.response);
                document.querySelector("#confirm-modal").showModal();
                setTimeout(function () {
                    window.location.reload();
                }, 2500);
            }
        },
    });
});
/*************** Create package assessment end **************/

/******************* Get assessment by id start *******************/
$(document).on("click", ".assessment-detail-page", function () {
    let id = $(this).attr("data-id");
    $(".assessment-response-loading").fadeIn();
    $.ajax({
        type: "get",
        url: `/get-assessment/${id}`,
        dataType: "json",
        success: function (data) {
            $(".assessment-response-loading").fadeOut();
            var parent = $(".operator-assessment .list-group");

            parent.html("");
            $(".operator-assessment .fa-arrow-left").fadeIn();
            let assesmentCount = 0;
            let assesmentScore = 0;
            data.calls.forEach((item, index) => {
                callAssessment = [];

                var callAssessment = document
                    .querySelector("#copycall .calls-assessment-details")
                    .cloneNode(true);
                callAssessment.querySelector(
                    ".btn-link"
                ).innerHTML = `<span> ${item.citizen_number} </span> / ${item.organName} / ${item.serviceName}`;

                callAssessment
                    .querySelector(".btn-link")
                    .setAttribute("data-target", "#collapse" + index);

                callAssessment.querySelector(".col-md-6").className =
                    "col-md-12";

                if (item.criterias !== null) {
                    var asessmentCriteriaList = "";
                    JSON.parse(item.criterias).forEach((values) => {
                        asessmentCriteriaList += `<li class="list-group-item d-flex justify-content-between align-items-center"> 
                            ${values.name}   <span class="badge badge-primary badge-pill selected-time"> 
                            ${values.time}   </span> <span class="badge badge-primary badge-pill">
                            ${values.count} </span>  <i class="far fa-play" data-second=
                            ${values.second}></i></li>`;
                    });
                    callAssessment.querySelector(".list-group").innerHTML =
                        asessmentCriteriaList;
                    assesmentCount += 1;
                    assesmentScore += item.count;
                }

                callAssessment.querySelector(".call-assessment").remove();

                callAssessment
                    .querySelector(".selected-items")
                    .setAttribute("data-call", item.id);

                callAssessment
                    .querySelector(".card-header")
                    .setAttribute("id", "heading" + index);

                callAssessment.querySelector(
                    ".card-header .mb-0 .common-score-count"
                ).innerText = item.count;

                callAssessment
                    .querySelector(".card-header")
                    .setAttribute("data-number", item.citizen_number);

                callAssessment
                    .querySelector(".card-header")
                    .setAttribute("data-start-date", item.callStart);

                callAssessment
                    .querySelector(".card-header")
                    .setAttribute("data-end-date", item.callEnd);

                callAssessment
                    .querySelector(".card-header")
                    .setAttribute("data-collapse", "0");

                callAssessment
                    .querySelector(".body-detail")
                    .setAttribute("id", "collapse" + index);

                callAssessment
                    .querySelector(".body-detail")
                    .setAttribute("aria-labelledby", "heading" + index);
                parent.append(callAssessment);
            });

            $(".assessment-statistics .call-status").text(assesmentCount);
            $(".assessment-statistics .calls-completed-score").text(
                Math.ceil(assesmentScore / assesmentCount)
            );
        },
        error: function (e) {
            console.log(e);
            $(".assessment-response-loading").fadeOut();
        },
    });
});
/******************* Get assessment by id end *******************/

/**************** Print assessment package start ***************/
$(document).on("click", ".assessment-packages .fa-print", function (e) {
    let id = $(this).attr("data-id");

    $.ajax({
        type: "get",
        url: `/get-package/${id}`,
        dataType: "json",
        success: function (data) {
            let str = "";
            data.package.assessment.forEach(function (item, index) {
                str +=
                    '<tr><td data-label="Account"> Q ' +
                    (index + 1) +
                    ' </td><td data-label="Account"> ' +
                    item.begin_date.split(" ")[0] +
                    " - " +
                    item.end_date.split(" ")[0] +
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
                    " / " +
                    item.score_percent +
                    "%" +
                    " </td></</tr>";
            });
            $(".package-user span").text(data.package.userName);
            $(".package-supervisor span").text(data.package.supervisorName);
            $(".print-modal table tbody").html(str);
            $(".completed-package-score").html(
                data.package.completedScore +
                    "/" +
                    data.package.completedPercent +
                    "%"
            );
 
        },
    });

    e.preventDefault();
});
/**************** Print assessment package end ****************/

/************************ complaint api start *****************/
$(document).on("click", ".step-icon div", function () {
    $(".step-icon div").removeClass("active-step");
    $(this).addClass("active-step");

    $(".complaint-tab-content tr").removeClass("show-tab");

    let current = $(this);

    $(".complaint-tab-content tr").each(function () {
        if ($(this).attr("data-complaint") == current.attr("data-complaint")) {
            $(this).addClass("show-tab");
        }
    });
});
$(document).on("click", ".finished-complaints-tab", function () {
    console.log(".finished-complaints-tab");
});

$(document).on("click", ".unfinished-complaints-tab", function () {
    console.log(".finished-complaints-tab");
});

/************************ complaint api end *****************/

$(document).on("change", "#assessment-agreement input", function (e) {
    $("#assessment-agreement button").attr("disabled", !e.target.checked);
});

$(document).on("click", "#assessment-agreement button", function () {
    let _token = $('input[name="_token"]').val();

    $.ajax({
        type: "post",
        url: `/accept-assessment/${$(this).attr("data-value")}`,
        dataType: "json",
        data: { _token },
        success: function (data) {
            if (data.status == 200) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message);
            }
        },
    });
});
