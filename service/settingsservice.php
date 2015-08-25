<?php
namespace OCA\Passwords\Service;

use OCP\IConfig;

class SettingsService {

	private $userId;
	private $settings;
	private $appName;

	public function __construct($UserId, IConfig $settings, $AppName) {
		$this->userId = $UserId;
		$this->settings = $settings;
		$this->appName = $AppName;
	}

	/**
	 * get the current settings
	 *
	 * @return array
	 */
	public function get() {
		$settings = array(
				// admin settings
				'backup_allowed' => (string)$this->settings->getAppValue($this->appName, 'backup_allowed', 'false'),
				'days_orange' => (string)$this->settings->getAppValue($this->appName, 'days_orange', '150'),
				'days_red' => (string)$this->settings->getAppValue($this->appName, 'days_red', '365'),
				'https_check' => (string)$this->settings->getAppValue($this->appName, 'https_check', 'true'),
				'icons_allowed' => (string)$this->settings->getAppValue($this->appName, 'icons_allowed', 'true'),
				'icons_service' => (string)$this->settings->getAppValue($this->appName, 'icons_service', 'ddg'),
				// user settings
				'hide_attributes' => (string)$this->settings->getUserValue($this->userId, $this->appName, 'hide_attributes', 'false'),
                                'hide_Strength_attributes' => (string)$this->settings->getUserValue($this->userId, $this->appName, 'hide_Strength_attributes', 'false'),
				'hide_passwords' => (string)$this->settings->getUserValue($this->userId, $this->appName, 'hide_passwords', 'true'),
				'hide_usernames' => (string)$this->settings->getUserValue($this->userId, $this->appName, 'hide_usernames', 'false'),
				'icons_show' => (string)$this->settings->getUserValue($this->userId, $this->appName, 'icons_show', 'true'),
				'database_path' => (string)$this->settings->getUserValue($this->userId, $this->appName, 'database_path', '')
		);
		return $settings;
	}

	public function getKey($key) {
		return $settings.$key;
	}

	/**
	 * set user setting
	 *
	 * @param $setting
	 * @param $value
	 * @return bool
	 */
	public function set($setting, $value) {
		return $this->settings->setUserValue($this->userId, $this->appName, $setting, $value);
	}

	/**
	 * set admin setting
	 *
	 * @param $setting
	 * @param $value
	 */
	public function setadmin($setting, $value, $admin1, $admin2) {
		return $this->settings->setAppValue($this->appName, $setting, $value);
	}
}
