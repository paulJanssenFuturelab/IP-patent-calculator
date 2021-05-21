<?php
// include options
include("include/options.php");

// filename for pdf and chart image
$path = $tool_url . '/tmp/';
$randomNumer = rand(0, 10000);
$filename = "PJFL_Patent Calculator Portfolio_" . $randomNumer . ".pdf";
$full_path = $path . '/' . $filename;


$tempPocData = html_entity_decode(stripslashes( $_POST["pocData"]));
$pocData = json_decode($tempPocData);

$tempuserChoices = html_entity_decode(stripslashes( $_POST["userChoices"]));
$userChoices = json_decode($tempuserChoices);

$europeanCountriesArrTemp = html_entity_decode(stripslashes( $_POST["europeanCountriesArr"]));
$europeanCountriesArr = json_decode($europeanCountriesArrTemp);

function base64_to_jpeg($base64_string, $output_file) {
    // open the output file for writing
    $ifp = fopen( $output_file, 'wb' );

    // split the string on commas
    // $data[ 0 ] == "data:image/png;base64"
    // $data[ 1 ] == <actual base64 string>
    $data = explode( ',', $base64_string );

    // we could add validation here with ensuring count( $data ) > 1
    fwrite( $ifp, base64_decode( $data[ 1 ] ) );

    // clean up the file resource
    fclose( $ifp );

    return $output_file;
}

$chartImages = json_decode(stripslashes($_POST["chartImages"]));

// $chartImageBase64 = stripslashes($_POST["chartImg"]);
foreach($chartImages as $key => $img){

	if($key === 0){
		$chartImage = base64_to_jpeg($img->image, $_SERVER['DOCUMENT_ROOT'] . '/tools/intellectual_property/online/'.$tool_folder.'/tmp/' . $randomNumer . "_Chartpoc.jpg");
	} else {
		$cumChartImage = base64_to_jpeg($img->image, $_SERVER['DOCUMENT_ROOT'] . '/tools/intellectual_property/online/'.$tool_folder.'/tmp/' . $randomNumer . "_CumChartpoc.jpg");
	}

}

include('tcpdf/config/tcpdf_config.php');
include('tcpdf/tcpdf.php');

// Pretty property name function
function prettyPropertName($propertyName) {

    // replace _
	$propertyName = str_replace('_', ' ', $propertyName);

	// delete any numbers
	$propertyName = str_replace('2', '', $propertyName);
	$propertyName = str_replace('3', '', $propertyName);

	// First letter uppercase
	$propertyName = ucfirst($propertyName);

	return $propertyName;
}


// Pretty property name function
function prettyPatentName($patentName) {

  if($patentName == 'default_patent'){
    $patentName = "Patent 1";
  }
  // replace _
	$patentName = str_replace('_', ' ', $patentName);

	// First letter uppercase
	$patentName = ucfirst($patentName);

	return $patentName;
}

function convertCountry($countryCode){

	include("include/options.php");

	return $countries[$countryCode];

}

	// retun data set
	function dataSet($userChoices, $pocData, $europeanCountriesArr, $route){

		$data = array();

		// patenten
		foreach($userChoices->patents as $patentName => $startYear){

			$data[$patentName] = array();

			// alleen voor het defalt patent niet 1 eraf halen om arrays zero based zijn
			if($patentName !== "default_patent"){
				$startYear = $startYear -1;
			}

			// priority
			foreach($pocData->costs_priority_year as $priorityCostKey => $priority_cost){

				$year = $pocData->costs_priority_year->{$priorityCostKey}->{$route}->year;
				$cost = $pocData->costs_priority_year->{$priorityCostKey}->{$route}->cost;

				$data[$patentName][$startYear + $year][$priorityCostKey] = $cost;
			} // end priority

			$europeanCosts = false;
			// countries
			foreach($userChoices->countries_arr as $country){

				// als een land in europa zit, knal deze kosten er eenmalig bij
				if(in_array($country, $europeanCountriesArr) && $europeanCosts == false){

					// alle region props
					$regionProperties = $pocData->regions->{"Europe"};

					// voor elke region kijk je naar alle properties die er zijn
					foreach($regionProperties as $propertyName => $value){

						$year = $regionProperties->{$propertyName}->{$route}->year;
						$cost = $regionProperties->{$propertyName}->{$route}->cost;

						if($regionProperties->{$propertyName}->{$route}->cost > 0){
							// Knal alle properties in de data array
							$data[$patentName][$startYear + $year][$propertyName] = $cost;
						}

					}

					$europeanCosts = true;
				}// end regionaal


				// pak al deze countries weer uit de data set
				$countryProperties = $pocData->countries->{$country};

				// voor elke country kijk je naar alle properties die er zijn
				foreach($countryProperties as $propertyName => $value){

					$year = $countryProperties->{$propertyName}->{$route}->year;
					$cost = $countryProperties->{$propertyName}->{$route}->cost;

					// default costs
					if($countryProperties->{$propertyName}->{$route}->cost > 0){
						if($propertyName !== "flag"){

							// als deze propertyname al bestaat, tel erbij op

							if(array_key_exists ($propertyName, $data[$patentName][$startYear + $year])){
								$data[$patentName][$startYear + $year][$propertyName] += $cost;
							}
							// zo niet, maak een nieuwe array aan
							else {
								$data[$patentName][$startYear + $year][$propertyName] = $cost;
							}
						}
					}

					// taxes
					if($propertyName == "taxes"){

						$taxObj = $countryProperties->{"taxes"};

						foreach($taxObj as $taxyear => $taxValue):

							if($taxValue !== null){
								if(array_key_exists ("Renewal_fee " . $taxYear, $data[$patentName][$startYear + $taxyear])):
									$data[$patentName][$startYear + $taxyear]["Renewal_fee " . $taxYear] += $taxValue;
								else:
									$data[$patentName][$startYear + $taxyear]["Renewal_fee " . $taxYear] = $taxValue;
								endif;
							}
						endforeach;
					}

				} // end countryproperties

			} // end country

			ksort($data[$patentName]);

		} // end each patent

		return $data;
	} // einde functie dataSet


// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    //Page header
    public function Header() {
		$this->SetY(15);
		$this->SetTextColor(0,120,184);
		$this->setCellPaddings( "1", "1", "1", "1");

        // Set font
        $this->SetFont('helvetica', '', 12);
        // Title
        $this->Cell(0, 15, 'Paul Janssen Futurelab Patent Calculator', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

    // Grote title
    public function bigTitle($titleText) {
		// set line style voor kop soort route
		$this->SetLineStyle(array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(20, 136, 202)));
		$this->SetFillColor(0,120,184);
		$this->SetTextColor(255,255,255);
		$this->setCellPaddings( "1", "1", "1", "1");
		$this->SetFont('', 'B', 20);

		// maak de cell
		$this->Cell( 0, 0, $titleText, 1, 1, 'C', 1, 0, 0, false, 'T', 'M' );


		// herstyle alles
		$this->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 0)));
		$this->SetFillColor(255,255,128);
        $this->SetTextColor(0);

		// $this->Ln();

    }

	// Colored table
    public function ColoredTable($header, $data, $route) {
        // Colors, line width and bold font
        $this->SetFillColor(237, 236, 235);
    	  $this->SetTextColor(0,120,184);
        $this->SetDrawColor(255, 255, 255);
        $this->SetLineWidth(0);
        $this->SetFont('', 'B', 10);
		    $this->setCellPaddings( "1", "1", "1", "1");

        // Header
        $w = array(20, 80, 80);
        $num_headers = count($header);
        for($i = 0; $i < $num_headers; ++$i) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
        }
        $this->Ln();
        // Color and font restoration
		if($route === "pct"){
			$this->SetFillColor(173, 220, 220);
		} else {
			$this->SetFillColor(229, 164, 166);
		}

    $this->SetTextColor(0);
    $this->SetFont('');
    // Data
    $fill = 0;
    $amount = 0;

		foreach($data as $patent){

			$tabelRijen = count($patent);
			$i = 1;
			foreach($patent as $year => $props){

				$officeActionCosts = 0;
				ksort($patent);

				foreach($props as $propName => $propCost){
					if($year !== 0 && !empty($propName)){

						// als het een office action is, sla deze loop over maar tel wel op voor dit jaar
						if (strpos($propName, 'office_action') !== false) {
							$officeActionCosts += $propCost;
							continue;
						}

						$this->Cell($w[0], 6, $year, 'LR', 0, 'L', $fill);
						$this->Cell($w[1], 6, prettyPropertName($propName), 'LR', 0, 'L', $fill);
						$this->Cell($w[1], 6, chr(128) . number_format($propCost) . ",-", 'LR', 0, 'L', $fill);

						$this->Ln();
						$fill=!$fill;
					}
				}
				// als er office action kosten zijn, toon ze hier
				if($officeActionCosts > 0){
					$this->Cell($w[0], 6, $year, 'LR', 0, 'L', $fill);
					$this->Cell($w[1], 6, "Office Action", 'LR', 0, 'L', $fill);
					$this->Cell($w[1], 6, chr(128) . number_format($officeActionCosts) . ",-", 'LR', 0, 'L', $fill);

					$this->Ln();
					$fill=!$fill;
				}

			$i++;
			}
		}

       //  $this->writeHTML("<br />\n", true, false, true, false, '');
    } // end colored table

	// Colored table multiple patents
  public function ColoredTableMultiplePatents($header, $data, $route, $userChoices) {
    // Colors, line width and bold font
    $this->SetFillColor(237, 236, 235);
    $this->SetTextColor(0,120,184);
    $this->SetDrawColor(255, 255, 255);
    $this->SetLineWidth(0);
    $this->SetFont('', 'B', 10);
    $this->setCellPaddings( "1", "1", "1", "1");

    // Header
    $w = array(50, 80, 50);
    $num_headers = count($header);
    for($i = 0; $i < $num_headers; ++$i) {
      $this->Cell($w[$i], 7, $header[$i], 1, 0, 'L', 1);
    }
    $this->Ln();
    // Color and font restoration
		if($route === "pct"){
			$this->SetFillColor(173, 220, 220);
		} else {
			$this->SetFillColor(229, 164, 166);
		}

    $this->SetTextColor(0);
    $this->SetFont('');
    // Data
    $fill = 0;
    $amount = 0;

		$newData = array();
		foreach($data as $patentName => $patent){

			foreach($patent as $year => $props){

				ksort($patent);
				$kosten = 0;

        $newData[$year]['cost_items'][$patentName] = array();

				foreach($props as $propName => $propCost){
					if($year !== 0 && !empty($propCost)){

            // tel de kosten per prop bij elkaar op
						$kosten = $kosten + $propCost;

            // zet elke prop in de cost_items array
            $newData[$year]['cost_items'][$patentName][$propName] = $propCost;
					}
				}
				// omdat er meerdere patenten zijn,
				// maak een nieuwe array per jaar met data
				$newData[$year]['kosten'] += $kosten;
			}
		}

		ksort($newData);

		$tabelRijen = count($newData);

		$i=1;
		$prevKosten = 0;

		// loop door de nieuwe array en maak de tabel
		foreach($newData as $year => $year_data){

			if($year !== 0 && $year_data['kosten'] !== 0){

        // elke regel is 6 punten.
        // bereken hoeveel cost items er zijn voor dit jaar en vermenigvuldig dit met de regel hoogte
        $singleLineHeight = 6;
        $lines = 1;

				// jaar
        $this->SetFont('', 'B', 10);
				$this->Cell($w[0] + $w[1] + $w[2], 6, 'Year ' . $year, 'LR', 0, 'L', $fill);
        $this->SetFont('');

        $lines++;

				// maak nieuwe prevkosten voor volgende loop
        $year_costs =  $year_data['kosten'];
				$prevKosten += $year_costs;

				$this->Ln();

        // cost items
        $cost_items_text = '';
        $cost_item_action = '';
        $cost_items_price = '';

        foreach($year_data['cost_items'] as $patentName => $propArray){

          foreach($propArray as $propName => $propCost){
            $cost_items_text  .= prettyPatentName($patentName);
            $cost_item_action .= prettyPropertName($propName);
            $cost_items_price .= chr(128) . number_format($propCost);

            $cost_items_text  .= "<br />";
            $cost_item_action  .= "<br />";
            $cost_items_price .= "<br />";

            $counter ++;
            $lines++;
          }

          if(count($propArray) > 1){
            $cost_items_text  .= '<br />';
            $cost_item_action  .= "<br />";
            $cost_items_price .= "<br />";
          }

          $lines++;

        }

        $cost_item_action .= 'Total';
        $cost_items_price .= number_format($year_costs);

        $this->writeHTMLCell(50, 6, '', '', $cost_items_text, 0, 0, $fill, true, 'left', true);
        $this->writeHTMLCell(80, 6, '', '', $cost_item_action, 0, 0, $fill, true, 'left', true);
        $this->writeHTMLCell(50, 6, '', '', $cost_items_price, 0, 0, $fill, true, 'left', true);

        // vermenigvuldig alle regels * de regelhoogt (6)
        // als de Y coordinaat hoger is dan de pageinahoogte minus de regelhoogte, voeg dan een nieuwe pagina toe
        $blockHeight = $singleLineHeight * $lines;
        $yy = $this->getY();
        if($yy > $this->getPageHeight() - $blockHeight){
          $this->addPage();
        }

        // // extra rege
        // $this->Ln();
        // $lines++;
        //
        // // lege cell
        // $this->SetFont('', 'B', 10);
        // $this->Cell(50, 12, '', 'LR', 0, 'L', $fill);
        //
        // // total text
        // $this->Cell(80, 12, 'Total', 'LR', 0, 'L', $fill);
        //
        // // total
				// $this->Cell(50, 12, chr(128) . number_format($year_costs), 'LR', 0, 'L', $fill);
        // $this->SetFont('');

        // voeg een extra regel toe voor de berekening voor een extra pagina
        $lines++;
        $lines++;

        $this->Ln();

				$fill=!$fill;
			}
		$i++;
		}

    $this->writeHTML("<br />\n", true, false, true, false, '');
    } // end colored table

} // end extend TCPDF

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-8859-1', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Paul Janssen Futurelab');
$pdf->SetTitle('Export data Patent Calculator');
$pdf->SetSubject('Export Patent Calculator');
$pdf->SetKeywords('Patent Calculator', 'Paul Janssen Futurelab', 'Leiden');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('helvetica', '', 10);

// Intro page
$pdf->AddPage('P');

$pdf->SetAutoPageBreak(false, 0);
$pdf->Image($tool_url . "/img/POC-sheet.jpg", 0, 0, 210, 297, '', '', '', false, 150, '', false, false, 0);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// $pdf->Image("https://staging.pauljanssenfuturelab.eu/tools/poq5.5/img/POC-sheet.jpg", 0, 0, 210, 297, 'JPG', '', '', true, 150, '', false, false, 1, false, false, false);


// GRAPH PAGE + uitleg landscape
$pdf->AddPage('P');

// set some text to print
$countPatents = count(get_object_vars($userChoices->patents));

// Image example with resizing
$pdf->SetLineStyle(array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 255, 255)));
$pdf->SetLineWidth( 0 );

// get selected country string
$countryString = '';
$aantal = count($userChoices->countries_arr);
$i = 1;
foreach($userChoices->countries_arr as $country){
	$countryString .= convertCountry($country);
	if($i !== $aantal){
		$countryString .= ', ';
	}
	$i++;
}

$txt = "<style>p {line-height:20px;}</style><p>This Patent Calculator assists you with estimating the costs related to the patent application and granting procedures, specifically in medical Life Sciences. The tables at the end of the document show a breakdown of the costs for each chosen route.
<br />
You have selected the following countries: " . $countryString . "</p>";

$pdf->writeHTML($txt, true, false, true, false, '');


$txt = "<style>p {line-height:20px; text-align:center;}</style><br /><p>Costs per year</p>";
$pdf->writeHTML($txt, true, false, true, false, '');

$chartImage = str_replace('/var/www/vhosts/', 'https://', $chartImage);
$chartImage = str_replace('httpdocs/', '', $chartImage);
$pdf->Image($chartImage, 32, 70, 125, 90, 'JPG', '', '', true, 150, '', false, false, 1, false, false, false);

$pdf->Ln();

$txt = "<style>p {line-height:20px; text-align:center;}</style><br /><p>Cumulative costs</p>";
$pdf->writeHTML($txt, true, false, true, false, '');

$cumChartImage = str_replace('/var/www/vhosts/', 'https://', $cumChartImage);
$cumChartImage = str_replace('httpdocs/', '', $cumChartImage);
$pdf->Image($cumChartImage, 32, 180, 125, 90, 'JPG', '', '', true, 150, '', false, false, 1, false, false, false);

//$pdf->writeHTML($htmlImg, true, false, true, false, '');
//$pdf->writeHTML($chartImage, true, false, true, false, '');

// PCT PAGE
if($userChoices->routes->pct === 1){
	$pdf->AddPage('P');
	$pdf->bigTitle("PCT route in " . count($userChoices->countries_arr) . " countries" );
	$dataPCT = dataSet($userChoices, $pocData, $europeanCountriesArr, 'pct');

	if(count((array)$userChoices->patents) > 1){
		// column titles
		$header = array('Patent', 'Action', 'Costs');
		// maak een tabel aan voor de priority costs voor meerdere patenten
		$pdf->ColoredTableMultiplePatents($header, $dataPCT, "pct", $userChoices);
	} else {
		// column titles
		$header = array('Year', 'Actions', 'Costs');
		// maak een tabel aan voor de priority costs voor 1 patent
		$pdf->ColoredTable($header, $dataPCT, "pct");
	}
}

// National PAGE
if($userChoices->routes->national === 1){
	$pdf->AddPage('P');
	$pdf->bigTitle("National route");
	$dataNational = dataSet($userChoices, $pocData, $europeanCountriesArr, 'national');

	if(count((array)$userChoices->patents) > 1){
		// column titles
		$header = array('Patent', 'Action', 'Costs');
		// maak een tabel aan voor de priority costs voor meerdere patenten
		$pdf->ColoredTableMultiplePatents($header, $dataNational, "national", $userChoices);
	} else {
		// column titles
		$header = array('Year', 'Actions', 'Costs');
		// maak een tabel aan voor de priority costs voor 1 patent
		$pdf->ColoredTable($header, $dataNational, "national");
	}
}


// footer and disclaimer
$pdf->addPage('P');

$txt = "<style>p {line-height:20px;}</style><p>This report shows the data you have selected in the calculator. The estimated patent costs include both general fees and costs of the patent attorney and examiner ('Office Actions'). Additional costs that may arise from large or extensive patent applications, for small business entities, or due to corrective actions or litigation procedures are not included. For educational purposes only. This tool is tested periodically and updated before the start of each new course.</p>";

// print a block of text using Write()
$pdf->writeHTML($txt, true, false, true, false, '');

// Logo
$image_file = 'img/logo.jpg';
$pdf->Image($image_file, 15, 70, 30, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);




// Close and output PDF document
// This method has several options, check the source code documentation for more information.
//$pdf->Output($_SERVER['DOCUMENT_ROOT'] . '/tools/poq5.5.2/tmp/' . $filename, 'F');
$pdf->Output($_SERVER['DOCUMENT_ROOT'] . '/tools/intellectual_property/online/'.$tool_folder.'/tmp/' . $filename, 'F');

//$data = dataSet($userChoices, $pocData, $europeanCountriesArr, 'pct');
//var_dump($data);

// var_dump($chartImage);

// echo the filename back to generate the download link
echo $filename;

// var_dump($dataPCT);

die();
?>
