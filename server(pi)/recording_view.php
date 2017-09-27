<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale:1.0,user-scalable=no" />
	<link rel="stylesheet" type="text/css" href="/static/css/recording_view.css">
	<!--<script language="javascript" src="http://code.jquery.com/jquery.min.js"></script>-->
	<script language="javascript" src="/static/jquery.min.js"></script>
	<script src="/static/jquery.mobile.custom.min.js"></script>
	<script>
		$(function(){
			<?php
			if($_GET){
				$date = $_GET["date"];
				$hour = $_GET["hour"];
				$cam = $_GET["cam"];
				print("$('#cam').prop('value','$cam');\n");
				print("$('#date').prop('value','$date');\n");
				print("$('#hour').prop('value','$hour');\n");
			}

			?>
			$("form").on("change",function(){
				$(this).submit();
			});
			$("#minute").on("change",function(){
				var cam = $("#cam").prop("value");
				var date = $("#date").prop("value");
				var hour = $("#hour").prop("value");
				var minute = $("#minute").prop("value");

				var path = cam+"/"+date+"/"+hour+"-"+minute+".avi.mp4";
				$("video").prop("src",path);
			});
		});
	</script>
</head>
<body>
	<header>녹화 영상</header>
	<section>
		<video controls autoplay src=""></video>
		<div id="control">
			<form method="get" id="submit">
				<select id="cam" name="cam">
					<option value="default">카메라 선택</option>
					<option value="record1">카메라1</option>
					<option value="record2">카메라2</option>
				</select>
				<select id="date" name="date">
					<option value="default">날짜 선택</option>
					<?php
						if($cam){
							$dir_scan = scandir($cam,1);
							$count = exec("ls -l $cam | grep - | wc -l");
							for($x=0;$x<$count;$x++){
								print("<option value='$dir_scan[$x]'>$dir_scan[$x]</option>\n");
							}
						}
					?>
				</select>
				<select id="hour" name="hour">
					<option value="default">시간 선택</option>
					<?php
						$vid_dir = "$cam/$date";
						$vid_dir_scan = scandir($vid_dir,1);
						$buf = array();
						$pattern = "/\d{2}-\d{2}.avi.mp4/";
						//$pattern = "/^\d{2}-\d{2}(.avi.mp4)$/";
						$vid_count = exec("ls -l $vid_dir | grep - | wc -l");

						for($x=0;$x<$vid_count;$x++){
							$h = substr($vid_dir_scan[$x],0,2);		// 시간부분만 잘라서 뽑아옴
							if(!(in_array($h, $buf)) && preg_match($pattern,$vid_dir_scan[$x])){
								array_push($buf,$h);
								print("<option value='$h'>$h"."시</option>\n");
							}
						}
					?>
				</select>
			</form>

			<select id="minute">
				<option value="default">분 선택</option>
				<?php
					$buf2 = array();
					$pattern = "/^($hour)-\d{2}.avi.mp4/";
					$vid_count = exec("ls -l $vid_dir | grep - | wc -l");

					for($x=0;$x<$vid_count;$x++){
						if(preg_match($pattern,$vid_dir_scan[$x])){
							$min = substr($vid_dir_scan[$x],3,2);
							print("<option value='$min'>$min"."분</option>");
						}
					}
				?>
			</select>
		</div>
	</section>
	<a href="smart_home.php" target="_self"><div id="main">메인화면</div></a>
</body>
</html>
