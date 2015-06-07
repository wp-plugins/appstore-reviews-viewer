<?php
/*
Plugin Name: AppStore Reviews Viewer
Version: 1.0.4
Plugin URI: http://www.gilthonwe.com
Description: Adds a shortcode so that you can display reviews and ratings of any app from the AppStore. Specify your AppId, the country where you want to see the reviews from and the minimum number of stars for the reviews.
Author: Gilthonwe Apps
Author URI: http://www.gilthonwe.com
*/

/*
Copyright 2015 Gilthonwe Apps (email: support@gilthonwe.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Version
define("ASRV_VERSION", "1.0.4");

// URLs
define("ASRV_BASE_DIRECTORY", WP_PLUGIN_DIR."/".basename(dirname(__FILE__)));
define("ASRV_CACHE_DIR", ASRV_BASE_DIRECTORY."/cache/");

// Admin
include_once("appstore-review-admin.php");
register_activation_hook(__FILE__, 'asrv_activate');
register_uninstall_hook(__FILE__, 'asrv_uninstall');

// AppStore URL
define('ASRV_APPSTORE_URL', 'http://itunes.apple.com/{country}/rss/customerreviews/id={id}/json');
//define('ASRV_APPSTORE_ALTERNATE_URL', 'http://itunes.apple.com/rss/customerreviews/page=1/id={id}/sortby=mostrecent/json?cc={country}');

/* Scripts and styles */

add_action('wp_enqueue_scripts', 'asrv_enqueue_sripts_and_styles');

function asrv_enqueue_sripts_and_styles() {
	wp_register_script('appstore-review-js', plugins_url('appstore-review.js', __FILE__));
	wp_enqueue_script('appstore-review-js');
	wp_enqueue_script('jquery');
	wp_register_style('appstore-review-css', plugins_url('appstore-review.css', __FILE__));
	wp_enqueue_style('appstore-review-css');
}

/* Shortcode [ios_app_review] */

add_shortcode('ios_app_review','appstore_review');

function appstore_review($atts) {
	$options = get_option("asrv_options");

	$atts = shortcode_atts(
		array(
			"id" 		=> "",
			"country"	=> $options['defaultCountry'],
			"minstar"	=> $options['defaultStars'],
			"recent"	=> $options['defaultRecent']
		), $atts);
		
	//Don't do anything if the ID is blank
	if ($atts['id'] == "") return;
	
	//Lowercase the country code
	$atts['country'] = strtolower($atts['country']);
	
	//Retrieve data and store it
	$json_data = asrv_fetch_data($atts);

	//Display data
	return asrv_review_output($json_data, $atts['minstar'], $atts['recent']);
}

/* Fetch data methods */

function asrv_fetch_data($atts) {
	if (!file_exists(ASRV_CACHE_DIR)) {
		mkdir(ASRV_CACHE_DIR, 0755);
	}

	//First, check if the data in cache is not too old
	$cacheTime = get_option("asrv_options")["cache"] * 60 * 60;
	$cacheFile = ASRV_CACHE_DIR.$atts['id'].$atts['country'].".appstore";
	if (is_readable($cacheFile) && (time() - $cacheTime < filemtime($cacheFile))) {
		$json_data = json_decode(file_get_contents($cacheFile));
	} 
	
	//Otherwise, we download fresh data and store them
	else {
	
		//Call the URL 4 times as it may not work the first time (API error?)
		$json_data = null;
		for ($i=0;$i<4;$i++) {
			if (function_exists('file_get_contents') && ini_get('allow_url_fopen')) {
				$json_data = asrv_fetch_data_fopen($atts);
			} else if (function_exists('curl_exec')) {
				$json_data = asrv_fetch_data_curl($atts);
			} else {
				wp_die('<h1>You must have either file_get_contents() or curl_exec() enabled on your web server.</h1>');
			}
			
			//Store JSON in its original state. 
			if ($json_data->feed->entry) {
				file_put_contents($cacheFile, json_encode($json_data));
				
				//Don't need to try to download anymore
				break;
			}
		}
		
		//If no data returned from Apple (error?), we just update the modification time of the file and load the data from the cache.
		if ($json_data == null || $json_data->feed->entry == null) {
			if (is_readable($cacheFile)) {
				touch($cacheFile);
			}
		}
	}
	
	return $json_data->feed;
}

function asrv_make_store_url($atts) {
	$url = str_replace(array("{country}", "{id}"), array($atts['country'], $atts['id']), ASRV_APPSTORE_URL);
	$url .= "?p" . rand() . "=" . rand();
	return $url;
}

function asrv_fetch_data_fopen($atts) {
	$data = file_get_contents(asrv_make_store_url($atts));
	return json_decode($data);
}

function asrv_fetch_data_curl($atts) {
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, asrv_make_store_url($atts));
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    	$output = curl_exec($ch);
    	curl_close($ch);
    	
	return json_decode($output);
}

/* Display data methods */

function asrv_review_output($json_data, $minStar, $nbToDisplay) {
	if ($json_data->entry == null) return;

	//Parse App info
	$appInfo = $json_data->entry[0];
	$app['name'] = $appInfo->{'im:name'}->label;
	$app['icon'] = asrv_get_appIcon($appInfo);
	$app['url']  = $appInfo->link->attributes->href;

	//Parse App reviews and get only the last X based on the number of stars
	$first = true;
	$reviews = array();
	foreach ($json_data->entry as $review) {
	
		//Skip first entry as it contains only app information
		if ($first) {
			$first = false;
			continue;
		}
		
		//Parse the review and store it if it has the minimum required amount of stars
		$r = asrv_get_review($review, $minStar);
		if ($r) {
			$reviews[] = $r;
			if (count($reviews) == $nbToDisplay) {
				break;
			}
		}
	}

	//Render HTML
	if (count($reviews) > 0) {
		return asrv_render_html($app, $reviews);
	}
}

function asrv_get_appIcon($appInfo) {
	foreach ($appInfo->{'im:image'} as $img) {
		if ($img->attributes->height == 53) {
			return $img->label;
		}
	}
	return null;
}

function asrv_get_review($data, $minStar) {
	$review = array();
	
	$review["author"]  = $data->author->name->label;
	$review["rating"]  = $data->{'im:rating'}->label;
	$review["version"] = $data->{'im:version'}->label;
	$review["title"]   = $data->title->label;
	$review["content"] = $data->content->label;

	if ($review["rating"] >= $minStar) {
		return $review;
	}
}

function asrv_transform_rating($rating) {
	$s1 = "<span style='color:gold'>" . str_repeat("★", $rating) . "</span>";
	$s2 = "<span style='color:#eee'>" . str_repeat("★", 5 - $rating) . "</span>";
	return $s1 . $s2;
}

function asrv_render_html($app, $reviews) {
	ob_start();
?>
	<div id="asrv_list">
		<ul>
		<?php foreach ($reviews as $review) { ?>
			<li class="asrv_review">
				<div class="asrv_app">
					<a href="<?php echo $app['url'] ?>" target="blank" title="<?php echo $app['name'] ?>">
						<img src="<?php echo $app['icon'] ?>" class="asrv_app_icon" />
					</a>
				</div>
				<div class="asrv_info">
					<span class="asrv_title"><?php echo $review['title'] ?></span>
					<span class="asrv_rating"><?php echo asrv_transform_rating($review['rating']) ?></span>
					<div class="asrv_content"><?php echo $review['content'] ?></div>
					<div class="asrv_extra">By <?php echo $review['author'] ?> for Version <?php echo $review['version'] ?></div>
				</div>
			</li>
		<?php } ?>
		</ul>
	</div>

<?php
	$return = ob_get_contents();
	ob_end_clean();

	return $return;
}