<?php
/**
 * Theme Customizer Core
 *
 * @package zeitfresser
 */

function zeitfresser_customize_register( $wp_customize ) {

	// Live Preview support
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
	 * Performance Tools Section
	 */
	$wp_customize->add_section(
		'ztfr_performance_tools',
		array(
			'title'    => 'Performance Tools Settings',
			'priority' => 160,
		)
	);

	/**
	 * Auto Optimize
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
			'description' => 'Automatically converts images to AVIF/WebP.',
		)
	);

	/**
	 * Auto Delete
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
			'label'       => 'Auto Delete Original Pictures',
			'description' => 'Deletes originals after optimization.',
		)
	);
}
add_action( 'customize_register', 'zeitfresser_customize_register' );

/**
 * Partial refresh helpers
 */
function zeitfresser_customize_partial_blogname() {
	bloginfo( 'name' );
}

function zeitfresser_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Live preview JS
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
 * Dependency UI logic
 */
function zeitfresser_customize_controls_dependency_js() {
	?>
	<script>
	(function() {

		function getOptimizeInput() {
			return document.querySelector('#customize-control-ztfr_auto_optimize input');
		}

		function getDeleteInput() {
			return document.querySelector('#customize-control-ztfr_auto_delete input');
		}

		function getDeleteControl() {
			return document.getElementById('customize-control-ztfr_auto_delete');
		}

		function ensureStatusBox() {

			let box = document.getElementById('ztfr-auto-status-box');

			if (box) return box;

			const optimizeControl = document.getElementById('customize-control-ztfr_auto_optimize');

			if (!optimizeControl || !optimizeControl.parentNode) return null;

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

	        let attempts = 0;

	        function tryInit() {
		        const optimize = getOptimizeInput();
		        const del = getDeleteInput();

		        if (optimize && del) {
			        updateState();
			        return;
		        }

		        // Retry max 10x
		        if (attempts < 10) {
			        attempts++;
			        setTimeout(tryInit, 200);
		        }
	        }

	        tryInit();

	        document.addEventListener('change', function(e) {
		        if (
			        e.target &&
			        (
				        e.target.matches('#customize-control-ztfr_auto_optimize input') ||
				        e.target.matches('#customize-control-ztfr_auto_delete input')
			        )
		        ) {
			        updateState();
		        }
	        });
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

/**
 * Small UI polish
 */
add_action( 'customize_controls_enqueue_scripts', function() {
?>
<style>
#customize-control-ztfr_auto_optimize > label,
#customize-control-ztfr_auto_delete > label {
	display:flex;
	align-items:flex-start;
	gap:6px;
}
</style>
<?php
});
