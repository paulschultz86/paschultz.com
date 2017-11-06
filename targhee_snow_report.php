
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
$html = file_get_contents('https://www.grandtarghee.com/the-mountain/snow-report/');

//write page document into DOMDocument
$page_content = new DOMDocument();
libxml_use_internal_errors(TRUE); //disable libxml errors

// get snow totals
if(!empty($html)){

	$page_content->loadHTML($html); //load HTML content
	libxml_clear_errors(); //remove errors for bad html

	//set xPath
	$page_xpath = new DOMXPath($page_content);
	//get divs where class = 'item'
	$snowTags = $page_xpath->query('//div[@class="snow-stat-large"]');

		if($snowTags->length > 0){
			//loop through span tags
			foreach($snowTags as $data){
				//get values from <span> tags where class = 'value'
				$report_values = $page_xpath->query('p[@class="number"]', $data)->item(0)->nodeValue;
				//write values into array
				$mtnStats[] = array($report_values);

			}
			//print_array

				// echo "<pre>";
				// print_r($mtnStats);
				// echo "</pre>";


			//set Date
			$date = new DateTime(null, new DateTimeZone('America/Denver'));
			$date = $date->format('Y-m-d');

			//extract values from nested arrays

			// echo "24 hour total: " 		. $mtnStats[0][0] 	. "<br>";
			// echo "48 hour total: " 		. $mtnStats[1][0] 	. "<br>";
			// echo "72 hour total: " 		. $mtnStats[2][0] 	. "<br>";
			// echo "base depth: "				. $mtnStats[3][0]		. "<br>";
			// echo "ytd snow: "					. $mtnStats[4][0]		. "<br>";

		}

		// get temperatures
		$temperatureTags = $page_xpath->query('//div[@class="currently-wrapper"]');

			if($temperatureTags->length > 0){
				foreach($temperatureTags as $weather_data){
					$weather_values = $page_xpath->query('p[@class="temp"]', $weather_data)->item(0)->nodeValue;
					$weatherStats[] = array($weather_values);
					//print_r($weatherStats);
				}
				// extract values
				//echo "temperature: " . $weatherStats[0][0] . "<br>";
			}

		// get wind speed
		$windTags = $page_xpath->query('//div[@class="currently-wrapper"]');
		if($temperatureTags->length > 0){
			foreach($temperatureTags as $wind_data){
				$wind_values = $page_xpath->query('p[@class="current-wind"]', $wind_data)->item(0)->nodeValue;
				$windSpeed[] = array($wind_values);
				//print_r($windSpeed);
			}
			// extract values
			//echo "wind speed: " . $windSpeed[0][0] . "<br>";
		}

}


// ************************************************************************** //
// ------------------ connect to database and insert data ------------------- //
// ************************************************************************** //
//connect to DB
//$connect=mysqli_connect("localhost", "root", "root", "pschultz") or die(mysqli_error($connect));
$connect=mysqli_connect("mysql.paschultz.com", "paschultz", "rxl3zzmi", "paschultz") or die(mysqli_error($connect));
echo "connected!";

//check for duplicates
$dupCount = "SELECT * FROM targhee_conditions WHERE date = '$date'";
$dupResults = mysqli_query($connect, $dupCount);
	//print_r($dupResults);

$dupCheck = mysqli_num_rows($dupResults);
	//echo "duplicate rows: " . $dupCheck;

if($dupCheck < 1){
//insert into DB_targhee conditions
$insertSql = "INSERT INTO targhee_conditions
						 ( 24_hr_snow,
							 48_hr_snow,
							 72_hr_snow,
							 base_depth,
							 ytd_snow,
							 temperatures,
							 wind_speed,
							 date
						 )
						 VALUES
						 ('".$mtnStats[0][0]."',
							'".$mtnStats[1][0]."',
							'".$mtnStats[2][0]."',
							'".$mtnStats[3][0]."',
							'".$mtnStats[4][0]."',
							'".$weatherStats[0][0]."',
							'".$windSpeed[0][0]."',
							'".$date."'
						 )";
echo $insertSql;
//echo $insertSql;
$insertData = mysqli_query($connect, $insertSql);

}

// ************************************************************************** //
// -------------------- query database and write table ---------------------- //
// ************************************************************************** //


echo "<table class = 'table is-striped is-hoverable'><thead>
				<tr>
					<th>24 Hour Snow Total</th>
					<th>48 Hour Snow Total</th>
					<th>72 Hour Snow Total</th>
					<th>Base Depth</th>
					<th>YTD Snow Total</th>
					<th>Temperatures</th>
					<th>Wind Speed</th>
					<th>Date Reported</th>
				</tr>
			</thead>
			<tbody>";

$conditions = "SELECT 24_hr_snow
							 , 48_hr_snow
							 , 72_hr_snow
							 , base_depth
							 , ytd_snow
							 , temperatures
							 , wind_speed
							 , date
							 FROM targhee_conditions
							";

$get_conditions = mysqli_query($connect, $conditions);
	while($results = mysqli_fetch_row($get_conditions)){;

	$twentyfour_snow		= $results[0];
	$fortyeight_snow 		= $results[1];
	$seventytwo_snow 		= $results[2];
	$base_depth 				= $results[3];
	$ytd_snow 					= $results[4];
	$temperatures 			= $results[5];
	$wind_speed					= $results[6];
	$date 							= $results[7];

	echo "<tr>
					<td>" . $results[0] . "</td>
					<td>" . $results[1] . "</td>
					<td>" . $results[2] . "</td>
					<td>" . $results[3] . "</td>
					<td>" . $results[4] . "</td>
					<td>" . $results[5] . "</td>
					<td>" . $results[6] . "</td>
					<td>" . $results[7] . "</td>
				</tr>";

}
	echo 		"</tbody>
				</table>"

?>
	</tbody>
</html>
