/**
 * Adminify UI - SSL gate.
 *
 * The Adminify UI dashboard frame only loads over HTTPS (see Inc/Admin/Admin.php).
 * So before letting the user enable the "Adminify UI" switcher we run an AJAX
 * check against the server. If HTTPS is not properly set up we revert the
 * toggle and show a warning notice right below the switcher.
 *
 * Plain jQuery, no build step. Enqueued only on the Adminify settings screen.
 */
(function ($) {
	'use strict';

	if (typeof PXLBSADMINIFY_SSL === 'undefined') {
		return;
	}

	var cfg = PXLBSADMINIFY_SSL;

	$(function () {
		var $input = $('input[data-depend-id="' + cfg.field_id + '"]').first();
		if (!$input.length) {
			return;
		}

		var $switcher = $input.closest('.adminify--switcher');
		if (!$switcher.length) {
			return;
		}

		// If it was already enabled on page load, HTTPS was already accepted —
		// don't gate toggling it off and back on again.
		var verified = $switcher.hasClass('adminify--active');
		var checking = false;

		// The whole settings row for this field. We drop the notice AFTER this
		// row (not inside the switcher column) so it can't overlap the toggle.
		function fieldRow() {
			var $row = $switcher.closest('.adminify-field');
			return $row.length ? $row : $switcher.closest('.adminify-fieldset');
		}

		function clearNotice() {
			fieldRow().next('.adminify-ui-ssl-warning').remove();
		}

		function showNotice(message) {
			clearNotice();
			// Inline styles keep the notice looking right even outside the
			// framework's .adminify-field-notice wrapper (which scopes its CSS).
			$('<div/>', {
				'class': 'adminify-notice adminify-notice-warning adminify-ui-ssl-warning',
				html: '<strong>' + cfg.i18n.label + '</strong> ' + message
			}).css({
				'clear': 'both',
				'width': '100%',
				'box-sizing': 'border-box',
				'margin': '0',
				'padding': '10px 14px',
				'background-color': '#fff8e5',
				'border-left': '4px solid #ffbc00',
				'color': '#693f00',
				'font-size': '13px',
				'line-height': '1.5'
			}).insertAfter(fieldRow());
		}

		function setBusy(busy) {
			checking = busy;
			$switcher.css('opacity', busy ? '0.6' : '');
			$switcher.css('pointer-events', busy ? 'none' : '');
		}

		// Programmatically flip the switcher ON the same way the framework does,
		// so dependency fields (templates, colors) react correctly.
		function enable() {
			$switcher.addClass('adminify--active');
			$input.val(1).trigger('change');
		}

		function runCheck() {
			if (checking) {
				return;
			}
			setBusy(true);

			$.post(cfg.ajax_url, {
				action: 'pxlbsadminify_ssl_check',
				nonce: cfg.nonce
			}).done(function (res) {
				if (res && res.success && res.data && res.data.https_ready) {
					verified = true;
					clearNotice();
					enable();
				} else {
					var msg = (res && res.data && res.data.message) ? res.data.message : cfg.i18n.generic_error;
					showNotice(msg);
				}
			}).fail(function () {
				showNotice(cfg.i18n.generic_error);
			}).always(function () {
				setBusy(false);
			});
		}

		// Capture phase, native listener: runs BEFORE the framework's bubble-phase
		// jQuery click handler. Lets us block the OFF -> ON toggle until verified,
		// so the UI never flickers into the enabled state on an unverified site.
		$switcher[0].addEventListener('click', function (e) {
			// Turning OFF, or already verified: let the framework handle it.
			if ($switcher.hasClass('adminify--active') || verified) {
				clearNotice();
				return;
			}
			// Turning ON, not verified yet: stop the framework toggle, check first.
			e.preventDefault();
			e.stopImmediatePropagation();
			runCheck();
		}, true);
	});
})(jQuery);
