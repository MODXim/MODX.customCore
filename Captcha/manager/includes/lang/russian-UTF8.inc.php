<?php

// Added by DWand. 05.02.2012. Replacing captcha.
$_lang["captcha_image_width"] = 'Ширина изображения:';
$_lang["captcha_image_width_message"] = 'Ширина изображения, которое будет сгенерировано.';
$_lang["captcha_image_height"] = 'Высота изображения:';
$_lang["captcha_image_height_message"] = 'Высота изображения, которое будет сгенерировано.';
$_lang["captcha_image_type"] = 'Формат изображения:';
$_lang["captcha_image_type_message"] = 'Выберите формат генерируемого изображения.';
$_lang["captcha_image_bg_color"] = 'Цвет фона:';
$_lang["captcha_image_bg_color_message"] = 'Введите цвет фона в HEX или RGB формате. Например:<br />HEX:&nbsp;&nbsp;&nbsp;&nbsp;#ffffff = белый; #fff = белый<br />RGB:&nbsp;&nbsp;&nbsp;&nbsp;255,255,255 = белый';
$_lang["captcha_text_color"] = 'Цвет текста:';
$_lang["captcha_text_color_message"] = 'Введите цвет текста в HEX или RGB формате. Например:<br />HEX:&nbsp;&nbsp;&nbsp;&nbsp;#ffffff = белый; #fff = белый<br />RGB:&nbsp;&nbsp;&nbsp;&nbsp;255,255,255 = белый';
$_lang["captcha_line_color"] = 'Цвет полос:';
$_lang["captcha_line_color_message"] = 'Введите цвет полос в HEX или RGB формате. Например:<br />HEX:&nbsp;&nbsp;&nbsp;&nbsp;#ffffff = белый; #fff = белый<br />RGB:&nbsp;&nbsp;&nbsp;&nbsp;255,255,255 = белый';
$_lang["captcha_noise_color"] = 'Цвет шума:';
$_lang["captcha_noise_color_message"] = 'Введите цвет шума в HEX или RGB формате. Например:<br />HEX:&nbsp;&nbsp;&nbsp;&nbsp;#ffffff = белый; #fff = белый<br />RGB:&nbsp;&nbsp;&nbsp;&nbsp;255,255,255 = белый';
$_lang["captcha_text_transparency_percentage"] = 'Прозрачность текста:';
$_lang["captcha_text_transparency_percentage_message"] = 'Укажите на сколько прозрачным делать текст. 0 = полностью не прозрачный, 100 = невидимый.';
$_lang["captcha_use_transparent_text"] = 'Использовать прозрачность текста:';
$_lang["captcha_use_transparent_text_message"] = 'Укажите нужно ли использовать прозрачность текста. Если включено, при генерации изображения для текста будут применяться настройки прозрачности.';
$_lang["captcha_code_length"] = 'Длина captcha кода:';
$_lang["captcha_code_length_message"] = 'Укажите из скольки символов должен состоять генерируемый код.';
$_lang["captcha_case_sensitive"] = 'Регистрочувствительность:';
$_lang["captcha_case_sensitive_message"] = 'Выберете нужно ли учитывать регистр символов при проверке кода.<br />Если выбрано '.$_lang['yes'].', то каждый символ кода должен быть введен в том регистре, в каком он сгенерирован.<br />Если выбрано '.$_lang['no'].', то все символы кода должны быть введены в нижнем регистре.';
$_lang["captcha_charset"] = 'Набор символов';
$_lang["captcha_charset_message"] = 'Введите набор символов, которые могут быть использованы при генерации captcha кода.';
$_lang["captcha_perturbation"] = 'Уровень искажения:';
$_lang["captcha_perturbation_message"] = 'Укажите уровень искажения изображения. 0.75 = нормальное искажение, 1 = очень сильное искажение.';
$_lang["captcha_num_lines"] = 'Количество линий:';
$_lang["captcha_num_lines_message"] = 'Укажите сколько линий нужно накладывать на изображение для повышения сложности.';
$_lang["captcha_noise_level"] = 'Уровень шума:';
$_lang["captcha_noise_level_message"] = 'Уровень шума (случайные точки) накладываемого на изображение. 0 = нет шума, 10 - максимальное количество шума.';
$_lang["captcha_image_signature"] = 'Подпись';
$_lang["captcha_image_signature_message"] = 'Введите подпись, которая будет отображаться в нижнем правом углу изображения.';
$_lang["captcha_signature_color"] = 'Цвет подписи';
$_lang["captcha_signature_color_message"] = 'Введите цвет подписи в HEX или RGB формате. Например:<br />HEX:&nbsp;&nbsp;&nbsp;&nbsp;#ffffff = белый; #fff = белый<br />RGB:&nbsp;&nbsp;&nbsp;&nbsp;255,255,255 = белый';
$_lang["captcha_type"] = 'Тип captcha кода:';
$_lang["captcha_type_string"] = 'Набор символов';
$_lang["captcha_type_mathematic"] = 'Математическая задача';
$_lang["captcha_type_message"] = 'Выберите какой тип captcha кода генерировать.<br />Набор символов = генерируется случайная последовательность символов указанной длины.<br />Математическая задача = генерируется не сложная математическая задача.';
?>