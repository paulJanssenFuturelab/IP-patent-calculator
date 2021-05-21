<?php
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
if (strpos($actual_link, 'localhost') === false) {
	require_once( $_SERVER['DOCUMENT_ROOT'] . '/login_outside_wp.php');
}
?>

<!DOCTYPE html>
<html>
	<head>
		<!-- jQuery -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="js/serializeObject.js"></script>

		<!-- Data obj-->
		<!-- <script src="js/data.js"></script> -->

		<!-- style -->
		<link rel="stylesheet" href="css/style2.css?v=<?php echo rand(0, 200); ?>" type="text/css" media="all" />
	</head>

	<body class="dashboard">

	<a href="<?php echo $_SERVER['REQUEST_URI']; ?>?logout=true" id="logout_tool">Logout</a>

	<?php
		if( !$user->data->ID && strpos($actual_link, 'localhost') === false):
			echo "Login to see this page";
			return '';
		endif;

		if(strpos($actual_link, 'localhost') === false){
			$user_meta = get_userdata($user->data->ID);
			$user_roles = $user_meta->roles;

			if(!user_can( $user->data->ID, 'manage_options' )){

				// sluit teachers hiervan uit
				if(!in_array("teacher", $user_roles)){
					echo "Log in as administrator";
					return '';
				}
			}
		}
	?>

	<?php

	// include options
	include("include/options.php");

	// get data object and convert to php
	ini_set("allow_url_fopen", 1);

	$json = file_get_contents($tool_url . '/js/data.js');

	// maak sommige dingen schoon zodat de json_edcode het echt doet. Zoals var pocData en de laatste ;
	$json = str_replace("pocData = ", "", $json);
	$json = str_replace(";", "", $json);
	$jsonData = html_entity_decode(stripslashes($json));

	$pocData=json_decode($jsonData);

	// print_r($pocData);

	// Pretty property name function
	function prettyPropertName($propertyName) {

	    // replace _
		$propertyName = str_replace('_', ' ', $propertyName);

		// First letter uppercase
		$propertyName = ucfirst($propertyName);

		return $propertyName;
	}

	function convertCountry($countryCode){

		include("include/options.php");

		return $countries[$countryCode];

	}

	// Show regions form
	function regionAndCountryForm($route, $dataKey, $pocData){

		if($route === "pct"){
			$title = strtoupper($route);
		} else {
			$title = ucfirst($route);
		}

		echo "<h4>" . $title . "</h4>";
		echo "<br /><br />";

		// Regions
		foreach($pocData->{$dataKey} as $regionKey => $regionObj){

			if($dataKey === "regions"){
				// bv Europe
				echo "<p><strong>".prettyPropertName($regionKey)."</strong></p>";
			} else {
				// bv Benelux
				echo "<p><strong>".convertCountry($regionKey)."</strong></p>";
			}

			echo "<br />";


			foreach($regionObj as $regionCostKey => $regionCostObj){

				$regionCostObjkeys = get_object_vars((object)$regionCostObj);

				// maak een uitzondering voor taxes
				// alleen bij PCT. Deze kosten staan los van welke route dan ook
				// + twee keer dezelfde forms werkt niet
				if($regionCostKey !== "taxes" ):

					foreach($regionCostObjkeys as $regionCostObjRoutesKey => $regionCostObjRoutes){

						if($regionCostObjRoutesKey == $route):

							$regionCostObjCosts = get_object_vars($regionCostObjRoutes);

							echo "<label>".prettyPropertName($regionCostKey)."</label>";

							foreach($regionCostObjCosts as $regionCostYearKey => $regionCostvalue){

								echo "<input type='text' value='".$pocData->{$dataKey}->{$regionKey}->{$regionCostKey}->{$regionCostObjRoutesKey}->{$regionCostYearKey}."'
								name='".$dataKey."[".$regionKey."][".$regionCostKey."][".$regionCostObjRoutesKey."][".$regionCostYearKey."]'>";

							}

						endif;
					}
				// het zijn taxes
				else:

					if($route == "pct"):

						echo "<div class='taxes_overview drie_px_border'>";
							echo "<p>Renewal fees for ".prettyPropertName($regionKey)." (PCT and National)</p>";
							echo "<br /><br />";

							$taxArray = $pocData->{$dataKey}->{$regionKey}->{$regionCostKey};
							foreach($taxArray as $taxKey => $taxVal){

								$keyInteger = (int)$taxKey;

								// echo $keyInteger;
								if($taxVal > 0){
									echo "<label>".prettyPropertName("Year " . $taxKey)."</label>";
									echo "<input type='text' value='".(int)$taxVal."' name='".$dataKey."[".$regionKey."][".$regionCostKey."][".$keyInteger."]'>";

									echo "<br />";
								}
							}
						echo "</div>";
					endif; // end if route is pct
				endif; // end if costs is taxes
			}

		}
	}
	?>

		<div class="container">

			<div class="fullWidth dashboard_outer">
				<!-- loader -->
				<div id="loader"></div>

				<!--warning -->
				<div id="warning"></div>

				<!--  FORM -->
				<div class="fullWidth">

					<form id="costs_priority_year" class="admin_form">

						<section class="dashboard_item drie_px_border">

							<h2>Costs priority year</h2>

							<div class="twoFourth">
								<fieldset>

									<h4>PCT</h4>
									<?php
									// priority
									foreach($pocData->costs_priority_year as $priorityCostKey => $priority_cost){

										echo "<label>".prettyPropertName($priorityCostKey)."</label>";

										echo "<input type='text' value='".$pocData->costs_priority_year->{$priorityCostKey}->pct->year."' name='costs_priority_year[".$priorityCostKey."][pct][year]'>";
										echo "<input type='text' value='".$pocData->costs_priority_year->{$priorityCostKey}->pct->cost."' name='costs_priority_year[".$priorityCostKey."][pct][cost]'>";

										echo "<br >";
									}
									?>
								</fieldset>
							</div>
							<div class="twoFourth">
								<fieldset>
									<h4>National</h4>
									<?php
									// priority
									foreach($pocData->costs_priority_year as $priorityCostKey => $priority_cost){

										echo "<label>".prettyPropertName($priorityCostKey)."</label>";

										echo "<input type='text' value='".$pocData->costs_priority_year->{$priorityCostKey}->national->year."' name='costs_priority_year[".$priorityCostKey."][national][year]'>";
										echo "<input type='text' value='".$pocData->costs_priority_year->{$priorityCostKey}->national->cost."' name='costs_priority_year[".$priorityCostKey."][national][cost]'>";

									}
									?>
								</fieldset>
							</div>
							<div class="clear"></div>
						</section>

						<section class="dashboard_item drie_px_border">
							<h2>Regional costs</h2>

							<div class="twoFourth">
								<fieldset>
									<?php regionAndCountryForm("pct", "regions", $pocData); ?>
								</fieldset>
							</div>
							<div class="twoFourth">
								<fieldset>
									<?php regionAndCountryForm("national", "regions", $pocData); ?>
								</fieldset>
							</div>
							<div class="clear"></div>
						</section>

						<section class="dashboard_item drie_px_border">
							<h2>Country costs</h2>

							<div class="twoFourth">
								<fieldset>
									<?php regionAndCountryForm("pct", "countries", $pocData); ?>
								</fieldset>
							</div>
							<div class="twoFourth">
								<fieldset>
									<?php regionAndCountryForm("national", "countries", $pocData); ?>
								</fieldset>
							</div>
							<div class="clear"></div>
						</section>

<!-- 						<input type="submit" value="Submit" />							 -->
					</form>
					<div class="clear"></div>
				</div>

				<div class="clear"></div>
			</div>	<!-- einde tweederde -->


			<script>
			jQuery( document ).ready(function() {

				$("form#costs_priority_year :input").change(function() {

				// $(document).on('submit','form#costs_priority_year',function(e){

					// e.preventDefault();

					var object = $('form#costs_priority_year').serializeObject();

					$.ajax({
							"url": "<?php echo $tool_url; ?>/saveToJson.php",
							"method": "POST",
							"data": {
								"savedData": JSON.stringify(object),
							},
							beforeSend: function() {

							},
							success: function(data) {

								console.log(data);
								console.log("yeah");

							},
							error: function(data) {

								console.log(data);
								console.log("aiii");
							}
						});

				});



			});
			</script>
	</body>
</html>
