<?php
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
if (strpos($actual_link, 'localhost') === false && strpos($actual_link, '://futurelab') === false) {
	require_once( $_SERVER['DOCUMENT_ROOT'] . '/login_outside_wp.php');
}
?>

<!DOCTYPE html>
<html>

	<head>
		<!-- jQuery -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

		<!-- Data obj-->
		<!-- <script src="js/data.js"></script> -->

		<!-- chart scripts -->
		<script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
		<script src="https://www.amcharts.com/lib/3/serial.js"></script>
		<script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
		<link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />

		<script src="https://www.amcharts.com/lib/3/themes/none.js"></script>
		<script src="js/responsive_chart.js"></script>

		<!-- dollar converter -->
		<!-- <script src="https://openexchangerates.github.io/money.js/money.min.js"></script>	-->

		<!-- confirm boxes -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.1.0/jquery-confirm.min.js"></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.1.0/jquery-confirm.min.css" type="text/css" media="all" />

		<!-- style -->
		<link rel="stylesheet" href="css/style2.css?v=<?php echo rand(0, 200); ?>" type="text/css" media="all" />

		<!-- favicon -->
		<link rel="shortcut icon" href="img/favicon.ico" />

	</head>

	<?php
	// free account with openexchagerates
	// ww radiohead
	// https://openexchangerates.org/account/app-ids
	// get currency converter data
	// Required file, could be e.g. '/historical/2011-01-01.json' or 'currencies.json'
	// $filename = 'latest.json';

	// Open CURL session:
	// $ch = curl_init('https://openexchangerates.org/api/' . $filename . '?app_id=5066b20f340f49f3bd81ac853ff7cd77');
	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	// Get the data:
	// $rates = curl_exec($ch);
	// curl_close($ch);

	// $exchange = json_decode($rates);

	// loop door de tmp heen. Elk bestand wat ouder is dan 24 uur mag worden gedelete
	$dir = "tmp/";

	foreach (glob($dir."*") as $file) {
		if(time() - filectime($file) > 86400){
	    	unlink($file);
	    }
	}

	// include options
	include("include/options.php");
	?>

	<body class="dashboard">

		<div class="container">

			<div class="oneFourth">

				<!-- input fields -->
				<div class="options fullWidth"><form id="options_form">

						<div class="open_option country_outer">
							<img src="img/world.png" alt="World" />
							<h3>Country <a class="helper_countries helperButton absolute">
								<img src="img/help.svg" alt="Help" /></a>
							</h3>
							<div class="option_inside">
								<div class="inner">

									<div class="checkboxAll">
										<label>Select all</label>
										<input type="checkbox" value="select_all" name="select_all" id="select_all" onclick="select_all_function(this.value)";>
									</div>
									<br />

									<div id="countryChoices">
										<?php
										foreach($countries as $countryCode => $country):

										// als de key een nummer is, zet dan een witte lijn en gebruik de val als titel
										if(is_numeric($countryCode)){
											echo "<br />";

											if($country === "Europe divider"): ?>

												<div class="checkboxEurope">
													<label>Select Europe</label>
													<input type="checkbox" value="select_europe" name="select_europe" id="select_europe" onclick="select_europe_function(this.value)";>
												</div>
												<br />
												<?php echo '<p class="europeClickOpen">Europe <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 76.48 58" id="driehoek"><defs><style>.blauwBg{fill:#0078b8;}</style></defs><path class="blauwBg" d="M74.71,0C76.36,0,77,1.12,76.05,2.5L39.9,57a1.85,1.85,0,0,1-3.32,0L.43,2.5C-.49,1.12.12,0,1.77,0Z"/></svg></p>';

												echo "<div class='europeContainer'>";
											endif;
											if($country === "Israel"):
												echo "</div>";
												echo "<br />";
											endif;

											continue;
										}

										$checked = false;
										if(!empty($choosen_countries) && in_array($countryCode, $choosen_countries)):
											$checked = true;
										endif;
										?>

										<?php echo $country; ?>
										<div class="onoffswitch">
											<input type="checkbox"  value="<?php echo $countryCode; ?>"
											name="select_countries[]" onclick="select_countries_func(this.value);" class="onoffswitch-checkbox"
											id="<?php echo $country; ?>" <?php if($checked): echo " checked"; endif;?>>
												<label class="onoffswitch-label" for="<?php echo $country; ?>">
													<span class="onoffswitch-inner"></span>
													<span class="onoffswitch-switch"></span>
												</label>
										</div>
										<div class="clear"></div>

										<?php endforeach; ?>
									</div>

								</div>
							</div>
						</div>


						<div class="open_option">
							<img src="img/routes.png" alt="Routes" />
							<h3>Route
								<a class="helper_routes helperButton absolute">
									<img src="img/help.svg" alt="Help" />
								</a>
							</h3>

							<div class="option_inside">
								<div class="inner">

									<div class="legenda pct"></div>PCT route
									<div class="onoffswitch">
									    <input type="checkbox" value="pct_route" name="route[]" onclick="select_pct_route_func();"
									    class="onoffswitch-checkbox" id="select_pct_route" <?php if($pctRoute == 1): echo " checked"; endif; ?>>
										    <label class="onoffswitch-label" for="select_pct_route">
										        <span class="onoffswitch-inner"></span>
										        <span class="onoffswitch-switch"></span>
										    </label>
									</div>
									<div class="clear"></div>

									<div class="legenda national"></div>National route
									<div class="onoffswitch">
									    <input type="checkbox" value="select_national_route" name="route[]" onclick="select_national_route_func();"
									    class="onoffswitch-checkbox" id="select_national_route" <?php if($nationalRoute == 1): echo " checked"; endif; ?>>
										    <label class="onoffswitch-label" for="select_national_route">
										        <span class="onoffswitch-inner"></span>
										        <span class="onoffswitch-switch"></span>
										    </label>
									</div>

								</div>
							</div>
						</div>

					</form>

					<div class="open_option">
						<img src="img/reset.png" alt="Reset" />
						<h3>Reset or logout</h3>
						<div class="option_inside">
							<div class="inner">
								<a href="#reset" alt="reset tool" id="reset_tool">Reset the tool</a>
								<br />
								<a href="<?php echo $_SERVER['REQUEST_URI']; ?>?logout=true" id="logout_tool">Logout</a>
							</div>
						</div>
					</div>

				</div>

			</div> <!-- einde eenderde -->

			<div class="threeFourth dashboard_outer">

				<!-- loader -->
				<div id="loader"></div>

				<!--warning -->
				<div id="warning"></div>

				<div class="fullWidth">
					<div class="dashboard_item drie_px_border active_patents center">
						<div class="dashboard_item_inner">
							<h2>Patent Calculator</h2>
							<p>You currently have <strong>1</strong> patent in the calculator</p>
						</div>
					</div>
				</div>

				<!--  CHART -->
				<div class="twoFourth">
					<div class="dashboard_item drie_px_border">

						<div class="dashboard_item_inner">

							<h2>Costs over the years
								<a class="helper_costs helperButton relative">
									<img src="img/help.svg" alt="Help" />
								</a>
							</h2>
							<p class="center blauw small">Use the || buttons or click and drag inside the graphs to zoom in</p>
<!--
							<div class="slideMenuOuter">
							    <a href="#costs_per_year" class="first on">Per year</a>
							    <a href="" class="tumbler">.</a>
							    <a href="#cumulative" class="second">Cumulative</a>
							</div>
-->

							<div id="costs_per_year">
								<div id="chartdiv"></div>
							</div>


						</div>

					</div>
				</div>

				<div class="twoFourth">
					<div class="dashboard_item drie_px_border">

						<div class="dashboard_item_inner">

							<h2>Cumulative costs
								<a class="helper_costs helperButton relative">
									<img src="img/help.svg" alt="Help" />
								</a>
							</h2>
							<p class="center blauw small">Use the || buttons or click and drag inside the graphs to zoom in</p>


							<div id="cumulative">
								<div id="cum_chartdiv"></div>
							</div>

						</div>

					</div>
				</div>



				<!--  TABLE -->
<!--
				<div class="twoThird">
					<div class="dashboard_item drie_px_border">
						<h2>Costs per country</h2>
						<table id="countryList" >

						</table>
					</div>
				</div>
-->



				<div class="twoThird">

					<!-- Europe message -->
					<div class="europe hidden center">
						<div class="dashboard_item drie_px_border add_portfolio">
							<div class="dashboard_item_inner">
								<h3>Regionale route</h3>
								<br />
								<p>
									When you select one or more of the European countries from the list, the cost calculations are based on the regional route via the European Patent Office (EPO).
								</p>
							</div>
						</div>
					</div>

					<!-- add patents -->
					<div class="add_portfolio_outer">
						<div class="dashboard_item drie_px_border add_portfolio">
							<div class="dashboard_item_inner">

									<h2>Patent portfolio of <?php echo $user->data->user_nicename; ?></h2>

									<p class="center">Patent 1 starts in year 1 and has already been added. Add extra patents to your portfolio</p>

									<br />
									<div class="add_patent_outer">
										<button class="add_one_patent"><h2>Add patent +</h2></button>
									</div>


									<div class="patent_1 active">
										<p class="title">Patent 1 starts in year 1</p>
									</div>

									<?php
										$buttons = '
											<a class="editFieldset" alt="Edit fieldset"><img src="img/edit.svg" width="20" height="20" alt="edit"/></a>
											<a class="removeFieldset" alt="Remove fieldset"><img src="img/Delete.svg" width="20" height="20" alt="delete"/></a>
											<div class="edit">
												<p class="center">fill in a start year and press calculate</p>
												<div class="buttons">
													<div class="inc button">+</div><div class="dec button">-</div>
													<button class="recalculateButton" alt="Calculate again" disabled>Recalculate</button>
												</div>
											</div>
										';
									?>

									<form id="add_patent_form">

										<fieldset>
<!--											<div class="legenda pct"></div>-->
											<label for="name">Patent 2 starts in year</label>
											<input type="number" value="0" min="1" placeholder="how many patents" id="patent_2" name="patent_2"/>
											<?php echo $buttons; ?>
										</fieldset>

										<fieldset>
											<label for="name">Patent 3 starts in year</label>
											<input type="number" value="0" min="1" placeholder="how many patents" id="patent_3" name="patent_3"/>
											<?php echo $buttons; ?>
										</fieldset>

										<fieldset>
											<label for="name">Patent 4 starts in year</label>
											<input type="number" value="0" min="1" placeholder="how many patents" id="patent_4" name="patent_4"/>
											<?php echo $buttons; ?>
										</fieldset>

										<fieldset>
											<label for="name">Patent 5 starts in year</label>
											<input type="number" value="0" min="1" placeholder="how many patents" id="patent_5" name="patent_5"/>
											<?php echo $buttons; ?>
										</fieldset>

		<!-- 								<input type="submit" value="re-calculate" id="recalculate" /> -->

									</form>

							</div>
						</div>
					</div>

					<div class="fullWidth disclaimer">
						<div class="dashboard_item drie_px_border center">
							<div class="dashboard_item_inner">
								<p class="blauw small">For educational purposes only. This tool is tested periodically and updated before the start of each new course.</p>
							</div>
						</div>
					</div>

				</div>


				<!--  TOTALS -->
				<div class="oneThird">

					<div id="settings_changed" class="dashboard_item drie_px_border center blauw hidden">
						<div class="dashboard_item_inner">
							<h2>Calculate</h2>
							<p>You changed the settings. Press the calculate button to update the graphs</p>
							<a class="recalculateButton" alt="Export">Calculate</a>
						</div>
					</div>

					<div class="export hidden"></div>

					<?php /* elseif($user->data->ID): ?>
						<div class="dashboard_item drie_px_border noExport center">
							<div class="dashboard_item_inner">
								<h2>Want to export the results to PDF?
								</h2>
								<p>This calculator is used in our course. <br /> Subscribe to <a href="https://www.pauljanssenfuturelab.eu/product/intellectual-property/" target="_blank">Intellectual Property</a></p>
							</div>
						</div>
					<?php else: ?>
						<div class="dashboard_item drie_px_border noExport center">
							<div class="dashboard_item_inner">
								<h2>Want to export the results to PDF?
								</h2>
								<p>This calculator is used in our course. <br /> Subscribe to <a href="https://www.pauljanssenfuturelab.eu/product/intellectual-property/" target="_blank">Intellectual Property</a> or login with your Paul Janssen Futurelab account.</p>
							</div>
						</div>
					<?php endif; */?>

					<div class="dashboard_item drie_px_border total_pct hidden">
						<div class="dashboard_item_inner">
							<h2>PCT route</h2>
							<p></p>
						</div>
					</div>
					<div class="dashboard_item drie_px_border total_national hidden">
						<div class="dashboard_item_inner">
							<h2>National route</h2>
							<p></p>
						</div>
					</div>
				</div>

			</div>	<!-- einde tweederde -->

		<script>
		/* -------------------------------------
			Global vars
		------------------------------------- */
			// clicked countries
			clicked_countries = [];

			// init map and chart
			map = '';
			chart = '';
			userChoices = '';
			settings_changed = false;

			// no warning in the beginning
			activeWarning = false;

			// is an active checked country part op Europe? In that case, show EPC message
			europe_active = false;

			// Check if a user defined object is stored in LocalStorage
			// convert de string naar een object
			var retrieveduserChoices = localStorage.getItem('userChoices');

			// als de string leeg is, maak dan een nieuw schoon object aan
			if(retrieveduserChoices){
				userChoices = JSON.parse(retrieveduserChoices);

				// check ook alle settings, based on this object
				if(userChoices.routes.pct === 1){
					$("#select_pct_route").prop('checked', true);
				}
				if(userChoices.routes.national === 1){
					$("#select_national_route").prop('checked', true);
				}

				for(i = 0; i < userChoices.countries_arr.length; i++ ){
					$("input[value='"+userChoices.countries_arr[i]+"']").prop('checked', true);
				}

				// selecteer ze alllemaal
				if(userChoices.country_selections && userChoices.country_selections.select_all){
					if(userChoices.country_selections.select_all === 1){
						$("#select_all").prop('checked', true);
					}
				} else {
					userChoices.country_selections = {};
					userChoices.country_selections.select_all = 0;
				}

				for(i = 0; i < userChoices.regions_arr.length; i++ ){
					$("input[value='"+userChoices.regions_arr[i]+"']").prop('checked', true);
				}

				for (var patentName in userChoices.patents){
				    if (userChoices.patents.hasOwnProperty(patentName)) {
					    var startYear = userChoices.patents[patentName];
						$("input#" + patentName).val(startYear);
				    }
				}
			} else {
				// choices from the user
				userChoices = {
					routes: {
						pct: '0',
						national: '0'
					},
					regions_arr: [],
					countries_arr: [],
					patents: {
						default_patent: 0
					},
					country_selections: {
						select_all: 0,
						select_europe: 0
					}
				}

				// alleen voor nieuwe mensen een alert
				$.confirm({
					title: 'Welcome to the Paul Janssen Futurelab Patent Calculator.',
					theme: 'pjfl_confirm',
					content: 'With this tool you can estimate the costs related to patent application and granting procedures, specifically in medical life sciences. The estimated patent costs include both general fees and costs of the patent attorney and examiner (‘Office Actions’). Additional costs that may arise from large or extensive patent applications, for small business entities, or due to corrective actions or litigation procedures are not included. <br /> <br />For educational purposes only. This tool is tested periodically and updated before the start of each new course.',
					buttons: {
						cancel: {
							text: 'Let\'s start',
							btnClass: 'message_button_green'
						}
					}
				});

			};

			// European countries
			europeanCountriesArr = [
				"DE", "FR", "GB", "ES", "IT", "NL", "BE", "AT", "DK", "IR", "SE", "CH"
			];


			// money.js settings
//			money = fx.noConflict();
//			money.rates = <?php //echo json_encode($exchange->rates); ?>;
//			money.base = "<?php // echo $exchangeRates->base; ?>";

			// routes prices objects
			pct_costs_obj = {};
			national_costs_obj = {};

			// different divs
			chartDiv = $("#chartdiv");
			cumChartDiv = $("#cum_chartdiv");
			tableDiv = $("#countryList");


			// INIT
			// create_map();
			var chartConfig = {
				"pathToImages": "<?php echo $tool_url; ?>/img/",
 				"mouseWheelZoomEnabled": true,
				"columnSpacing": 0,
				"columnWidth": 0.5,
				"chartScrollbar": {
					"autoGridCount": true,
					"scrollbarHeight": 40
				},
				"chartCursor": {
				   "color":"#000000",
					"cursorColor":"#EDECEB",
					"oneBalloonOnly": true,
					"cursorPosition": "mouse",
					"selectionAlpha": 0.6
				},
				"theme": "none",
				"showLastLabel": true,
				"type": "serial",
				"responsive": {
					"enabled": true
				},
				"dataProvider": [{
					"year": "1",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "2",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "3",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "4",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "5",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "6",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "7",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "8",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "9",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "10",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "11",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "12",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "13",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "14",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "15",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "16",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "17",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "18",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "19",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				},{
					"year": "20",
					"route_pct_patent_default": 0,
					"route_national_patent_default": 0
				}],
				"startDuration": 1,
				"graphs": [
				{
					"balloonText": "PCT<br />Patent 1: <b>€[[value]]<br />Total: <b>[[route_pct_column_total]]</b>",
					"fillAlphas": 1,
					"fillColor": "#ffffff",
					"lineAlpha": 0,
					"color": "#009292",
					"fillColors": "#009292",
					"lineColor": "#009292",
					"title": "PCT route",
					"type": "column",
					"valueField": "route_pct_patent_default",
				},
				{
					"balloonText": "PCT<br />Patent 2: <b>€[[value]]</b><br />Total: <b>[[route_pct_column_total]]</b>",
					"fillAlphas": 1,
					"lineAlpha": 0,
					"color": "#009292",
					"lineColor": "#009292",
					"title": "PCT route patent 2",
					"type": "column",
					"lineColor": "#009292",
					"valueField": "route_pct_patent_2",
					"pattern": {
						"url": "<?php echo $tool_url; ?>/img/patterns/pct_skewed.png",
						"width": 4,
						"height": 4
					}
				},
				{
					"balloonText": "PCT<br />Patent 3: <b>€[[value]]</b><br />Total: <b>[[route_pct_column_total]]</b>",
					"fillAlphas": 1,
					"lineAlpha": 0,
					"color": "#009292",
					"lineColor": "#009292",
					"lineThickness": 1,
					"pattern": {
						"url": "<?php echo $tool_url; ?>/img/patterns/pct_dotted.png",
						"width": 4,
						"height": 4
					},
					"title": "PCT route patent 3",
					"type": "column",
					"valueField": "route_pct_patent_3"
				},
				{
					"balloonText": "PCT<br />Patent 4: <b>€[[value]]</b><br />Total: <b>[[route_pct_column_total]]</b>",
					"fillAlphas": 1,
					"lineAlpha": 0,
					"color": "#009292",
					"lineColor": "#009292",
					"pattern": {
						"url": "<?php echo $tool_url; ?>/img/patterns/pct_horizontal.png",
						"width": 4,
						"height": 4
					},
					"title": "PCT route patent 4",
					"type": "column",
					"valueField": "route_pct_patent_4"
				},
				{
					"balloonText": "PCT<br />Patent 5: <b>€[[value]]</b><br />Total: <b>[[route_pct_column_total]]</b>",
					"fillAlphas": 1,
					"lineAlpha": 0,
					"lineColor": "#009292",
					"color": "#009292",
					"fillColors": "#80C1BF",
					"title": "PCT route patent 5",
					"type": "column",
					"valueField": "route_pct_patent_5"
				},
				{
					"balloonText": "PCT - total: <b>€[[value]]</b>",
					"fillAlphas": 1,
					"fillColor": "#ffffff",
					"lineAlpha": 0,
					"color": "#009292",
					"fillColors": "#009292",
					"lineColor": "#009292",
					"title": "Total PCT route",
					"type": "column",
					"valueField": "route_pct_total",
				},
				{
					"newStack": true,
					"balloonText": "National<br />Patent 1: <b>€[[value]]</b><br />Total: <b>[[route_national_column_total]]",
					"fillAlphas": 1,
					"lineColor": "#C42C30",
					"lineAlpha": 0,
					"fillColors": "#C42C30",
					"title": "National route patent 1",
					"type": "column",
					"valueField": "route_national_patent_default"
				},
				{
					"balloonText": "National<br />Patent 2: <b>€[[value]]</b><br />Total: <b>[[route_national_column_total]]</b>",
					"fillAlphas": 1,
					"lineAlpha": 0,
					"lineColor": "#C42C30",
					"color": "#009292",
					"pattern": {
						"url": "<?php echo $tool_url; ?>/img/patterns/national_skewed.png",
						"width": 4,
						"height": 4
					},
					"title": "National route patent 2",
					"type": "column",
					"valueField": "route_national_patent_2"
				},
				{
					"balloonText": "National<br />Patent 3: <b>€[[value]]</b><br />Total: <b>[[route_national_column_total]]</b>",
					"fillAlphas": 1,
					"lineAlpha": 0,
					"lineColor": "#C42C30",
					"pattern": {
						"url": "<?php echo $tool_url; ?>/img/patterns/national_dotted.png",
						"width": 4,
						"height": 4
					},
					"title": "National route patent 3",
					"type": "column",
					"valueField": "route_national_patent_3"
				},
				{
					"balloonText": "National<br />Patent 4: <b>€[[value]]</b><br />Total: <b>[[route_national_column_total]]</b>",
					"fillAlphas": 1,
					"lineAlpha": 0,
					"lineColor": "#C42C30",
					"color": "#009292",
					"pattern": {
						"url": "<?php echo $tool_url; ?>/img/patterns/national_horizontal.png",
						"width": 4,
						"height": 4
					},
					"title": "National route patent 4",
					"type": "column",
					"valueField": "route_national_patent_4"
				},
				{
					"balloonText": "National<br />Patent 5: <b>€[[value]]</b><br />Total: <b>[[route_national_column_total]]</b>",
					"fillAlphas": 1,
					"lineAlpha": 0,
					"lineColor": "#C42C30",
					"color": "#009292",
					"fillColors": "#E5A4A6",
					"title": "National route patent 5",
					"type": "column",
					"valueField": "route_national_patent_5"
				},
				{
					"balloonText": "National - total: <b>€[[value]]</b>",
					"fillAlphas": 1,
					"lineColor": "#C42C30",
					"lineAlpha": 0,
					"fillColors": "#C42C30",
					"title": "Total national route",
					"type": "column",
					"valueField": "route_national_total"
				},
				],
				"plotAreaFillAlphas": 0.1,
				"categoryField": "year",
				"valueAxes": [{
					"unit": " euro",
					"position": "left",
					"title": "",
					"color": "#0078b9",
					"stackType": "regular"
				}],
				"categoryAxis": {
					"unit": " year",
					"gridPosition": "start",
					"titleColor": "#0078b9",
					"title": "Year",
					"titleFontSize": 12,
					"titleBold": true,
					"position": "bottom"
				},
				"listeners": [{
					"event": "rendered",
					"method": function(e) {
						console.log('rendered!');
					}
				}],
				"export": {
					"enabled": true
				 }
			}

			function clone(obj) {
			  var copy;

			  // Handle the 3 simple types, and null or undefined
			  if (null == obj || "object" != typeof obj) return obj;

			  // Handle Date
			  if (obj instanceof Date) {
				copy = new Date();
				copy.setTime(obj.getTime());
				return copy;
			  }

			  // Handle Array
			  if (obj instanceof Array) {
				copy = [];
				for (var i = 0, len = obj.length; i < len; i++) {
				  copy[i] = clone(obj[i]);
				}
				return copy;
			  }

			  // Handle Object
			  if (obj instanceof Object) {
				copy = {};
				for (var attr in obj) {
				  if (obj.hasOwnProperty(attr)) copy[attr] = clone(obj[attr]);
				}
				return copy;
			  }

			  throw new Error("Unable to copy obj! Its type isn't supported.");
			}

			var chartConfig1 = clone(chartConfig);
			// modify a copy of the config
			chartConfig1.export = {
				"enabled": true,
				"removeImages": false,
				  "menu": [ {
					"class": "export-main",
					"menu": [ ]
				  } ]
				// "divId": "exportdiv",
				// 'position': 'bottom-right'
			};

			var chartConfig2 = clone(chartConfig);
			// modify a copy of the config
			chartConfig2.export = {
				"enabled": true,
				"removeImages": false,
				  "menu": [ {
					"class": "export-main",
					"menu": [ ]
				  } ]
				// "divId": "exportdiv2",
				// 'position': 'bottom-right'
			};

			create_chart(chartConfig1);
			create_cum_chart(chartConfig2);

			// bereken de chart en de table opnieuw
			pocData = '';

			// INIT
			// get JSON data from file
			// after, calculate the whole tool
			var PC_data = "<?php echo $tool_url; ?>/js/data.js";
			$.getJSON( PC_data, {
				format: "json"
			})
			.done(function( data ) {
				pocData = data;
				// de tool is geladen
				recalculate();
    	});

		/* -------------------------------------
			INTERACTION
		------------------------------------- */

			/* -------------------------------------
				Settings
			------------------------------------- */
			function changed_settings(status) {
				$.when(check_for_warnings()).done(function() {
					if(activeWarning === false){
						show_elements();

						if(status == true && settings_changed == true){
							return;
						}

						var settings_div = $('#settings_changed'),
								export_div = $('div.export'),
								cum_chart_div = $('#cumulative'),
								year_chart_div = $('#costs_per_year');

						if(status){
							zero_chart(chart);
							zero_chart(cum_chart);
							settings_div.slideDown();
							export_div.slideUp();
							cum_chart_div.css('opacity', '.5');
							cum_chart_div.prepend('<button class="recalculateButton" style="opacity:1;" alt="Calculate again">Recalculate</button></div>');
							year_chart_div.css('opacity', '.5');
							year_chart_div.prepend('<button class="recalculateButton" style="opacity:1;" alt="Calculate again">Recalculate</button></div>');
							settings_changed = true;
						} else {
							settings_div.slideUp();
							export_div.slideDown();
							cum_chart_div.css('opacity', '1');
							cum_chart_div.find("button").remove();
							year_chart_div.css('opacity', '1');
							year_chart_div.find("button").remove();
							settings_changed = false;
						}

					} else {
						hide_elements();
					}
				});
			}

			/* -------------------------------------
				Info popups
			------------------------------------- */
			$(".helper_countries").on("click", function() {
				$.confirm({
					title: 'Countries for which you are seeking patent protection',
					theme: 'pjfl_confirm',
					content: 'Select one or more countries for which you are seeking patent protection',
					buttons: {
						cancel: {
							text: 'I got it',
							btnClass: 'message_button_green'
						}
					}
				});
			});

			$(".helper_routes").on("click", function() {
				$.confirm({
					title: 'Routes',
					theme: 'pjfl_confirm',
					content: 'PCT route <br />The Patent Cooperation Treaty (PCT) provides a unified procedure for filing patent applications in each of the contracted states. During this international phase, a search report and a written opinion concerning the patentability may assist to determine the patentability of your application. The PCT route includes an international and a national/regional phase. At 30 months from the priority date, the international phase ends and the application enters the national and regional phase (in Europe). The patent examination procedures take place during the national/regional phase. <br /><br /> National route<br />Patent applications can also follow national routes directly. For Europe, the calculator follows the European Patent Office (EPO) route, after which the European Patent (EP) is validated in the selected member states.',
					buttons: {
						cancel: {
							text: 'I got it',
							btnClass: 'message_button_green'
						}
					}
				});
			});

			$(".helper_costs").on("click", function() {
				$.confirm({
					title: 'Cumulative - or annual costs',
					theme: 'pjfl_confirm',
					content: 'Cost per year shown over a period of 1-20 years per patent. Costs are displayed in Euro’s.',
					buttons: {
						cancel: {
							text: 'I got it',
							btnClass: 'message_button_green'
						}
					}
				});
			});

			$(".helper_patents").on("click", function() {
				$.confirm({
					title: 'Patent Portfolio',
					theme: 'pjfl_confirm',
					content: 'This free Patent Calculator helps you to estimate the cost for a single patent. A premium version is available for course participants of the Intellectual Property. Use the premium Patent Calculator to estimate the cost for up to 5 patents and create a patent portfolio',
					buttons: {
						cancel: {
							text: 'I got it',
							btnClass: 'message_button_green'
						}
					}
				});
			});
			/* -------------------------------------
				Increment buttons extra patents form
			------------------------------------- */
			var addFormFieldset = $("#add_patent_form fieldset:not(.active)");

			// hide on load
			addFormFieldset.hide();

			// voeg een fieldset toe
			$("button.add_one_patent").on("click", function(e){

				changed_settings(true);

				e.preventDefault();

				var el = $("#add_patent_form fieldset:hidden:first");

				if(el.length){
					el.fadeIn();
					el.find('.edit').slideDown();
					el.find('input').val(1);
				}

				// kijk of er nog verborgen velden zijn, zo ja, maak hem nog zichrtbaar
				var hiddenFields = $("#add_patent_form fieldset:hidden");
				if(hiddenFields.length){
					$(this).prop('disabled', false);
				} else {
					$(this).prop('disabled', true);
				}

				// als er op add is gelikt, maak dan remove ook zichtbaar
				$("button.remove_one_patent").prop('disabled', false);

				// $("html, body").animate({ scrollTop: $(document).height() }, 1000);

			});

			// open and close edit div
			$(".editFieldset").on("click", function(e){

				e.preventDefault();
				var $this = $(this);

				$this.parents('fieldset').removeClass('active');
				$this.siblings("div.edit").slideToggle();
			});

			// remove deze fieldset
			$(".removeFieldset").on("click", function(e){

				e.preventDefault();

				// zet in var
				var parentFieldset = $(this).parent("fieldset");

				// remove hem
				parentFieldset.slideToggle().removeClass('active');

				// zoek de input en zet op 0
				var input = parentFieldset.find("input");

				input.val(0);

				// haal dit patent uit de userChoices
				var patentProp = input.attr('id');
				delete userChoices.patents[patentProp];

				// enable de add patent button
				$("button.add_one_patent").prop('disabled', false);

				changed_settings(true);

			});

			$("#add_patent_form .button").on("click", function() {

				var $button = $(this);
				var oldValue = $button.parents('fieldset').find("input").val(),
					recalculate = $button.siblings('.recalculateButton');

				recalculate.prop('disabled', false);

				if ($button.text() == "+") {
					var newVal = parseFloat(oldValue) + 1;
				} else {
					// Don't allow decrementing below zero
					if (oldValue > 1) {
						var newVal = parseFloat(oldValue) - 1;
					} else {
						newVal = 1;
					}
				}

				// set new input
				$button.parents('fieldset').find("input").val(newVal);

				// settings changed
				changed_settings(true);

			});

			$("#add_patent_form :input").on("change", function() {
				$(this).find('.recalculateButton').prop('disabled', false);
			});

			// recalculate
			$(document).on("click", ".recalculateButton", function(e) {
				e.preventDefault();

				// recalculate the form
				// insert_extra_patents();
				recalculate();
			});


			function pressExportButton(){
				$(".exportBtn").on("click", function(click) {

					click.preventDefault();

					// recalculate the form
					//exportData(chart);
					exportData(AmCharts.charts);
				});
			}

			/* -------------------------------------
				Reset tool
			------------------------------------- */
			$('#reset_tool').on("click", function() {
				disableData();
			})


			/* -------------------------------------
				Options slide panels
			------------------------------------- */
//			jQuery( document ).ready(function() {
//				// $('.option_inside').hide();
//				$('.open_option h3').on("click", function() {
//					var $this = $(this);
//
//					$this.next().slideToggle('fast');
//				});
//			});

			/* -------------------------------------
				Switch graphs
			------------------------------------- */
			function switch_graphs($this, id){
//				if(id === "#costs_per_year"){
//					$("#cumulative").slideToggle();
//				} else {
//					$("#costs_per_year").slideToggle();
//				}

				// $(id).slideToggle();
			}

//			$(".first").on("click", function(){
//
//				if($(".second").hasClass("on"),$(".tumbler").hasClass("on")){
//
//					var $this = $(this),
//						id = $this.attr('href');
//
//					switch_graphs($this, id);
//
//			    	$(".second").removeClass("on"),
//					$(".tumbler").removeClass("on"),
//					$(".first").addClass("on");
//				}
//
//			  return false;
//			});

//			$(".second").on("click", function(){
//				if($(".first").hasClass('on')){
//
//					var $this = $(this),
//						id = $this.attr('href');
//
//					switch_graphs($this, id);
//
//					$(".first").removeClass('on'),
//					$(".second").addClass('on'),
//					$(".tumbler").addClass('on');
//				}
//			  return false;
//			});

//			$(".tumbler").on("click", function(){
//				if($(".tumbler").hasClass('on'),$(".second").hasClass('on')) {
//
//					var $this = $(".first"),
//						id = $this.attr('href');
//
//					switch_graphs($this, id);
//
//					$(".tumbler").removeClass('on'),
//					$(".second").removeClass('on'),
//					$(".first").addClass('on');
//				} else {
//
//					var $this = $(".second"),
//						id = $this.attr('href');
//
//					switch_graphs($this, id);
//
//					$(".tumbler").addClass('on'),
//					$(".first").removeClass('on'),
//					$(".second").addClass('on');
//			  }
//			  return false;
//
//			});


			/* -------------------------------------
				Hide europe countries and show them on click
			------------------------------------- */
			var europeContainer = $(".europeContainer"),
				europeClicker = $(".europeClickOpen");

			if(!retrieveduserChoices){
				europeContainer.hide();
			}

			$(europeClicker).on("click", function() {
				europeContainer.slideToggle();
			});

			/* -------------------------------------
				On change checkbox buttons, enable or disable routes
			------------------------------------- */
			function select_all_function(value){

				// var
				selectAllCheckbox = $('#select_all');

				// check or uncheck
				var parent = selectAllCheckbox.parent('div.checkboxAll').find(':checkbox');
				var checkboxes = $('div#countryChoices').find(':checkbox');

				checkboxes.prop('checked', selectAllCheckbox.is(':checked'));

				userChoices.countries_arr = [];

				// delete or add countries
				if(!document.getElementById('select_all').checked) {
					$(".europeContainer").slideUp();
					userChoices.country_selections.select_all = 0;
				} else {
					$(".europeContainer").slideDown();

					$.each(checkboxes, function(){
						countryCode = $(this).val();
						if(countryCode !== "select_europe" && countryCode !== "select_all" && countryCode !== "pct_route" && countryCode !== "select_national_route"){
							// console.log(countryCode);
							userChoices.countries_arr.push(countryCode);
						}
					});
					userChoices.country_selections.select_all = 1;
				}

				changed_settings(true);

			}

			/* -------------------------------------
				On change checkbox buttons, select europe
			------------------------------------- */
			function select_europe_function(value){

				// var
				selectAllCheckbox = $('#select_europe');

				// check or uncheck
				var parent = selectAllCheckbox.parent('div.checkboxEurope').find(':checkbox');
				var checkboxes = $('div#countryChoices').find(':checkbox');


				// delete or add countries
				if(!document.getElementById('select_europe').checked) {
					$(".europeContainer").slideUp();
// 					userChoices.country_selections.select_all = 0;
						$.each(checkboxes, function(){
							countryCode = $(this).val();

							if(inArray(countryCode,europeanCountriesArr)) {

								$(this).prop('checked', false);

								for( var i = 0; i < userChoices.countries_arr.length; i++){
								   if ( userChoices.countries_arr[i] === countryCode) {
								     userChoices.countries_arr.splice(i, 1);
								   }
								}

							}

						});

				} else {
					$(".europeContainer").slideDown();

					$.each(checkboxes, function(){
						countryCode = $(this).val();

						if(inArray(countryCode,europeanCountriesArr)) {

							$(this).prop('checked', true);

							userChoices.countries_arr.push(countryCode);
						}

					});
				// 	userChoices.country_selections.select_europe = 1;

				}

				changed_settings(true);

			}

			/* -------------------------------------
				On change checkbox buttons, enable or disable routes
			------------------------------------- */
			function select_pct_route_func(){

				if (document.getElementById('select_pct_route').checked) {
				  userChoices.routes.pct = 1;
				} else {
				  userChoices.routes.pct = 0;
				}

				changed_settings(true);

			}

			function select_national_route_func(){

				if (document.getElementById('select_national_route').checked) {
				 userChoices.routes.national = 1;
				} else {
				 userChoices.routes.national = 0;
				}

				changed_settings(true);
			}

			function select_countries_func(value){

				// if the country doesn't exist in the array, add it
				var i = userChoices.countries_arr.indexOf(value);

				if(i === -1){
					userChoices.countries_arr.push(value);
				}
				// else delete it
				else {
					userChoices.countries_arr.splice(i, 1);
					userChoices.country_selections.select_all = 0;
					$("#select_all").prop("checked", false);
				}

				console.log('select countries func');
				console.log(userChoices);
				changed_settings(true);

			}


			function select_regions_func(value){

				// als het object leeg is, knal erin
				if(Object.keys(userChoices.regions_arr).length === 0 && userChoices.regions_arr.constructor === Object){
					userChoices.regions_arr.push(value);
				} else {
					// if the country doesn't exist in the array, add it
					var i = userChoices.regions_arr.indexOf(value);

					if(i === -1){
						userChoices.regions_arr.push(value);
					}
					// else delete it
					else {
						userChoices.regions_arr.splice(i, 1);
					}
				}

				changed_settings(true);

			}

			function containsRegion(needle, haystack){
				// als de geselecteerde landen niet leeg zijn
				if(haystack.length > 0){

 					for(var i in haystack) {
						if(haystack[i] == needle) return true;
					}
    				return false;
				}

				return false;
			}


			/* -------------------------------------
				(re)Draw the chart
			------------------------------------- */
			function draw_chart() {

				$.when(chartDiv.find("svg").fadeOut(350)).done(function() {
					// update de chart
					$.when(update_chart()).done(function() {
						chartDiv.find("svg").fadeIn(350);
					});
				});

			}; // end draw chart function

			function zero_chart(chartVar) {
				for (var barYear = 1; barYear <= chartVar.dataProvider.length; barYear++) {

					//console.log(Object.keys(chartVar.dataProvider[barYear -1 ]));
					Object.keys(chartVar.dataProvider[barYear -1 ]).forEach(function(key) {

						if(key !== "year"){
							delete chartVar.dataProvider[barYear - 1][key];
						}

					});

/*
					chartVar.dataProvider[barYear -1].route_pct = 0
					chartVar.dataProvider[barYear -1].route_national = 0;
*/

				}

				//chartVar.clearLabels();
				// chartVar.clearLabels();
				chartVar.validateData();

			}

			function update_chart() {

				var pct_patenten = Object.keys(pct_costs_obj),
				national_patenten = Object.keys(national_costs_obj);

				// loop door het aantal jaren die in de dataProvider staan (20)
				// bereken per bar de prijs voor zowel de pct als de nationale route
				// dit is een som va kosten per land (taxes) maar ook vaste kosten
				for (var barYear = 1; barYear <= chart.dataProvider.length; barYear++) {

					// define bar amounts
					if(userChoices.routes.pct === 1){

						// set each year to 0
						chart.dataProvider[barYear -1]["route_pct_column_total"] = 0

						// kijk in alle patenten en zoek hier de prijzen
						for(var u = 0; u < pct_patenten.length; u++){

							var route_pct_amounts = 0,
									property = pct_patenten[u];

							if(pct_costs_obj[property].hasOwnProperty(barYear)){

								// als het hoger is dan 0, maak een object aan in de chartdata
								if(pct_costs_obj[property][barYear] > 0){
									route_pct_amounts += pct_costs_obj[property][barYear];
									chart.dataProvider[barYear -1]["route_pct_" + property] = route_pct_amounts;
									chart.dataProvider[barYear -1]["route_pct_column_total"] += route_pct_amounts;
								} else {
									delete chart.dataProvider[barYear -1]["route_pct_" + property];
								}
							}
						}

						chart.dataProvider[barYear -1]["route_pct_column_total"] = euroNumber(chart.dataProvider[barYear -1]["route_pct_column_total"], false);

					} else {
						// er is niet voor deze route gekozen, zet de totals en de bars op 0
						delete chart.dataProvider[barYear -1]["route_pct_patent_default"];
						delete chart.dataProvider[barYear -1]["route_pct_" + property];
					}

					// National = nationale costs which are for PCT + national costs which are for national only + taxes
					if(userChoices.routes.national === 1){

						chart.dataProvider[barYear -1]["route_national_column_total"] = 0

						// kijk in alle patenten en zoek hier de prijzen
						for(var u = 0; u < national_patenten.length; u++){

							var route_national_amounts = 0,
									property = national_patenten[u];

							if(national_costs_obj[property].hasOwnProperty(barYear)){

								if(national_costs_obj[property][barYear] > 0){
									route_national_amounts += national_costs_obj[property][barYear];
									chart.dataProvider[barYear -1]["route_national_" + property] = route_national_amounts;
									chart.dataProvider[barYear -1]["route_national_column_total"] += route_national_amounts;
								} else {
									delete chart.dataProvider[barYear -1]["route_national_" + property];
								}
							}
						}

						chart.dataProvider[barYear -1]["route_national_column_total"] = euroNumber(chart.dataProvider[barYear -1]["route_national_column_total"], false);

					} else {
						// er is niet voor deze route gekozen, zet de totals en de bars op 0
						delete chart.dataProvider[barYear -1]["route_national_patent_default"];
						delete chart.dataProvider[barYear -1]["route_national_" + property];
					}

				} // end loop per bar year

				// console.log('chart data');
				// console.log(chart.dataProvider);

				chart.invalidateSize();
				chart.clearLabels();
				// chart.addLabel("0", "!20", event.mapObject.title, "center", 16);
				chart.validateData();

			}


			/* -------------------------------------
				(re)Draw the CUM chart
			------------------------------------- */
			function draw_cum_chart() {

				$.when(cumChartDiv.find("svg").fadeOut(350)).done(function() {
					$.when(update_cum_chart()).done(function() {
						cumChartDiv.find("svg").fadeIn(350);
					});
				});

			}; // end draw chart function

			function update_cum_chart() {

				var pct_patenten = Object.keys(pct_costs_obj),
					national_patenten = Object.keys(national_costs_obj);

				var pctCumAmount = 0,
					nationalCumAmount = 0;


				// loop door het aantal jaren die in de dataProvider staan (20)
				// bereken per bar de prijs voor zowel de pct als de nationale route
				// dit is een som va kosten per land (taxes) maar ook vaste kosten
				for (var barYear = 1; barYear <= chart.dataProvider.length; barYear++) {
					// define bar amounts
					// PCT = pct route costs + nationale costs which are for PCT + taxes
					if(userChoices.routes.pct === 1){

						// kijk in alle patenten en zoek hier de prijzen
						for(var u = 0; u < pct_patenten.length; u++){

							var property = pct_patenten[u];

							if(pct_costs_obj[property].hasOwnProperty(barYear)){

								pctCumAmount += parseInt(pct_costs_obj[property][barYear]);
								cum_chart.dataProvider[barYear -1]["route_pct_total"] = pctCumAmount;
							}
						}

					} else {
						// er is niet voor deze route gekozen, zet de totals en de bars op 0
						cum_chart.dataProvider[barYear -1].route_pct_total = 0
					}

					// National = nationale costs which are for PCT + national costs which are for national only + taxes
					if(userChoices.routes.national === 1){

						// kijk in alle patenten en zoek hier de prijzen
						for(var u = 0; u < national_patenten.length; u++){

							var property = national_patenten[u];
							if(national_costs_obj[property].hasOwnProperty(barYear)){
								nationalCumAmount += parseInt(national_costs_obj[property][barYear]);
								cum_chart.dataProvider[barYear -1]["route_national_total"] = nationalCumAmount;
							}
						}

					} else {
						// er is niet voor deze route gekozen, zet de totals en de bars op 0
						cum_chart.dataProvider[barYear -1].route_national_total = 0;
					}

/*
					console.log("Jaar " + barYear);
					console.log("Jaar " + pctCumAmount);
*/

					cum_chart.invalidateSize();
					cum_chart.clearLabels();
					cum_chart.validateData();

				} // end loop per bar year


				//console.log(cum_chart.dataProvider);

				// nu hebben we van beiden routes de opgetelde getallen
				// update de totals blokken
				update_totals(pctCumAmount, nationalCumAmount);

			} // end function

			function update_totals(pctCumAmount, nationalCumAmount){

				// insert total in total prices
				if(userChoices.routes.pct === 1){
					$('div.total_pct').fadeIn();
					$('div.total_pct p').text("Total costs: €" + euroNumber(pctCumAmount, true));
				} else if(userChoices.routes.pct === 0) {
					$('div.total_pct').slideUp();
				}

				if(userChoices.routes.national === 1){
					$('div.total_national').fadeIn();
					$('.total_national p').text("Total costs: €" + euroNumber(nationalCumAmount, true));
				} else {
					$('div.total_national').slideUp();
				}
			}

		/* -------------------------------------
			EXPORT
		------------------------------------- */
		function exportData(charts){

			// iterate through all of the charts and prepare their images for export
			var images = [];
			var pending = charts.length;
			for ( var i = 0; i < charts.length; i++ ) {
				var chart = charts[ i ];
				chart["export"].capture( {}, function() {
					this.toJPG( {
						multiplier: 2
					}, function( data ) {
					images.push( {
						"image": data,
						"fit": [ 523.28, 769.89 ]
					} );
					pending--;
					if ( pending === 0 ) {

						$.ajax({
							"url": "<?php echo $tool_url; ?>/export.php",
							"method": "POST",
							"data": {
								"pocData": JSON.stringify(pocData),
								"userChoices": JSON.stringify(userChoices),
								// "chartImg": chartImgData,
								"chartImages": JSON.stringify(images),
								"europeanCountriesArr": JSON.stringify(europeanCountriesArr)
							},
							beforeSend: function() {

								$(".export").html('<a><h4>Loading</h4></a>');
							},
							success: function(data) {

								var filename = data;

								// open de link die returned wordt
								window.open(
									"<?php echo $tool_url; ?>/tmp/" + filename,
									'_blank' // <- This is what makes it open in a new window.
								);

								$('div.export').html('<a class="exportBtn" alt="Export"><img src="img/download.svg" width="20" height="20" style="margin-right: 1em; position: relative; top: 3px; "/>Generate PDF</a>').fadeIn();
								pressExportButton();

								// $(".export").html('<a href="https://pauljanssenfuturelab.eu/tools/intellectual_property/online/pc/tmp/'+filename+'" alt="Download the PDF" target="_blank" class="done"><img src="img/download.svg" width="20" height="20" style="margin-right: 1em; position: relative; top: 3px; "/>Download</a>');

								// $("body").prepend(data);
								// $(".export").append("succes");

							},
							error: function(data) {

								// $("body").prepend(data);
								$(".export").append("There was an error while making the PDF. Please contact our support - contact@pauljanssenfuturelab.eu.");
							}
						});	// end ajax

					}
				  } );
				} );
			}
		}


		/* -------------------------------------
			TABLE
		------------------------------------- */
			/* -------------------------------------
				Fill table with data
			------------------------------------- */
			function fill_table() {

				$.when(tableDiv.fadeOut(350)).done(function() {
					$.when(update_table()).done(function() {
						tableDiv.fadeIn(350);
					});
				});

			}

			function update_table(){

				var table = $('#countryList');

				// clear table to fill in new data
				table.find("tr").remove();

				if(userChoices.countries_arr.length > 0){
					// loop door alle landen in het userChoices object en toon per land data
					for (var j = 0; j < userChoices.countries_arr.length; j++) {

						var sTxt = ''

						if(j === 0){
							sTxt += '<tr class="cat_row">';
							sTxt += '<th>Country</th>';
							sTxt += '<th>Initial costs</th>';

							if(userChoices.routes.national === 1) {
								sTxt += '<th>Total National route</th>';
							}

							if(userChoices.routes.pct === 1) {
								sTxt += '<th>Total PCT route</th>';
							}

							sTxt += '<th>Taxes</th>';
							sTxt += '</tr>';
						}

						// het gekozen land
						var choosen_country = userChoices.countries_arr[j],
							pocData_country_obj = pocData.countries[choosen_country];

						sTxt += '<tr>';
						sTxt += '<td><span>Country: </span><img src="img/flags/' +  pocData_country_obj.flag + '"/></td>';
						sTxt += '<td><span>Initial costs: </span>' +  euroNumber(pocData.first_submission) + '</td>';

						if(userChoices.routes.national === 1) {
							sTxt += '<td><span>Total national route: </span>' +  euroNumber(calc_total_national(choosen_country, pocData_country_obj)) + '</td>';
						}

						if(userChoices.routes.pct === 1) {
							sTxt += '<td><span>Total PCT route: </span>' +  euroNumber(calc_total_pct(choosen_country, pocData)) + '</td>';
						}

						sTxt += '<td><span>Total taxes: </span>' +  euroNumber(calc_total_taxes(choosen_country, pocData_country_obj)) + '</td>';
						sTxt += '</tr>';

						table.append(sTxt);

					} // einde for loop per land

				} else {

						sTxt += '<tr>';
						sTxt += '<td>No country selected</td>';
						sTxt += '</tr>';

						table.append(sTxt);
				} // end if landen geselecteerd

			}

		/* -------------------------------------
			CALC FUNCTIONS
		------------------------------------- */

		/* -------------------------------------
			INIT FUNCTIONS
		------------------------------------- */
			/* -------------------------------------
				Recalculate
			------------------------------------- */
			function recalculate(){

				$.when(calculate_pct_costs()).done(function() {
					$.when(calculate_national_costs()).done(function() {
						$.when(check_for_warnings()).done(function() {

							$.when(insert_extra_patents()).done(function() {
								if(activeWarning === false){
									$.when(redraw()).done(function() {

										// console.log('userChoices');
										// console.log(userChoices);
										//
										// console.log('PCT costs');
										// console.log(pct_costs_obj);
										//
										//
										// console.log('dataprovider');
										// console.log(chart.dataProvider);

										show_elements();
										changed_settings(false);
									});
								} else {
									hide_elements();
								}
							});

						});
					});
				});
			}

			// deze wordt gedaan na de recalculate knop
			function redraw() {

				$.when(draw_chart()).done(function() {
					$.when(draw_cum_chart()).done(function() {

						// add userchoices to localStorage
						localStorage.setItem('userChoices', JSON.stringify(userChoices));


						// $('div.export').html('<a class="exportBtn" alt="Export"><img src="img/download.svg" width="20" height="20" style="margin-right: 1em; position: relative; top: 3px; "/>Generate PDF</a>').fadeIn();
							// $.when(fill_table()).done(function() {
								// console.log("loaded");
								// console.log(national_costs_obj);
							// });

					});
				});


			}

			function calculate_national_costs(route){
				if(userChoices.routes.national === 0){
					return '';
				}

				// first patent obj
				national_costs_obj.patent_default = {};

				// reset to zero
				for (i = 1; i < 21; i++) {
					national_costs_obj.patent_default[i] = 0;
				}

				get_priority_costs("national", 0, "patent_default");
				get_region_costs("national", 0), "patent_default";
				get_country_costs("national", 0, "patent_default");
			}

			function calculate_pct_costs(){

				if(userChoices.routes.pct === 0){
					return '';
				}

				// first patent obj
				pct_costs_obj.patent_default = {};

				// set to zero
				for (i = 1; i < 21; i++) {
					pct_costs_obj.patent_default[i] = 0;
				}

				get_priority_costs("pct", 0, "patent_default");
				get_region_costs("pct", 0, "patent_default");
				get_country_costs("pct", 0, "patent_default");

			}

			function get_priority_costs(route, addYears, name){

				if(userChoices.countries_arr.length === 0){
					return '';
				}

				var priority_costs = Object.keys(pocData.costs_priority_year),
					priority_length = priority_costs.length;


				for (var i = 0; i < priority_length; i++) {
					// soort kosten
					var attName = priority_costs[i];

					// pct
					if(route === "pct"){

						// kosten zijn in euro's dus hoeven niet omgerekend te worden
						var year = pocData.costs_priority_year[attName].pct.year,
							cost = pocData.costs_priority_year[attName].pct.cost;

						// add to right object
						add_to_price_obj("pct_costs_obj", cost, name, year, addYears);
					}

					// national
					if(route === "national"){

						var year = pocData.costs_priority_year[attName].national.year,
							cost = pocData.costs_priority_year[attName].national.cost;

						// add to right object
						add_to_price_obj("national_costs_obj", cost, name, year, addYears);
					}


				} // end for loop
			}

			function get_region_costs(route, addYears, name){

				// verkrijg de countries
				choosen_countries = userChoices.countries_arr,
				countries_length = choosen_countries.length;

				if(countries_length === 0){
					return '';
				}

				for (var i = 0; i < countries_length; i++) {

					// region
					var countryName = choosen_countries[i];

					// als dit land voorkomt in de europese landen array (hierboven gedefinieerd), knal dan eenmalig de europese regio kosten erbij, daarna een break;
					if(europeanCountriesArr.indexOf(countryName) > -1){

						// set global var to true to fadeIn europe message
						europe_active = true;

						// add european costs eenmalig
						var region_costs = Object.keys(pocData.regions["Europe"]),
							region_costs_length = region_costs.length;

						// all property within this region
						for (var j = 0; j < region_costs_length; j++) {

							// soort kosten
							var propertyName = region_costs[j],
								regionCost = pocData.regions["Europe"][propertyName];

							// if property exists
							if(regionCost.hasOwnProperty(route)){

								// add to pct pricing object
								if(route === "pct"){

									var year = regionCost.pct.year,
										cost = convert_cost(regionCost.pct.cost, "Europe");

									// add to right object
									add_to_price_obj("pct_costs_obj", cost, name, year, addYears);
								}

								// add to national pricing object
								if(route === "national"){

									var year = regionCost.national.year,
										cost = convert_cost(regionCost.national.cost, "Europe");

									// add to right object
									add_to_price_obj("national_costs_obj", cost, name, year, addYears);
								}
							}

						} // end for loop

						// stop de loop door alle landen
						break;
					} else {
						europe_active = false;
					}



/*
					if(regionName !== "Europe"){
						// if United States, add taxes
						var tax_obj = pocData.regions[regionName].taxes;
						// get_taxes_costs(route, tax_obj, addYears, choosen_regions[i]);
						get_taxes_costs(route, tax_obj, addYears, name);
					}
*/

				} // end for loop per region
			}

			function get_country_costs(route, addYears, name){

				var choosen_countries = userChoices.countries_arr,
					countries_length = choosen_countries.length;

				if(countries_length === 0){
					return '';
				}

				// loop door de countries die geselecteerd zijn
				for (var i = 0; i < countries_length; i++) {

					// country
					var countryName = choosen_countries[i];

					// haal de vars eruit die geen object zijn in de pocData
					// bv. "select_europe"
					if(typeof pocData.countries[countryName] !== 'object' && pocData.countries[countryName] == null) {
						continue;
					}

					var	country_costs = Object.keys(pocData.countries[countryName]),
						country_costs_length = country_costs.length;

					// all property within this country without the taxes
					for (var k = 0; k < country_costs_length; k++) {

						// soort kosten
						var propertyName = country_costs[k],
							countryCost = pocData.countries[countryName][propertyName];

						// exclude flag and taxes
						if(propertyName !== "taxes" || propertyName !== "taxes"){
							// if property exists
							if(countryCost.hasOwnProperty(route)){
								// add to pct pricing object
								if(route === "pct"){

									var year = countryCost.pct.year,
										cost = convert_cost(countryCost.pct.cost, countryName);


									// add to right object
									add_to_price_obj("pct_costs_obj", cost, name, year, addYears);

								}
								// add to national pricing object
								if(route === "national"){

									var year = countryCost.national.year,
										cost = countryCost.national.cost;

									cost = convert_cost(cost, countryName);

									// add to right object
									add_to_price_obj("national_costs_obj", cost, name, year, addYears);
								}
							}
						}

					} // end for loop regular properties

					// taxes
					var tax_obj = pocData.countries[countryName].taxes;
					// get_taxes_costs(route, tax_obj, addYears, choosen_countries[i]);
					get_taxes_costs(route, tax_obj, addYears, name);
				} // end for loop per country

			} // end function


			function get_taxes_costs(route, tax_arr, addYears, name){

				// start in jaar 0
				for (var year = 0; year < tax_arr.length; year++) {

					if(tax_arr[year] !== null){

						var cost = convert_cost(parseInt(tax_arr[year], name));


						if(route === "pct"){
							// console.log(year + " " + tax_arr[year]);
							add_to_price_obj("pct_costs_obj", cost, name, parseInt(year), parseInt(addYears));
						}
						if(route === "national"){
							add_to_price_obj("national_costs_obj", cost, name, parseInt(year), parseInt(addYears));
						}
					}

				}

			}

			function add_to_price_obj(price_obj, cost, name, year, addYears){


					if(addYears === 0){
						// add costs per year to pricing object
						this[price_obj].patent_default[parseInt(year)] += parseInt(cost);
					} else {

						var concatenate = +year + +addYears;


						if (typeof this[price_obj][name] !== "undefined") {

							// add costs per year to pricing object
							// haal hier 1 af omdat er geteld wordt vanaf 0. Door er 1 af te halen starten de kosten
							// ook in het jaar waarin ze worden aangegeven
							this[price_obj][name][parseInt(year) + parseInt(addYears) -1 ] += parseInt(cost);

						}

					}

			}



			function extendPricingObject(years, name){
				// count 20 years from the start point
				for (h = 0 + years; h < 21 + years; h++) {

					// check of dit jaar als bestaat in de dataProvider
					// vul de provider aan met extra jaren
					if (typeof chart.dataProvider[h - 1] === "undefined") {
						// add extra data to charts
						chart.dataProvider.push( {
							"year": h,
							"route_pct_patent_default": 0,
							"route_national_patent_default": 0
						});

						cum_chart.dataProvider.push( {
							"year": h,
							"route_pct_patent_default": 0,
							"route_national_patent_default": 0
						});
					}

					// fill pct object
					// add years to dataProvider as well
					if(userChoices.routes.pct === 1){

						// fill obj
						pct_costs_obj[name][h] = 0;

						// fill dataProvider
						chart.dataProvider[h -1]["route_pct_" + name] = 0;
						cum_chart.dataProvider[h -1]["route_pct_" + name] = 0;

					}

					// fill national pricing object
					// add years to dataProvider as well
					if(userChoices.routes.national === 1){

						// fill obj
						national_costs_obj[name][h] = 0;

						// fill dataProvider
						chart.dataProvider[h-1]["route_national_" + name] = 0;
						cum_chart.dataProvider[h-1]["route_national_" + name] = 0;
					}
				}

			}


			function insert_extra_patents(){
				var add_patent_form = $("#add_patent_form"),
					formData = [];

				formData = add_patent_form.serializeArray();
				add_patents(formData);
			}

			function add_patents(formData){

				// standaard staan ze op dicht
				$("#add_patent_form fieldset .edit").hide();

				if(formData === ''){

					// doe niks
					return '';
				}

				// check of alles op 0 staat
				// zo ja, stop deze functie
				var value = 0;
				for(var i = 0; i < formData.length; i++){
					var fieldValue = parseInt(formData[i].value);
					if(fieldValue > value){
						value = fieldValue;
					}
				}

				// delete
				if(value === 0){

					// restore patents to default patent
					userChoices.patents = {default_patent: 0};
					count_patents_message("1");

					// delete alle jaren na de highest
					chart.dataProvider.splice(20, chart.dataProvider.length);
					cum_chart.dataProvider.splice(20, cum_chart.dataProvider.length);

					// delete all active patents
					$("#add_patent_form fieldset.active").removeClass('active');

					// als er geen warning is
					if(activeWarning === false){
						// alles is 0, kijk of er extra patenten zijn toegevoegd, zo ja, delete ze en redraw
						for(var chartYears in chart.dataProvider){
							Object.keys(chart.dataProvider[chartYears]).forEach(function(property) {
							if(property !== "route_pct_patent_default" && property !== "route_national_patent_default" && property !== "year"){
								delete chart.dataProvider[chartYears][property];
								delete cum_chart.dataProvider[chartYears][property];
							  }
							});
						}

						for(var props in pct_costs_obj){
							if(props !== "patent_default"){
								delete pct_costs_obj[props];
							}
						}

						for(var props in national_costs_obj){
							if(props !== "patent_default"){
								delete national_costs_obj[props];
							}
						}
						redraw();
					}

					return '';
				}


				// is het hoger dan 0, ga door
				var highest = 0,
					aantal_patents = 1;

				// loop door de input velden
				for(var i = 0; i < formData.length; i++){

					// zet de jaren om in een int
					var years = parseInt(formData[i].value),
						name = formData[i].name;

					// add patent to userChoices obj
					if(years > 0){
						userChoices.patents[name] = years;
					}

					// find highest number
					if(years >= highest){
						highest = years;
					}

					// als het object wel bestaat, delete het
					// hierna worden patenten die ingevuld zijn (> 0) opnieuw gevuld
					if(userChoices.routes.pct === 1){
						if (typeof pct_costs_obj[name] != "undefined") {
							delete pct_costs_obj[name];
						}
					}

					if(userChoices.routes.national === 1){
						if (typeof national_costs_obj[name] != "undefined") {
							delete national_costs_obj[name];
						}
					}

					// als de jaren op 0 staan, doe er niks mee
					// bereken ook geen getallen
					if(years === 0){

						var clickedFieldFieldgroup = $("input[type='number'][name='"+name+"']").parent();

						if(clickedFieldFieldgroup.hasClass('active')){
							clickedFieldFieldgroup.removeClass('active');
						}

						// delete deze property uit de data.provider
						for(var chartYear in chart.dataProvider){
							if (typeof chart.dataProvider[chartYear]["route_pct_" + name] !== "undefined") {
								delete chart.dataProvider[chartYear]["route_pct_" + name];
								delete cum_chart.dataProvider[chartYear]["route_pct_" + name];
							}
							if (typeof chart.dataProvider[chartYear]["route_national_" + name] !== "undefined") {
								delete chart.dataProvider[chartYear]["route_national_" + name];
								delete cum_chart.dataProvider[chartYear]["route_national_" + name];
							}
						}

						continue;
					} else {
						$("input[type='number'][name='"+name+"']").parent().addClass("active").show();
					}

					aantal_patents++;

					// make new pricing object
					// add object to dataProvider
					if(userChoices.routes.pct === 1){
						pct_costs_obj[name] = {};
					}

					if(userChoices.routes.national === 1){
						national_costs_obj[name] = {};
					}



					// pas het pricing object aan. Maak voor elk patent een apart object en rek het aantal jaren op
					// als dat gedaan is, bereken dan de kosten
					$.when(extendPricingObject(years, name)).done(function() {
						if(userChoices.routes.national === 1){
							get_priority_costs("national", years, name);
							get_region_costs("national", years, name);
							get_country_costs("national", years, name);
						}

						if(userChoices.routes.pct === 1){
							get_priority_costs("pct", years, name);
							get_region_costs("pct", years, name);
							get_country_costs("pct", years, name);
						}
					});

				} // einde loop door alle velden

				// change message
				count_patents_message(aantal_patents);

				// als de hoogste groter is dan 1
				if(highest > 0){

					// delete alle jaren na de highest
					chart.dataProvider.splice(20 + parseInt(highest), chart.dataProvider.length);
					cum_chart.dataProvider.splice(20 + parseInt(highest), cum_chart.dataProvider.length);

				} else {
					// als alle velden op 0 staan, zet de charts weer op 20
					// delete extra years from pricing object too
					var pct_obj_length = Object.keys(pct_costs_obj).length + 1;
					for(var y = 21; y < pct_obj_length; y++){
						delete pct_costs_obj[y];
					}

					var national_obj_length = Object.keys(national_costs_obj).length + 1;
					for(var y = 20; y < national_obj_length; y++){
						delete national_costs_obj[y];
					}

					// set chart data back to 20
					chart.dataProvider.splice(20, chart.dataProvider.length);
					cum_chart.dataProvider.splice(20, cum_chart.dataProvider.length);
				}

				// console.log(userChoices);

				// fill the pricing object with data and add years
				// redraw();

			}

			/* -------------------------------------
				Default chart data
			------------------------------------- */
			function create_chart(chartObject){
				chart = AmCharts.makeChart("chartdiv", chartObject);
			}

			function create_cum_chart(chartObject){
				cum_chart = AmCharts.makeChart("cum_chartdiv", chartObject);
			}

		/* -------------------------------------
			MESSAGE FUNCTIONS
		------------------------------------- */
		function count_patents_message(aantal_patents){
			if(aantal_patents > 1){
				$('.active_patents p').html('You currently have <strong>'+aantal_patents+'</strong> patents in the calculator')
			} else {
				$('.active_patents p').html('You currently have <strong>'+aantal_patents+'</strong> patent in the calculator')
			}

		}

		/* -------------------------------------
			WARNING FUNCTIONS
		------------------------------------- */
			/* -------------------------------------
				Add a warning to the app
			------------------------------------- */
			function check_for_warnings(){

				disableWarning();

				$.when(countries_warning()).done(function() {
					if(activeWarning === false){
						route_warning();
					}
				})
			}

			/* -------------------------------------
				Add a warning to the routes
			------------------------------------- */
			function route_warning(){

				if(parseInt(userChoices.routes.pct) === 0 && parseInt(userChoices.routes.national) === 0){
				  warning("Please select a route");
					$("html, body").animate({ scrollTop: 0 }, 1000);
					activeWarning = true;
					// $(".europeContainer").slideUp();
				} else {
					activeWarning = false;
				}
		  	}

			/* -------------------------------------
				Add a warning to the countries
			------------------------------------- */
			function countries_warning(){
				if(userChoices.countries_arr.length == 0 ){
				  warning("Please select a country");
				  $("html, body").animate({ scrollTop: 0 }, 1000);
				  activeWarning = true;
				  $(".europeContainer").slideUp();
				} else {
					activeWarning = false;
				}
			}

			/* -------------------------------------
				warning content
			------------------------------------- */
			function warning(text){
				var warningDiv = $('#warning');

				warningDiv.html('');
				$('.warningDesc').remove();

				warningDiv.append("<p>" + text + "</p>");

				warningDiv.slideDown();

				$("<div class='warningDesc blauw'><p class='center'>Scroll down in the left menu and select one or more countries and the route(s) for your patent. Click the ? for more information.</p></div>").insertAfter(warningDiv).hide().fadeIn();

				activeWarning = true;
			}

			/* -------------------------------------
				disable warnings
			------------------------------------- */
			function disableWarning() {
				var warningDiv = $('#warning');
				warningDiv.slideUp();
				$("div.warningDesc").hide();
				warningDiv.html('');
			}

		/* -------------------------------------
			SHOW FUNCTIONS
		------------------------------------- */
			function show_elements(){
				$('.add_portfolio').fadeIn();
				$('.active_patents').parent().fadeIn();
				$('div.recalculate').fadeIn();
				$('div.disclaimer').fadeIn();
				$("#costs_per_year").parents('div.twoFourth').fadeIn();
				$("#cumulative").parents('div.twoFourth').fadeIn();
				$("#settings_changed").closest('div.oneThird').fadeIn();

				if(europe_active){
					$('div.europe').fadeIn();
				} else {
					$('div.europe').fadeOut();
				}

				if(settings_changed == false){
					$('div.export').html('<a class="exportBtn" alt="Export"><img src="img/download.svg" width="20" height="20" style="margin-right: 1em; position: relative; top: 3px; "/>Generate PDF</a>').fadeIn();
				}

				pressExportButton();

				// $("html, body").animate({ scrollTop: 0 }, 1000);
			}

		/* -------------------------------------
			DELETE FUNCTIONS
		------------------------------------- */
			function hide_elements(){
				// zero charts
				zero_chart(chart);
				zero_chart(cum_chart);

				$("#costs_per_year").parents('div.twoFourth').fadeOut();
				$("#cumulative").parents('div.twoFourth').fadeOut();
				$("#settings_changed").closest('div.oneThird').fadeOut();

				// hide totals
				$('.total_pct, .total_national').fadeOut();

				// hide add patent form
				$('.add_portfolio').hide();

				// hide calculate again button
				$('div.recalculate').fadeOut();

				// hide export button
				$('div.export').fadeOut();

				// active patents
				$('.active_patents').parent().hide();

				// active patents
				$('.disclaimer').hide();

			}

			function disableData() {

				// disable all charts
				// hide totals
				hide_elements();

				zero_chart(chart);
				zero_chart(cum_chart);

				// delete localstorage
				localStorage.userChoices = '';

				// all checked inputs to false
				$("input").prop('checked', false);

				for(var year in pct_costs_obj){
					pct_costs_obj[year] = 0;
				}

				for(var year in national_costs_obj){
					national_costs_obj[year] = 0;
				}

				// delete route options
				userChoices.routes.pct = 0;
				userChoices.routes.national = 0;

				// delete all user array items from regions and countries
				userChoices.regions_arr.splice(0, userChoices.regions_arr.length);
				userChoices.countries_arr.splice(0, userChoices.countries_arr.length);

				warning("Please select a country");

			}

		/* -------------------------------------
			HELPER FUNCTIONS
		------------------------------------- */

			/* -------------------------------------
				Change to euro numbers
			------------------------------------- */
			function euroNumber(number, noSign){
				// euro number
				// var number = Number(number).toLocaleString("es-ES", {minimumFractionDigits: 0});
				// var number = Number(number).toLocaleString("es-ES", {minimumFractionDigits: 0});

				// number but written in US format
				var number = Number(number).toLocaleString("en-IN", {minimumFractionDigits: 0});

				if(noSign === true){
					return number;
				}else{
					return "&#8364;" + number;
				}

			}

			/* -------------------------------------
				Convert currencies, based on the openexchange API
			------------------------------------- */
			function convert_cost(cost, name){

//				if(name === "United_States"){
//
//					return Math.round(money(cost).from("USD").to("EUR"));
//				}
//
//				if(name === "GB"){
//					return Math.round(money(cost).from("GBP").to("EUR"));
//				}
//
//				if(name === "DK"){
//					return Math.round(money(cost).from("DKK").to("EUR"));
//				}
//
//				if(name === "SE"){
//					return Math.round(money(cost).from("SEK").to("EUR"));
//				}
//
//				if(name === "CH"){
//					return Math.round(money(cost).from("CHF").to("EUR"));
//				}
//
//				if(name === "IL"){
//					return Math.round(money(cost).from("ILS").to("EUR"));
//				}
//
//				if(name === "RU"){
//					return Math.round(money(cost).from("RUB").to("EUR"));
//				}
//
//				if(name === "CA"){
//					return Math.round(money(cost).from("CAD").to("EUR"));
//				}
//
//				if(name === "BR"){
//					return Math.round(money(cost).from("BRL").to("EUR"));
//				}
//
//				if(name === "AU"){
//					return Math.round(money(cost).from("AUD").to("EUR"));
//				}
//
//				if(name === "CN"){
//					return Math.round(money(cost).from("CYN").to("EUR"));
//				}
//
//				if(name === "IN"){
//					return Math.round(money(cost).from("INR").to("EUR"));
//				}
//
//				if(name === "JP"){
//					return Math.round(money(cost).from("JPY").to("EUR"));
//				}
//
//				if(name === "KR"){
//					return Math.round(money(cost).from("KRW").to("EUR"));
//				}

				return cost;

			}

			/* -------------------------------------
				Check if value exists in object
			------------------------------------- */
			function inObject(haystack, needle){
				for (k in haystack) {
					if (haystack[k] == needle)
						return true;
				}
			}

			/* -------------------------------------
				Check if value exists in array
			------------------------------------- */
			function inArray(needle,haystack) {
				var count=haystack.length;
				for(var i=0;i<count;i++)
				{
					if(haystack[i]===needle){return true;}
				}
				return false;
			}

			/* -------------------------------------
				Function which extracts currently selected country list.
				Returns array consisting of country ISO2 codes
			------------------------------------- */
//			function getSelectedCountries() {
//			  var selected = [];
//			  for(var i = 0; i < map.dataProvider.areas.length; i++) {
//				if(map.dataProvider.areas[i].showAsSelected)
//				  selected.push(map.dataProvider.areas[i].id);
//			  }
//			  return selected;
//			}

			/* -------------------------------------
			Function which extracts the year string from the hidden input field. It creates a string 	from the input and converts it to an object.
			------------------------------------- */
			function create_years_obj(selector){
				yearsFromField = document.getElementById(selector).value;
				yearsFromField = yearsFromField.split(",");

				years = {};
				for (var i = 0; i < yearsFromField.length; i++) {
					var number = i;
					years[number] = yearsFromField[i];
				}

				return years;
			}

		</script>

		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=UA-111434783-1"></script>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag('js', new Date());

		  gtag('config', 'UA-111434783-1');
		</script>

	</body>
</html>
