<?php
$countries = array(
	"CA" => "Canada",
	"US" => "United States",
	"BR" => "Brasil",
	0 => "Europe divider",
	"AT" => "Austria",
	"BE" => "Belgium",
	"DK" => "Denmark",
	"FR" => "France",
	"DE" =>	"Germany",
	"IR" => "Ireland",
	"IT" => "Italy",
	"NL" => "Netherlands",
	"ES" => "Spain",
	"SE" => "Sweden",
	"CH" => "Switzerland",
	"GB" =>	"United Kingdom",
	1 => "Israel",
	"IL" => "Israel",
	2 => "Russia",
	"RU" => "Russia",
	3 => "Other",
	"AU" => "Australia",
	"CN" => "China",
	"IN" => "India",
	"JP" => "Japan",
	"KR" => "South Korea"
);

$regions = array(
	"Europe" 				=> "Europe",
	// "Australia"				=> "Australia",
	// "United_States"		 	=> "United States"
);

$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
if (strpos($actual_link, 'localhost') !== false) {
	$tool_url = "http://localhost/pc2";
} else {
	$tool_url = "https://pauljanssenfuturelab.eu/tools/intellectual_property/online/pc2";
}


$tool_folder = "pc2";
?>
