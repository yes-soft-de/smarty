<?php
namespace SimpleJWTLogin\Modules;


class SimpleJWTLoginHooks {
	const LOGIN_ACTION_NAME = 'simple_jwt_login_login_hook';
	const LOGIN_REDIRECT_NAME = 'simple_jwt_login_redirect_hook';
	const REGISTER_ACTION_NAME = 'simple_jwt_login_register_hook';
	const DELETE_USER_ACTION_NAME = 'simple_jwt_login_delete_user_hook';

	const HOOK_TYPE_ACTION = 'action';
	const HOOK_TYPE_FILTER = 'filter';

	public static function getHooksDetails(){
		return [
			[
				'name' => self::LOGIN_ACTION_NAME,
				'type' => self::HOOK_TYPE_ACTION,
				'parameters' => [
					'Wp_User $user'
				],
				'description' => __('This hook is called after the user is logged in.', 'simple-jwt-login'),
			],
			[
				'name' => self::LOGIN_REDIRECT_NAME,
				'type' => self::HOOK_TYPE_ACTION,
				'parameters' => [
					'string $url',
					'array $request'
				],
				'description' => __('This hook is called before the user is redirected to the page he specified in the login section.', 'simple-jwt-login'),
			],
			[
				'name' => self::REGISTER_ACTION_NAME,
				'type' => self::HOOK_TYPE_ACTION,
				'parameters' => [
					'Wp_User $user',
					'string $password'
				],
				'description' => __('This hook is called after a new user is created.', 'simple-jwt-login'),
			],
			[
				'name' => self::DELETE_USER_ACTION_NAME,
				'type' => self::HOOK_TYPE_ACTION,
				'parameters' => [
					'Wp_User $user'
				],
				'description' => __('This hook is called right after the user was deleted.','simple-jwt-login')
			],
		];
	}
}
