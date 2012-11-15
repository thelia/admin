$('#motcle').focus(function(){
	$('.navbar .form-search').addClass("active");
	$('.search-result').slideDown();
});
$('#search-fermer').click(function(){
	$('.search-result').slideUp();
	$('.navbar .form-search').removeClass("active");
	return false;
});
$('#search-utilisateur, #search-produit, #search-commande, #search-contenu').toggle(function() {
  $(this).removeClass("btn-success");
	$(this).removeClass("selforsearch");
	$(this).children("i").removeClass("icon-white");
	GetSearch();
}, function() {
  $(this).addClass("btn-success");
	$(this).addClass("selforsearch");
	$(this).children("i").addClass("icon-white");
	GetSearch();
});

$("input#motcle").keyup( function() {
		GetSearch();
});

function GetSearch(){
		inputString = $("input#motcle").val();
		if(inputString.length <= 3) {
			$('.search-result ul').html('');
			$(".form-search .search-result ul").css("height", "20px");
		}else{
			$('.search-result ul').html('<li id="loadingsearch"></li>');
			
			var searchtype = new Array;
			$('.selforsearch').each(function(){
				searchtype.push($(this).attr("id"));
			});
			$.get('ajax/recherche/recherche.php', {motcle: inputString, searchtype: searchtype.join(",")}, function(data){
				$('.search-result ul').html(data).queue(function(){
				
				var nb = $('.search-result ul li').length;
				
				var ht = $(window).height();
				var htdispo = ht - 148;
				var htblock = nb * 63;
				
				if(htblock > htdispo){
					$(".form-search .search-result ul").css("height", htdispo+"px");
				}else{
					$(".form-search .search-result ul").css("height", htblock+"px");
				}
				$(this).dequeue();
			});
			});
		}
}