<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale:1.0,user-scalable=no" />
	<link rel="stylesheet" type="text/css" href="/static/css/index.css">
	<script language="javascript" src="http://code.jquery.com/jquery.min.js"></script>
	<script src="/static/jquery.mobile-1.4.5.min.js"></script>
	<script>
		var x=0;
		var y=0;
		$(function(){
			$("#wrapper").on("swipeleft", function(e){
				$("#wrapper").css("transform","rotateY("+(y-=90)+"deg)");
			});
			$("#wrapper").on("swiperight", function(e){
				$("#wrapper").css("transform","rotateY("+(y+=90)+"deg)");
			});
			$(document).on("keydown", function(e){
				switch(e.keyCode){
					case 37: // left
					$("#wrapper").css("transform", "rotateY("+(y-=90)+"deg)");
					break;

					case 38: // up
					$("#wrapper").css("transform", "rotateX("+(x-=90)+"deg)");
					break;

					case 39: // right
					$("#wrapper").css("transform", "rotateY("+(y+=90)+"deg)");
					break;

					case 40: // down
					$("#wrapper").css("transform", "rotateX("+(x+=90)+"deg)");
					break;
				};
			});
		});
	</script>
</head>
<body>
	<header>Menu</header>
	<section>
		<div id="wrapper">
		<a><div id="one"></div></a>
		<a href="smart_home.php" target="_self"><div id="two">Smart Home</div></a>
		<a><div id="three">택배 보관함</div></a>
		<a><div id="fore"></div></a>
		<a><div id="five">PET Helper</div></a>
		<a><div id="six">실시간 영상</div></a>
		</div>
	</section>
</body>
</html>
