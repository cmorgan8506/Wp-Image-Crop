jQuery(document).ready(function() {
	
	//Default Image Upload
	jQuery('#upload_image_button').click(function() {
		formfield = jQuery('#wpic_default').attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');

		return false;
	});

	window.send_to_editor = function(html) {
		imgurl = jQuery('img',html).attr('src');
		jQuery('#wpic_default').val(imgurl);
		tb_remove();
	}
	
	//Add trusted site ajax
	jQuery(".wpic_domains_button").live('click', function(){
			var method = jQuery(this).val();
			
			if(method == 'add'){
				var domain = jQuery("#wpic_domains").val(); 
			}else{
				var domain = jQuery(this).parent().text();
				var oldElement = jQuery(this).parent();
			}

			if(domain != ''){
				jQuery.ajax({
					type: 'POST',
					url: 'admin-ajax.php',
					data: {
					    action: 'wpic_handle_domain',
					    domain: domain,
					    method: method
					},
					success: function(data, textStatus, XMLHttpRequest){
						if(method == 'add' ){
					    	jQuery('#domains').append("<li>" + domain + " <input type='button' class='wpic_domains_button'value='remove' /></li>");
					    }else if(method == 'remove'){
					    	oldElement.remove();
					    }
					},
					error: function(MLHttpRequest, textStatus, errorThrown){
					    alert(errorThrown);
					}
				});
			}else alert("Please enter a domain first");


		});
});

