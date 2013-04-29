<?php
// Edited by DWand. 05.02.2012. Replacing captcha.
// Replace standard VeriWord class with Securimage
include_once("config.inc.php");
include_once(dirname(dirname(__FILE__))."/media/securimage/securimage.php");

// Author DWand
// 05.02.2012
class VeriWord extends Securimage {
	public function __construct($width = 0, $height = 0) {
		global $database_server, $database_user, $database_password, $dbase, $table_prefix;
		startCMSSession();
		
		$params = array();
		if (mysql_connect($database_server, $database_user, $database_password) &&
 		    mysql_select_db(substr($dbase,1,-1))) {
			
			if (function_exists('mysql_real_escape_string')) $tp = mysql_real_escape_string($table_prefix);
			else $tp = mysql_escape_string($table_prefix);
			
			$sql = 'SELECT * FROM `'.$tp.'system_settings` WHERE setting_name IN (
			"captcha_image_width",
			"captcha_image_height",
			"captcha_image_type",
			"captcha_image_bg_color",
			"captcha_text_color",
			"captcha_line_color",
			"captcha_noise_color",
			"captcha_text_transparency_percentage",
			"captcha_use_transparent_text",
			"captcha_code_length",
			"captcha_case_sensitive",
			"captcha_charset",
			"captcha_perturbation",
			"captcha_num_lines",
			"captcha_noise_level",
			"captcha_image_signature",
			"captcha_signature_color",
			"captcha_type");';
			$query = mysql_query($sql);
			while ($result = mysql_fetch_row($query)) {
				switch ($result[0]) {
				case "captcha_image_width":
				case "captcha_image_height":
				case "captcha_image_type":
				case "captcha_text_transparency_percentage":
				case "captcha_use_transparent_text":
				case "captcha_code_length":
				case "captcha_case_sensitive":
				case "captcha_num_lines":
				case "captcha_noise_level":
				case "captcha_type":
					$result[1] = (int)$result[1];
					break;
					
				case "captcha_perturbation":
					$result[1] = (float)$result[1];
					break;
					
				case "captcha_image_bg_color":
				case "captcha_text_color":
				case "captcha_line_color":
				case "captcha_noise_color":
				case "captcha_signature_color":
					if (substr($result[1],0,1) == "#") {
						$result[1] = strtolower($result[1]);
						if (!preg_match("/^#[a-f\d]{6}$/",$result[1]) && 
							!preg_match("/^#[a-f\d]{3}$/",$result[1])) unset($result);
					} else if (preg_match("/^([\d]{1,3})[\s]*,[\s]*([\d]{1,3})[\s]*,[\s]*([\d]{1,3})[,\s]*$/", $result[1], &$matches)) {
						unset($result[1]);
						$result[1] = array();
						$result[1][0] = $matches[1];
						$result[1][1] = $matches[2];
						$result[1][2] = $matches[3];
					} else unset($result);
					break;
				
				case "captcha_charset":
					if (strlen($result[1]) == 0) {
						unset($result);
						break;
					}
				case "captcha_image_signature":
					$result[1] = stripslashes($result[1]);
					break;
				
				default:
					unset($result);
					break;
				}
				if (isset($result)) $params[substr($result[0],8)] = $result[1];
			}
			if (isset($params['type'])) {
				$params['captcha_type'] = $params['type'];
				unset($params['type']);
			}
			unset($result);
		}
		
		if ($width > 0) $params['image_width'] = $width;
		if ($height > 0) $params['image_height'] = $height;
		
		parent::__construct($params);
		unset($params);
	}
	
	public function output_image() {
		return $this -> show();
	}
	
	public function destroy_image() {
		imagedestroy($this->im);
	}
	
	protected function saveData() {
		$_SESSION['veriword'] = $this->code;
	}
	
	/* Backward compatibility */
	// [obsolete]
	public function set_veriword() {
		$this -> createCode();
	}
	
	// [obsolete]
	public function pick_word() {
		return $this -> generateCode($this->code_length);
	}
	
	// [obsolete]
	public function draw_text() {
		return false;	// There are no such method in Securimage
	}
	
	// [obsolete]
	public function draw_image() {
		$this -> doImage();
		return $this -> im;
	}
}

$img = new VeriWord();
$img -> show();
?>