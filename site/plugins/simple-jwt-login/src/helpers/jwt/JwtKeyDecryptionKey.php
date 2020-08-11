<?php


namespace SimpleJWTLogin\Helpers\Jwt;


class JwtKeyDecryptionKey extends JwtKeyBasic implements JwtKeyInterface {
	/**
	 * @return string
	 */
	public function getPublicKey() {
		return $this->settings->getDecryptionKey();
	}

	/**
	 * @return string
	 */
	public function getPrivateKey() {
		return $this->settings->getDecryptionKey();
	}
}