<?php

if(!class_exists('WP_Image_Crop')){
	
	class WP_Image_Crop{
		/*
		* Plugin Options
		*/
		private $options;
		
		/*
		* Original image parameters
		*/
		private $width;
		private $height;
		
		/*
		* Resized/Cropped image/url
		*/
		private $cropped_image_url;

		/*
		* The website URL and CDN
		*/
		private $siteurl;
		private $cdnurl;
		
		/*
		* default image
		*/
		private $default_image;
		
		function __construct(){
			$this->options = get_option('wpic_options');
			
			$this->siteurl = get_bloginfo('url');

			if($this->options['cdn_url'])
				$this->sitecdn = rtrim($this->options['cdn_url'], '/');
			else
				$this->sitecdn = $this->siteurl;
			
			if($this->options['default_image']){
				$this->default_image = str_replace($this->siteurl . '/', '', $this->options['default_image']);
				$this->default_image = str_replace($this->sitecdn . '/', '', $this->options['default_image']);
			}	
		}
		
		/**
		 *
		 * Resize and crop an Image
		 * and return the image url.
		 *
		 * @param    string $url
		 * @param    int $width 
		 * @param    int $height
		 * @return   string/bool
		 *
		 */
		public function get_sized_image( $url = null, $width = 150, $height = 150){
			global $post;
			
			$url = $this->get_image($url);
			
			// Retrieve and set image info
			if($this->image_info( $url ) == false){
				return "Image Does Not Exist";
			}
			
			//build new image destination
			$this->build_img_url( $url, $width, $height );
			
			// check if cropped image already exists
			if( file_exists( str_replace($this->sitecdn . '/', '', $this->cropped_image_url) ) ){
				return $this->cropped_image_url;
			}
			
			// check if the image is already the correct size or too small
			if( $this->compare_image($width, $height) ){
				return $this->sitecdn . '/' . $url;
			}
			
			// crop or resize image
			$resize = image_resize( $url, $width, $height, true );

			// Check crop for error
			if( is_wp_error( $resize ) ){
				$error_string = $resize->get_error_message();
				echo "Error: " . $error_string;
				return false;
			}else{
				return $this->cropped_image_url;
			}
		}
		
		/*
		 * Request a resize/cropped image
		 * and display it.
		 *
		 * @param    string $url
		 * @param    int $width
		 * @param    int $height
		 * @return   void/error
		 */
		public function sized_image( $url = null, $width = 150, $height = 150 ){
			global $post;
			
			// Check if image is external
			$url = $this->get_image($url);

			// Retrieve and set image info
			if($this->image_info( $url ) == false){
				echo "Image does not exist.";
				return false;
			}

			//build new image destination
			$this->build_img_url( $url, $width, $height );

			// check if cropped image already exists
			if( file_exists( str_replace($this->sitecdn . '/', '', $this->cropped_image_url) ) ){
				echo '<img src="' . $this->cropped_image_url . '" alt="' . $post->post_title . '" />';
				return;
			}
			
			// check if the image is already the correct size or too small
			if( $this->compare_image($width, $height) ){
				echo '<img src="' . $this->sitecdn . '/' . $url . '" alt="' . $post->post_title . '" />';
				return;
			}
			
			// trim $url to crop
			$url = ltrim( $url, '/' );
			
			// crop or resize image
			$resize = image_resize( $url, $width, $height, true );

			// Check crop for error
			if( is_wp_error( $resize ) ){
				$error_string = $resize->get_error_message();
				echo "Error: " . $error_string;
				return false;
			}else{
				echo '<img src="' . $this->cropped_image_url . '" alt="' . $post->post_title . '" />';
				return;
			} 
		}
		 
		 /*
		 * Build new image path
		 *
		 * @param    string $url
		 * @param    int $width
		 * @param    int $height
		 * @return   void 
		 */
		 private function build_img_url( $url, $width, $height ){
			global $post;
			
			//build new cropped image url
			$url_info = pathinfo( '/' . $url );
			
			if($url_info['extension'] == 'jpeg') $ext = "jpg";
			else $ext = $url_info['extension'];
				
			$url_basename = rtrim( basename( '/' . $url, $url_info['extension'] ), '.' );
			$this->cropped_image_url = $this->sitecdn . $url_info['dirname'] . '/' . $url_basename . "-" . $width . "x" . 
								   $height . "." . $ext;
		 }
		 
		 /*
		 * Compare requested image sizes
		 * to existing image sizes.
		 *
		 * @param    int $width
		 * @param    int $height
		 * @return  bool
		 */
		 private function compare_image($width, $height){
			// Check if requested dimensions are the same as existing dimensions
			if( $this->width == $width && $this->height == $height ){
				return true;
			
			// Check if requested dimensions are larger than existing dimensions	
			}elseif( $this->width < $width || $this->height < $height ){
				return true;
			}
			
			return false;
		 }
		 
		 /*
		 * Gather information
		 * about the image to be cropped.
		 *
		 * @param   string $url
		 * @return  bool
		 */
		 private function image_info( &$url ){
			global $post;

			if( $url != null ){
				//Water the original URL down to a relative path
				$url = str_replace( array($this->sitecdn, $this->siteurl), '', $url );
				$url = ltrim( $url, '/' );
				$url = preg_replace('/%20/', ' ', $url);
		
				//check if the image exists
				if( file_exists( $url ) == false ){
					if( isset( $this->default_image ) ){
						$url = str_replace($this->siteurl . '/', '', $this->default_image);
					}else{
						return false;
					}
				}
			
				//get orginial dimensions
				$image = getimagesize( $url );
				$this->width = $image[0];
				$this->height = $image[1];

				return true;
			}else{
				return false;
			}
		}
		
		public function get_image($url){
			$url = preg_replace('/ /', '%20', $url);
			
			if($this->options['trusted_domains'] != NULL){
				//Check for trusted domains
				foreach($this->options['trusted_domains'] as $domain){
					if(preg_match("/\A" . preg_quote($domain, '/') . "/i", $url)){
						$info = pathinfo($url);
						$base = preg_replace('/%20/', '', $info['basename']);
						$img = ABSPATH . 'wp-content/uploads/' . $base;
						$img_url = 'wp-content/uploads/' . $base;
						
						//if match, check if it already exists
						if(file_exists($img)) return $img_url;
						else{
							$file = file_get_contents($url);
						}
							
						if($file != false)
							if(file_put_contents($img, $file)) return $img_url;
							else return NULL;
						else
							return NULL;
					}
				}
			}
			return $url;	
		}
	} // END CLASS
	
	$imagesizer = New WP_Image_Crop; 
}

?>
