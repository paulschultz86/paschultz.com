
<html>
	<head>
<!-- *********************************************************************** -->
<!-- 												bulma and font awesome											   	 -->
<!-- *********************************************************************** -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.6.0/css/bulma.css">

		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	</head>
	<tbody>

<?php

// ************************************************************************** //
// --------------------- scrape HTML from snowbasin.com --------------------- //
// ************************************************************************** //

//get the html returned from the following url
$html = file_get_contents('https://www.snowbasin.com/the-mountain/conditions/');

//write page document into DOMDocument
$page_content = new DOMDocument();
libxml_use_internal_errors(TRUE); //disable libxml errors

if(!empty($html)){

	$page_content->loadHTML($html); //load HTML content
	libxml_clear_errors(); //remove errors for bad html

	//set xPath
	$page_xpath = new DOMXPath($page_content);
	//get divs where class = 'item'
	$spanTags = $page_xpath->query('//div[@class="item"]');

		if($spanTags->length > 0){
			//loop through span tags
			foreach($spanTags as $data){
				//get values from <span> tags where class = 'value'
				$report_values = $page_xpath->query('span[@class="value"]', $data)->item(0)->nodeValue;
				//write values into array
				$mtnStats[] = array($report_values);

			}
			//print_array
				/*
				echo "<pre>";
				print_r($mtnStats);
				echo "</pre>";
				*/

			//set Date
			$date = new DateTime(null, new DateTimeZone('America/Denver'));
			$date = $date->format('Y-m-d');

			//extract values from nested arrays
			/*
			echo "base temp: " 							. $mtnStats[0][0] 	. "<br>";
			echo "mid mountain temp: " 			. $mtnStats[1][0] 	. "<br>";
			echo "wind speed: " 						. $mtnStats[2][0] 	. "<br>";
			echo "uphill travel: "					. $mtnStats[4][0]		. "<br>";
			echo "overnight snowfall: "			. $mtnStats[5][0]		. "<br>";
			echo "24 hour snowfall: "				. $mtnStats[6][0]		. "<br>";
			echo "48 hour snowfall: "				. $mtnStats[7][0]		. "<br>";
			echo "storm total: "						. $mtnStats[8][0]		. "<br>";
			echo "current base: "						. $mtnStats[9][0]		. "<br>";
			echo "season total: "						. $mtnStats[10][0]	. "<br>";
			echo "date: "										. $date->format('Y-m-d H:i:s')	.	"<br>";
			*/
		}
	}

// ************************************************************************** //
// ------------------ connect to database and insert data ------------------- //
// ************************************************************************** //
//connect to DB
//$connect=mysqli_connect("localhost", "root", "root", "pschultz") or die(mysqli_error($connect));
$connect=mysqli_connect("mysql.paschultz.com", "paschultz", "rxl3zzmi", "paschultz") or die(mysqli_error($connect));
//echo "connected!";

//check for duplicates
$dupCount = "SELECT * FROM snowbasin_conditions WHERE date = '$date'";
$dupResults = mysqli_query($connect, $dupCount);
	//print_r($dupResults);

$dupCheck = mysqli_num_rows($dupResults);
	//echo "duplicate rows: " . $dupCheck;

if($dupCheck < 1){
//insert into DB_snowbasin conditions
$insertSql = "INSERT INTO snowbasin_conditions
						 ( base_temp,
							 mid_mtn_temp,
							 wind_speed,
							 uphill_travel,
							 overnight_snow,
							 24_hr_snow,
							 48_hr_snow,
							 storm_total,
							 current_base,
							 season_total,
							 date
						 )
						 VALUES
						 ('".$mtnStats[0][0]."',
							'".$mtnStats[1][0]."',
							'".$mtnStats[2][0]."',
							'".$mtnStats[4][0]."',
							'".$mtnStats[5][0]."',
							'".$mtnStats[6][0]."',
							'".$mtnStats[7][0]."',
							'".$mtnStats[8][0]."',
							'".$mtnStats[9][0]."',
							'".$mtnStats[10][0]."',
							'".$date."'
						 )";

$insertData = mysqli_query($connect, $insertSql);
}

// ************************************************************************** //
// -------------------- query database and write table ---------------------- //
// ************************************************************************** //


echo "<table class = 'table is-striped is-hoverable'><thead>
				<tr>
					<th>Base Temp</th>
					<th>Mid Mountain Temp</th>
					<th>Wind Speed</th>
					<th>Uphill Travel</th>
					<th>Overnight Snow Total</th>
					<th>24 Hour Snow Total</th>
					<th>48 Hour Snow Total</th>
					<th>Storm Snow Total</th>
					<th>Current Base</th>
					<th>Season Total</th>
					<th>Date Reported</th>
				</tr>
			</thead>
			<tbody>";

$conditions = "SELECT base_temp
							 , mid_mtn_temp
							 , wind_speed
							 , uphill_travel
							 , overnight_snow
							 , 24_hr_snow
							 , 48_hr_snow
							 , storm_total
							 , current_base
							 , season_total
							 , date
							 FROM snowbasin_conditions
							";

$get_conditions = mysqli_query($connect, $conditions);
	while($results = mysqli_fetch_row($get_conditions)){;

	$base_temp 				= $results[0];
	$mid_mtn_temp 		= $results[1];
	$wind_speed 			= $results[2];
	$uphill_travel 		= $results[3];
	$overnight_snow 	= $results[4];
	$twentyfour_snow 	= $results[5];
	$fortyeight_snow 	= $results[6];
	$storm_total 			= $results[7];
	$current_base 		= $results[8];
	$season_total 		= $results[9];
	$date 						= $results[10];

	echo "<tr>
					<td>" . $results[0] . "</td>
					<td>" . $results[1] . "</td>
					<td>" . $results[2] . "</td>
					<td>" . $results[3] . "</td>
					<td>" . $results[4] . "</td>
					<td>" . $results[5] . "</td>
					<td>" . $results[6] . "</td>
					<td>" . $results[7] . "</td>
					<td>" . $results[8] . "</td>
					<td>" . $results[9] . "</td>
					<td>" . $results[10] . "</td>
				</tr>";

}
	echo 		"</tbody>
				</table>"

?>
	</tbody>
</html>
