

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
$html = file_get_contents('https://www.snowbird.com/mountain-report/');

//write page document into DOMDocument
$page_content = new DOMDocument();
libxml_use_internal_errors(TRUE); //disable libxml errors

if(!empty($html)){

	$page_content->loadHTML($html); //load HTML content
	libxml_clear_errors(); //remove errors for bad html

	//set xPath
	$page_xpath = new DOMXPath($page_content);
	//get divs where class = 'item'
	$conditions = $page_xpath->query('//div[@class="sb-condition_values"]');

		if($conditions->length > 0){
			//loop through span tags
			foreach($conditions as $data){
				//get values from <span> tags where class = 'value'
				$report_values = $page_xpath->query('div[@class="sb-condition_value"]', $data)->item(0)->nodeValue;
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
      echo "12 hr: "     . $mtnStats[4][0] . "<br>";
      echo "24 hr: "     . $mtnStats[5][0] . "<br>";
      echo "48 hr: "     . $mtnStats[6][0] . "<br>";
      echo "depth: "     . $mtnStats[7][0] . "<br>";
      echo "ytd:   "     . $mtnStats[8][0] . "<br>";
      echo "base temp: " . $mtnStats[9][0] . "<br>";
      echo "mid mtn temp: " . $mtnStats[10][0] . "<br>";
      echo "peak temp: "    . $mtnStats[11][0] . "<br>";
      echo "wind speed: "   . $mtnStats[12][0] . "<br>";
      echo "date: "         . $date->format('Y-m-d H:i:s') . "<br>";
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
  $dupCount = "SELECT * FROM snowbird_conditions WHERE date = '$date'";
  $dupResults = mysqli_query($connect, $dupCount);

  $dupCheck = mysqli_num_rows($dupResults);
  	//echo "duplicate rows: " . $dupCheck;

  if($dupCheck < 1){
  //insert into DB_snowbird conditions
  $insertSql = "INSERT INTO snowbird_conditions
              ( 12_hr_total,
                24_hr_total,
                48_hr_total,
                base_depth,
                ytd_snow,
                base_temp,
                mid_temp,
                peak_temp,
                wind_speed,
                date
               )
               VALUES
               ( '".$mtnStats[4][0]."',
                 '".$mtnStats[5][0]."',
                 '".$mtnStats[6][0]."',
                 '".$mtnStats[7][0]."',
                 '".$mtnStats[8][0]."',
                 '".$mtnStats[9][0]."',
                 '".$mtnStats[10][0]."',
                 '".$mtnStats[11][0]."',
                 '".$mtnStats[12][0]."',
                 '".$date."'
              )";

$insertData = mysqli_query($connect, $insertSql);
}

// ************************************************************************** //
// -------------------- query database and write table ---------------------- //
// ************************************************************************** //

echo "<table class = 'table is-striped is-hoverable'><thead>
				<tr>
					<th>12 Hour Snow Total</th>
					<th>24 Hour Snow Total</th>
					<th>48 Hour Snow Total</th>
					<th>Base Depth</th>
					<th>YTD Snow</th>
					<th>Base Temperature</th>
					<th>Mid Mountain Temperature</th>
					<th>Peak Temperature</th>
					<th>Wind Speed</th>
					<th>Date Reported</th>
				</tr>
			</thead>
			<tbody>";

$conditions = "SELECT 12_hr_total
              , 24_hr_total
              , 48_hr_total
              , base_depth
              , ytd_snow
              , base_temp
              , mid_temp
              , peak_temp
              , wind_speed
              , date
              FROM snowbird_conditions
              ";

$get_conditions = mysqli_query($connect, $conditions);
  while($results = mysqli_fetch_row($get_conditions)){;

	$twelve_hour_total 			= $results[0];
	$twenty_four_total   		= $results[1];
	$forty_eight_tota 			= $results[2];
	$base_depth 		        = $results[3];
	$ytd_snow 	            = $results[4];
	$base_temp             	= $results[5];
	$mid_temp 	            = $results[6];
	$peak_temp 		        	= $results[7];
	$wind_speed 	        	= $results[8];
	$date 		              = $results[9];


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
