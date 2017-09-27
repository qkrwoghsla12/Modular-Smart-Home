<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale:1.0,user-scalable=no" />
	<link rel="stylesheet" type="text/css" href="static/css/stream.css">
	<!--<script language="javascript" src="http://code.jquery.com/jquery.min.js"></script>-->
	<script language="javascript" src="/static/jquery.min.js"></script>
	<script>
		$(function(){
			$("#stream").on("click",function(){
				if($("#stream").prop("src") == "http://192.168.0.83:8889/"){
					$("#stream").prop("src","http://192.168.0.83:8888/");
				}
				else{
					$("#stream").prop("src","http://192.168.0.83:8889/");
				}
			});

		})
	</script>
</head>
<body>
	<header>실시간 영상</header>
	<img id="stream" src="http://192.168.0.83:8888" />
	<a href="smart_home.php" target="_self"><div id="main">메인화면</div></a>
</body>
</html>
