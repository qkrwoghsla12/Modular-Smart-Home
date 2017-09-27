<?php
	define("DB_HOST", "localhost");
	define("DB_USER", "root");
	define("DB_PASSWORD", "haha6009");
	define("DB_NAME", "smart_home");

	if(isset($_POST["Token"])){
		$token = $_POST["Token"];
		$num = $_POST["PhoneNum"];
		$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		$query = "INSERT INTO FCMKEY Values (now(),'$token','$num') ON DUPLICATE KEY UPDATE Token = '$token'; ";
		mysqli_query($conn, $query);

		mysqli_close($conn);
	}
?>

