<?php

/* Activation & Uninstall */

register_activation_hook(ASRV_BASE_DIRECTORY."/appstore-review.php", 'asrv_activate');
register_uninstall_hook(ASRV_BASE_DIRECTORY."/appstore-review.php", 'asrv_uninstall');

function asrv_activate() {

	//Default params
	$options = array(
		"cache" 	 => 48,
		"defaultCountry" => "us",
		"defaultStars" 	 => 5,
		"defaultRecent"	 => 10
	);
	add_option("asrv_options", $options);
	
	//Cache directory
	mkdir(ASRV_CACHE_DIR, 0777);
}

function asrv_uninstall() {
	delete_option("asrv_options");
}

/* Menu page */

//Shortcut to the Admin page
add_filter('plugin_action_links', 'asrv_plugin_action_links', 10, 2);
	
function asrv_plugin_action_links($links, $plugin_file) {
	static $plugin;

	if (!isset($plugin)) {
		$plugin = "appstore-reviews-viewer/appstore-review.php";
	}

	if ($plugin == $plugin_file) {
		$settings_link = '<a href="options-general.php?page=appstore-reviews-viewer/appstore-review-admin.php">Settings</a>';
        	array_unshift($links, $settings_link);
        }

	return $links;
}
	
//Menu
add_action('admin_init', 'asrv_register_settings_and_fields');
add_action('admin_menu', 'asrv_admin_page');

function asrv_admin_page() {
	add_options_page('AppStore Reviews Viewer - Options', 'AppStore Reviews Viewer', 'manage_options', __FILE__, 'asrv_render_admin_form');
}

function asrv_register_settings_and_fields() {
	register_setting('asrv_options','asrv_options');
	add_settings_section('asrv_plugin_main_section', 'Main Settings', 'asrv_plugin_cb', __FILE__);
	add_settings_field('cache', 'Cache Time: ', 'asrv_form_option_cache', __FILE__, 'asrv_plugin_main_section');
	add_settings_field('defaultCountry', 'AppStore Country: ', 'asrv_form_option_country', __FILE__, 'asrv_plugin_main_section');
	add_settings_field('defaultStars', 'Retrieve reviews with at least: ', 'asrv_form_option_stars', __FILE__, 'asrv_plugin_main_section');
	add_settings_field('defaultRecent', 'Show only last X reviews: ', 'asrv_form_option_recent', __FILE__, 'asrv_plugin_main_section');
}

function asrv_plugin_cb() {
	echo "Those are the default parameters for all the shortcodes you use on your site. You can override those parameters for any shortcode (except the cache).";
}

function asrv_form_option_cache() {
	$options = get_option("asrv_options");
	
	$caches = array(
		"0"   => "Don't cache",
		"1"   => "1 hour",
		"6"   => "6 hours",
		"12"  => "12 hours",
		"24"  => "1 day",
		"48"  => "2 days",
		"168" => "1 week"
	);
	
	echo "<select name='asrv_options[cache]'>";
	foreach ($caches as $k => $v) {
		echo "<option value='" . $k . "'" . selected($k, $options['cache'], false) . ">" . $v . "</option>";
	}
	echo "</select>";
	echo "<span style='color:grey;margin-left:2px;'>This option determines how long before the plugin requests new data from Apple's servers.</span>";
}

function asrv_form_option_country() {
	$options = get_option("asrv_options");
	
	$countries = array(
		"AL" => "Albania",
		"DZ" => "Algeria",
		"AO" => "Angola",
		"AI" => "Anguilla",
		"AG" => "Antigua and Barbuda",
		"AR" => "Argentina",
		"AM" => "Armenia",
		"AU" => "Australia",
		"AT" => "Austria",
		"AZ" => "Azerbaijan",
		"BS" => "Bahamas",
		"BH" => "Bahrain",
		"BB" => "Barbados",
		"BY" => "Belarus",
		"BE" => "Belgium",
		"BZ" => "Belize",
		"BJ" => "Benin",
		"BM" => "Bermuda",
		"BT" => "Bhutan",
		"BO" => "Bolivia",
		"BW" => "Botswana",
		"BR" => "Brazil",
		"BN" => "Brunei Darussalam",
		"BG" => "Bulgaria",
		"BF" => "Burkina Faso",
		"KH" => "Cambodia",
		"CA" => "Canada",
		"CV" => "Cape Verde",
		"KY" => "Cayman Islands",
		"TD" => "Chad",
		"CL" => "Chile",
		"CN" => "China",
		"CO" => "Colombia",
		"CG" => "Congo, Republic of the",
		"CR" => "Costa Rica",
		"HR" => "Croatia",
		"CY" => "Cyprus",
		"CZ" => "Czech Republic",
		"DK" => "Denmark",
		"DM" => "Dominica",
		"DO" => "Dominican Republic",
		"EC" => "Ecuador",
		"EG" => "Egypt",
		"SV" => "El Salvador",
		"EE" => "Estonia",
		"FJ" => "Fiji",
		"FI" => "Finland",
		"FR" => "France",
		"GM" => "Gambia",
		"DE" => "Germany",
		"GH" => "Ghana",
		"GR" => "Greece",
		"GD" => "Grenada",
		"GT" => "Guatemala",
		"GW" => "Guinea-Bissau",
		"GY" => "Guyana",
		"HN" => "Honduras",
		"HK" => "Hong Kong",
		"HU" => "Hungary",
		"IS" => "Iceland",
		"IN" => "India",
		"ID" => "Indonesia",
		"IE" => "Ireland",
		"IL" => "Israel",
		"IT" => "Italy",
		"JM" => "Jamaica",
		"JP" => "Japan",
		"JO" => "Jordan",
		"KZ" => "Kazakhstan",
		"KE" => "Kenya",
		"KR" => "Korea, Republic Of",
		"KW" => "Kuwait",
		"KG" => "Kyrgyzstan",
		"LA" => "Lao, People's Democratic Republic",
		"LV" => "Latvia",
		"LB" => "Lebanon",
		"LR" => "Liberia",
		"LT" => "Lithuania",
		"LU" => "Luxembourg",
		"MO" => "Macau",
		"MK" => "Macedonia",
		"MG" => "Madagascar",
		"MW" => "Malawi",
		"MY" => "Malaysia",
		"ML" => "Mali",
		"MT" => "Malta",
		"MR" => "Mauritania",
		"MU" => "Mauritius",
		"MX" => "Mexico",
		"FM" => "Micronesia, Federated States of",
		"MD" => "Moldova",
		"MN" => "Mongolia",
		"MS" => "Montserrat",
		"MZ" => "Mozambique",
		"NA" => "Namibia",
		"NP" => "Nepal",
		"NL" => "Netherlands",
		"NZ" => "New Zealand",
		"NI" => "Nicaragua",
		"NE" => "Niger",
		"NG" => "Nigeria",
		"NO" => "Norway",
		"OM" => "Oman",
		"PK" => "Pakistan",
		"PW" => "Palau",
		"PA" => "Panama",
		"PG" => "Papua New Guinea",
		"PY" => "Paraguay",
		"PE" => "Peru",
		"PH" => "Philippines",
		"PL" => "Poland",
		"PT" => "Portugal",
		"QA" => "Qatar",
		"RO" => "Romania",
		"RU" => "Russia",
		"ST" => "São Tomé and Príncipe",
		"SA" => "Saudi Arabia",
		"SN" => "Senegal",
		"SC" => "Seychelles",
		"SL" => "Sierra Leone",
		"SG" => "Singapore",
		"SK" => "Slovakia",
		"SI" => "Slovenia",
		"SB" => "Solomon Islands",
		"ZA" => "South Africa",
		"ES" => "Spain",
		"LK" => "Sri Lanka",
		"KN" => "St. Kitts and Nevis",
		"LC" => "St. Lucia",
		"VC" => "St. Vincent and The Grenadines",
		"SR" => "Suriname",
		"SZ" => "Swaziland",
		"SE" => "Sweden",
		"CH" => "Switzerland",
		"TW" => "Taiwan",
		"TJ" => "Tajikistan",
		"TZ" => "Tanzania",
		"TH" => "Thailand",
		"TT" => "Trinidad and Tobago",
		"TN" => "Tunisia",
		"TR" => "Turkey",
		"TM" => "Turkmenistan",
		"TC" => "Turks and Caicos",
		"UG" => "Uganda",
		"GB" => "United Kingdom",
		"UA" => "Ukraine",
		"AE" => "United Arab Emirates",
		"UY" => "Uruguay",
		"US" => "USA",
		"UZ" => "Uzbekistan",
		"VE" => "Venezuela",
		"VN" => "Vietnam",
		"VG" => "Virgin Islands, British",
		"YE" => "Yemen",
		"ZW" => "Zimbabwe"
	);
	
	echo "<select name='asrv_options[defaultCountry]'>";
	foreach ($countries as $k => $v) {
		echo "<option value='" . strtolower($k) . "'" . selected(strtolower($k), $options['defaultCountry'], false) . ">" . $v . "</option>";
	}
	echo "</select>";
	echo "<span style='color:grey;margin-left:2px;'>This option determines which AppStore country you want to download the reviews from.</span>";

}

function asrv_form_option_stars() {
	$options = get_option("asrv_options");
	
	$stars = array(
		"1" => "1 star",
		"2" => "2 stars",
		"3" => "3 stars",
		"4" => "4 stars",
		"5" => "5 stars"
	);
	
	echo "<select name='asrv_options[defaultStars]'>";
	foreach ($stars as $k => $v) {
		echo "<option value='" . $k . "'" . selected($k, $options['defaultStars'], false) . ">" . $v . "</option>";
	}
	echo "</select>";
}

function asrv_form_option_recent() {
	$options = get_option("asrv_options");
	
	$recent = array(5, 10, 15, 20, 25);
	
	echo "<select name='asrv_options[defaultRecent]'>";
	foreach ($recent as $k) {
		echo "<option value='" . $k . "'" . selected($k, $options['defaultRecent'], false) . ">" . $k . "</option>";
	}
	echo "</select>";
}

function asrv_render_admin_form() {
?>
	
	<div style="float:right">
		<a href="https://flattr.com/submit/auto?user_id=gilthonwe&url=https%3A%2F%2Fprofiles.wordpress.org%2Fgilthonwe" target="_blank"><img src="//button.flattr.com/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0"></a>
	</div>
	
	<h3>Welcome to the AppStore Reviews Viewer - Settings Page</h3>
	<p>Here, you will be able to specify some settings for the plugin.</p>
	
	<br/>
	<h3>Usage of the shortcode</h3>
	<p>Minimal configuration</p>
	<p><code>[ios_app_review id="12345678"]</code> where <b>12345678</b> is the ID of your app on the AppStore.</p>
	<p>Full configuration</p>
	<p><code>[ios_app_review id="12345678" country="us" minstar="4" recent="10"]</code>
		<ul>
			<li><b>id</b>:  the ID of your app on the AppStore</li>
			<li><b>country</b>: the <a href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2" target="blank">country code</a> of the Appstore</li>
			<li><b>minstar</b>: the minimum number of stars a review must have to be displayed (1-5)</li>
			<li><b>recent</b>: the number of reviews to display (max 25)</li>
		</ul>
	</p>
	
	<br/>
	<form method="post" action="options.php">
		<?php
			settings_fields('asrv_options');
			do_settings_sections(__FILE__);
		?>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
	
<?php
}