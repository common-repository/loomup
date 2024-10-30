<?php
/*
Plugin Name: Loomup
Plugin URI: http://Loomup.fr
Description: Affiche la vidéo personnalisée
Version: 0.1
Author: Loomup
Author URI: http://Loomup.fr
Text Domain: loofacemup
License:
*/

define('LOOMUP_PLUGIN_DIR_CLASS_ADMIN', plugin_dir_path( __FILE__ ).'class/admin/');
define('LOOMUP_API_URL','http://server.loomup.fr/api/wordpress/api/');

add_action('init', 'loomup_init');
add_action('init', 'loomup_ini_custom_post');
if(isset($_GET['page']) && sanitize_text_field($_GET['page']) == 'loomup_videos')
{
	// echo sanitize_text_field($_GET['page']);die();
	add_action('init','loomup_add_videos_if_not_yet_added');
}

add_shortcode('loomup', 'loomup_set_video');


function loomup_set_video($atts)
{
	if(is_order_received_page())
	{

		// Récupération des données
		// de la commande et replissage
		// du tableau de correspondance WooCommerce

		$atts_video = [];
		$order_id = wc_get_order_id_by_order_key($_GET['key']);
		$order = wc_get_order( $order_id );
		$post_meta = get_post_meta( $order_id );

		$order_items = $order->get_items();

		$items = [];
		$i = 0;
		foreach ($order_items as $item ) {
			$product = new WC_Product($item['item_meta']['_product_id'][0]);
			$items[$i]['wc_order_item_name'] = $item['name'];
			$items[$i]['wc_order_item_post_content'] = $product->get_post_data()->post_content;
			$items[$i]['wc_order_item_post_excerpt'] = $product->get_post_data()->post_excerpt;
			$items[$i]['wc_order_item_img'] = wp_get_attachment_image_src(get_post_thumbnail_id(8))[0];
			$items[$i]['wc_order_item_qty'] = $item['item_meta']['_qty'][0];
			$i++;
		}

		setlocale(LC_TIME, "fr_FR");

		$atts_wc = array(
			'wc_client_name' => $post_meta['_shipping_first_name'][0].' '.$post_meta['_shipping_last_name'][0],
			'wc_client_point' => '267',
			'wc_client_genre' => 'un client',
			'wc_order_number' => $order_id,
			'wc_order_date' => strftime("%d %B"),
			'wc_order_total_sale' => $order->get_total(),
			'wc_order_items' => $items
		);

		// Récupération des données
		// du shortcode loomup

		$video_id = $atts['id'];
		$post_id = $atts['post'];
		$atts_lu = get_post_meta($post_id,'',false);
		unset($atts_lu['_edit_lock']);
		unset($atts_lu['_edit_last']);

		foreach ($atts_lu as $att_lu => $att_wc ) {

			$atts_video[$att_lu] = $atts_wc[trim($att_wc[0])];
		}

		$atts_video['user_avatar'] = '/images/avatars/avatar.jpg';
		$atts_video['user_pseudo'] = $post_meta['_shipping_first_name'][0].' '.$post_meta['_shipping_last_name'][0];

		if(!empty($atts_video))
		{
			// Formatage des données
			$data_api = array(
				'video' => array(
					'video_id' => $video_id,
				),
				'user' => array(
					'user_id' => $order_id,
					'user_data_video' => $atts_video
				)
			);
			$user = loomup_handle_request('POST','user',$data_api)['msg'];

			$inputs = '';
			foreach ($data_api['user']['user_data_video'] as $att  => $val ){
				$inputs.='<input type="hidden" id = '.esc_attr($att).' value="'.esc_attr($val).'">';
			}

			echo $inputs;

			loomup_set_div($video_id,$order_id);
			loomup_set_script();

		}
	}
}

function loomup_init()
{
	require(LOOMUP_PLUGIN_DIR_CLASS_ADMIN.'Loomup_Admin.php');
	new Loomup_Admin();
}

function loomup_set_script()
{
	wp_enqueue_script( 'loomupapi', "http://server.loomup.fr/api/wordpress/js/loomupapi.min.js", array(), '1.0.0', true );
}

function loomup_set_div($idVid,$idUser)
{
		$idCl = get_option('loomup_key');
		echo "
		<div class='loomupvid'
			data-idCl = ".esc_attr($idCl)."
			data-idVid = ".esc_attr($idVid)."
			data-idUser = ".esc_attr($idUser).">
		</div>";
}

// CUSTOM POSTS
function loomup_ini_custom_post() {
	register_post_type(
		'loomup_videos',
		array(
			'label' => 'Vos vidéos Loomup',
			'labels' => array(
				'name' => 'Vos vidéos Loomup',
				'singular_name' => 'Vidéo Loomup',
				'all_items' => 'Toutes les vidéos',
				'add_new_item' => 'Ajouter une vidéo',
				'edit_item' => 'Modifier la vidéo',
				'new_item' => 'Nouvelle vidéo',
				'view_item' => 'Voir la vidéo',
				'search_items' => 'Rechercher parmi les vidéos',
				'not_found' => 'Pas de vidéo trouvées',
				'not_found_in_trash' => 'Pas de vidéos dans la corbeille'
			),
			'show_ui'=>true,
			'show_in_menu'=>'loomup_videos',
			'public' => true,
			'menu_icon' => 'dashicons-calendar',
			'supports' => array(
				'title',
				'author',
				'revisions',
				'page-attributes',
				'custom-fields',
				'post-formats'
			),
			'has_archive' => false,
			'exclude_from_search' => false,
		)
	);
}

function loomup_add_videos_if_not_yet_added ()
{
	if(Loomup_Admin::loomup_isKeyDefined())
	{
		$videos = loomup_handle_request('GET','videos',array())['msg'];

		foreach ($videos as $video)
		{
			$post = get_page_by_path("loomup_".$video->video_slug,OBJECT,'loomup_videos');

			if (!$post)
			{
				$my_post = array(
					'post_type'       => "loomup_videos",
					'post_content'    => "Information about {$video->video_name}",
					'post_title'      => $video->video_name,
					'post_name'       => "loomup_".$video->video_slug,
					'post_status'     => "publish",
					'comment_status'  => "closed",
					'ping_status'     => "closed",
					'post_parent'     => "0",
				);
				// Insert the post into the database
				wp_insert_post( $my_post );

				$post = get_page_by_path("loomup_".$video->video_slug,OBJECT,'loomup_videos');
				if(!empty($video->attributes ))
				{
					foreach ($video->attributes as $attribute)
					{
						add_post_meta( $post->ID,$attribute->attr_slug,$attribute->attr_wc, true );
					}
				}
			}
		}

		if(isset($_GET['remove_custom_post']))
		{
			$remove_custom_post_id = intval($_GET['remove_custom_post']);

			if ( !$remove_custom_post_id ) {
  			$remove_custom_post_id = '';
			}

			wp_delete_post($remove_custom_post_id);
			header('Location:'.admin_url().'admin.php?page=loomup_videos');
		}
	}
}

function loomup_handle_request($method,$what,$data)
{
	if(get_option('loomup_key') )
	{
		$api = loomup_callAPI($method,LOOMUP_API_URL.$what,$data,get_option('loomup_key'));

		if($api['status'] != 200)
		{
			return $api;
		}
		return $api;
	}
	else{
		return loomup_integrite(['status'=>204]);
	}
}


function loomup_integrite ($array)
{
	if($array['status'] == 200)
	{
		return true;
	}
	else
	{
		switch ($array['status'])
		{
		case 204:
			echo "Vieullez fournir votre clé cliente.";
			break;
		case 406:
			$this->admin_loomup->title = "Votre clé cliente n'est pas correcte.";
			break;
		case 500:
			$this->admin_loomup->title = "Une erreur serveur est survenue.";
			break;
	}
	return false;
	}
}

function loomup_callAPI($method, $url, $data = false,$api_key = NULL)
{
	$curl = curl_init();

	switch ($method)
	{
		case "POST":
			curl_setopt($curl, CURLOPT_POST, 1);
			if ($data)
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
			break;
		case "PUT":
			curl_setopt($curl, CURLOPT_PUT, 1);
			break;
		default:
			if ($data)
				$url = sprintf("%s?%s", $url, http_build_query($data));
	}
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		'X-Api-Key:'.$api_key
    ));

	$result = curl_exec($curl);

	$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	if($httpcode == 200)
	{
		return array('status' => $httpcode,'msg'=>json_decode($result));
	}
	else
	{
		return array('status' => $httpcode,'msg'=> $httpcode);
	}
}

if ( !function_exists( 'is_order_received_page' ) )
{
	function is_order_received_page()
	{
		global $wp;
		return ( is_page( wc_get_page_id( 'checkout' ) ) && isset( $wp->query_vars['order-received'] ) );
	}
}
