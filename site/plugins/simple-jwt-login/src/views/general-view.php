<?php

use SimpleJWTLogin\Libraries\JWT;
use SimpleJWTLogin\Modules\SimpleJWTLoginSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$displayCert = strpos($jwtSettings->getJWTDecryptAlgorithm(),'RS') !== false;
?>

<div class="row">
    <div class="col-md-12">
        <h3 class="section-title"><?php echo __( 'Route Namespace', 'simple-jwt-login' ); ?></h3>
        <div class="form-group">
            <input type="text" name="route_namespace" value="<?php echo $jwtSettings->getRouteNamespace(); ?>"
                   class="form-control"
                   placeholder="<?php echo __( 'Default route namespace', 'simple-jwt-login' ); ?>"
            />
        </div>
    </div>
</div>
<hr/>

<div class="row">
    <div class="col-md-6">
        <h3 class="section-title"><?php echo __( 'JWT Decryption Key', 'simple-jwt-login' ); ?></h3>
        <div class="info"><?php echo __( 'JWT decryption signature | JWT Verify Signature',
				'simple-jwt-login' ); ?></div>

        <div class="form-group decryption-input-group"
             style="<?php echo $displayCert === true ?'display:none' :''?>"
             >
            <div class="input-group" id="decryption_key_container">
                <input type="password" name="decryption_key" class="form-control"
                       id="decryption_key"
                       value="<?php echo $jwtSettings->getDecryptionKey(); ?>"
                       placeholder="<?php echo __( 'JWT decryption key here', 'simple-jwt-login' ); ?>"
                />
                <div class="input-group-addon">
                    <a href="javascript:void(0)"
                       onclick="showDecryptionKey()"
                       class="toggle_key_button"
                       title="<?php echo __( 'Toggle decryption key', 'simple-jwt-login' ); ?>"
                    >
                        <i class="toggle-image" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="form-group decryption-textarea-group" style="<?php echo $displayCert === false ?'display:none' :''?>">
            <label for="simple-jwt-login-public-key">Public Key</label>
            <textarea
                    class="form-control"
                    id="simple-jwt-login-public-key"
                    rows="6"
                    name="decryption_key_public"
            ><?php echo $jwtSettings->getDecryptionKeyPublic();?></textarea>
        </div>
        <div class="form-group  decryption-textarea-group" style="<?php echo $displayCert === false ?'display:none' :''?>">
            <label for="simple-jwt-login-private-key">Private Key</label>
            <textarea
                    class="form-control"
                    id="simple-jwt-login-private-key"
                    rows="6"
                    name="decryption_key_private"
            ><?php echo $jwtSettings->getDecryptionKeyPrivate();?></textarea>
        </div>
    </div>
    <div class="col-md-6">
        <h3 class="section-title"> <?php echo __( 'JWT Decrypt Algorithm', 'simple-jwt-login' ); ?></h3>
        <div class="info"><?php echo __( 'The algorithm that should be used to verify the JWT signature.',
				'simple-jwt-login' ); ?></div>
        <div class="form-group">
            <select name="jwt_algorithm" class="form-control" id="simple-jwt-login-jwt-algorithm">
				<?php
				foreach ( JWT::$supported_algs as $alg => $arr ) {
					$selected = $jwtSettings->getJWTDecryptAlgorithm() === $alg
						? 'selected'
						: '';
					echo "<option value=\"" . $alg . "\" " . $selected . ">" . $alg . "</option>\n";
				}
				?>
            </select>

        </div>
    </div>
</div>
<hr/>

<div class="row">
    <div class="col-md-12">
        <h3 class="section-title"><?php echo __( 'Get JWT token from', 'simple-jwt-login' ); ?></h3>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        1. URL
    </div>
    <div class="col-md-3">
        <select name="request_jwt_url" class="form-control onOff">
            <option value="0" <?php echo $jwtSettings->getJwtFromURLEnabled() === false ? "selected" : ""; ?> >
				<?php echo __( 'Off', 'simple-jwt-login' ); ?>
            </option>
            <option value="1" <?php echo $jwtSettings->getJwtFromURLEnabled() === true ? "selected" : ""; ?>>
				<?php echo __( 'On', 'simple-jwt-login' ); ?>
            </option>
        </select>
    </div>
    <div class="col-md-6">
        <div class="code">&jwt=<b>YOUR JWT HERE</b></div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        2. SESSION
    </div>
    <div class="col-md-3">
        <select name="request_jwt_session" class="form-control onOff">
            <option value="0" <?php echo $jwtSettings->getJwtFromSessionEnabled() === false ? "selected" : ""; ?>>
				<?php echo __( 'Off', 'simple-jwt-login' ); ?>
            </option>
            <option value="1" <?php echo $jwtSettings->getJwtFromSessionEnabled() === true ? "selected" : ""; ?>>
				<?php echo __( 'On', 'simple-jwt-login' ); ?>
            </option>
        </select>
    </div>
    <div class="col-md-6">
        <div class="code">$_SESSION['<b>simple-jwt-login-token</b>']</div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        3. COOKIE
    </div>
    <div class="col-md-3">
        <select name="request_jwt_cookie" class="form-control onOff">
            <option value="0" <?php echo $jwtSettings->getJwtFromCookieEnabled() === false ? "selected" : ""; ?>>
				<?php echo __( 'Off', 'simple-jwt-login' ); ?>
            </option>
            <option value="1" <?php echo $jwtSettings->getJwtFromCookieEnabled() === true ? "selected" : ""; ?>>
				<?php echo __( 'On', 'simple-jwt-login' ); ?>
            </option>
        </select>
    </div>
    <div class="col-md-6">
        <div class="code">$_COOKIE['<b>simple-jwt-login-token</b>']</div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        4. Header
    </div>
    <div class="col-md-3">
        <select name="request_jwt_header" class="form-control onOff">
            <option value="0" <?php echo $jwtSettings->getJwtFromHeaderEnabled() === false ? "selected" : ""; ?>>
				<?php echo __( 'Off', 'simple-jwt-login' ); ?>
            </option>
            <option value="1" <?php echo $jwtSettings->getJwtFromHeaderEnabled() === true ? "selected" : ""; ?>>
				<?php echo __( 'On', 'simple-jwt-login' ); ?>
            </option>
        </select>
    </div>
    <div class="col-md-6">
        <div class="code">Authorisation: Bearer <b>YOUR_JWT_HERE</b></div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <p class="text-muted">
            * <?php echo __( 'If the JWT is present in multiple places, the higher number of the option overwrites the smaller number.',
				'simple-jwt-login' ); ?>
        </p>
    </div>
</div>
<hr />

<div class="row">
    <div class="col-md-12">
        <input type="checkbox" name="api_middleware[enabled]" value="1" <?php echo $jwtSettings->isMiddlewareEnabled() ? 'checked="checked"' : ""?> />
        <span class="beta">beta</span>
        All WordPress endpoints checks for JWT authentication <Br />
        <p class="text-muted">
            * If the JWT is provided on other endpoints, the plugin will try to authenticate the user from the JWT in order to perform that API call.
        </p>
    </div>
</div>