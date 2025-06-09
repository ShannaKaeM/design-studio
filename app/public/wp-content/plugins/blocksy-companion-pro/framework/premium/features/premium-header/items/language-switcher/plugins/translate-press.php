<?php

global $TRP_LANGUAGE;


$settings = new TRP_Settings();

$settings_array = $settings->get_settings();

$trp = TRP_Translate_Press::get_trp_instance();

$trp_lang_switcher = new TRP_Language_Switcher(
	$settings->get_settings(),
	TRP_Translate_Press::get_trp_instance()
);

$trp_languages = $trp->get_component('languages');

if (current_user_can(apply_filters(
	'trp_translating_capability',
	'manage_options'
))) {
	$languages_to_display = $settings_array['translation-languages'];
} else {
	$languages_to_display = $settings_array['publish-languages'];
}

$url_converter = $trp->get_component('url_converter');

$languages = $trp_languages->get_language_names(
	$languages_to_display
);

if (empty($languages)) {
	return;
}

$descriptors = [];

foreach ($languages as $code => $lang) {

	$flags_path = TRP_PLUGIN_URL .'assets/images/flags/';
	$flags_path = apply_filters('trp_flags_path', $flags_path, $code);

	// File name for specific flag
	$flag_file_name = $code .'.png';
	$flag_file_name = apply_filters('trp_flag_file_name', $flag_file_name, $code);

	if ($code === $TRP_LANGUAGE) {
		$descriptors['current'] = [
			'url' => $url_converter->get_url_for_language($code, false),
			'country_flag_url' => esc_url($flags_path . $flag_file_name),
			'language_code' => $code,
			'native_name' => $lang,
			'short_name' => strtoupper(
				$url_converter->get_url_slug($code, false)
			),
		];

		continue;
	}

	$descriptors[] = [
		'url' => $url_converter->get_url_for_language($code, false),
		'country_flag_url' => esc_url($flags_path . $flag_file_name),
		'language_code' => $code,
		'native_name' => $lang,
		'short_name' => strtoupper(
			$url_converter->get_url_slug($code, false)
		),
	];
}
