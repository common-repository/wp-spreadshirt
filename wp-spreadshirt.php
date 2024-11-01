<?php
/**
 * Plugin Name: WP Spreadshirt
 * Plugin URI: http://ppfeufer.de/wordpress-plugin/wp-spreadshirt/
 * Description: Adding a shortcode to show your Spreadshirt-Articles in a page or post.
 * Version: 1.6.3
 * Author: H.-Peter Pfeufer
 * Author URI: http://ppfeufer.de
 */

/**
 * Avoid direct calls to this file
 *
 * @since 1.0
 * @author ppfeufer
 *
 * @package WP Spreadshirt
 */
if(!function_exists('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');

	exit();
} // END if(!function_exists('add_action'))

/**
 * The WP_Spreadshirt Class
 *
 * @since 1.1
 * @author ppfeufer
 */
if(!class_exists('WP_Spreadshirt')) {
	class WP_Spreadshirt {
		private $var_sTextdomain = 'wp-spreadshirt';

		function WP_Spreadshirt() {
			WP_Spreadshirt::__construct();
		}

		function __construct() {
			add_action('init', array(
				$this,
				'plugin_init'
			));

			add_action('wp_head', array(
				$this,
				'wp_spreadshirt_head'
			));

			add_shortcode('spreadshirt', array(
				$this,
				'sc_wp_spreadshirt'
			));
		}

		/**
		 * Initialize Plugin
		 *
		 * @since 1.2
		 * @author ppfeufer
		 */
		function plugin_init() {
			/**
			 * Sprachdatei wählen
			 */
			if(function_exists('load_plugin_textdomain')) {
				load_plugin_textdomain($this->var_sTextdomain, false, dirname(plugin_basename( __FILE__ )) . '/l10n');
			} // END if(function_exists('load_plugin_textdomain'))
		} // END function plugin_init()

		/**
		 * Shortcode [spreadshirt] bereit stellen
		 *
		 * @since 1.0
		 * @author ppfeufer
		 */
		function sc_wp_spreadshirt($atts) {
			$shop_id = '';
			$shop_url = '';

			extract(shortcode_atts(array(
				'shop_id' => '',
				'shop_url' => '',
				'shop_location' => 'eu'
			), $atts));

			if(!empty($shop_id) && !empty($shop_url)) {
				/**
				 * Let's see what API we have to talk to.
				 *
				 * @since 1.6
				 * @author ppfeufer
				 */
				$var_sApiUrl = '';
				switch($shop_location) {
					case 'eu':
						$var_sApiUrl = 'http://api.spreadshirt.net/api/v1/shops/' . $shop_id . '/articleCategories/510/articles';
						break;

					case 'na':
						$var_sApiUrl = 'http://api.spreadshirt.com/api/v1/shops/' . $shop_id . '/articleCategories/510/articles';
						break;
				} // END switch($shop_location)

				$array_ArticleData = $this->_get_spreadshirt_data($shop_url, $var_sApiUrl);

				if($array_ArticleData === false) {
					return false;
				} // END if($array_ArticleData === false)

				if(is_array($array_ArticleData)) {
					ob_start();
					?>
					<div class="spreadshirt-items clearfix">
						<?php
						foreach((array) $array_ArticleData as $article) {
							?>
							<div class="spreadshirt-article clearfix">
								<h3><a href="<?php echo $article['article-uri']; ?>"><?php echo $article['article-name']; ?></a></h3>
								<p><a href="<?php echo $article['article-uri']; ?>"><img src="<?php echo $article['article-image'] ?>" width="190" alt="<?php echo $article['article-name']; ?>" /></a></p>
								<div>
									<?php
									if(!empty($article['article-description'])) {
										?>
										<p><?php echo $article['article-description']; ?></p>
										<?php
									} // END if(!empty($article['article-description']))
									?>
									<p>
										<?php echo __('Price (without tax):', $this->var_sTextdomain); ?> <?php echo $article['article-price-vatExcluded']; ?> <?php echo $article['article-price-currency']; ?><br />
										<?php echo __('Price (with tax):', $this->var_sTextdomain); ?> <?php echo $article['article-price-vatIncluded']; ?> <?php echo $article['article-price-currency']; ?><br />
									</p>
								</div>
							</div>
							<?php
						} // END foreach((array) $array_ArticleData as $article)
						?>
					</div>
					<?php

					$var_sReturn = ob_get_contents();
					ob_end_clean();

					return $var_sReturn;
				} // END if(is_array($array_ArticleData))
			} // END if(!empty($shop_id) && !empty($shop_url))
		} // END function sc_wp_spreadshirt($atts)

		/**
		 * Daten von der Spreadshirt-API holen
		 *
		 * @since 1.0
		 * @author ppfeufer
		 *
		 * @param string $var_sShopUri
		 * @param string $var_sShopId
		 * @return boolean|multitype:multitype:string
		 */
		private function _get_spreadshirt_data($var_sShopUri = null, $var_sApiUrl) {
			if(!empty($var_sShopUri) && !empty($var_sApiUrl)) {
				$array_Articles = get_transient('spreadshirt-article-data');

				if($array_Articles === false) {
					$var_sXmlShop = wp_remote_retrieve_body(wp_remote_get($var_sApiUrl));

					if(is_wp_error($var_sXmlShop)) {
						return false;
					} // END if(is_wp_error($var_sXmlShop))

					/**
					 * Check if we have a valid XML.
					 * If not, the shop seems to be not existing.
					 *
					 * @since 1.6
					 * @author ppfeufer
					 */
					if(substr($var_sXmlShop, 0, 5) != "<?xml") {
						echo __('The Shop doesn\'t exist', $this->var_sTextdomain);

						return false;
					} // if(substr($var_sXmlShop, 0, 5) != "<?xml")

					try {
						$obj_ShopData = new SimpleXmlElement($var_sXmlShop);

						$array_Articles = array();
						$var_sArticleUri = '';

						foreach($obj_ShopData->article as $article) {
							$var_sXmlArticle = wp_remote_retrieve_body(wp_remote_get($article->attributes('http://www.w3.org/1999/xlink')));

							if(is_wp_error($var_sXmlArticle)) {
								return false;
							} // END if(is_wp_error($var_sXmlArticle))

							try {
								$obj_ArticleData = new SimpleXmlElement($var_sXmlArticle);

								$var_sXmlCurreny = wp_remote_retrieve_body(wp_remote_get($obj_ArticleData->price->currency->attributes('http://www.w3.org/1999/xlink')));
								try {
									$obj_CurrencyData = new SimpleXmlElement($var_sXmlCurreny);
								} catch(Exception $e) {}
							} catch(Exception $e) {}

							/**
							 * Search and replace to clean up the article uri
							 */
							$array_Pattern = array(
								',',
								'.',
								' ',
								'"',
								'ä',
								'Ä',
								'ö',
								'Ö',
								'ü',
								'Ü',
								'ß',
								'\''
							);
							$array_Replace = array(
								'',
								'-',
								'-',
								'',
								'ae',
								'ae',
								'oe',
								'oe',
								'ue',
								'ue',
								'ss',
								'-'
							);

							$var_sArticleUri = strtolower((string) $obj_ArticleData->name);
							$var_sArticleUri = str_replace($array_Pattern, $array_Replace, $var_sArticleUri);
// 							$var_sArticleUri = str_replace('.', '-', $var_sArticleUri);
// 							$var_sArticleUri = str_replace(' ', '-', $var_sArticleUri);
// 							$var_sArticleUri = str_replace('"', '', $var_sArticleUri);

							/**
							 * Check if we have a "shop/" at the end of the shop-uri
							 *
							 * @since 1.3
							 * @author ppfeufer
							 */
							if(preg_match('/\/shop\//',  $var_sShopUri)) {

								$var_sArticleUri = preg_replace('/\/shop\//', '', $var_sShopUri) . $var_sArticleUri . '-A' . (int) $article->attributes()->id;
							} else {
								$var_sArticleUri = $var_sShopUri . $var_sArticleUri . '-A' . (int) $article->attributes()->id;
							} // END if(preg_match('/\/shop\//',  $var_sShopUri))

							$array_Article = array(
								'article-name' => (string) $obj_ArticleData->name,
								'article-description' => (string) $obj_ArticleData->description,
								'article-uri' => $this->reduce_dashes($var_sArticleUri, '-', 1),
								'article-image' => (string) $article->resources->resource->attributes('http://www.w3.org/1999/xlink'),
								'article-price-vatExcluded' => (string) $obj_ArticleData->price->vatExcluded,
								'article-price-vatIncluded' => (string) $obj_ArticleData->price->vatIncluded,
								'article-price-vat' => (string) $obj_ArticleData->price->vat,
								'article-price-currency' => (string) $obj_CurrencyData->symbol,
							);

							$array_Articles[] = $array_Article;

							set_transient('spreadshirt-article-data', $array_Articles, 3600);
						} // END foreach($obj_ShopData->article as $article)
					} catch(Exception $e) {}
				} /// END if($array_Articles === false)

				return $array_Articles;
			} else {
				return false;
			} // END if(!empty($var_sShopUri) && !empty($var_sShopId))
		} // END private function _get_spreadshirt_data($var_sShopUri = null, $var_sShopId = null)

		/**
		 * mehrfach wiederholende Zeichen aus einem String entfernen
		 *
		 * @param string $string Zu bearbeitender String
		 * @param string $chars Zeichen die beachtet werden sollen
		 * @param int $maxRepeats
		 * @return string
		 *
		 * @since 1.6.2
		 * @author ppfeufer
		 */
		private function reduce_dashes($var_sString, $var_sChars, $var_iMaxRepeats) {
			$patternParts = array();

			foreach(str_split($var_sChars) as $char) {
				$patternParts[] = sprintf('%s{%d,}', $char, $var_iMaxRepeats);
			}

			$var_sString = preg_replace_callback(
				sprintf('/%s/i', join('|', $patternParts)),
				create_function('$matches', 'return $matches[0][0];'),
				$var_sString
			);

			return $var_sString;
		}

		/**
		 * Etwas CSS in den <head> schubsen
		 *
		 * @since 1.0
		 * @author ppfeufer
		 */
		function wp_spreadshirt_head() {
			echo "\n" . '<style type="text/css">.spreadshirt-article {float:left; margin-top:20px; margin-right:2px; width:225px; height:450px; overflow:hidden;} .spreadshirt-article img, .spreadshirt-article h3 {display:block; text-align:center; margin:0 auto;} .spreadshirt-article div {text-align:left;}</style>' . "\n\n";
		} // END function wp_spreadshirt_head()
	} // END class WP_Spreadshirt

	new WP_Spreadshirt();
} // END if(!class_exists('WP_Spreadshirt'))