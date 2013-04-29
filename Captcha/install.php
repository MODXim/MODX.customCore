<?php
@include_once(dirname(__FILE__).'/manager/includes/config.inc.php');
@mysql_connect($database_server, $database_user, $database_password) or die("Unable to connect to database.");
@mysql_select_db(substr($dbase,1,-1)) or die("Unable to select database.");

$sql = '
INSERT INTO '.$dbase.'.`'.$table_prefix.'system_settings` (setting_name, setting_value) VALUES
("captcha_image_width","215"),
("captcha_image_height","80"),
("captcha_image_type","1"),
("captcha_image_bg_color","#ffffff"),
("captcha_text_color","#707070"),
("captcha_line_color","#707070"),
("captcha_noise_color","#707070"),
("captcha_text_transparency_percentage","50"),
("captcha_use_transparent_text","1"),
("captcha_code_length","6"),
("captcha_case_sensitive","1"),
("captcha_charset","ABCDEFGHKLMNPRSTUVWYZabcdefghklmnprstuvwyz23456789"),
("captcha_perturbation","0.75"),
("captcha_num_lines","8"),
("captcha_noise_level","0"),
("captcha_image_signature",""),
("captcha_signature_color","#707070"),
("captcha_type","0")
ON DUPLICATE KEY UPDATE setting_value=setting_value;
';

@mysql_query($sql) or die("Query failed : " . mysql_error());
echo "Your ModX database successfully patched.";
?>