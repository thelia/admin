$('.dropdown').hover(
function(){$(this).addClass("open");},
function(){$(this).removeClass("open");});

$(document).ready(function(){
   $(".config_menu a").click(function(){
      var target = $(this).attr("data-target");
      if(target){
          location.href = target;
      }

   });
});
