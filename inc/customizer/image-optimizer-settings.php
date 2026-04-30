<?php
/**
 * Image Optimizer (Settings + UI)
 *
 * @package zeitfresser
 */

/**
 * ------------------------------------------------------------------------
 * Settings
 * ------------------------------------------------------------------------
 */
function zeitfresser_customize_image_optimizer_settings( $wp_customize ) {

	/**
	 * Image Optimizer Section
	 */
	$wp_customize->add_section(
		'ztfr_image_optimizer',
		array(
			'title'    => 'Image Optimizer',
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
			'section'     => 'ztfr_image_optimizer',
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
			'section'     => 'ztfr_image_optimizer',
			'label'       => 'Auto Delete Original Pictures',
			'description' => 'Deletes originals after optimization.',
		)
	);
}
add_action( 'customize_register', 'zeitfresser_customize_image_optimizer_settings' );


/**
 * ------------------------------------------------------------------------
 * UI Logic (JS)
 * ------------------------------------------------------------------------
 */
function zeitfresser_customize_image_optimizer_ui() {
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
add_action( 'customize_controls_enqueue_scripts', 'zeitfresser_customize_image_optimizer_ui' );


/**
 * ------------------------------------------------------------------------
 * UI Styles
 * ------------------------------------------------------------------------
 */
function zeitfresser_customize_image_optimizer_ui_styles() {
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
}
add_action(
	'customize_controls_enqueue_scripts',
	'zeitfresser_customize_image_optimizer_ui_styles'
);
