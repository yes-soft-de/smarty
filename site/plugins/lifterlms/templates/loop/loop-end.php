<?php
/**
 * LifterLMS Loop End Wrapper
 *
 * @package LifterLMS/Templates
 *
 * @since   1.0.0
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
          </div><!-- .row -->
        </ul>
      <?php $request_uri = explode('/', $_SERVER['REQUEST_URI']);
        if ( in_array('meditations', $request_uri, false) ): ?>
            <div class="col-12 meditation-bottom-section">
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Adipisci expedita explicabo perspiciatis sed! Assumenda consequatur esse est eum, incidunt iusto modi molestias nemo neque non obcaecati qui rem repudiandae tempora.</p>
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Adipisci expedita explicabo perspiciatis sed! Assumenda consequatur esse est eum, incidunt iusto modi molestias nemo neque non obcaecati qui rem repudiandae tempora.</p>
            </div>
          </div><!-- .container -->
      <?php endif; ?>
		</div><!-- .col-12 -->
	</div><!-- .row -->
</div><!-- .llms-loop -->
