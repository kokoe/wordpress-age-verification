<?php

class Age_Verification {

	static private $verification_url = '/' . AGE_GATE_SLUG . '/';

	static private $redirect_get_name = 'location';

	static private $token_post_name = 'token';

	static private $verified_cookie_name = 'age_verify';

	static private $verified_cookie_value = '1';

	/**
	 * 年齢認証・初期処理
	 *
	 * @param String $unset_redirect_url - location（リダイレクト先）未定義時のリダイレクト先
	 */
	static public function verification_init($unset_redirect_url = '/') {
		self::session_start();
		if (self::is_verified()) {
			self::recirect($unset_redirect_url);
		}
	}

	/**
	 * セッション開始
	 */
	static public function session_start() {
		if (!isset($_SESSION)) {
			session_start();
		}
	}

	/**
	 * 認証確認。未認証なら年齢認証ページへリダイレクト
	 */
	static public function verify() {
		if (self::is_verified()) {
			return; // 認証済み
		} elseif (!self::is_verification_url(self::get_location())) {
			// 認証ページへリダイレクト
			self::recirect(self::$verification_url . '?location=' . urlencode(self::get_location()));
		} else {
			return; // リダイレクトループ状態
		}
	}

	/**
	 * リダイレクト
	 *
	 * @param String $url
	 */
	static public function recirect($url) {
		header('Location: ' . self::h($url));
		exit;
	}

	/**
	 * 現在のページのURLを取得する
	 *
	 * @return String
	 */
	static public function get_location() {
		return (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	/**
	 * セッションIDを元にトークンを生成
	 *
	 * @return String
	 */
	static private function generate_token() {
		return hash('sha256', session_id());
	}

	/**
	 * トークンを返す
	 *
	 * @return String
	 */
	static public function get_token() {
		return self::generate_token();
	}

	/**
	 * 認証承認処理（POST）トークン検証もする
	 *
	 * @param String $unset_redirect_url - location（リダイレクト先）未定義時のリダイレクト先
	 */
	static public function post_verification($unset_redirect_url = '/') {
		if (empty($_POST)) {
			// TODO 不正アクセス -> エラーページ
			exit;
		}
		$token_name = self::$token_post_name;
		$post_token = isset($_POST[$token_name]) ? $_POST[$token_name] : null;

		if (self::validate_token($post_token)) {
			self::post_verified($unset_redirect_url);
		} else {
			// TODO 不正アクセス -> エラーページ
			exit;
		}
	}

	/**
	 * 認証承認後の処理
	 *
	 * @param String $unset_redirect_url - location（リダイレクト先）未定義時のリダイレクト先
	 */
	static private function post_verified($unset_redirect_url = '/') {
		// cookieに認証済みの値を設定
		self::set_verified();

		// セッションIDの追跡を防ぐ
		session_regenerate_id(true);

		if (empty($_GET)) {
			self::recirect($unset_redirect_url);
		}

		$redirect_url = isset($_GET[self::$redirect_get_name]) ? $_GET[self::$redirect_get_name] : $unset_redirect_url;

		if (!self::validate_url($redirect_url)) {
			$redirect_url = $unset_redirect_url;
		}

		// リダイレクト
		self::recirect($redirect_url);
	}

	/**
	 * トークン検証
	 *
	 * @param String $token
	 * @return Boolean
	 */
	static private function validate_token($token) {
		return $token === self::get_token();
	}

	/**
	 * エスケープ。ヘッダインジェクション対策
	 *
	 * @param String $str
	 * @return Boolean
	 */
	static public function h($str) {
		return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * URL検証
	 *
	 * @param String $url
	 * @return Boolean
	 */
	static public function validate_url($url = '') {
		$parsed_url = parse_url($url);

		if ($parsed_url === false) {
			return false; // URLでない
		} elseif (empty($parsed_url['host']) && empty($parsed_url['path'])) {
			return false; // ホストもパスもない => URLでない
		} else {
			return true; //OK
		}
	}

	/**
	 * 認証済みか
	 *
	 * @return Boolean
	 */
	static public function is_verified() {
		if (empty($_COOKIE)) {
			return false;
		}

		$cookie = !empty($_COOKIE[self::$verified_cookie_name]) ? $_COOKIE[self::$verified_cookie_name] : null;

		return $cookie === self::$verified_cookie_value;
	}

	/**
	 * cookieで認証済み設定
	 */
	static private function set_verified() {
		setcookie(self::$verified_cookie_name, self::$verified_cookie_value, 0, '/');
	}

	/**
	 * $locationが認証ページになるか判定リダイレクトループになるかを返す
	 *
	 * @param String $location
	 */
	static public function is_verification_url($location) {
		$parsed_location = parse_url($location);
		$location_host = !empty($parsed_location) && !empty($parsed_location['host']) ? $parsed_location['host'] : $_SERVER['HTTP_HOST'];
		$location_path = !empty($parsed_location) && !empty($parsed_location['path']) ? $parsed_location['path'] : null;

		$current_host = $_SERVER['HTTP_HOST'];
		$current_path = strtok(self::$verification_url, '?');

		return ($location_host === $current_host) && ($location_path === $current_path);
	}
}
