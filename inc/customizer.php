<?php
/**
 * daisy blog Theme Customizer
 *
 * @package zeitfresser
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return void
 */
function zeitfresser_customize_register( $wp_customize ) {

	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'zeitfresser_customize_partial_blogname',
			)
		);

		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'zeitfresser_customize_partial_blogdescription',
			)
		);
	}

	/**
	 * Performance Tools section
	 */
	$wp_customize->add_section(
		'ztfr_performance_tools',
		array(
			'title'    => 'Performance Tools Settings',
			'priority' => 160,
		)
	);

	/**
	 * Auto optimize uploaded images.
	 */
	$wp_customize->add_setting(
		'ztfr_auto_optimize',
		array(
			'default'           => true,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);

	$wp_customize->add_control(
		'ztfr_auto_optimize',
		array(
			'type'        => 'checkbox',
			'section'     => 'ztfr_performance_tools',
			'label'       => 'Auto Optimize Pictures on Upload',
			'description' => 'Automatically converts uploaded images to modern formats (AVIF/WebP) for improved performance.',
		)
	);

	/**
	 * Auto delete originals after successful optimization.
	 */
	$wp_customize->add_setting(
		'ztfr_auto_delete',
		array(
			'default'           => false,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);

	$wp_customize->add_control(
		'ztfr_auto_delete',
		array(
			'type'        => 'checkbox',
			'section'     => 'ztfr_performance_tools',
			'label'       => 'Auto Delete Original Pictures on Upload',
			'description' => 'Automatically deletes original images after optimization. Warning: deleting original images cannot be undone.',
		)
	);
}
add_action( 'customize_register', 'zeitfresser_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function zeitfresser_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function zeitfresser_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Bind JS handlers to make Theme Customizer preview reload changes asynchronously.
 *
 * @return void
 */
function zeitfresser_customize_preview_js() {
	wp_enqueue_script(
		'zeitfresser-customizer',
		get_template_directory_uri() . '/js/customizer.js',
		array( 'customize-preview' ),
		ZEITFRESSER_VERSION,
		true
	);
}
add_action( 'customize_preview_init', 'zeitfresser_customize_preview_js' );

/**
 * Add dependency logic and status box for Performance Tools settings.
 *
 * @return void
 */
function zeitfresser_customize_controls_dependency_js() {
	?>
	<script>
	(function() {
		function getOptimizeInput() {
			return document.querySelector('#customize-control-ztfr_auto_optimize input[type="checkbox"]');
		}

		function getDeleteInput() {
			return document.querySelector('#customize-control-ztfr_auto_delete input[type="checkbox"]');
		}

		function getDeleteControl() {
			return document.getElementById('customize-control-ztfr_auto_delete');
		}

		function ensureStatusBox() {
			let box = document.getElementById('ztfr-auto-status-box');

			if (box) {
				return box;
			}

			const optimizeControl = document.getElementById('customize-control-ztfr_auto_optimize');

			if (!optimizeControl || !optimizeControl.parentNode) {
				return null;
			}

			box = document.createElement('li');
			box.id = 'ztfr-auto-status-box';
			box.className = 'customize-control';
			box.innerHTML =
				'<span style="display:block;font-weight:600;margin-bottom:6px;">Current Mode</span>' +
				'<span id="ztfr-auto-status-text">Checking...</span>';

			optimizeControl.parentNode.insertBefore(box, optimizeControl);

			return box;
		}

		function updateState() {
			const optimizeInput = getOptimizeInput();
			const deleteInput   = getDeleteInput();
			const deleteControl = getDeleteControl();
			const statusBox     = ensureStatusBox();
			const statusText    = document.getElementById('ztfr-auto-status-text');

			if (!optimizeInput || !deleteInput || !deleteControl || !statusBox || !statusText) {
				return;
			}

			if (!optimizeInput.checked) {
				deleteInput.checked = false;
				deleteInput.disabled = true;
				deleteControl.style.opacity = '0.5';
				statusText.textContent = '⚪ Manual Mode (no automation)';
			} else {
				deleteInput.disabled = false;
				deleteControl.style.opacity = '1';

				if (deleteInput.checked) {
					statusText.textContent = '🟢 Full Auto Mode (optimize + delete)';
				} else {
					statusText.textContent = '🟡 Auto Optimize enabled (originals kept)';
				}
			}
		}

		function init() {
			updateState();

			document.addEventListener('change', function(event) {
				const target = event.target;

				if (
					target &&
					(
						target.matches('#customize-control-ztfr_auto_optimize input[type="checkbox"]') ||
						target.matches('#customize-control-ztfr_auto_delete input[type="checkbox"]')
					)
				) {
					updateState();
				}
			});

			let tries = 0;
			const interval = setInterval(function() {
				updateState();
				tries++;

				if (tries > 20) {
					clearInterval(interval);
				}
			}, 300);
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', init);
		} else {
			init();
		}
	})();
	</script>
	<?php
}
add_action( 'customize_controls_enqueue_scripts', 'zeitfresser_customize_controls_dependency_js' );
