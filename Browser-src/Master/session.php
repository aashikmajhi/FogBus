<html>
<head><title>HealthKeeper - Login</title></head>
<body>

<h1>HealthKeeper - Session Page</h1>

<?php

session_start();

if(isset($_GET['username'])){
	$username = $_GET['username'];
	echo "<h2>Hello ".htmlspecialchars($username)."</h2> ";
}
else{
	echo "<h2>Hello</h2> ";
}

?>

Connect to Bluetooth Device:

<form>
<input type='submit' name='Pair Device' value='Pair Device' />
</form>

<?php

$device = "Pulse Oximeter";
echo "Connected Device : ".$device;

?>

<form id='data' method='post'>
<input type='text' name='data' id='data'  maxlength="500" />
<input type='submit' name='analyze' value='analyze' />
</form>

<?php

if(isset($_POST['data'])){

	$file = fopen("data.txt", "a");
	$content = $_POST['data'];
	echo "Data Values Stored : ";
	echo $content;
	if(0==filesize("data.txt")){
		fwrite($file,$content);
	}else{
		fwrite($file,",".$content);
	}
	fclose($file);
}

?>



<?php

if(isset($_POST['analyze'])){

	$content = $_POST['data'];
	
	$file = fopen("config.txt", "r");
	$line = fgets($file);
	$toMaster = true;	
	if($line == "DisableMaster"){
		$toMaster = false;	
	}
	$ips = array();
	while(($line = fgets($file)) !== false){
	  array_push($ips, $line);
	}
	$loads = array();
	foreach($ips as $ip){
		$ip = preg_replace('/\s+/', '', $ip);
		$dataFromExternalServer=file_get_contents("http://".$ip."/HealthKeeper/load.php"); 
		$dataFromExternalServer = preg_replace('/\s+/', '', $dataFromExternalServer);		
		$my_var = 0.0 + $dataFromExternalServer;
		echo "<br/>Woker load with IP ".$ip.": ".$my_var;
		array_push($loads, $my_var);	
	  	if($my_var <= 0.8){
	  		$toMaster = false;
	  	}
	}
	
	$result = "";
	if(!$toMaster){
		$min = 100;
		$minindex = 0;
		foreach($loads as $load){
			if ($min > $load){
				$min = $load;			
			}		
		}
		foreach($loads as $load ){
			if($min == $load){
				break;			
			}		
			$minindex = $minindex+1;
		}
		$ipworker = $ips[$minindex];
		$ipworker = preg_replace('/\s+/', '', $ipworker);
		echo "<br/><br/>Work sent to Worker ".($minindex+1)." with IP address : ".$ipworker."<br/><br/>";	
		$result = file_get_contents('http://'.$ipworker.'/HealthKeeper/worker.php/?data='.$_POST['data']);
	}
	else {
		$minindex = 0;
		$ipworker = "localhost";	
		echo "<br/><br/>Work Done by Master<br/><br/>";
		$result = file_get_contents('http://'.$ipworker.'/HealthKeeper/RPi/Worker/worker.php/?data='.$_POST['data']);
	}
	
	echo $result;

	// Graph 
	$file1 = fopen("data.txt", "r");
	$result = fgets($file1);
	$allArray = explode(",", $result);
	$dataPoints = array();
	$criticalPoints = array();
	foreach ($allArray as $value) {
    array_push($dataPoints, array("y" => (int)$value, "label" => "-"));
    array_push($criticalPoints, array("y" => 88, "label" => "-"));
	}


	fclose($file1);

}

?>
<script>
window.onload = function () {
 
var chart = new CanvasJS.Chart("chartContainer", {
	title: {
		text: "Sleep Apnea Graph"
	},
	axisY: {
		title: "Oxygen Level"
	},
	data: [{
		markerType: "none",
		type: "line",
		dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>},
		{
	   markerType: "none", 
		type: "line",
		dataPoints: <?php echo json_encode($criticalPoints, JSON_NUMERIC_CHECK); ?>
	}]
});
chart.render();
 
}
</script>
<div id="chartContainer" style="height: 370px; width: 50%;"></div>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>

<form id='data' method='post'>
<input type='submit' name='reset' value='Reset All Data' />
</form>
<?php

if(isset($_POST['reset'])){
	file_put_contents("data.txt", "");
	echo "All Data removed<br/>";
}
?>


</body>
</html>