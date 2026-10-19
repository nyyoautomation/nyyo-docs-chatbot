<?php
/**
 * Plugin Name:       Nyyo Docs Chatbot
 * Plugin URI:        https://github.com/nyyoautomation/nyyo-docs-chatbot
 * Description:       A chatbot that answers visitors from your own published pages and posts, and shows the source page of every answer. Shortcode or floating widget. Runs on your site, no external service by default.
 * Version:           1.1.1
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Nyyo Automation
 * Author URI:        https://github.com/nyyoautomation
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       nyyo-docs-chatbot
 *
 * @package NyyoDocsChatbot
 */

/*
Nyyo Docs Chatbot is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Nyyo Docs Chatbot is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NYYO_DOCS_CHAT_VERSION', '1.1.1' );
define( 'NYYO_DOCS_CHAT_OPT', 'nyyo_docs_chat_settings' );
define( 'NYYO_DOCS_CHAT_INDEX_OPT', 'nyyo_docs_chat_index' );
define( 'NYYO_DOCS_CHAT_MAX_POSTS', 500 );
define( 'NYYO_DOCS_CHAT_CHUNK_WORDS', 120 );

require_once __DIR__ . '/includes/class-nyyo-docs-chat-index.php';

/**
 * Default settings. Local mode is the default: no external call is made.
 *
 * @return array
 */
function nyyo_docs_chat_defaults() {
	return array(
		'mode'         => 'local',
		'bot_url'      => '',
		'post_types'   => array( 'page', 'post' ),
		'auto_inject'  => 0,
		'position'     => 'bottom-right',
		'button_label' => '',
		'welcome'      => '',
		'max_results'  => 3,
	);
}

/**
 * Saved settings merged with defaults.
 *
 * @return array
 */
function nyyo_docs_chat_get_settings() {
	$saved = get_option( NYYO_DOCS_CHAT_OPT, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	$s = array_merge( nyyo_docs_chat_defaults(), $saved );
	if ( 'external' === $s['mode'] && '' === (string) $s['bot_url'] ) {
		$s['mode'] = 'local';
	}
	return $s;
}

/**
 * Button label, translated when the admin left it empty.
 *
 * @param array $s Settings.
 * @return string
 */
function nyyo_docs_chat_button_label( $s ) {
	return '' !== $s['button_label'] ? $s['button_label'] : __( 'Ask our docs', 'nyyo-docs-chatbot' );
}

/**
 * Welcome message, translated when the admin left it empty.
 *
 * @param array $s Settings.
 * @return string
 */
function nyyo_docs_chat_welcome( $s ) {
	return '' !== $s['welcome'] ? $s['welcome'] : __( 'Hi! Ask a question. I answer from the pages of this site and show the source.', 'nyyo-docs-chatbot' );
}

/**
 * Sanitize settings.
 *
 * @param mixed $input Raw input.
 * @return array
 */
function nyyo_docs_chat_sanitize( $input ) {
	$out = nyyo_docs_chat_defaults();
	if ( ! is_array( $input ) ) {
		return $out;
	}
	$mode        = isset( $input['mode'] ) ? sanitize_key( $input['mode'] ) : 'local';
	$out['mode'] = in_array( $mode, array( 'local', 'external' ), true ) ? $mode : 'local';
	if ( ! empty( $input['bot_url'] ) ) {
		$out['bot_url'] = esc_url_raw( trim( (string) $input['bot_url'] ), array( 'https', 'http' ) );
	}
	// External mode needs a URL set by the administrator. Without one, stay on this site.
	if ( 'external' === $out['mode'] && '' === $out['bot_url'] ) {
		$out['mode'] = 'local';
		add_settings_error( NYYO_DOCS_CHAT_OPT, 'nyyo_docs_chat_no_url', __( 'External mode needs a chatbot URL. The answer source was set back to This site.', 'nyyo-docs-chatbot' ) );
	}
	$public_types = get_post_types( array( 'public' => true ) );
	$types        = array();
	if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
		foreach ( $input['post_types'] as $t ) {
			$t = sanitize_key( $t );
			if ( isset( $public_types[ $t ] ) && 'attachment' !== $t ) {
				$types[] = $t;
			}
		}
	}
	$out['post_types']   = $types ? $types : array( 'page' );
	$out['auto_inject']  = empty( $input['auto_inject'] ) ? 0 : 1;
	$pos                 = isset( $input['position'] ) ? sanitize_key( $input['position'] ) : 'bottom-right';
	$out['position']     = in_array( $pos, array( 'bottom-right', 'bottom-left' ), true ) ? $pos : 'bottom-right';
	$out['button_label'] = isset( $input['button_label'] ) ? sanitize_text_field( $input['button_label'] ) : '';
	$out['welcome']      = isset( $input['welcome'] ) ? sanitize_text_field( $input['welcome'] ) : '';
	$max                 = isset( $input['max_results'] ) ? absint( $input['max_results'] ) : 3;
	$out['max_results']  = min( 5, max( 1, $max ) );

	// Content selection may have changed: rebuild the local index on next use.
	delete_option( NYYO_DOCS_CHAT_INDEX_OPT );
	return $out;
}

/**
 * Register the settings.
 */
function nyyo_docs_chat_register_settings() {
	register_setting(
		'nyyo_docs_chat_group',
		NYYO_DOCS_CHAT_OPT,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'nyyo_docs_chat_sanitize',
			'default'           => nyyo_docs_chat_defaults(),
		)
	);
}
add_action( 'admin_init', 'nyyo_docs_chat_register_settings' );

/**
 * Settings page menu entry.
 */
function nyyo_docs_chat_admin_menu() {
	add_options_page(
		__( 'Nyyo Docs Chatbot', 'nyyo-docs-chatbot' ),
		__( 'Nyyo Docs Chatbot', 'nyyo-docs-chatbot' ),
		'manage_options',
		'nyyo-docs-chatbot',
		'nyyo_docs_chat_render_settings'
	);
}
add_action( 'admin_menu', 'nyyo_docs_chat_admin_menu' );

/**
 * Settings link on the Plugins screen.
 *
 * @param array $links Links.
 * @return array
 */
function nyyo_docs_chat_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=nyyo-docs-chatbot' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'nyyo-docs-chatbot' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'nyyo_docs_chat_action_links' );

/**
 * Render the settings page.
 */
function nyyo_docs_chat_render_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s       = nyyo_docs_chat_get_settings();
	$opt     = NYYO_DOCS_CHAT_OPT;
	$index   = Nyyo_Docs_Chat_Index::get();
	$types   = get_post_types( array( 'public' => true ), 'objects' );
	$rebuilt = isset( $_GET['nyyo_rebuilt'] ) ? absint( wp_unslash( $_GET['nyyo_rebuilt'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only flag after a nonce-checked redirect.
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Nyyo Docs Chatbot', 'nyyo-docs-chatbot' ); ?></h1>
		<?php if ( $rebuilt ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Index rebuilt.', 'nyyo-docs-chatbot' ); ?></p></div>
		<?php endif; ?>
		<p>
			<?php
			printf(
				/* translators: %s: the shortcode. */
				esc_html__( 'Place %s on any page, or turn on the floating widget below.', 'nyyo-docs-chatbot' ),
				'<code>[nyyo_docs_chat]</code>'
			);
			?>
		</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'nyyo_docs_chat_group' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Answer source', 'nyyo-docs-chatbot' ); ?></th>
					<td>
						<fieldset>
							<label><input type="radio" name="<?php echo esc_attr( $opt ); ?>[mode]" value="local" <?php checked( $s['mode'], 'local' ); ?> />
								<?php esc_html_e( 'This site (recommended): answers from your published content, on your server. No external service.', 'nyyo-docs-chatbot' ); ?></label><br />
							<label><input type="radio" name="<?php echo esc_attr( $opt ); ?>[mode]" value="external" <?php checked( $s['mode'], 'external' ); ?> />
								<?php esc_html_e( 'External chatbot page: embeds the URL below in a frame. Visitors\' browsers load that page and send their questions to it.', 'nyyo-docs-chatbot' ); ?></label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="nyyo_bot_url"><?php esc_html_e( 'External chatbot URL', 'nyyo-docs-chatbot' ); ?></label></th>
					<td>
						<input name="<?php echo esc_attr( $opt ); ?>[bot_url]" type="url" id="nyyo_bot_url" placeholder="https://" value="<?php echo esc_attr( $s['bot_url'] ); ?>" class="regular-text" />
						<p class="description"><?php esc_html_e( 'Only used in External mode, and empty by default. Enter the address of your own chatbot page, for example https://help.example.com/chat/.', 'nyyo-docs-chatbot' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Content to answer from', 'nyyo-docs-chatbot' ); ?></th>
					<td>
						<fieldset>
						<?php foreach ( $types as $type ) : ?>
							<?php
							if ( 'attachment' === $type->name ) {
								continue;
							}
							?>
							<label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[post_types][]" value="<?php echo esc_attr( $type->name ); ?>" <?php checked( in_array( $type->name, (array) $s['post_types'], true ) ); ?> /> <?php echo esc_html( $type->labels->name ); ?></label><br />
						<?php endforeach; ?>
						</fieldset>
						<p class="description"><?php esc_html_e( 'Only published, public, non password-protected content is used.', 'nyyo-docs-chatbot' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Floating widget', 'nyyo-docs-chatbot' ); ?></th>
					<td><label><input name="<?php echo esc_attr( $opt ); ?>[auto_inject]" type="checkbox" value="1" <?php checked( $s['auto_inject'], 1 ); ?> /> <?php esc_html_e( 'Show a floating chat button on all public pages', 'nyyo-docs-chatbot' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><label for="nyyo_position"><?php esc_html_e( 'Button position', 'nyyo-docs-chatbot' ); ?></label></th>
					<td>
						<select name="<?php echo esc_attr( $opt ); ?>[position]" id="nyyo_position">
							<option value="bottom-right" <?php selected( $s['position'], 'bottom-right' ); ?>><?php esc_html_e( 'Bottom right', 'nyyo-docs-chatbot' ); ?></option>
							<option value="bottom-left" <?php selected( $s['position'], 'bottom-left' ); ?>><?php esc_html_e( 'Bottom left', 'nyyo-docs-chatbot' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="nyyo_label"><?php esc_html_e( 'Button label', 'nyyo-docs-chatbot' ); ?></label></th>
					<td><input name="<?php echo esc_attr( $opt ); ?>[button_label]" type="text" id="nyyo_label" value="<?php echo esc_attr( $s['button_label'] ); ?>" placeholder="<?php echo esc_attr__( 'Ask our docs', 'nyyo-docs-chatbot' ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="nyyo_welcome"><?php esc_html_e( 'Welcome message', 'nyyo-docs-chatbot' ); ?></label></th>
					<td><input name="<?php echo esc_attr( $opt ); ?>[welcome]" type="text" id="nyyo_welcome" value="<?php echo esc_attr( $s['welcome'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="nyyo_max"><?php esc_html_e( 'Sources per answer', 'nyyo-docs-chatbot' ); ?></label></th>
					<td><input name="<?php echo esc_attr( $opt ); ?>[max_results]" type="number" min="1" max="5" id="nyyo_max" value="<?php echo esc_attr( (string) $s['max_results'] ); ?>" class="small-text" /></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>

		<h2><?php esc_html_e( 'Local index', 'nyyo-docs-chatbot' ); ?></h2>
		<p>
			<?php
			if ( $index ) {
				printf(
					/* translators: 1: number of pages/posts, 2: number of passages. */
					esc_html__( '%1$d items indexed into %2$d passages. The index is rebuilt automatically when content changes.', 'nyyo-docs-chatbot' ),
					(int) $index['items'],
					count( $index['chunks'] )
				);
			} else {
				esc_html_e( 'The index will be built on the first question, or click the button below.', 'nyyo-docs-chatbot' );
			}
			?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="nyyo_docs_chat_rebuild" />
			<?php wp_nonce_field( 'nyyo_docs_chat_rebuild' ); ?>
			<?php submit_button( __( 'Rebuild index now', 'nyyo-docs-chatbot' ), 'secondary', 'submit', false ); ?>
		</form>
	</div>
	<?php
}

/**
 * Handle the "Rebuild index" button (nonce + capability checked).
 */
function nyyo_docs_chat_handle_rebuild() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'nyyo-docs-chatbot' ), 403 );
	}
	check_admin_referer( 'nyyo_docs_chat_rebuild' );
	Nyyo_Docs_Chat_Index::build( nyyo_docs_chat_get_settings() );
	wp_safe_redirect( admin_url( 'options-general.php?page=nyyo-docs-chatbot&nyyo_rebuilt=1' ) );
	exit;
}
add_action( 'admin_post_nyyo_docs_chat_rebuild', 'nyyo_docs_chat_handle_rebuild' );

/**
 * Drop the index when content changes; it is rebuilt lazily on the next question.
 */
function nyyo_docs_chat_invalidate() {
	delete_option( NYYO_DOCS_CHAT_INDEX_OPT );
}
add_action( 'save_post', 'nyyo_docs_chat_invalidate' );
add_action( 'deleted_post', 'nyyo_docs_chat_invalidate' );
add_action( 'trashed_post', 'nyyo_docs_chat_invalidate' );

/**
 * REST route: GET /wp-json/nyyo-docs-chat/v1/ask?q=...
 * Public and read-only: it only returns excerpts of content that is already public.
 */
function nyyo_docs_chat_register_rest() {
	register_rest_route(
		'nyyo-docs-chat/v1',
		'/ask',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'permission_callback' => '__return_true',
			'callback'            => 'nyyo_docs_chat_rest_ask',
			'args'                => array(
				'q' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => static function ( $value ) {
						return is_string( $value ) && '' !== trim( $value ) && strlen( $value ) <= 500;
					},
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'nyyo_docs_chat_register_rest' );

/**
 * REST callback.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function nyyo_docs_chat_rest_ask( $request ) {
	$s      = nyyo_docs_chat_get_settings();
	$result = Nyyo_Docs_Chat_Index::answer( (string) $request->get_param( 'q' ), $s );
	if ( ! $result['sources'] ) {
		$result['answer'] = __( 'I could not find this on this site. Try other words, or contact us.', 'nyyo-docs-chatbot' );
	}
	return rest_ensure_response( $result );
}

/**
 * Register front-end assets (enqueued only when needed).
 */
function nyyo_docs_chat_register_assets() {
	$s = nyyo_docs_chat_get_settings();
	wp_register_script( 'nyyo-docs-chat-widget', plugins_url( 'assets/widget.js', __FILE__ ), array(), NYYO_DOCS_CHAT_VERSION, true );
	wp_register_style( 'nyyo-docs-chat-widget', plugins_url( 'assets/widget.css', __FILE__ ), array(), NYYO_DOCS_CHAT_VERSION );
	wp_localize_script(
		'nyyo-docs-chat-widget',
		'NyyoDocsChat',
		array(
			'mode'        => $s['mode'],
			'botUrl'      => 'external' === $s['mode'] ? esc_url_raw( $s['bot_url'] ) : '',
			'restUrl'     => esc_url_raw( rest_url( 'nyyo-docs-chat/v1/ask' ) ),
			'autoInject'  => (bool) $s['auto_inject'],
			'position'    => $s['position'],
			'buttonLabel' => nyyo_docs_chat_button_label( $s ),
			'i18n'        => array(
				'welcome'     => nyyo_docs_chat_welcome( $s ),
				'placeholder' => __( 'Type your question', 'nyyo-docs-chatbot' ),
				'send'        => __( 'Ask', 'nyyo-docs-chatbot' ),
				'you'         => __( 'You', 'nyyo-docs-chatbot' ),
				'bot'         => __( 'Assistant', 'nyyo-docs-chatbot' ),
				'sources'     => __( 'Sources', 'nyyo-docs-chatbot' ),
				'error'       => __( 'Sorry, something went wrong. Please try again.', 'nyyo-docs-chatbot' ),
				'close'       => __( 'Close chat', 'nyyo-docs-chatbot' ),
				'title'       => __( 'Docs chatbot', 'nyyo-docs-chatbot' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'nyyo_docs_chat_register_assets', 5 );

/**
 * Enqueue on every page only when the floating widget is on.
 */
function nyyo_docs_chat_enqueue_widget() {
	$s = nyyo_docs_chat_get_settings();
	if ( ! empty( $s['auto_inject'] ) ) {
		wp_enqueue_script( 'nyyo-docs-chat-widget' );
		wp_enqueue_style( 'nyyo-docs-chat-widget' );
	}
}
add_action( 'wp_enqueue_scripts', 'nyyo_docs_chat_enqueue_widget' );

/**
 * Shortcode [nyyo_docs_chat height="560"].
 *
 * @param array|string $atts Attributes.
 * @return string
 */
function nyyo_docs_chat_shortcode( $atts ) {
	$s    = nyyo_docs_chat_get_settings();
	$atts = shortcode_atts( array( 'height' => '560' ), $atts, 'nyyo_docs_chat' );
	$h    = absint( $atts['height'] );
	if ( $h < 320 || $h > 1600 ) {
		$h = 560;
	}
	wp_enqueue_script( 'nyyo-docs-chat-widget' );
	wp_enqueue_style( 'nyyo-docs-chat-widget' );
	if ( 'external' === $s['mode'] ) {
		return '<div class="nyyo-docs-chat-embed"><iframe src="' . esc_url( $s['bot_url'] ) . '" title="' . esc_attr__( 'Docs chatbot', 'nyyo-docs-chatbot' ) . '" style="width:100%;height:' . esc_attr( (string) $h ) . 'px;border:0;border-radius:12px" loading="lazy"></iframe></div>';
	}
	return '<div class="nyyo-docs-chat-inline" data-nyyo-docs-chat="inline" style="height:' . esc_attr( (string) $h ) . 'px"></div>';
}
add_shortcode( 'nyyo_docs_chat', 'nyyo_docs_chat_shortcode' );
