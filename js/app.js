
var video = videojs('video-active');
video.markers({
    markerStyle: {
        'width':'7px',
        'border-radius': '30%',
        'background-color': 'red'
    },
    markerTip:{
        display: true,
        text: function(marker) {
            return "Break: "+ marker.text;
        },
        time: function(marker) {
            return marker.time;
        }
    },
    breakOverlay:{
        display: false,
        displayTime: 3,
        style:{
            'width':'100%',
            'height': '20%',
            'background-color': 'rgba(0,0,0,0.7)',
            'color': 'white',
            'font-size': '17px'
        },
        text: function(marker) {
            return "Break overlay: " + marker.overlayText;
        }
    },
    onMarkerClick: function(marker) {},
    onMarkerReached: function(marker) {},
    markers: [{
        time: 0,
        text: "start",
    },
    {
        time: 0,
        text: "end",
        }]
    });

$(document).ready(function () {
  $("#upload-images").fileinput();

  function select_image(el) {
    el.classList.add('selected');
    $(el).find('.wrapper > div').text($('.selected').length);
  }

  function deselect_image(el) {
    el.classList.remove('selected');
    let index = parseInt($(el).find('.wrapper > div')[0].innerText);
    $('.selected > .wrapper > div').each(function () {
      let i = parseInt(this.innerText);
      if (i > index) {
        $(this).text(i - 1);
      }
    });
  }

  $("#set-start-time").click(function () {
    $("#start-time").val(Number.parseFloat( video.currentTime() ).toFixed(2) );
    var markers = video.markers.getMarkers();
    var end_time = 0;
    markers.forEach( (item, i) => {
        if (item.text === "end") {
            end_time = item.time;
        }
    });
    video.markers.reset([{ time: video.currentTime(), text: "start"}, { time: end_time, text: "end"}]);
  });

  $("#set-end-time").click(function () {
    $("#end-time").val(Number.parseFloat( video.currentTime() ).toFixed(2) );
    var markers = video.markers.getMarkers();
    var start_time = 0;
    markers.forEach( (item, i) => {
        if (item.text === "start") {
            start_time = item.time;
        }
    });
    video.markers.reset([{ time: video.currentTime(), text: "end"}, { time: start_time, text: "start"}]);
  });

  $('.image-panel > img').click(function () {
    el = this.parentNode;
    if (el.classList.contains('selected')) {
      deselect_image(el);
    } else {
      select_image(el);
    }
  });

  $('.image-panel > video').click(function () {
    el = this.parentNode;
    if (el.classList.contains('selected')) {
      deselect_image(el);
    } else {
      select_image(el);
    }
  });

  var el_img;
  var el_vid;
  $('.add-video').click(function (e) {
    var itm = this.parentNode;
    var cln = $(itm).clone(true, true);
    $("#uploadContainerVideo").append(cln);
    console.log($("#uploadContainerVideo > :last-child"));
    $("#uploadContainerVideo > :last-child").removeClass("selected");
  });

  $('.remove-video').click(function (e) {
    var el = this.parentNode;
    if ( el.classList.contains('selected') ) {
        let index = parseInt($(el).find('.wrapper > div')[0].innerText);
        $('.selected > .wrapper > div').each(function () {
          let i = parseInt(this.innerText);
          if (i > index) {
            $(this).text(i - 1);
          }
        });
    }
    el.remove();
  });


  $('.edit-image').click(function (e) {
    el_img = this.parentNode;
    $('#animation')[0].value = $(el_img).find('.image-animation > span').attr('value');
    $('#overlay-text').val($(el_img).find('.image-overlay-text > span').text());
  });

  $('.edit-video').click(function (e) {
    el_vid = this.parentNode;
    $(el_vid).find('#width').val( $(el).find("video")[0].videoWidth );
    $(el_vid).find('#height').val( $(el).find("video")[0].videoHeight );
    var videoFile = $(this).prev().find("source").attr("src");
    var s_time = $(el_vid).find('#saved-start-time').val();
    var e_time = $(el_vid).find('#saved-end-time').val();
    if (s_time === "" || e_time === "") {
        video.src({type: 'video/mp4', src: videoFile});
    } else {
        var start_time = parseFloat( s_time );
        var end_time = parseFloat( e_time );
        $("#start-time").val( parseFloat(start_time).toFixed(2) );
        $("#end-time").val( parseFloat(end_time).toFixed(2) );
        $('#overlay-video-text').val($(el_vid).find('.image-overlay-text > span').text());
        video.src({type: 'video/mp4', src: videoFile});
        setTimeout(function(){ 
            video.markers.reset([{ time: start_time, text: "start"}, { time: end_time, text: "end"}]);
        }, 1000);
    }
  });
  
  $('#animation-submit').click(function(e) {
    let el_select = $('#animation')[0];
    $(el_img).find('.image-animation > span').text(el_select.options[el_select.selectedIndex].innerText);
    $(el_img).find('.image-animation > span').attr('value', el_select.value);
    $(el_img).find('.image-overlay-text > span').text($('#overlay-text').val());
  });

  $('#animation-video-submit').click(function(e) {
    var markers = video.markers.getMarkers();
    var start_time = 0;
    var end_time = 0;
    markers.forEach( (item, i) => {
        if (item.text === "start") {
            start_time = item.time;
        } else {
            end_time = item.time;
        }
    });
    if (start_time >= end_time) {
        alert("Set time correctly!");
    } else {
        $(el_vid).find('#saved-start-time').val(start_time);
        $(el_vid).find('#saved-end-time').val(end_time);
        $(el_vid).find('.image-overlay-text > span').text($('#overlay-video-text').val());
    }
  });
  
  $('#btn-generate-video').click(function() {

    $(this).button('loading');

    let images = [];
    $('.image-panel.selected').each(function()
    {
      images.push(1);
    });

    $('.image-panel.selected').each(function()
    {
        if ($(this).find('img').length) {
            images[parseInt($(this).find('.wrapper > div').text())-1] = {
                'src': $(this).find('img').attr('val'),
                'animation': $(this).find('.image-animation > span').attr('value'),
                'overlay_text': $(this).find('.image-overlay-text > span').text()
            };
        } else {
            images[parseInt($(this).find('.wrapper > div').text())-1] = {
                'src': $(this).find('source').attr('src'),
                'start_time': $(this).find('#saved-start-time').val(),
                'end_time': $(this).find('#saved-end-time').val(),
                'width': $(this).find('#width').val(),
                'height': $(this).find('#height').val(),
                'overlay_text': $(this).find('.image-overlay-text > span').text()
            };
        }
    });

    let data = {
      'user_id': user_id,
      'vid_dir': vid_dir,
      'images': images,
      'top_bar_text': $('#top-bar-text').val(),
      'bottom_bar_text': $('#bottom-bar-text').val(),
      'end_screen_text': $('#end-screen-text').val(),
      'your_brand_name': $('#your-brand-name').val(),
      'select_image_fit': $('#select-image-fit').val(),
      'select_sound': $('#select-sound').val(),
      'select_per_frame': parseFloat($('#select-per-frame').val()),
      'select_bg_color': parseInt($('#select-bg-color').val())
    }

    console.log(data);
    $.post('make-video.php', {'param': JSON.stringify(data)}, function(result){
      console.log(result);
      $('#result-video')[0].pause();
      $('#result-video > source').attr('src', 'video.php?param=' + new Date().getTime());
      $('#result-video')[0].load();
      $('#result-video')[0].play();
      $('#btn-generate-video').button('reset');
    });
  });
});