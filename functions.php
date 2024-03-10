<?php

// Add post formats support
add_theme_support(
	'post-formats',
	[
		'aside', 'quote', 'status', 'image', 'gallery', 'chat', 'link', 'audio', 'video',
	]
);

// Add background image selection to Customizer interface via Customizer API
add_action(
	'customize_register',
	function (WP_Customize_Manager $wp_customize) {
		$wp_customize->add_section(
			'styling',
			[
				'title' => 'Styling',
				'priority' => 30,
				'capability' => 'edit_theme_options',
			]
		);
		$wp_customize->add_setting(
			'background_image',
			[
				'type' => 'theme_mod',
				'capability' => 'edit_theme_options',
				'theme_supports' => '',
				'default' => 0,
				'transport' => 'refresh',
				'sanitize_callback' => function ($value) {
					return filter_var(
						$value,
						FILTER_SANITIZE_NUMBER_INT,
						['options' => ['min_range' => 0]]
					);
				},
				'sanitize_js_callback' => '',
			]
		);
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'background_image',
				[
					'label' => __('Homepage Background Image', 'tj_net'),
					'section' => 'styling',
					'mime_type' => 'image',
				]
			)
		);
	}
);

// Add responsive selectable background image to body::before via inline CSS.
add_action(
	'wp_head',
	function () {
		$bgCss = 'body{positon:relative;}body::before {'.
			'content: "";'.
			'position: absolute;'.
			'width: 100%;'.
			'height: 100%;'.
			'background-size: cover;'.
			'background-image: var(--theme-background-image);'.
			'background-repeat: no-repeat;'.
			'background-position: top center;'.
			'filter: brightness(0.5);'.
			'mask-image: linear-gradient(to bottom, rgba(0,0,0,1), rgba(0,0,0,0) 600px);'.
			'z-index: -1'.
			'}';
		$bgImgId = get_theme_mod('background_image');
		$bgImgMeta = wp_get_attachment_metadata($bgImgId);
		if (is_array($bgImgMeta)) {
			if (isset($bgImgMeta['sizes'])) {
				uasort($bgImgMeta['sizes'], function ($a, $b) {
					return $a['width'] - $b['width'];
				});
				foreach ($bgImgMeta['sizes'] as $size => $sizeData) {
					$url = wp_get_attachment_image_url($bgImgId, $size);
					$bgCss .= '@media (min-width: '.$sizeData['width'].'px) {';
					$bgCss .= ':root {'.
						'--theme-background-image: url("'.
						esc_url($url).
						'");'.
						'}';
					$bgCss .= '}';
				}
			} else {
				$bgCss .= ':root {'.
					'--theme-background-image: url("'.
					esc_url(wp_get_attachment_image_url($bgImgId, 'full')).
					'");'.
					'}';
			}
		}

		echo '<style type="text/css">', $bgCss, '</style>';
	}
);

// Add main style
wp_enqueue_style(
	'tj',
	get_stylesheet_directory_uri().'/style.css',
	[],
	filemtime(get_stylesheet_directory().'/style.css'),
	false
);
