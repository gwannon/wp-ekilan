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
    update_option('_wp_ekilan_client_id', $_POST['_wp_ekilan_client_id']);
    update_option('_wp_ekilan_client_secret', $_POST['_wp_ekilan_client_secret']);
    update_option('_wp_ekilan_redirect_url', $_POST['_wp_ekilan_redirect_url']);
    update_option('_wp_ekilan_scope', $_POST['_wp_ekilan_scope']);

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



    <hr/>
    <b><?php _e("Client ID", 'wp-ekilan'); ?>:</b><br/>
    <input type="text" name="_wp_ekilan_client_id" value="<?php echo get_option("_wp_ekilan_client_id"); ?>" style="width: calc(100% - 20px);" /><br/><br/>
    <b><?php _e("Client Secret", 'wp-ekilan'); ?>:</b><br/>
    <input type="text" name="_wp_ekilan_client_secret" value="<?php echo get_option("_wp_ekilan_client_secret"); ?>" style="width: calc(100% - 20px);" /><br/><br/>
    <b><?php _e("Redirect URL", 'wp-ekilan'); ?>:</b><br/>
    <input type="text" name="_wp_ekilan_redirect_url" value="<?php echo get_option("_wp_ekilan_redirect_url"); ?>" style="width: calc(100% - 20px);" /><br/><br/>
    
    <b><?php _e("Scope", 'wp-ekilan'); ?>:</b><br/>
    <input type="text" name="_wp_ekilan_scope" value="<?php echo get_option("_wp_ekilan_scope"); ?>" style="width: calc(100% - 20px);" /><br/><br/>

    <br/><br/>
    <input type="submit" name="send" class="button button-primary" value="<?php _e("Guardar", 'wp-ekilan'); ?>" />
  </form>
  <hr/>

  <a class="button" href="https://accounts.zoho.eu/oauth/v2/auth?scope=<?php echo get_option('_wp_ekilan_scope'); ?>&client_id=<?php echo get_option("_wp_ekilan_client_id"); ?>&response_type=code&access_type=offline&redirect_uri=<?php echo get_option("_wp_ekilan_redirect_url"); ?>">Solicitar TOKEN</a><br/>
  Token: <?php echo get_option("_wp_ekilan_code"); ?><br/>
  Location: <?php echo get_option("_wp_ekilan_location"); ?><br/>
  Account-server: <?php echo get_option("_wp_ekilan_accounts-server"); ?><br/><br/>

  TEST:<br/>
  
  Access_token: <?php echo get_option('_wp_ekilan_access_token'); ?><br/>
  Refresh_token: <?php echo get_option('_wp_ekilan_refresh_token'); ?><br/>
  Scope: <?php echo get_option('_wp_ekilan_scope'); ?><br/>
  API_domain: <?php echo get_option('_wp_ekilan_api_domain'); ?><br/>
  Token_type: <?php echo get_option('_wp_ekilan_token_type'); ?><br/>


  <?php 




  //TEST INSERT LEAD -------------------------- 
  /*
  $payload['data'][] = [
    "Last_Name" => "Monclus",
    "First_Name" => "Jorge",
    "Email" => "jorge@enutt.net"
  ];

  echo json_encode($payload);
  unset($headers);
  $curl = curl_init();
  $headers[] = 'Content-Type: application/json';
  $headers[] = 'Authorization: Zoho-oauthtoken '.get_option('_wp_ekilan_access_token');
  curl_setopt($curl, CURLOPT_URL, get_option('_wp_ekilan_api_domain')."/crm/v7/Leads");
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
  $response = curl_exec($curl);
  $json = json_decode($response);
  echo "<pre>";
  print_r($json);
  echo "</pre>";*/


  //TEST INSERT COMMENT --------------------
  /*$lead_id = "513177000001121002";

  $payload['data'][] = [
    "Note_Content" => "Respuestas TEST AUTOEVALUA: abcabcabc"
  ];

  echo json_encode($payload);

  $curl = curl_init();
  $headers[] = 'Content-Type: application/json';
  $headers[] = 'Authorization: Zoho-oauthtoken '.get_option('_wp_ekilan_access_token');
  curl_setopt($curl, CURLOPT_URL, get_option('_wp_ekilan_api_domain')."/crm/v7/Leads/{$lead_id}/Notes");
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
  $response = curl_exec($curl);
  $json = json_decode($response);
  echo "<pre>";
  print_r($json);
  echo "</pre>";*/



  //TEST GET LEADS -------------------------------
  unset($headers);
  $curl = curl_init();
  $headers[] = 'Authorization: Zoho-oauthtoken '.get_option('_wp_ekilan_access_token');
  curl_setopt($curl, CURLOPT_URL, get_option('_wp_ekilan_api_domain')."/crm/v7/Leads?fields=First_Name,Last_Name,Email");
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($curl);
  $json = array_slice(json_decode($response)->data, 0, 3);
  echo "<pre>";
  print_r($json);
  echo "</pre>";
}


add_action( 'wp_ajax_zohocrm', 'wp_ekilan_action_zohocrm' );
function wp_ekilan_action_zohocrm() {
  update_option('_wp_ekilan_code', $_GET['code']);
  update_option('_wp_ekilan_location', $_GET['location']);
  update_option('_wp_ekilan_accounts-server', $_GET['accounts-server']);
  //echo "<pre>";
  //print_r($_GET);
  //echo "</pre>";

  $link = get_option("_wp_ekilan_accounts-server")."/oauth/v2/token";
  $payload = [
    "grant_type" => "authorization_code",
    "client_id" => get_option("_wp_ekilan_client_id"),
    "client_secret" => get_option("_wp_ekilan_client_secret"),
    "redirect_uri" => get_option("_wp_ekilan_redirect_url"),
    "code" => get_option("_wp_ekilan_code")
  ];
  $response = wp_ekilan_curl_call($link, $payload);
  //echo "<pre>";
  //print_r($payload);
  //print_r($response);
  //echo "</pre>";
  
  update_option('_wp_ekilan_access_token', $response->access_token);
  update_option('_wp_ekilan_refresh_token', $response->refresh_token);
  //update_option('_wp_ekilan_scope', $response->scope);
  update_option('_wp_ekilan_api_domain', $response->api_domain);
  update_option('_wp_ekilan_token_type', $response->token_type);
  wp_redirect ("https://pruebas.enuttisworking.com/wp-admin/edit.php?post_type=emprendedor-pregunta&page=wp-ekilan");
  wp_die();
}


//https://pruebas.enuttisworking.com/wp-admin/admin-ajax.php?action=refresh-zohocrm
add_action( 'wp_ajax_refresh-zohocrm', 'wp_ekilan_action_refresh_zohocrm' );
function wp_ekilan_action_refresh_zohocrm() {
  $link = get_option("_wp_ekilan_accounts-server")."/oauth/v2/token";
  $payload = [
    "grant_type" => "refresh_token",
    "client_id" => get_option("_wp_ekilan_client_id"),
    "client_secret" => get_option("_wp_ekilan_client_secret"),
    "refresh_token" => get_option("_wp_ekilan_refresh_token")
  ];
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $link);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
  $response = curl_exec($curl);
  echo $response;
  update_option('_wp_ekilan_access_token', json_decode($response)->access_token);
  wp_die();
}



function wp_ekilan_curl_call($link, $payload = false) {
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $link);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
  $response = curl_exec($curl);
  $json = json_decode($response);
  $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  if (in_array($httpcode, array(200, 201, 204))) {
    return $json;
  } else {
    return false;
  }
}



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
