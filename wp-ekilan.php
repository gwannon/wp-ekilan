<?php

/**
 * Plugin Name: EKILAN - Autodiagnóstico en competencias emprendedoras
 * Description: Shortcode para montar un formulario de autodiagnóstico en competencias emprendedoras [emprendedores-preguntas]
 * Version:     1.0
 * Author:      jorge@enutt.net
 * Author URI:  https://enutt.net/
 * License:     GNU General Public License v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-ekilan
 *
 * PHP 8.2
 * WordPress 6.4.2
 */

define("DEBUG_ECHO", false);
define("DEBUG_EMAIL", true);

/* ----------- Multi-idioma ------------------ */
function wp_ekilan_plugins_loaded() {
	load_plugin_textdomain('wp-ekilan', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
}
add_action('plugins_loaded', 'wp_ekilan_plugins_loaded', 0 );

/* ----------- Includes ------------------ */
include_once(plugin_dir_path(__FILE__).'custom_posts.php');
include_once(plugin_dir_path(__FILE__).'emprendedores-preguntas.php');
include_once(plugin_dir_path(__FILE__).'admin.php');

/* ----------- Shortcode ------------------ */
function wp_ekilan_shortcode($params = array(), $content = null) {
  global $post;
  ob_start(); 
  $control = 0;
	$currentstep = 0;
	$responses = [];

  if(defined('ICL_LANGUAGE_CODE')) $current_lang = ICL_LANGUAGE_CODE;
  else $current_lang = get_bloginfo("language"); ?>
  <div id="emprendedores-preguntas">
		<?php if(isset($params['titulo'])) { ?>
			<h2>
				<?php echo apply_filters("the_title", $params['titulo']); ?>
			</h2>
		<?php } ?>
		<?php if(isset($_POST['enviar'])) {
			if(isset($_POST['responses'])) $responses = json_decode(stripslashes($_POST['responses']), true);
			if(isset($_POST['preguntas'])) foreach($_POST['preguntas'] as $id_pregunta => $pregunta) $responses[$id_pregunta] = $pregunta;
			$currentstep = $_POST['nextstep'];
			if($currentstep == 0) {
				$control = 1;

				//Guardar datos en CSV
				/*$f=fopen(__DIR__."/csv/autodiagnostico.csv", "a+");
				$csv[] = date("Y-m-d H:i:s");
				$csv[] = $current_lang;
				foreach($responses as $response) $csv[] = $response;
				fputcsv($f, $csv);
				fclose($f);*/

				//Conseguimos un access token no caducado con el refresh token
				$link = admin_url('admin-ajax.php')."?action=refresh-zohocrm";
				$response_api = wp_ekilan_curl_call_get($link);
				wp_ekilan_send_advise("Resultado de pedir un nuevo access_token", $response_api, $link, $responses, "", []);
				if(DEBUG_ECHO) { echo "<pre>PEDIMOS UN NUEVO TOKEN: "; print_r($response_api); echo "</pre>"; }

				//Generamos el PDF
				$filename = wp_ekilan_generate_pdf($responses);

				//Enviamos email de aviso al usuario
				if(isset($_POST['email']) && is_email($_POST['email'])) {
					$headers = [];
					$headers = array('Content-Type: text/html; charset=UTF-8');
					$message = sprintf(__('<table border="0" width="600" cellpadding="10" align="center" bgcolor="ffffff">
					<tbody>
					<tr><td><img src="%simages/logos.jpg" alt="" width="600"></td></tr>
					<tr>
					<td><span style="font-family: Arial; font-size: medium;">Hola,</span></td>
					</tr>
					<tr>
					<td><span style="font-family: Arial; font-size: medium;">Aquí tienes tu informe de "Autodiagnóstico en competencias emprendedoras".</span></td>
					</tr>
					<tr>
					<td><span style="font-family: Arial; font-size: medium;">Muchas gracias.</span></td>
					</tr>
					<tr>
					<td><span style="font-family: Arial; font-size: medium;">Un saludo</span></td>
					</tr>
					<tr>
					<td align="center"><span style="font-family: Arial; font-size: medium;"><a style="color: #000;" href="https://ekilan.asle.es/">ekilan.asle.es</a></span></td>
					</tr>
					</tbody>
					</table>', 'wp-ekilan'), plugin_dir_url( __FILE__ ));
					wp_mail($_POST['email'], __("Aquí tienes tu informe de \"Autodiagnóstico en competencias emprendedoras\"", 'wp-ekilan'), $message, $headers, plugin_dir_path(__FILE__).'pdf/'.$filename);
				}

				//Enviamos email de aviso a los admin
				$headers = [];
				$headers = array('Content-Type: text/html; charset=UTF-8');
				$emails = explode(",", get_option("_wp_ekilan_emails"));
				foreach($emails as $email) {
					wp_mail(chop($email), 
						"Aviso de cuestionario de \"Autodiagnóstico en competencias emprendedoras\" rellenado", 
						"<b>Cuestionario de \"Autodiagnóstico en competencias emprendedoras\" rellenado</b><br><br/>Nombre: ".$_POST['first_name']." ".$_POST['last_name']."<br/>Email: ".$_POST['email']."<br/>Respuestas: ".implode(", ", $responses), 
						$headers);
				} ?>
					<h4 class="emprendedores-preguntas-mensaje"><?php _e("Eskerrik asko! Gracias por completar el formulario.", "wp-ekilan"); ?></h4>
					<p style="color: #000;">
						<?php _e("Si no visualiza correctamente el informe en formato PDF en su navegador, aplicación de ordenador, tablet, dispositivo móvil, etc., recomendamos que instale el programa Adobe Acrobat Reader (Software gratuito para visualizar documentos en formato PDF). Puede descargarlo en: <a href='https://get.adobe.com/es/reader/' target='_blank'>https://get.adobe.com/es/reader/</a>.", "wp-ekilan"); ?>
					</p>
				<?php
				/* --------- CONEXIÓN CON ZOHOCRM -------------- */
				if(isset($response_api->access_token) && $response_api->access_token != '') {
					//Chequeamos que si existe el lead
					sleep(1);
					$curl = curl_init();
					$headers = [];
					$headers[] = 'Authorization: Zoho-oauthtoken '.$response_api->access_token;
					$link = get_option('_wp_ekilan_api_domain')."/crm/v7/Leads/search?email=".$_POST['email'];
					curl_setopt($curl, CURLOPT_URL, $link);
					curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					$response = curl_exec($curl);
					$json = json_decode($response);
					wp_ekilan_send_advise("Resultado de búsqueda del email ".$_POST['email'], $response, $link, $responses, "", $headers);
					if(DEBUG_ECHO) { echo "<pre>CHEQUEAMOS SI EXISTE EL LEAD: "; print_r($json); echo "</pre>"; }
					if(isset($json->data[0]->id) && $json->data[0]->id != '') { //Si existe nos guardamos el ID
						$lead_id = $json->data[0]->id;
					} else { //Si no existe lo creamos
						$payload = [];
						$payload['data'][] = [
							"Last_Name" => $_POST['last_name'],
							"First_Name" => $_POST['first_name'],
							"Email" => $_POST['email']
						];
						$curl = curl_init();
						$headers = [];
						$headers[] = 'Content-Type: application/json';
						$headers[] = 'Authorization: Zoho-oauthtoken '.$response_api->access_token;
						$link = get_option('_wp_ekilan_api_domain')."/crm/v7/Leads";
						curl_setopt($curl, CURLOPT_URL, $link);
						curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
						curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
						$response = curl_exec($curl);
						$json = json_decode($response);
						if(DEBUG_ECHO) { echo "<pre>INSERTAMOS EL LEAD: "; print_r($json); echo "</pre>"; }
						if(isset($json->data[0]->status) && $json->data[0]->status == 'success') {
							wp_ekilan_send_advise("EXITO al insertar Posible Cliente en ZohoCRM", $json, $link, $responses, $payload, $headers);
							$lead_id = $json->data[0]->details->id;
						} else {
							wp_ekilan_send_advise("ERROR al insertar Posible Cliente en ZohoCRM", $json, $link, $responses, $payload, $headers);
						}
					}

					//Si tenemos un lead id metemos la nota y la etiqueta
					if(isset($lead_id) && $lead_id != '') {
						//Metemos la etiqueta
						sleep(1);
						$payload = [];
						$payload['tags'][] = [
							"name" => "Ekilan TESTautoevaluación",
							"id" => "530022000010808001",
							"color_code" => "#D297EE"
						];
						$curl = curl_init();
						$headers = [];
						$headers[] = 'Content-Type: application/json';
						$headers[] = 'Authorization: Zoho-oauthtoken '.$response_api->access_token;
						$link = get_option('_wp_ekilan_api_domain')."/crm/v7/Leads/".$lead_id."/actions/add_tags";
						curl_setopt($curl, CURLOPT_URL, $link);
						curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
						curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
						$response = curl_exec($curl);
						$json = json_decode($response);
						if(DEBUG_ECHO) { echo "<pre>INSERTAMOS LA ETIQUETA: "; print_r($json); echo "</pre>"; }
						if(isset($json->data[0]->status) && $json->data[0]->status == 'success') {
							wp_ekilan_send_advise("EXITO al insertar ETIQUETA en ZohoCRM", $json, $link, $responses, $payload, $headers);
						} else {
							wp_ekilan_send_advise("ERROR al insertar ETIQUETA en ZohoCRM", $json, $link, $responses, $payload, $headers);
						}


						//Metemos la nota
						sleep(1);
						$payload = [];
						$payload['data'][] = [
							"Note_Title" => "Respuestas TEST AUTOEVALUA",
							"Note_Content" => implode(", ", $responses)
						];
						$curl = curl_init();
						$headers = [];
						$headers[] = 'Content-Type: application/json';
						$headers[] = 'Authorization: Zoho-oauthtoken '.$response_api->access_token;
						$link = get_option('_wp_ekilan_api_domain')."/crm/v7/Leads/".$lead_id."/Notes";
						curl_setopt($curl, CURLOPT_URL, $link);
						curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
						curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
						$response = curl_exec($curl);
						$json = json_decode($response);
						if(DEBUG_ECHO) { echo "<pre>INSERTAMOS LA NOTA: "; print_r($json); echo "</pre>"; }
						if(isset($json->data[0]->status) && $json->data[0]->status == 'success') {
							wp_ekilan_send_advise("EXITO al insertar Nota en ZohoCRM", $json, $link, $responses, $payload, $headers);
						} else {
							wp_ekilan_send_advise("ERROR al insertar Nota en ZohoCRM", $json, $link, $responses, $payload, $headers);
						} 
					}
				}
			}
		} ?>
		<?php if($control == 0) { ?> 
			<?php if(isset($content)) { ?>
    		<div>
      		<?php echo apply_filters("the_content", $content); ?>
    		</div>
			<?php } ?>
			<form id="emprendedores-preguntas-form" method="post" action="<?php echo get_the_permalink(); ?>#emprendedores-preguntas">
				<?php
					$sections = get_terms( array(
						'taxonomy'   => 'test',
						'hide_empty' => true,
						'orderby' => 'slug',
						'order' => 'ASC'
					));
					$steps = '<ul class="steps">';
					foreach($sections as $index => $section) {
						if($currentstep == $index) $steps .= "<li class='current'></li>";
						else $steps .= "<li></li>";
					}
					$steps .= "</ul>";
					echo $steps;

					foreach($sections as $index => $section) {
						if($currentstep == $index) { $nextstep = $index + 1;
							echo "<h3>".$section->name."</h3>";
							
							$args = array(
								'post_type' => 'emprendedor-pregunta',
								'posts_per_page' => -1,
								'post_status' => 'publish',
								'tax_query' => array(
									array (
											'taxonomy' => 'test',
											'field' => 'term_id',
											'terms' => $section->term_id,
									)
								),
								'orderby' => 'menu_order',
								'order' => 'ASC'
							);
						
							$the_query = new WP_Query( $args);
							if ($the_query->have_posts()) {
								while ($the_query->have_posts()) { $the_query->the_post(); ?>
								<h4><?=get_the_title();?></h4>
								<?php foreach(array('a', 'b', 'c') as $letra) { ?>
									<label><input type="radio" name="preguntas[<?=get_the_id();?>]" value="<?=$letra;?>"<?=($letra == 'a' ? " required" : "");?>> <?=get_post_meta(get_the_id(), '_emprendedor-pregunta_respuesta-'.$letra, true );?> <!-- <?=get_post_meta(get_the_id(), '_emprendedor-pregunta_valor-'.$letra, true );?> --></label>
								<?php } ?>
							<?php } } wp_reset_query(); ?>
						<?php if(!isset($sections[$nextstep])) { ?>
							<h4><?php _e("Datos personales", 'wp-ekilan'); ?></h4>
							<label><b><?php _e("Nombre", 'wp-ekilan'); ?>*</b><br/>
							<input type="text" name="first_name" placeholder='Nombre *' value='' required></label><br/>
							<label><b><?php _e("Apellidos", 'wp-ekilan'); ?>*</b><br/>
							<input type="text" name="last_name" placeholder='Apellidos *' value='' required></label><br/>
							<label><b><?php _e("Email", 'wp-ekilan'); ?>*</b><br/>
							<input type="email" name="email" placeholder='email@dominio.com' value='' required></label><br/>
							<p><?php _e("Recuerda revisar el email porque será a donde mandemos el informe con tus resultados.", 'wp-ekilan'); ?></p>
							<div class="legal">
								<?php echo stripslashes(get_option("_wp_ekilan_aviso_legal_".$current_lang)); ?>
							</div>
						<?php } ?>
						<input type="hidden" name="responses" value='<?=json_encode($responses);?>'>
						<input type="hidden" name="currentstep" value="<?=$index;?>">
						<input type="hidden" name="nextstep" value="<?php  echo (isset($sections[$nextstep]) ? $nextstep : "0"); ?>"> 
						<input  type="submit" name="enviar" value="<?php echo (isset($sections[$nextstep]) ? __("Continuar", "wp-ekilan") : __("Enviar", "wp-ekilan")); ?>">
					<?php break; } ?> 	
				<?php } ?>
			</form>
		<?php } ?>
	</div>
  <style>  
  	#emprendedores-preguntas-form label {
  		display: block;
  		position: relative;
  		padding-left: 30px;
  	}

		#emprendedores-preguntas-form label:has(input[type=email]),
		#emprendedores-preguntas-form label:has(input[type=text]) {
			padding-left: 0;
		}
  	
  	#emprendedores-preguntas-form label input[type=radio] {
  		position: absolute;
  		left: 0px;
  		top: 3px;
  	
  	}

		#emprendedores-preguntas-form label input[type=email],
		#emprendedores-preguntas-form label input[type=text] {
			width: 100%;
			display: block;
		}
  	
  	#emprendedores-preguntas-form .legal {
  		padding: 10px;
  		height: 50px;
  		border: 1px solid #cecece;
  		background-color: #dfdfdf;
  		overflow: auto;
  	}
  	
  	#emprendedores-preguntas-form label + h4 {
  		margin-top: 30px;
  	}
  	
  	#emprendedores-preguntas-form input[type=submit] {
  		margin-top: 30px;
  	
  	}

		#emprendedores-preguntas a.download {
			display: inline-block;
			margin: 10px auto;
			padding: 20px;
			background-color: red;
			color: white;
			font-weight: bold;
			text-decoration: none;
		}

		/* Steps */
		#emprendedores-preguntas ul.steps {
			--black-color: #000;
			--size: 25px;
			margin: 0px;
			list-style-type: none;
			display: flex;
			flex-wrap: wrap;
			counter-reset: my-counter;
			flex-direction: column;
			align-items: stretch;
			justify-content: center;
			align-content: center;
		}

		@media (max-width: 599px) {
			#emprendedores-preguntas ul.steps li br {
				display: none;
			}
		}

		@media (min-width: 600px) {
			#emprendedores-preguntas ul.steps {
				flex-direction: row;
			}
		}

		#emprendedores-preguntas ul.steps li {
			margin: 0;
			list-style: none;
			display: flex;
			align-items: end;
			color: var(--color-content-text);
			padding: 0px 20px 5px 0px;
			position: relative;
			font-size: 12px;
			line-height: 12px;
			min-width: 80px;
			margin-bottom: 40px;
			counter-increment: my-counter;
			padding-left: 50px;
		}

		@media (min-width: 600px) {
			#emprendedores-preguntas ul.steps li {
				padding-left: 0px;
				margin-bottom: 60px;
			}
		}

		#emprendedores-preguntas ul.steps li:last-child {
			min-width: 0px;
			padding: 0px 0px 5px 50px;
		}

		@media (min-width: 600px) {
			#emprendedores-preguntas ul.steps li:last-child {
				padding: 0px 0px 5px 0px;
				margin-bottom: 60px;
			}
		}

		#emprendedores-preguntas ul.steps li:before {
			position: absolute;
			content: counter(my-counter);
			color: var(--color-content-text);
			background-color: var(--color-content-link);
			display: flex;
			width: 40px;
			height: 40px;
			border-radius: 50%;
			bottom: -8px;
			left: 0px;
			font-size: 20px;
			font-weight: 700;
			justify-content: center;
			align-items: center;
		}

		@media (min-width: 600px) {
			#emprendedores-preguntas ul.steps li:before {
				bottom: -40px;
			}
		}

		#emprendedores-preguntas ul.steps li.current:before {
			background-color: var(--color-content-text);
			color: var(--color-content-link);
		}

		#emprendedores-preguntas ul.steps li:after {
			position: absolute;
			content: "";
			background-color: var(--color-content-link);
			display: none;
		}

		@media (min-width: 600px) {
			#emprendedores-preguntas ul.steps li:after {
				display: block;
				width: calc(100% - 40px);
				height: 2px;
				bottom: -20px;
				left: 40px;
				font-size: 20px;
				font-weight: 700;
			}
		}

		#emprendedores-preguntas ul.steps li:last-of-type:after {
			display: none;
		}

		#emprendedores-preguntas ul.steps li.current:after {
			background-color: var(--black-color);
		}
	</style>
  <?php return ob_get_clean();
}
add_shortcode('emprendedores-preguntas', 'wp_ekilan_shortcode');

function wp_ekilan_generate_pdf($responses) {
	require_once __DIR__ . '/vendor/autoload.php';

	$filename = "EKILAN-".__("cuestionario-autodiagnostico-competencias-emprendedoras", 'wp-ekilan')."-".hash("md5", implode("", $responses).date("YmdHis")).".pdf";

	$conclusions = [ 
		//Generales
		[
			"3" => __("En función de las respuestas obtenidas, de acuerdo al Marco Europeo de Competencias de Emprendimiento (EntreComp), te damos la enhorabuena por los resultados:", 'wp-ekilan'),
			"2" => __("En función de las respuestas obtenidas, de acuerdo al Marco Europeo de Competencias de Emprendimiento (EntreComp), puedes mejorar las habilidades medidas en los cuatro grupos principales:", 'wp-ekilan'),
			"1" => __("En función de las respuestas obtenidas, de acuerdo al Marco Europeo de Competencias de Emprendimiento (EntreComp), recomendamos que mejores las habilidades medidas en los cuatro grupos principales:", 'wp-ekilan')
		],
		//Bloque 1
		[
			"3" => __("Enhorabuena por tus habilidades emprendedoras en la parte de ideas y oportunidades. Sácale todo el potencial que puedas a este conjunto de habilidades.", 'wp-ekilan'),
			"2" => __("De acuerdo a los resultados obtenidos en el primer grupo de preguntas, puedes mejorar tus habilidades emprendedoras en la parte de ideas y oportunidades.", 'wp-ekilan'),
			"1" => __("De acuerdo a los resultados obtenidos en el primer grupo de preguntas, recomendamos que mejores tus habilidades emprendedoras en la parte de ideas y oportunidades.", 'wp-ekilan')
		],
		//Bloque 2
		[
			"3" => __("Enhorabuena por tus habilidades emprendedoras en el segundo grupo de respuestas, en del área de recursos.", 'wp-ekilan'),
			"2" => __("En el segundo grupo de habilidades emprendedoras en el área de recursos tienes un potencial que con experiencia y formación seguro que vas a mejorar.", 'wp-ekilan'),
			"1" => __("De acuerdo a los resultados obtenidos en el segundo grupo de preguntas, recomendamos que mejores tus habilidades emprendedoras en la parte de recursos.", 'wp-ekilan')
		],
		//Bloque 3
		[
			"3" => __("Enhorabuena por tus habilidades emprendedoras para pasar a la acción, minimizando riesgos. Sácale todo el potencial que puedas a este conjunto de habilidades.", 'wp-ekilan'),
			"2" => __("En el tercer grupo de habilidades emprendedoras para pasar a la acción, minimizando riesgos, tienes un potencial que con experiencia y formación puedes mejorar.", 'wp-ekilan'),
			"1" => __("De acuerdo a los resultados obtenidos en el tercer grupo de preguntas, recomendamos que mejores tus habilidades emprendedoras para pasar a la acción, minimizando riesgos.", 'wp-ekilan')
		],
		//Bloque 4
		[
			"3" => __("Enhorabuena por tus habilidades para emprender en equipo. Sácale todo el potencial que puedas a este conjunto de habilidades y mucho ánimo con tus proyectos colaborativos.", 'wp-ekilan'),
			"2" => __("En el cuarto grupo de habilidades relacionadas con la predisposición para emprendimiento colectivo, tienes un potencial interesante que puedes mejorar.", 'wp-ekilan'),
			"1" => __("Tus habilidades emprendedoras en la parte de predisposición para emprendimiento colectivo se pueden mejorar. ¡Ánimo con ello!", 'wp-ekilan')
		],
	];


	$mpdf = new \Mpdf\Mpdf([
		'format' => 'A4',
		//'margin_header' => 30,     // 30mm not pixel
		//'margin_footer' => 30,     // 10mm
		'setAutoBottomMargin' => 'pad',
		'setAutoTopMargin' => 'pad',
		'fontDir' => __DIR__ . '/fonts/',
		'fontdata' => [
			'dosis' => [
					'R' => 'Dosis-Regular.ttf'
			],
			'roboto' => [
				'R' => 'Roboto-Light.ttf',
				'I' => 'Roboto-LightItalic.ttf'
			]
		],
		'default_font' => 'dosis'
	]);

	$mpdf->SetHeader("");
	$mpdf->SetFooter("");
	$mpdf->AddPage();

	//Generamos el HTML
	$htmlsections = "";
	$htmlconclusions = "";
	$conclusionscounter = 1;
	$html = "<table border='0' width='100%' cellpadding='5'><tr><td><img src='".plugin_dir_url( __FILE__ )."images/logos.jpg' alt=''></td></tr></table>";
	$html .= "<h1>".__("Cuestionario de \"Autodiagnóstico en competencias emprendedoras\"", 'wp-ekilan')."</h1>";
	$html .= "<p>".__("Una vez cumplimentado el test de autodiagnóstico de competencias para emprender basado en el marco europeo de competencias de emprendimiento (EntreComp), mostramos las respuestas que has elegido y un pequeño resumen de la información basada en el ámbito del emprendimiento en Europa.", 'wp-ekilan')."</p>";
	$sections = get_terms( array(
		'taxonomy'   => 'test',
		'hide_empty' => true,
		'orderby' => 'slug',
		'order' => 'ASC'
	));
	$counter_responses_total = ['3' => 0, '2' => 0, '1' => 0];
	foreach($sections as $index => $section) {
		$html .= "<h2>".$section->name."</h2>";
		$args = array(
			'post_type' => 'emprendedor-pregunta',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'tax_query' => array(
				array (
						'taxonomy' => 'test',
						'field' => 'term_id',
						'terms' => $section->term_id,
				)
			),
			'orderby' => 'menu_order',
			'order' => 'ASC'
		);
		$counter_responses_section = ['3' => 0, '2' => 0, '1' => 0];
		$the_query = new WP_Query( $args);
		if ($the_query->have_posts()) {
			while ($the_query->have_posts()) { $the_query->the_post(); $post_id = get_the_id();
				$html .= "<hr><h3>".get_the_title()."</h3>";
				$letter_points = get_post_meta(get_the_id(), '_emprendedor-pregunta_valor-'.$responses[$post_id], true );
				$counter_responses_total[$letter_points]++;
				$counter_responses_section[$letter_points]++;
				foreach(array('a', 'b', 'c') as $letra) { 
					if($letra != 'a') $html .= "<tr>";
					$html .= "<p style='padding: 5px; ".($letra == $responses[$post_id] ? " color: white; background-color: black;" : "")."'>".$letra.") ".get_post_meta(get_the_id(), '_emprendedor-pregunta_respuesta-'.$letra, true )."</p>";
				}
				

				$html .= "<table cellpadding='10' style='background-color: #cecece;'><tr><td>".get_the_content()."</td><td width='200'><img src='".plugin_dir_url( __FILE__ )."images/".$letter_points.".png' width='150'></td></tr></table>";
			} 
		} wp_reset_query();
		//echo "<pre>"; print_r($counter_responses_section); echo "</pre>";
		$maxs = array_keys($counter_responses_section, max($counter_responses_section));

		$term_meta = get_option( "taxonomy_".$section->term_id);

		$htmlsections .= "<p><b>".$term_meta['texto_resumen_pdf']."</b></p>";
		$htmlsections .= "<table cellpadding='10' width='100%' style='background-color: #cecece; border: 1px solid #000;'><tr><td><h2>".$section->description."</h2></td><td width='200'><img src='".plugin_dir_url( __FILE__ )."images/".$maxs[0].".png' width='150'></td></tr></table>";
		$htmlsections .= stripslashes($term_meta['enlaces_resumen_pdf'])."<br/>";
		$htmlconclusions .= "<li>".$conclusions[$conclusionscounter][$maxs[0]]."</li>";
		$conclusionscounter++;
		$html .= "<hr/><table cellpadding='10' width='100%' style='background-color: #cecece; border: 1px solid #000;'><tr><td><h2>".$section->description."</h2></td><td width='200'><img src='".plugin_dir_url( __FILE__ )."images/".$maxs[0].".png' width='150'></td></tr></table><hr/>";
		
	}
	$mpdf->WriteHTML($html);
	$html = ""; 
	$mpdf->AddPage();

	$html .= "<h1>".__("Conclusiones del cuestionario de \"Autodiagnóstico en competencias emprendedoras\":", 'wp-ekilan')."</h1>";
	$html .= "<p>".__("Este informe presenta el perfil como persona emprendedora en base a las respuestas al cuestionario de autodiagnóstico online cumplimentado.", 'wp-ekilan')."</p>";
	$html .= "<p>".__("Las competencias emprendedoras se refieren a los análisis de ideas, oportunidades, recursos, habilidades y predisposición para emprender de forma individual o en equipo.", 'wp-ekilan')."</p>";
	$html .= "<p>".__("Este perfil emprendedor se basa en la estructura del Marco Europeo de Competencias de Emprendimiento (EntreComp).", 'wp-ekilan')."</p>";

	$html .= $htmlsections;

	$maxs = array_keys($counter_responses_total, max($counter_responses_total));

	/*$mpdf->WriteHTML($html);
	$html = ""; 
	$mpdf->AddPage();*/

	//$html .= "<h2 style='text-align: center;'>".__("En función de las respuestas obtenidas, de acuerdo al Marco Europeo de Competencias de Emprendimiento (EntreComp), la valoración media es de:", "wp-ekilan")."</h2>";
	$html .= "<h2 style='text-align: center;'>".$conclusions[0][$maxs[0]]."</h2>";
	$html .= "<ul>".$htmlconclusions."</ul>";
	
	
	
	
	$html .= "<p style='text-align: center;'><img src='".plugin_dir_url( __FILE__ )."images/".$maxs[0].".png' width='250'></p>";
	$html .= "<p>".__("Este perfil es el resumen del conjunto de respuestas aportadas dentro del Marco Europeo de Competencias de Emprendimiento (EntreComp). Recomendamos contrastar estas respuestas con la documentación complementaria que las entidades que colaboran en este informe facilitan para completar y mejorar las aptitudes emprendedoras.", "wp-ekilan")."</p>";
	$html .= "<p>".__("Gracias por participar.", "wp-ekilan")."</p>";
	
	//Guardamos el PDF
	$mpdf->WriteHTML($html);
	$mpdf->SetTitle(__("Cuestionario de \"Autodiagnóstico en competencias emprendedoras\"", 'wp-ekilan'));
	$mpdf->SetAuthor("ASLE · Asociación empresarial sin ánimo de lucro");	
	$mpdf->Output(plugin_dir_path(__FILE__).'pdf/'.$filename,'F');
	return $filename;

}

function wp_ekilan_send_advise($title, $json, $link, $responses, $payload = false, $api_headers = []) {
	if(DEBUG_EMAIL) {
		$headers = [];
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$emails = explode(",", get_option("_wp_ekilan_emails"));
		foreach($emails as $email) {
			wp_mail(chop($email), $title, ($payload != false ? "Payload".json_encode($payload)."<br>" : "").
				"Enlace: ".$link."<br><br/>".
				"Respuesta del servidor: ".json_encode($json)."<br><br/>".
				"Nombre: ".$_POST['first_name']." ".$_POST['last_name']."<br/>Email: ".$_POST['email']."<br/>".
				"Respuestas: ".implode(", ", $responses)."<br/>".
				"API Headers: ".implode(" |", $api_headers), 
			$headers);
		}
	}
}
