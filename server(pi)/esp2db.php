
<?php
	define("DB_HOST", "localhost");
	define("DB_USER", "root");
	define("DB_PASSWORD", "haha6009");
	define("DB_NAME", "smart_home");

	function send_notification ($tokens, $message, $num, $ip){
		$url = 'https://fcm.googleapis.com/fcm/send';
		$message["number"]=$num;
		$message["ip"]=$ip;

		$fields = array(
			'registration_ids' => $tokens,
			//'to' => $tokens[0],
			'data' => $message,
		);
		$headers = array(
			'Authorization:key =AAAAmJh48TM:APA91bGgtYsuhnjCFYlbLT24bNQNE-z-VjnCWWR11J7FT0J2dAiXZfC_45LlwxSnh28VcHmXE44JRdGHQ_rlypK-z_PNt3ii88HJcgJr_wft4GK7Fqm6lImCTroPWH7jtT-j86MScnOp',
			'Content-Type: application/json'
		);

		$ch = curl_init();
  		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$a = curl_exec($ch);
		curl_close($ch);

		print($a);
		return $a;
	}

	date_default_timezone_set('Asia/Seoul');
	$f = fopen("set_notice.txt","r");
	$f = fgets($f);
	$set = explode(",",$f);
	$r = array();
	for($i=0;$i<count($set);$i++){
		for($z=0;$z<2;$z++){
			array_push($r,explode(":",$set[$i])[$z]);
		}
	}
	$set_r = array();
	for($i=0;$i<count($r);$i++){
		$set_r[$r[$i*2]]=$r[$i*2+1];
	}

	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$tokens = array();
	//$t = date("Y-m-d H:i:s");
	$result = mysqli_query($conn,"select Token from FCMKEY");
	if(mysqli_num_rows($result) > 0 ){
		while ($row = mysqli_fetch_assoc($result)) {
			$tokens[] = $row["Token"];
		}
	}

	$title = "SMART HOME";
	$num = $_GET["num"];
	if(isset($_GET["avr_ip"])){
		system("sudo mkdir ".$_GET["avr_ip"]);
	}

	if(isset($_GET["pir"])){
		$last_detect = mysqli_query($conn,"select detect_time".$num." from pir where detect_time".$num." is not null order by detect_time".$num." desc limit 1;");
		$last_detect = mysqli_fetch_assoc($last_detect);
		if((date("Y-m-d H:i:s",time()-60*10) > $last_detect["detect_time".$num]) || !$last_detect["detect_time".$num]){
			// 연속적으로 감지되더라도 10분에 1번씩만 인지
			$query = "INSERT INTO pir(detect_time".$num.") values(now());";
			if($set_r["psen".$num]=="on"){
				//print($num."on");
				$body = "센서에 움직임이 감지되었습니다.";
				$message = array("title"=>$title,"body"=>$body);
				//$x = send_notification($tokens, $message);
			}
		}
	}
	if(isset($_GET["fire"])){
		$last_detect = mysqli_query($conn,"select detect_time".$num." from fire where detect_time".$num." is not null order by detect_time".$num." desc limit 1;");
		$last_detect = mysqli_fetch_assoc($last_detect);

		if((date("Y-m-d H:i:s",time()-60) > $last_detect["detect_time".$num]) || !$last_detect["detect_time".$num]){
			// 연속적으로 감지되더라도 1분에 1번씩만 인지
			$query = "INSERT INTO fire(detect_time".$num.") values(now());";
			 if($set_r["fsen".$num]=="on"){
				$body = "센서에 불꽃이 감지되었습니다.";
				$message = array("title"=>$title,"body"=>$body);
			}
		}
	}
	if(isset($_GET["tem"])){
		$tem = $_GET['tem'];
		$hum = $_GET['hum'];
		//$query = "INSERT INTO sensor(temp,humi,time) values($tem,$hum,'$t');";
		$query = "INSERT INTO temp(detect_time,temp".$num.",humi".$num.") values(now(),$tem,$hum);";

		if(($set_r["tsen".$num]=="on") && ($set_r["hsen".$num]=="on") && ($set_r["tem".$num] <= $tem) && ($set_r["humi".$num] <= $hum)){
			$body = "온도가 ".$tem."℃ 입니다.\n"."습도가 ".$hum."% 입니다.";
			$message = array("title"=>$title,"body"=>$body);
			//$x = send_notification($tokens, $message);
		}
		else if(($set_r["tsen".$num]=="on") && ($set_r["tem".$num] <= $tem)){
			$body = "온도가 ".$tem."℃ 입니다.";
			$message = array("title"=>$title,"body"=>$body);
			//$x = send_notification($tokens, $message);
			//sleep(10);
		}
		else if(($set_r["hsen".$num]=="on") && ($set_r["humi".$num] <= $hum)){
			$body = "습도가 ".$hum."% 입니다.";
			$message = array("title"=>$title,"body"=>$body);
			//$x = send_notification($tokens, $message);
		}
	}
	if(isset($_GET["dust"])){
		$dust = $_GET['dust'];
		$query = "INSERT INTO dust(detect_time,dust".$num.") values(now(),$dust);";
		if(($set_r["dsen".$num]=="on") && ($set_r["dust".$num] <= $dust)){
			$body = "먼지량이 ".$dust."mg/m3 입니다.";
			$message = array("title"=>$title,"body"=>$body);
			//$x = send_notification($tokens, $message);
		}
	}
	if(isset($_GET["sonic"])){
		$sonic = $_GET['sonic'];
		$query = "INSERT INTO sonic(detect_time,distance".$num.") values(now(),$sonic);";
		if(($set_r["ssen".$num]=="on") && ($set_r["sonic".$num] <= $sonic)){	// num 변수에 감지된 센서의 번호가 넘어옴
			$body = "거리가 ".$sonic."cm 입니다.";
			$message = array("title"=>$title,"body"=>$body);
			//$x = send_notification($tokens, $message);
		}
	}
	if(isset($_GET["pot1"])){
		$pot1 = $_GET['pot1'];
		$pot2 = $_GET['pot2'];
		$pot3 = $_GET['pot3'];
		$pot4 = $_GET['pot4'];
		if($pot1>=0 && $pot1<=100 && $pot2>=0 && $pot2<=100 && $pot3>=0 && $pot3<=100 && $pot4>=0 && $pot4<=100){
			$query = "INSERT INTO garden(detect_time,pot1,pot2,pot3,pot4) values(now(),$pot1,$pot2,$pot3,$pot4);";
			if(($set_r["gsen1"]=="on") && ($set_r["garden1"] >= $pot1)){
				$body = "1번 화분의 습도가 $pot1 ％ 입니다.\n";
			}
			if(($set_r["gsen2"]=="on") && ($set_r["garden2"] >= $pot2)){
				$body = $body."2번 화분의 습도가 $pot2 ％ 입니다.\n";
			}
			if(($set_r["gsen3"]=="on") && ($set_r["garden3"] >= $pot3)){
				$body = $body."3번 화분의 습도가 $pot3 ％ 입니다.\n";
			}
			if(($set_r["gsen4"]=="on") && ($set_r["garden4"] >= $pot4)){
				$body = $body."4번 화분의 습도가 $pot4 ％ 입니다.\n";
			}
			if($body){
				$num = "0";
				$message = array("title"=>$title,"body"=>$body);
				//$x = send_notification($tokens, $message);
			}
		}
	}
	if(isset($_GET["electronic"])){
		$ele = $_GET['electronic'];
		$query = "INSERT INTO electronic(detect_time,electronic".$num.") values(now(),$ele)";
		if(($set_r["esen".$num]=="on") && ($set_r["electronic".$num] <= $ele)){
			$body = "전류사용량이 ".$ele."A 입니다.";
			$message = array("title"=>$title,"body"=>$body);
			//$x = send_notification($tokens, $message);
		}
	}

	if($body){
		$ip = $_SERVER['SERVER_ADDR'];
		$x = send_notification($tokens, $message, $num, $ip);
		//print($ip);
	}

	mysqli_query($conn, $query);
	mysqli_close($conn);
	//print($x);
?>

