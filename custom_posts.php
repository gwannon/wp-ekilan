<?php

function get_wp_ekilan_custom_fields ($type) {
  if($type == 'emprendedor-pregunta') return get_emprendedores_preguntas_custom_fields();
}

function wp_ekilan_show_custom_fields() { //Show box
  global $post;
  $type = get_post_type($post->ID);
  $fields = get_wp_ekilan_custom_fields ($type); ?>
		<div>
      <?php foreach ($fields as $field => $datos) { ?>
        <?php if(!isset($datos['is']) || (isset($datos['is']) && has_term($datos['is']['id'], $datos['is']['taxonomy'], $post->ID))) { ?>
          <?php if($datos['tipo'] != 'repeater' && $datos['tipo'] != 'map' && $datos['tipo'] != 'separator') { ?><div style="width: calc(50% - 10px); float: left; padding: 5px;"><?php } else { ?><div style="width: calc(100% - 10px); float: left; padding: 5px;"><?php } ?>
            <?php if($datos['tipo'] == 'separator') { ?><h3 style="background-color: #000; color: #fff; padding: 5px; margin: 0px;"><?php echo $datos['titulo']; ?></h3><?php } else { ?><p><b><?php echo $datos['titulo']; ?></b></p><?php } ?>
            <?php if($datos['tipo'] == 'text' || $datos['tipo'] == 'link') { ?>
              <input  type="text" class="_<?php echo $type; ?>_<?php echo $field; ?>" id="_<?php echo $type; ?>_<?php echo $field; ?>" style="width: 100%;" name="_<?php echo $type; ?>_<?php echo $field; ?>" value="<?php echo str_replace('"', '\"', get_post_meta( $post->ID, '_'.$type.'_'.$field, true )); ?>"<?php echo (isset($datos['placeholder']) ? " placeholder='".$datos['placeholder']."'" : "" ); ?>/>
            <?php } else if($datos['tipo'] == 'date') { ?>
              <input type="date" class="_<?php echo $type; ?>_<?php echo $field; ?>" id="_<?php echo $type; ?>_<?php echo $field; ?>" style="width: 50%;" name="_<?php echo $type; ?>_<?php echo $field; ?>" value="<?php echo get_post_meta( $post->ID, '_'.$type.'_'.$field, true ); ?>" />
            <?php } else if($datos['tipo'] == 'number') { ?>
              <input type="number" step="0.01" class="_<?php echo $type; ?>_<?php echo $field; ?>" id="_<?php echo $type; ?>_<?php echo $field; ?>" style="width: 50%;" name="_<?php echo $type; ?>_<?php echo $field; ?>" value="<?php echo get_post_meta( $post->ID, '_'.$type.'_'.$field, true ); ?>" />
            <?php } else if($datos['tipo'] == 'code') { ?>
              <textarea class="_<?php echo $type; ?>_<?php echo $field; ?>" id="_<?php echo $type; ?>_<?php echo $field; ?>" style="width: 100%;" rows="5" name="_<?php echo $type; ?>_<?php echo $field; ?>"<?php echo (isset($datos['placeholder']) ? " placeholder='".$datos['placeholder']."'" : "" ); ?>><?php echo get_post_meta( $post->ID, '_'.$type.'_'.$field, true ); ?></textarea>
            <?php } else if($datos['tipo'] == 'hidden') { ?>
              <input disabled="disabled" type="text" class="_<?php echo $type; ?>_<?php echo $field; ?>" id="_<?php echo $type; ?>_<?php echo $field; ?>" style="width: 50%;" name="_<?php echo $type; ?>_<?php echo $field; ?>" value="<?php echo get_post_meta( $post->ID, '_'.$type.'_'.$field, true ); ?>" />
            <?php } else if($datos['tipo'] == 'image') { ?>
              <input type="text" class="_<?php echo $type; ?>_<?php echo $field; ?>" id="input_<?php echo $type; ?>_<?php echo $field; ?>" style="width: 80%;" name="_<?php echo $type; ?>_<?php echo $field; ?>" value='<?php echo get_post_meta( $post->ID, '_'.$type.'_'.$field, true ); ?>' />
              <a href="#" id="button_media_<?php echo $field; ?>" class="button insert-media add_media" data-editor="input_<?php echo $type; ?>_<?php echo $field; ?>" title="<?php _e("Add file", 'emprendedores'); ?>"><span class="wp-media-buttons-icon"></span> <?php _e("Add file", 'emprendedores'); ?></a>
              <script>
                jQuery(document).ready(function () {			
                  jQuery("#input_<?php echo $type; ?>_<?php echo $field; ?>").change(function() {
                    a_imgurlar = jQuery(this).val().match(/<a href=\"([^\"]+)\"/);
                    img_imgurlar = jQuery(this).val().match(/<img[^>]+src=\"([^\"]+)\"/);
                    if(img_imgurlar !==null ) jQuery(this).val(img_imgurlar[1]);
                    else  jQuery(this).val(a_imgurlar[1]);
                  });
                });
              </script>
            <?php } /*else if($datos['tipo'] == 'text') { ?>
              <input  type="text" class="_<?php echo $type; ?>_<?php echo $field; ?>" id="_<?php echo $type; ?>_<?php echo $field; ?>" style="width: 100%;" name="_<?php echo $type; ?>_<?php echo $field; ?>" value="<?php echo get_post_meta( $post->ID, '_'.$type.'_'.$field, true ); ?>" />
            <?php }*/ else if($datos['tipo'] == 'textarea') { ?>
              <?php $settings = array( 'media_buttons' => true, 'quicktags' => true, 'textarea_rows' => 5 ); ?>
              <?php wp_editor( get_post_meta( $post->ID, '_'.$type.'_'.$field, true ), '_'.$type.'_'.$field, $settings ); ?>
            <?php } else if ($datos['tipo'] == 'select') { ?>
              <select name="_<?php echo $type; ?>_<?php echo $field; ?>" style="width: 100%;">
                <?php foreach($datos['valores'] as $key => $value) { ?>
                  <option value="<?php echo $key; ?>"<?php if ($key == get_post_meta( $post->ID, '_'.$type.'_'.$field, true )) echo " selected='selected'"; ?>><?php echo $value; ?></option>
                <?php } ?>	
              </select>
            <?php } else if ($datos['tipo'] == 'multiple') {  ?>
              <select name="_<?php echo $type; ?>_<?php echo $field; ?>[]" multiple="multiple" style="width: 100%;">
                <?php foreach($datos['valores'] as $key => $value) { ?>
                  <option value="<?php echo $key; ?>"<?php if (in_array($key, get_post_meta( $post->ID, '_'.$type.'_'.$field, true ))) echo " selected='selected'"; ?>><?php echo $value; ?></option>
                <?php } ?>	
              </select>
            <?php } else if ($datos['tipo'] == 'checkbox') { ?>
              <?php $results = get_post_meta( $post->ID, '_'.$type.'_'.$field, true ); ?>
              <?php foreach($datos['valores'] as $key => $value) { ?>
                <input type="checkbox" class="_<?php echo $type; ?>_<?php echo $field; ?>" id="_<?php echo $type; ?>_<?php echo $field; ?>" name="_<?php echo $type; ?>_<?php echo $field; ?>[]" value="<?php echo $key; ?>" <?php if(is_array($results) && in_array($key, $results)) { echo "checked='checked'"; } ?> /> <?php echo $value; ?><br/>
              <?php } ?>
            <?php } else if ($datos['tipo'] == 'map') { ?>
              <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false&key=<?php echo $datos['api_key']; ?>"></script>
              <script type="text/javascript">
                var geocoder;
                var map;
                var marker;
                function initialize() {
                  geocoder = new google.maps.Geocoder();
                  // Configuración del mapa
                  var mapProp = {
                    center: new google.maps.LatLng(<?php if(get_post_meta( $post->ID, '_'.$type.'_'.$field, true ) != '') echo get_post_meta( $post->ID, '_'.$type.'_'.$field, true ); else echo $datos['defaultcoords'] ?>),
                    zoom: <?php echo $datos['defaultzoom']; ?>,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                  };
                  map = new google.maps.Map(document.getElementById("_<?php echo $type; ?>_<?php echo $field; ?>_map"), mapProp);
                  // Creando un marker en el mapa
                  marker = new google.maps.Marker({
                    position: new google.maps.LatLng(<?php if(get_post_meta( $post->ID, '_'.$type.'_'.$field, true ) != '') echo get_post_meta( $post->ID, '_'.$type.'_'.$field, true ); else echo $datos['defaultcoords'] ?>),
                    map: map,
                    title: '<?php _e("Drag and drop", 'emprendedores'); ?>',
                    draggable: true //que el marcador se pueda arrastrar
                  });
                  // Registrando el evento drag, en este caso imprime checkbox
                  google.maps.event.addListener(marker,'drag',function(event) {
                    jQuery("#_<?php echo $type; ?>_<?php echo $field; ?>").val(event.latLng.lat()+","+event.latLng.lng());
                  });
                }

                function codeAddress() {
                  var address = document.getElementById('<?php echo $type; ?>_<?php echo $field; ?>_address').value;
                  geocoder.geocode( { 'address': address}, function(results, status) {
                    if (status == 'OK') {
                      map.setCenter(results[0].geometry.location);
                      marker.setPosition(results[0].geometry.location);
                      jQuery("#_<?php echo $type; ?>_<?php echo $field; ?>").val(marker.getPosition().lat()+","+marker.getPosition().lng());
                    } else {
                      alert('Geocode was not successful for the following reason: ' + status);
                    }
                  });
                }
                // Inicializando el mapa cuando se carga la página
                google.maps.event.addDomListener(window, 'load', initialize);
              </script>
              <div>
                <input id="<?php echo $type; ?>_<?php echo $field; ?>_address" type="textbox" placeholder="<?php _e('Write here the location', 'emprendedores'); ?>">
                <input type="button" value="<?php _e('Search location', 'emprendedores'); ?>" onclick="codeAddress()">
              </div>
              <div id="_<?php echo $type; ?>_<?php echo $field; ?>_map" style="width: 100%; height: 350px;"></div>
              <input  type="text" class="_<?php echo $type; ?>_<?php echo $field; ?>" id="_<?php echo $type; ?>_<?php echo $field; ?>" style="width: 100%;" name="_<?php echo $type; ?>_<?php echo $field; ?>" value="<?php echo get_post_meta( $post->ID, '_'.$type.'_'.$field, true ); ?>" />
          <?php } else if ($datos['tipo'] == 'repeater') { 
            $rest = get_post_meta( $post->ID, '_'.$type.'_'.$field, true ); 
            if(is_array($rest)) $rest_counter = count($rest); else $rest_counter = 1;
            if(isset($datos['min'])) $min = $datos['min']; else $min = 6; if(isset($datos['max'])) $max = $datos['max']; else $max = ($rest_counter < $min ? $min : ($rest_counter + 1)); ?>
            <style>
              .repeater {
                display: flex;
                align-items: center;
                flex-direction: column;
              }
              .repeater > div {
                display: flex;
                align-content: flex-start;
                align-items: flex-start;
                column-gap: 10px;
                justify-content: space-around;
                width: 100%;
                border-bottom: 1px solid #cecece;
                padding-bottom: 10px;
                margin-bottom: 10px;
              }

              .repeater > div > * {
                /*width: 33%;*/
              }

            </style>
            
            <div id="repeater_<?php echo $field; ?>" class="repeater">
              <?php for ($i = 0; $i < $max; $i ++) { ?>
                <div>
                  <?php foreach($datos['fields'] as $key => $subfields) { ?>
                    <?php if($subfields['tipo'] == 'image') { ?>
                      <input type="hidden" class="_<?php echo $type; ?>_<?php echo $field; ?>_<?php echo $key; ?>" id="input_<?php echo $type; ?>_<?php echo $field; ?>_<?php echo $key; ?>_<?php echo $i; ?>" style="width: 80%;" name="_<?php echo $type; ?>_<?php echo $field; ?>[<?php echo $i; ?>][<?php echo $key; ?>]" value="<?php echo (is_array($rest) ? $rest[$i][$key] : ""); ?>" placeholder="<?php echo $subfields['titulo']; ?>" />
                      <a href="#" id="button_media_<?php echo $field; ?>_<?php echo $key; ?>_<?php echo $i; ?>" class="button insert-media add_media" data-editor="input_<?php echo $type; ?>_<?php echo $field; ?>_<?php echo $key; ?>_<?php echo $i; ?>" title="<?php _e("Add image", 'emprendedores'); ?>"><span class="wp-media-buttons-icon"></span> <?php _e("Add image", 'emprendedores'); ?></a>
                      <div id="preview_media_<?php echo $field; ?>_<?php echo $key; ?>_<?php echo $i; ?>" style="max-width: 200px;"><?php echo wp_get_attachment_image($rest[$i][$key]); ?></div>
                      <style>#preview_media_<?php echo $field; ?>_<?php echo $key; ?>_<?php echo $i; ?>,
                      #preview_media_<?php echo $field; ?>_<?php echo $key; ?>_<?php echo $i; ?> img {
                        max-width: 150px;
                        width: 100%; 
                        height: auto;
                      }</style>
                      <script>
                        jQuery(document).ready(function () {			
                          jQuery("#input_<?php echo $type; ?>_<?php echo $field; ?>_<?php echo $key; ?>_<?php echo $i; ?>").change(function() {
                            console.log(jQuery(this).val());
                            imgid = jQuery(this).val().match(/wp-image-([^\"]+)/);
                            //img_url = jQuery(this).val().match(/<img[^>]+src=\"([^\"]+)\"/);
                            //console.log(img_iurl[1]);
                            jQuery("#preview_media_<?php echo $field; ?>_<?php echo $key; ?>_<?php echo $i; ?>").html("");
                            jQuery("#preview_media_<?php echo $field; ?>_<?php echo $key; ?>_<?php echo $i; ?>").html(jQuery(this).val());
                            jQuery(this).val(imgid[1]);
                          });
                        });
                      </script>
                      <?php } else if($subfields['tipo'] == 'file') { ?>
                      <input type="text" class="_<?php echo $type; ?>_<?php echo $field; ?>_<?php echo $key; ?>" id="input_<?php echo $type; ?>_<?php echo $field; ?>_<?php echo $key; ?>_<?php echo $i; ?>" style="width: 80%;" name="_<?php echo $type; ?>_<?php echo $field; ?>[<?php echo $i; ?>][<?php echo $key; ?>]" value="<?php echo (is_array($rest) ? $rest[$i][$key] : ""); ?>" placeholder="<?php echo $subfields['titulo']; ?>" />
                      <a href="#" id="button_media_<?php echo $field; ?>_<?php echo $key; ?>_<?php echo $i; ?>" class="button insert-media add_media" data-editor="input_<?php echo $type; ?>_<?php echo $field; ?>_<?php echo $key; ?>_<?php echo $i; ?>" title="<?php _e("Add file", 'emprendedores'); ?>"><span class="wp-media-buttons-icon"></span> <?php _e("Add file", 'emprendedores'); ?></a>
                      <script>
                        jQuery(document).ready(function () {			
                          jQuery("#input_<?php echo $type; ?>_<?php echo $field; ?>_<?php echo $key; ?>_<?php echo $i; ?>").change(function() {
                            a_imgurlar = jQuery(this).val().match(/<a href=\"([^\"]+)\"/);
                            img_imgurlar = jQuery(this).val().match(/<img[^>]+src=\"([^\"]+)\"/);
                            if(img_imgurlar !==null ) jQuery(this).val(img_imgurlar[1]);
                            else  jQuery(this).val(a_imgurlar[1]);
                          });
                        });
                      </script>
                      <?php } else if($subfields['tipo'] == 'textarea') { ?>
                        <?php $settings = array( 'media_buttons' => false, 'quicktags' => true, 'textarea_rows' => 5 );  ?>
                        <?php wp_editor($rest[$i][$key], '_'.$type.'_'.$field.'['.$i.']['.$key.']', $settings ); ?>
                      <?php } else if ($subfields['tipo'] == 'select') {  ?>
                        <select name="_<?php echo $type; ?>_<?php echo $field; ?>[<?php echo $i; ?>][<?php echo $key; ?>]">
                          <?php foreach($subfields['valores'] as $label => $value) { ?>
                            <option value="<?php echo $label; ?>"<?php if ($label == $rest[$i][$key]) echo " selected='selected'"; ?>><?php echo $value; ?></option>
                          <?php } ?>	
                        </select>
                      <?php } else { ?>
                        <input  type="text" class="_<?php echo $type; ?>_<?php echo $field; ?>[<?php echo $i; ?>][<?php echo $key; ?>]" id="_<?php echo $type; ?>_<?php echo $field; ?>_<?php echo $i; ?>_<?php echo $key; ?>" style="width: 100%;" name="_<?php echo $type; ?>_<?php echo $field; ?>[<?php echo $i; ?>][<?php echo $key; ?>]" value="<?php echo (is_array($rest) ? $rest[$i][$key] : ""); ?>" placeholder="<?php echo $subfields['titulo']; ?>" />
                      <?php } ?>
                    <?php } ?>
                    
                  </div>
                  
                <?php } ?>
              </div>
              <hr style="margin: 10px 0px;"/>
            <?php } ?>
          </div>
        <?php } ?>
      <?php } ?>
    <div style="clear: both;"></div>
	</div><?php
}

function wp_ekilan_save_custom_fields( $post_id ) { //Save changes
	global $wpdb;
  $type = get_post_type($post_id);
  $fields = get_wp_ekilan_custom_fields ($type);
  //print_r($fields); print_r($_POST); die;
	foreach ($fields as $field => $datos) {
		$label = '_'.$type.'_'.$field;
    if ($datos['tipo'] == 'repeater') { //Limpiamos vacios
			if(isset($_POST[$label])) {
		    $temp = array_remove_empty($_POST[$label]);
		    unset($_POST[$label]);
		    $_POST[$label] = array();
		    foreach($temp as $item) {
		      $_POST[$label][] = $item;
		    }
      }
    }
		if (isset($_POST[$label])) update_post_meta( $post_id, $label, $_POST[$label]);
		else if (!isset($_POST[$label]) && $datos['tipo'] == 'checkbox') delete_post_meta( $post_id, $label);
    else if (!isset($_POST[$label]) && $datos['tipo'] == 'multiple') delete_post_meta( $post_id, $label);
	}
}

function array_remove_empty($haystack) {
  foreach ($haystack as $key => $value) {
    if (is_array($value)) {
      $haystack[$key] = array_remove_empty($haystack[$key]);
    }
    if (empty($haystack[$key])) {
        unset($haystack[$key]);
    }
  }
  return $haystack;
}

// Libs ----------------------------------------
function sort_terms_hierarchically($terms) {
	if(count($terms) > 1) usort($terms, "cmp");
	return $terms;
}

function cmp($a, $b) {
	return strcmp($a->parent, $b->parent);
}
