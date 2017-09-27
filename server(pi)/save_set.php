
<?php
$check = ["psen","tsen","hsen","dsen","ssen","gsen","esen","fsen"];
$value = ["tem","humi","dust","sonic","garden","electronic"];

$str;

if(isset($_POST["set_notice"])){
	$f = fopen("set_notice.txt","w");				// 알람 설정을 저장할 파일
	for($i=0;$i<count($check);$i++){				// 센서의 종류별로
		for($num=1;$num<=4;$num++){				// 같은 종류의 4개의 센서를 json 배열로 변환
			$buf = $check[$i].$num;
			$str = $str.$buf.":".$_POST[$buf].",";
		}
	}
	for($i=0;$i<count($value);$i++){
		for($num=1;$num<=4;$num++){
			$buf = $value[$i].$num;
			$str = $str.$buf.":".$_POST[$buf].",";
		}
	}
	fputs($f,$str);
}
if(isset($_POST["set_garden"])){
	$f = fopen("set_garden.txt","w");				// 화분 모듈의 설정을 저장할 파일

	$pot1_h = $_POST["pot1_h"];
	$pot2_h = $_POST["pot2_h"];
	$pot3_h = $_POST["pot3_h"];
	$pot4_h = $_POST["pot4_h"];
	$auto = $_POST["auto"];						// 자동 물주기 여부

	$str_pot_h = "\"auto\":"."\"$auto\","."\"pot1_h\":"."\"$pot1_h\","."\"pot2_h\":"."\"$pot2_h\","."\"pot3_h\":"."\"$pot3_h\","."\"pot4_h\":"."\"$pot4_h\"";
	$str_pot_h = "{".$str_pot_h."}";
	fputs($f,$str_pot_h);		// json 배열로 만든 데이터를 write
}
if(isset($_POST["set_button"])){
	$f = fopen("set_button.txt","w");				// 버튼 모듈의 동작방식설정을 저장할 파일

	$button1 = $_POST["button1"];
	$button2 = $_POST["button2"];
	$button3 = $_POST["button3"];
	$button4 = $_POST["button4"];

	$str_button = "\"button1\":"."\"$button1\","."\"button2\":"."\"$button2\","."\"button3\":"."\"$button3\","."\"button4\":"."\"$button4\"";
	$str_button = "{".$str_button."}";
	fputs($f,$str_button);
}
?>
