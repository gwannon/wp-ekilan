<?php

// Aids ----------------------------------------
// ------------------------------------------------
add_action( 'init', 'emprendedores_preguntas_create_post_type' );
function emprendedores_preguntas_create_post_type() {
	$labels = array(
		'name'               => __( 'Preguntas', 'wp-ekilan' ),
		'singular_name'      => __( 'Pregunta', 'wp-ekilan' ),
		'add_new'            => __( 'Añadir nueva', 'wp-ekilan' ),
		'add_new_item'       => __( 'Añadir nueva pregunta', 'wp-ekilan' ),
		'edit_item'          => __( 'Editar pregunta', 'wp-ekilan' ),
		'new_item'           => __( 'Nueva pregunta', 'wp-ekilan' ),
		'all_items'          => __( 'Todas las preguntas', 'wp-ekilan' ),
		'view_item'          => __( 'Ver pregunta', 'wp-ekilan' ),
		'search_items'       => __( 'Buscar preguntass', 'wp-ekilan' ),
		'not_found'          => __( 'Pregunta no encontrada', 'wp-ekilan' ),
		'not_found_in_trash' => __( 'Pregunta no encontrada en la papelera', 'wp-ekilan' ),
		'menu_name'          => __( 'Preguntas Autodiagnóstico', 'wp-ekilan' ),
	);
	$args = array(
		'labels'        => $labels,
		'description'   => __( 'Añadir nueva pregunta', 'wp-ekilan' ),
		//'menu_position' => 7,
		'taxonomies' 		=> array('test'),
		'supports'      => array( 
      'title', 
      'editor',
      //'thumbnail', 
      'page-attributes' 
    ),
		'rewrite'	      => false,
		'query_var'	    => false,
		'has_archive' 	=> false,
		'hierarchical'	=> true,
  	'exclude_from_search' => true,
		'publicly_queryable' => false,
		'show_in_nav_menus' => false,
		'public'             => true
	);
	register_post_type( 'emprendedor-pregunta', $args );
}

//Sections -------------------------
add_action( 'init', 'emprendedores_preguntas_test_create_type' );
function emprendedores_preguntas_test_create_type() {
	$labels = array(
		'name'              => __( 'Secciones', 'wp-ekilan' ),
		'singular_name'     => __( 'Sección', 'wp-ekilan' ),
		'search_items'      => __( 'Buscar secciones', 'wp-ekilan' ),
		'all_items'         => __( 'Todas las secciones', 'wp-ekilan' ),
		'parent_item'       => __( 'Pariente sección', 'wp-ekilan' ),
		'parent_item_colon' => __( 'Pariente sección', 'wp-ekilan' ).":",
		'edit_item'         => __( 'Editar sección', 'wp-ekilan' ),
		'update_item'       => __( 'Actualizar sección', 'wp-ekilan' ),
		'add_new_item'      => __( 'Añadir sección', 'wp-ekilan' ),
		'new_item_name'     => __( 'Nueva sección', 'wp-ekilan' ),
		'menu_name'         => __( 'Secciones', 'wp-ekilan' ),
	);
	$args = array(
		'labels' 		        => $labels,
		'hierarchical' 	    => true,
		'public'		        => true,
		'query_var'		      => true,
		'show_in_nav_menus' => false,
		'has_archive'       => false,
    'rewrite'           =>  false,
    'publicly_queryable' => false
	);
  register_taxonomy( 'test', 'emprendedor-pregunta', $args );
}

function emprendedores_preguntas_test_edition_fields($tag) {
	//check for existing taxonomy meta for term ID
	$t_id = $tag->term_id;
	$term_meta = get_option( "taxonomy_$t_id"); ?>
	<tr class="form-field">
		<th scope="row" valign="top"><?php _e('Texto resumen PDF', "wp-ekilan"); ?></th>
		<td>
			<textarea name="term_meta[texto_resumen_pdf]" rows="5" cols="50" id="term_meta[texto_resumen_pdf]" class="large-text"><?php echo (isset($term_meta['texto_resumen_pdf']) ? $term_meta['texto_resumen_pdf'] : ''); ?></textarea>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><?php _e('Enlaces resumen PDF', "wp-ekilan"); ?></th>
		<td>
			<textarea name="term_meta[enlaces_resumen_pdf]" rows="5" cols="50" id="term_meta[enlaces_resumen_pdf]" class="large-text"><?php echo (isset($term_meta['enlaces_resumen_pdf']) ? stripslashes($term_meta['enlaces_resumen_pdf']) : ''); ?></textarea>
		</td>
	</tr>
	<?php
}

add_action( 'test_edit_form_fields', 'emprendedores_preguntas_test_edition_fields', 10, 2);
 
function emprendedores_preguntas_test_save_fields( $term_id ) {
	if ( isset( $_POST['term_meta'] ) ) {
		$t_id = $term_id;
		$term_meta = get_option( "taxonomy_$t_id");
		$cat_keys = array_keys($_POST['term_meta']);
		foreach ($cat_keys as $key){
			if (isset($_POST['term_meta'][$key]) && $_POST['term_meta'][$key] != ''){
				$term_meta2[$key] = $_POST['term_meta'][$key];
			}
		}
		update_option( "taxonomy_$t_id", $term_meta2 );
	}
}

add_action( 'edited_test', 'emprendedores_preguntas_test_save_fields', 10, 2);



//CAMPOS personalizados ---------------------------
// ------------------------------------------------
function get_emprendedores_preguntas_custom_fields () {
	$fields = array(
		'separator-a' => array('tipo' => 'separator', 'titulo' => __( 'Respuesta A', 'wp-ekilan' )),
		'respuesta-a' => array ('titulo' => __( 'Texto respuesta A', 'wp-ekilan' ), 'tipo' => 'textarea'),
		'valor-a' => array ('titulo' => __( 'Valor respuesta A', 'wp-ekilan' ), 'tipo' => 'select', 'valores' => [
			"1" => 1,
			"2" => 2,
			"3" => 3
		]),
		'separator-b' => array('tipo' => 'separator', 'titulo' => __( 'Respuesta B', 'wp-ekilan' )),
		'respuesta-b' => array ('titulo' => __( 'Texto respuesta B', 'wp-ekilan' ), 'tipo' => 'textarea'),
		'valor-b' => array ('titulo' => __( 'Valor respuesta B', 'wp-ekilan' ), 'tipo' => 'select', 'valores' => [
			"1" => 1,
			"2" => 2,
			"3" => 3
		]),
		'separator-c' => array('tipo' => 'separator', 'titulo' => __( 'Respuesta C', 'wp-ekilan' )),
		'respuesta-c' => array ('titulo' => __( 'Texto respuesta C', 'wp-ekilan' ), 'tipo' => 'textarea'),
		'valor-c' => array ('titulo' => __( 'Valor respuesta C', 'wp-ekilan' ), 'tipo' => 'select', 'valores' => [
			"1" => 1,
			"2" => 2,
			"3" => 3
		])
	);
	return $fields;
}

function emprendedores_preguntas_add_custom_fields() {
  add_meta_box(
    'box_activities', // $id
    __('Respuestas', 'wp-ekilan'), // $title 
    'wp_ekilan_show_custom_fields', // $callback
    'emprendedor-pregunta', // $page
    'normal', // $context
    'high'); // $priority
}
add_action('add_meta_boxes', 'emprendedores_preguntas_add_custom_fields');
add_action('save_post', 'wp_ekilan_save_custom_fields' );

//Columnas , filtros y ordenaciones ------------------------------------------------
function emprendedores_preguntas_set_custom_edit_columns($columns) {
  $columns['test'] = __( 'Sección', 'wp-ekilan');
	$columns['answers'] = __( 'Respuestas', 'wp-ekilan');
  unset($columns['date']);
	return $columns;
}

function emprendedores_preguntas_custom_column( $column ) {
  global $post;
  if ($column == 'test') {
    $terms = get_the_terms( $post->ID, 'test'); 
		$sorted_terms = sort_terms_hierarchically( $terms );
    $string = array();
    foreach($sorted_terms as $term) {
      $string[] = $term->name;
    }
    if(count($string) > 0) echo implode (", ", $string);
  } else if ($column == 'answers') {
		echo "<ul>";
		echo "<li>".get_post_meta( $post->ID, '_emprendedor-pregunta_valor-a', true )." -> ".get_post_meta( $post->ID, '_emprendedor-pregunta_respuesta-a', true )."</li>";
		echo "<li>".get_post_meta( $post->ID, '_emprendedor-pregunta_valor-b', true )." -> ".get_post_meta( $post->ID, '_emprendedor-pregunta_respuesta-b', true )."</li>";
		echo "<li>".get_post_meta( $post->ID, '_emprendedor-pregunta_valor-c', true )." -> ".get_post_meta( $post->ID, '_emprendedor-pregunta_respuesta-c', true )."</li>";
		echo "</ul>";
	}
	/*else if ($column == 'centro_estudios-emprendedor-pregunta') {
		echo get_post_meta( $post->ID, '_emprendedor-pregunta_centro_estudios', true );
  }*/
}

function emprendedores_preguntas_test_post_by_taxonomy() {
	global $typenow;
	$post_type = 'emprendedor-pregunta'; // change to your post type
	$taxonomy  = 'test'; // change to your taxonomy
	if ($typenow == $post_type) {
		$selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
		$info_taxonomy = get_taxonomy($taxonomy);
		wp_dropdown_categories(array(
			'hierarchical' 		=> 1,
			'show_option_all' => __( 'Mostrar todas las secciones', 'wp-ekilan' ),
			'taxonomy'        => $taxonomy,
			'name'            => $taxonomy,
			'orderby'         => 'name',
			'selected'        => $selected,
			'show_count'      => true,
			'hide_empty'      => true,
		));
	};
}

function emprendedores_preguntas_test_id_to_term_in_query($query) {
	global $pagenow;
	$post_type = 'emprendedor-pregunta'; // change to your post type
	$taxonomy  = 'test'; // change to your taxonomy
	$q_vars    = &$query->query_vars;
	if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
		$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
		$q_vars[$taxonomy] = $term->slug;
	}
}

//Los hooks si estamos en el admin 
if ( is_admin() && 'edit.php' == $pagenow && isset($_GET['post_type']) && 'emprendedor-pregunta' == $_GET['post_type'] ) {
  add_filter( 'manage_edit-emprendedor-pregunta_columns', 'emprendedores_preguntas_set_custom_edit_columns' ); //Metemos columnas
  add_action( 'manage_emprendedor-pregunta_posts_custom_column' , 'emprendedores_preguntas_custom_column', 'category' ); //Metemos columnas
  
  add_action( 'restrict_manage_posts', 'emprendedores_preguntas_test_post_by_taxonomy' ); //Añadimos filtro sección
  add_filter( 'parse_query', 'emprendedores_preguntas_test_id_to_term_in_query' ); //Añadimos filtro sección
  
  add_filter( 'months_dropdown_results', '__return_empty_array' ); //Quitamos el filtro de fechas en el admin
}
