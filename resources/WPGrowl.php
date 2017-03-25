<?php

namespace WPGrowl;

/**
 * Class Styles
 *
 * To really improve the UI / UX, we do really need to teach WordPress some styling.
 */
class WPGrowl
{
	public function __construct()
	{
		$this->init();
	}

	private function init()
	{
		add_action('admin_enqueue_scripts', [$this, 'loadStyles']);
		add_action('wp_ajax_wp-growl-snooze-notice', [$this, 'snoozeNotice']);
	}

	public function loadStyles()
	{
		wp_enqueue_script('jquery-initialize', WP_GROWL_ASSETS . 'js/jquery-initialize.min.js', ['jquery'],
			WP_GROWL_VERSION, true);

		wp_enqueue_style('growl-css', WP_GROWL_ASSETS . 'css/growl.min.css', false,
			WP_GROWL_VERSION);

		wp_register_script('growl-js', WP_GROWL_ASSETS . 'js/growl.min.js', ['jquery', 'jquery-initialize'],
			WP_GROWL_VERSION, true);

		wp_localize_script('growl-js', 'growlL10n', $this->growlTranslations());
		wp_localize_script('growl-js', 'growl_snoozed_notices', $this->snoozedNotices());
		wp_enqueue_script('growl-js');
	}

	public function snoozedNotices()
	{
		$notices = get_user_meta(get_current_user_id(), 'wp-growl-snoozed-notices', true);
		$oldNotices = $notices;

		if (!empty($notices)) {
			foreach ($notices as $notice => $wakeup) {
				if ($wakeup < time()) {
					unset($notices[$notice]);
				} else {
					unset($notices[$notice]);
					$notices[stripslashes($notice)] = $wakeup;
				}
			}

			if ($oldNotices !== $notices) {
				update_user_meta(get_current_user_id(), 'wp-growl-snoozed-notices', $notices);
			}
		}

		return $notices;
	}

	public function snoozeNotice()
	{

		if (empty($_POST['notice'] || empty($_POST['wakeup']))) {
			die(
			json_encode(
				[
					'success' => false,
					'message' => 'Missing required information.'
				]
			)
			);
		}

		$notices = get_user_meta(get_current_user_id(), 'wp-growl-snoozed-notices', true);

		$notices[$_POST['notice']] = $_POST['wakeup'];

		update_user_meta(get_current_user_id(), 'wp-growl-snoozed-notices', $notices);

		die(
		json_encode(
			[
				'success' => true,
			]
		)
		);
	}

	public function growlTranslations() {
		return [
			'hour' => __('Remind me in 1 Hour', 'wp-growl'),
			'day' => __('Remind me tomorrow', 'wp-growl'),
			'week' => __('Remind me in 1 week', 'wp-growl'),
			'year' => __('Ignore this notification', 'wp-growl')
		];
	}
}