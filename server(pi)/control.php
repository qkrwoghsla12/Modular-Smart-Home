<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale:1.0,user-scalable=no" />
        <link rel="stylesheet" type="text/css" href="/static/css/control.css">
        <!--<script language="javascript" src="http://code.jquery.com/jquery.min.js"></script>-->
        <script language="javascript" src="/static/jquery.min.js"></script>
        <script src="/static/jquery.mobile.custom.min.js"></script>
	<script>
		$(function(){
			$(".sel_btn0").on("vmousedown",function(){
				switch($(this).text()){
					case "화분":
						loadPage("화분 설정","/control/garden_control.php");
						break;
					case "버튼":
						loadPage("버튼 조작","/control/button_control.php");
						break;
					case "전류":
						loadPage("어댑터 조작","/control/electronic_control.php");
						break;
					case "알림":
						loadPage("알림 설정","/control/set_notice2.php");
						break;
				}
			});
		});
		var loadPage = function(head,path){
			$("#sel_btns").fadeOut(800);
			$("nav").load(path,function(){
				$("nav").fadeIn(800);
				$("header").text(head);
				$("#prev").prop("href","/control.php");
				$("#main:first-child").text("이전화면");
			});
		};
	</script>
</head>
<body>
	<header>설정,제어</header>
	<section id="sel_btns">
		<button class="sel_btn0">화분</button>
		<button class="sel_btn0">버튼</button>
		<button class="sel_btn0">전류</button>
		<button class="sel_btn0">알림</button>
		<button class="sel_btn0">예비</button>
		<button class="sel_btn0">예비</button>
		<button class="sel_btn0">예비</button>
		<button class="sel_btn0">예비</button>
	</section>
	<nav>
	</nav>
	<a id="prev" href="smart_home.php" target="_self"><div id="main">메인화면</div></a>
</body>
</html>
