<?php
/**
 * Operation is simple: Non-ajax renderings of the register form simply display a spinner,
 * wait a half-second, then load the form contents via ajax.
 *
 * If elgg_recaptcha is active, its widget will not be rendered until the form is displayed.
 */

namespace Elgg\Plugin\AjaxRegister;

const DELAY = 500;

function alter_form($hook, $view, $output, $params) {
	if (elgg_is_xhr()) {
		return;
	}

	// just leave a spinner and re-load the form by ajax
	ob_start();
	?>
	<div class="elgg-ajax-loader mtl"></div>
	<script id="ajax-register-loader">
	require(['jquery', 'elgg'], function ($, elgg) {
		function load() {
			elgg.get(elgg.normalize_url('ajax_register_form')).done(function (html) {
				$('#ajax-register-loader').parent().html(html);

				if (window.elgg_recaptcha_render) {
					elgg_recaptcha_render();
				}
			});
		}
		setTimeout(load, <?= DELAY ?>);
	});
	</script>
	<?php
	return ob_get_clean();
}

function send_form() {
	if (!elgg_is_xhr()) {
		return false;
	}

	echo elgg_view_form('register');
	return true;
}

// late priority so elgg_recaptcha doesn't add its widget on initial page rendering
elgg_register_plugin_hook_handler('view', 'forms/register', __NAMESPACE__ . '\alter_form', 999);

elgg_register_page_handler('ajax_register_form', __NAMESPACE__ . '\send_form');
