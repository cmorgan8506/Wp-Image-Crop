
<h1>WP Image Crop</h1>
<p>WP Image Crop is a plugin developed to size and crop images in WordPress themes on the fly. The plugin is designed to give theme developers an easy to use tool when dealing with multiple image sizes and aspect ratios through out their WordPress themes. WP Image Crop also has a white list feature that allows you to use images from external sources securely, as well as, delegating a CDN url to use for sized images.</p>

<h2>How to use it</h2>
Download, install, and activate the plugin.

Example: Size and display image
<pre>
if( class_exists( 'WP_Image_Crop' ) ) {
    $imagesizer = New WP_Image_Crop;
    $imagesizer->sized_image( wp_get_attachment_url( get_post_thumbnail_id() ), 200, 200 );
}
</pre>

Example: Size image and return url 
<pre>
if( class_exists( 'WP_Image_Crop' ) ) {
    $imagesizer = New WP_Image_Crop;
    $url = $imagesizer->get_sized_image( wp_get_attachment_url( get_post_thumbnail_id() ), 200, 200 );
}
</pre>

<b>Function Options:</b>
<pre>
sized_image( $url = null, $width = 150, $height = 150 )
get_sized_image( $url = null, $width = 150, $height = 150 )
</pre>

<b>WordPress Versions Supported:</b> 3.0+

