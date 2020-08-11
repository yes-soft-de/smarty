<?php
namespace SimpleJWTLogin\Helpers\Jwt;

use SimpleJWTLogin\Modules\SimpleJWTLoginSettings;

class JwtKeyFactory {

	/**
	 * @param string $algorithm
	 * @param SimpleJWTLoginSettings $settings
	 *
	 * @return JwtKeyInterface
	 */
	public static function getFactory($settings){
		$algorithm = $settings->getJWTDecryptAlgorithm();
		if(strpos($algorithm,'RS') !== false){
			return new JwtKeyCertificate($settings);
		}
		return new JwtKeyDecryptionKey($settings);
	}
}