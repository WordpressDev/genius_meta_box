<?php
/*
Plugin Name: Genius Meta Box
Plugin URI: http://www.geniusdeveloper.com.br/wordpress/plugins/2010/09/semi-plugin-genius-meta-box/
Description: Cria itens adicionais nos posts
Version: 0.1
Author: Rafael Cirolini
Author URI: http://www.geniusti.com.br
/* ----------------------------------------------*/

//escreve o valor na tela
function theValue($value) {
	$post_id = get_the_ID();
	$dado = get_post_meta($post_id, $value.'_value', true);
	echo "$dado";
}
//retorna o valor
function returnValue($value) {
	$post_id = get_the_ID();
	$dado = get_post_meta($post_id, $value.'_value', true);
	return $dado;
}

//array para registro dos itens
$new_meta_boxes =
	array(
		"text" => array(
			"name" => "text",
			"std" => "",
			"title" => "text",
			"description" => "text",
			"type" => "text"),
			
		"textearea" => array(
			"name" => "textearea",
			"std" => "",
			"title" => "textearea",
			"description" => "textearea",
			"type" => "textarea"),
			
		"radiobuttom" => array(
			"name" => "radiobuttom",
			"std" => "",
			"title" => "radiobuttom",
			"description" => "radiobuttom",
			"type" => "radiobuttom",
			"value" => "sim"),
			
		"select" => array(
			"name" => "select",
			"std" => "",
			"title" => "select",
			"description" => "select",
			"type" => "select"
			"grade" => array( "value1", "value2"))
		
	);

//gera o html dentro da edição do post
function new_meta_boxes() {
	global $post, $new_meta_boxes;

	foreach($new_meta_boxes as $meta_box) {
	
		//pega o valor do campo caso ja esteja cadastrado, se não coloca o valor padrão
		$meta_box_value = get_post_meta($post->ID, $meta_box['name'].'_value', true);
		if($meta_box_value == "") $meta_box_value = $meta_box['std'];
		
		//cria uma hash de validação
		echo'<input type="hidden" name="'.$meta_box['name'].'_noncename" id="'.$meta_box['name'].'_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';

	
		switch ($meta_box['type']) {
			case "text":
				echo'<input type="text" name="'.$meta_box['name'].'_value" value="'.$meta_box_value.'" size="55" /><br />';
				echo'<p><label for="'.$meta_box['name'].'_value">'.$meta_box['description'].'</label></p>';
				break;
			
			case "textarea":
				echo '<textarea name="'.$meta_box['name'].'_value" rows="2" cols="30">';
				echo ''.$meta_box_value.'</textarea>';
				echo'<p><label for="'.$meta_box['name'].'_value">'.$meta_box['description'].'</label></p>';
				break;
			
			case "radiobuttom":
				echo '<label for="'.$meta_box['name'].'_value" class="selectit">';
				echo '<input name='.$meta_box['name'].'_value" type="checkbox" value="'.$meta_box['value'].'"> '.$meta_box['description'].'</label><br>';
				break;
				
			case "select":
				echo '<select name="'.$meta_box['name'].'_value">
				<option value="#NONE#">— Selecione —</option>';
			 
				$i = 0;
				foreach($meta_box['grade'] as $meta_grade) {
					if ($meta_grade[$i] == $meta_box_value) { 
						$selected = 'selected="selected"';
					}
					else {
							$selected = '';
					}
					echo '<option value="'.$meta_grade[$i].'" '. $selected. '> '.$meta_grade[$i].' </option>';
					$i++;
				}
			
				echo '</select>';
				echo'<p><label for="'.$meta_box['name'].'_value">'.$meta_box['description'].'</label></p>';
				break;
		}
	
	}
}

//registra o meta box
function create_meta_box() {
	global $theme_name;
	
	if ( function_exists('add_meta_box') ) {
		add_meta_box( 'new-meta-boxes', 'Dados adicionais', 'new_meta_boxes', 'post', 'normal', 'high' );
	}
}

//para quando salvar os dados salvar os campos adicionais
function save_postdata( $post_id ) {
	global $post, $new_meta_boxes;

	foreach($new_meta_boxes as $meta_box) {
		
		if ( !wp_verify_nonce( $_POST[$meta_box['name'].'_noncename'], plugin_basename(__FILE__) )) {
			return $post_id;
		}

		if ( 'page' == $_POST['post_type'] ) {
			if ( !current_user_can( 'edit_page', $post_id ))
				return $post_id;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ))
			return $post_id;
		}

		$data = $_POST[$meta_box['name'].'_value'];

		if(get_post_meta($post_id, $meta_box['name'].'_value') == "") 
			add_post_meta($post_id, $meta_box['name'].'_value', $data, true);
	
		elseif($data != get_post_meta($post_id, $meta_box['name'].'_value', true)) 
			update_post_meta($post_id, $meta_box['name'].'_value', $data);
		
		elseif($data == "") 
			delete_post_meta($post_id, $meta_box['name'].'_value', get_post_meta($post_id, $meta_box['name'].'_value', true));
	}
}

//adiciona os hooks necessarios para o plugin
add_action('admin_menu', 'create_meta_box');
add_action('save_post', 'save_postdata');

?>