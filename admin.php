<?php

//Administrador --------------------- 
add_action( 'admin_menu', 'wp_ekilan_plugin_menu' );
function wp_ekilan_plugin_menu() {
  add_submenu_page( 'edit.php?post_type=emprendedor-pregunta', __('Configuración', 'wp-ekilan'), __('Configuración', 'wp-ekilan'), 'manage_options', 'wp-ekilan', 'wp_ekilan_admin_page');
}

function wp_ekilan_admin_page() { 
  $langs = array("es" => "Castellano", "eu" => "Euskera");
  $settings = array( 'media_buttons' => true, 'quicktags' => true, 'textarea_rows' => 15 ); ?>
  <h1><?php _e("Configuración de cuestionario de Autodiagnóstico en competencias emprendedoras", 'wp-ekilan'); ?></h1>
  <a href="<?php echo get_admin_url(); ?>options-general.php?page=wp-ekilan&csv=true" class="button"><?php _e("Exportar a CSV", 'wp-ekilan'); ?></a>
  <?php if(isset($_REQUEST['send']) && $_REQUEST['send'] != '') { 
    ?><p style="border: 1px solid green; color: green; text-align: center;"><?php _e("Datos guardados correctamente.", 'wp-ekilan'); ?></p><?php
    update_option('_wp_ekilan_emails', $_POST['_wp_ekilan_emails']);
    foreach ($langs as $label => $lang) {
      update_option('_wp_ekilan_aviso_legal_'.$label, $_POST['_wp_ekilan_aviso_legal_'.$label]);
    }
  } ?>
  <form method="post">
    <b><?php _e("Emails a los que avisar de la descarga", 'wp-ekilan'); ?> <small>(<?php _e("Separados por comas", 'wp-ekilan'); ?>)</small>:</b><br/>
    <input type="text" name="_wp_ekilan_emails" value="<?php echo get_option("_wp_ekilan_emails"); ?>" style="width: calc(100% - 20px);" /><br/><br/>
    <?php foreach ($langs as $label => $lang) { ?>
      <b><?php _e("Aviso legal", 'wp-ekilan'); ?> <?php echo strtoupper($lang); ?>:</b><br/>  
      <?php wp_editor( stripslashes(get_option("_wp_ekilan_aviso_legal_".$label)), '_wp_ekilan_aviso_legal_'.$label, $settings ); ?><br/>
    <?php } ?>
    <br/><br/>
    <input type="submit" name="send" class="button button-primary" value="<?php _e("Guardar", 'wp-ekilan'); ?>" />
  </form>
<?php }

//Exportar a CSV ---------------------
function wp_ekilan_export_to_CSV() {
  if (isset($_GET['page']) && $_GET['page'] == 'wp-ekilan' && isset($_GET['csv']) && $_GET['csv'] == 'true') {
    $csv = "Fecha,Idioma,Respuestas"."\n";
		$f = fopen(__DIR__."/csv/autodiagnostico.csv", "a+");
    while (($datos = fgetcsv($f, 0, ",")) !== FALSE) {
      $csv .= "\"".implode('","', $datos)."\""."\n";
    }
    fclose($f);
		
		$now = gmdate("D, d M Y H:i:s");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download
		header("Content-Description: File Transfer");
		header("Content-Encoding: UTF-8");
		header("Content-Type: text/csv; charset=UTF-8");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename=autodiagnostico-emprendedores-".date("Y-m-d_His").".csv");
		header("Content-Transfer-Encoding: binary");
		echo $csv;
		die;
  }
}
add_action( 'admin_init', 'wp_ekilan_export_to_CSV', 1 );
