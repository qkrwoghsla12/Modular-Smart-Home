<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale:1.0,user-scalable=no" />
	<link rel="stylesheet" type="text/css" href="/static/css/history.css">
	<!--<script language="javascript" src="http://code.jquery.com/jquery.min.js"></script>-->
	<!--<script language="javascript" src="https://www.gstatic.com/charts/loader.js"></script>-->
	<script language="javascript" src="/static/jquery.min.js"></script>
	<script src="/static/jquery.mobile.custom.min.js"></script>
	<script type="text/javascript" src="/static/loader.js"></script>

	<script>
		var sel_sen;
		var all_sen=".pir,.fire,#humi,#tem,#dust,#sonic,#pot,#electronic";
		var temp = [[]];
		var humi = [[]];
		var dust = [[]];
		var sonic = [[]];
		var pot = [[]];
		var electronic = [[]];
		google.charts.load('current', {'packages':['corechart']});
		//google.charts.load('current', {'packages':['line']});
		//google.charts.setOnLoadCallback(drawChart);

		$(function(){
			<?php
			define("DB_HOST", "localhost");
 	 		define("DB_USER", "root");
       			define("DB_PASSWORD", "haha6009");
        		define("DB_NAME", "smart_home");

			$conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
			$pir = mysqli_query($conn,"select * from pir");
			$fire = mysqli_query($conn,"select * from fire");
			$temp = mysqli_query($conn,"select detect_time,temp1,temp2,temp3,temp4 from temp");
			$humi = mysqli_query($conn,"select detect_time,humi1,humi2,humi3,humi4 from temp");
			$dust = mysqli_query($conn,"select detect_time,dust1,dust2,dust3,dust4 from dust");
			$sonic = mysqli_query($conn,"select detect_time,distance1,distance2,distance3,distance4 from sonic");
			$pot = mysqli_query($conn,"select detect_time,pot1,pot2,pot3,pot4 from garden");
			$electronic = mysqli_query($conn,"select detect_time,electronic1,electronic2,electronic3,electronic4 from electronic");

			while($d = mysqli_fetch_array($pir)){
				for($i=0;$i<4;$i++){
					if($d[$i]){
						echo "$('.pir:eq($i)').append('<p>감지시각 : $d[$i]</p>');\n";
					}
				}
			}

			while($d = mysqli_fetch_array($fire)){
				for($i=0;$i<4;$i++){
					if($d[$i]){
						echo "$('.fire:eq($i)').append('<p>감지시각 : $d[$i]</p>');\n";
					}
				}
			}

			data_fetch($temp,"temp");
			data_fetch($humi,"humi");
			data_fetch($dust,"dust");
			data_fetch($sonic,"sonic");
			data_fetch($pot,"pot");
			data_fetch($electronic,"electronic");

			function data_fetch($data,$name){
				$i = 0;
				while($d = mysqli_fetch_row($data)){
					$x = json_encode($d);
					echo "$name"."[$i]=$x; \n";
					$i++;
				}
			}

			?>
			dateConvert(temp);
			dateConvert(humi);
			dateConvert(dust);
			dateConvert(pot);
			dateConvert(sonic);
			dateConvert(electronic);

			$(".sel_btn").on("vmousedown",function(){
				$(".sel_btn").css("background-color","#25aea1");
				$(".time").css("background-color","chocolate");
				$(this).css("background-color","#ff7b7b");
				$(".chart").hide();
				$(".pir,.fire").hide();
				$(".menu").hide();
				$(".chart_menu").show();
				switch($(this).text()){
					case "움직임":
						$(".chart_menu").hide();
						$(".menu").show();
						name = "pir";
						break;
					case "온도":
						sel_sen = temp;
						name = "temp";
						break;
					case "습도":
						sel_sen = humi;
						name = "humi";
						break;
					case "먼지":
						sel_sen = dust;
						name = "dust";
						break;
					case "초음파":
						sel_sen = sonic;
						name = "sonic";
						break;
					case "화분":
						sel_sen = pot;
						name = "pot";
						break;
					case "전류":
						sel_sen = electronic;
						name = "electronic";
						break;
					case "화재":
						$(".chart_menu").hide();
						$(".menu").show();
						name = "fire";
						break;
				}
			});
			$(".chart_menu>.time").on("vmousedown",function(){
				$(".time").css("background-color","chocolate");
				$(this).css("background-color","#ff7b7b");
				switch($(this).text()){
					case "24시간":
						drawChart(sel_sen,name,1);
						break;
					case "7일":
						drawChart(sel_sen,name,2);
						break;
					case "1개월":
						drawChart(sel_sen,name,3);
						break;
					case "1년":
						drawChart(sel_sen,name,4);
						break;
				}
				$(".chart, .chart>div").show();
			});


			$(".menu>.time").on("vmousedown",function(){
				$(".time").css("background-color","chocolate");
				$(this).css("background-color","#ff7b7b");
				$(".pir,.fire").hide();
				switch($(this).text()){
					case "센서1":
						$("."+name+":eq(0)").show();
						break;
					case "센서2":
						$("."+name+":eq(1)").show();
						break;
					case "센서3":
						$("."+name+":eq(2)").show();
						break;
					case "센서4":
						$("."+name+":eq(3)").show();
						break;
				}
			});
		});
		function dateConvert(arr){
			for(i=0;i<arr.length;i++){
				arr[i][0] = new Date(arr[i][0]);
				for(c=1;c<5;c++){
					arr[i][c]=parseFloat(arr[i][c]);
				}
			}
		}

		function drawChart(sdata,name,time) {
			var box = document.getElementById('chart');
			var data = new google.visualization.DataTable();
			data.addColumn('datetime', '시간');
			data.addColumn('number', '1');
			data.addColumn('number', '2');
			data.addColumn('number', '3');
			data.addColumn('number', '4');
			data.addRows(sdata);

			var options = {
				interpolateNulls : true,
				width: 350,
				height: 230,
				legend: {
					position: 'bottom',
					textStyle:{
						fontSize:15,
						bold:true,
					}
				},
				enableInteractivity: true,
				chartArea: {
					width:'87%',
					height:160,
					top:25,
					right:10,
				},
				hAxis: {
					//title:'시간',
					viewWindow: {
						min: new Date("2017-08-30 00:00:00"),
						max: new Date("2017-08-31 00:00:00")
					},
					format:'yyyy/MM/dd,HH:mm',
					gridlines: {
						color:'blue',
						count: 8,
					},
				},
				vAxis:{
					//title:'온도',
					viewWindow: {
						min: -10,
						max: 50
					},
					gridlines:{
						count:10,
					},
				},
				colors: ['red', 'blue','green','black'],
				crosshair: {
					color: 'yellow',
					trigger: 'selection'
				}
			};
			switch(name){
				case "temp":
					options.vAxis.viewWindow.min = -10;
					options.vAxis.viewWindow.max = 50;
					options.vAxis.format = '##°C';
					break;
				case "humi":
					options.vAxis.viewWindow.min = 0;
					options.vAxis.viewWindow.max = 100;
					//options.vAxis.format = '###%';
					break;
				case "pot":
					options.vAxis.viewWindow.min = 0;
					options.vAxis.viewWindow.max = 100;
					//options.vAxis.format = '##%';
					break;
				case "sonic":
					options.vAxis.viewWindow.min = 0;
					options.vAxis.viewWindow.max = 200;
					options.vAxis.format = '###cm';
					break;
				case "electronic":
					options.vAxis.viewWindow.min = 0;
					options.vAxis.viewWindow.max = 1;
					//options.vAxis.format = '##mA';
					break;
				case "dust":
					options.vAxis.viewWindow.min = 0;
					options.vAxis.viewWindow.max = 0.48;
					//options.vAxis.format = '#.##';
					break;

			}
			switch(time){
				case 1:
					var d = new Date();
					var min = d.getFullYear()+"-"+(d.getMonth()+1)+"-"+d.getDate()+" 00:00:00";
					var max = d.getFullYear()+"-"+(d.getMonth()+1)+"-"+d.getDate()+" 23:59:59";
					options.hAxis.viewWindow.min = new Date(min);
					options.hAxis.viewWindow.max = new Date(max);
					options.hAxis.format = 'HH:mm';
					break;
				case 2:
					var d = new Date();
					var min = d - 7 * 1000 * 60 * 60 * 24;
					options.hAxis.viewWindow.min = new Date(min);
					options.hAxis.viewWindow.max = new Date();
					options.hAxis.format = 'MM/dd';
					break;
				case 3:
					var d = new Date();
					var min = d.getFullYear()+"-"+(d.getMonth()+1)+"-01 00:00:00";
					var max = d.getFullYear()+"-"+(d.getMonth()+2)+"-01 00:00:00";
					options.hAxis.viewWindow.min = new Date(min);
					options.hAxis.viewWindow.max = new Date(max);
					options.hAxis.format = 'MM/dd';
					break;
				case 4:
					var d = new Date();
					var min = d.getFullYear()+"-01-01 00:00:00";
					var max = d.getFullYear()+"-12-31 00:00:00";
					options.hAxis.viewWindow.min = new Date(min);
					options.hAxis.viewWindow.max = new Date(max);
					options.hAxis.format = 'yyyy/MM';
					break;
			}
			new google.visualization.LineChart(box).draw(data,options);
			//new google.charts.Line(box).draw(data,google.charts.Line.convertOptions(options));
		}
	</script>
</head>
<body>
	<header>센서 데이터</header>
	<section>
		<section>
			<button class="sel_btn">움직임</button>
			<button class="sel_btn">온도</button>
			<button class="sel_btn">습도</button>
			<button class="sel_btn">먼지</button>
			<button class="sel_btn">초음파</button>
			<button class="sel_btn">화분</button>
			<button class="sel_btn">전류</button>
			<button class="sel_btn">화재</button>
		</section>
		<div class="menu">
			<button class="time">센서1</button>
			<button class="time">센서2</button>
			<button class="time">센서3</button>
			<button class="time">센서4</button>
		</div>
		<div class="chart_menu">
			<button class="time">24시간</button>
			<button class="time">7일</button>
			<button class="time">1개월</button>
			<button class="time">1년</button>
		</div>
		<div class="pir"></div><div class="pir"></div><div class="pir"></div><div class="pir"></div>
		<div class="fire"></div><div class="fire"></div><div class="fire"></div><div class="fire"></div>
		<div id="chart" class="chart"></div>
	</section>
	<a href="smart_home.php" target="_self"><div id="main">메인화면</div></a>
</body>
</html>
