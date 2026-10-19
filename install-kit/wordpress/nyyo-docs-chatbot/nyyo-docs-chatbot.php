<?php
/**
 * Plugin Name: Nyyo Docs Chatbot
 * Description: Embed the Nyyo docs chatbot as a shortcode or auto-injected site widget. Settings: bot URL.
 * Version: 1.0.0
 * Author: Nyyo Automation
 * License: MIT
 * Text Domain: nyyo-docs-chatbot
 */

if (!defined('ABSPATH')) {
    exit;
}

define('NYYO_DOCS_CHAT_VERSION', '1.0.0');
define('NYYO_DOCS_CHAT_OPT', 'nyyo_docs_chat_settings');

function nyyo_docs_chat_defaults() {
    return array(
        'bot_url'      => 'https://nyyoautomation.github.io/nyyo-docs-chatbot/',
        'auto_inject'  => 0,
        'position'     => 'bottom-right',
        'button_label' => 'Ask docs',
    );
}

function nyyo_docs_chat_get_settings() {
    $saved = get_option(NYYO_DOCS_CHAT_OPT, array());
    if (!is_array($saved)) {
        $saved = array();
    }
    return array_merge(nyyo_docs_chat_defaults(), $saved);
}

function nyyo_docs_chat_sanitize($input) {
    $out = nyyo_docs_chat_defaults();
    if (!is_array($input)) {
        return $out;
    }
    if (!empty($input['bot_url'])) {
        $out['bot_url'] = esc_url_raw($input['bot_url']);
    }
    $out['auto_inject'] = empty($input['auto_inject']) ? 0 : 1;
    $pos = isset($input['position']) ? sanitize_text_field($input['position']) : 'bottom-right';
    $out['position'] = in_array($pos, array('bottom-right', 'bottom-left'), true) ? $pos : 'bottom-right';
    if (!empty($input['button_label'])) {
        $out['button_label'] = sanitize_text_field($input['button_label']);
    }
    return $out;
}

function nyyo_docs_chat_register_settings() {
    register_setting('nyyo_docs_chat_group', NYYO_DOCS_CHAT_OPT, array(
        'type'              => 'array',
        'sanitize_callback' => 'nyyo_docs_chat_sanitize',
        'default'           => nyyo_docs_chat_defaults(),
    ));
}
add_action('admin_init', 'nyyo_docs_chat_register_settings');

function nyyo_docs_chat_admin_menu() {
    add_options_page(
        'Nyyo Docs Chatbot',
        'Nyyo Chatbot',
        'manage_options',
        'nyyo-docs-chatbot',
        'nyyo_docs_chat_render_settings'
    );
}
add_action('admin_menu', 'nyyo_docs_chat_admin_menu');

function nyyo_docs_chat_render_settings() {
    if (!current_user_can('manage_options')) {
        return;
    }
    $s = nyyo_docs_chat_get_settings();
    ?>
    <div class="wrap">
      <h1>Nyyo Docs Chatbot</h1>
      <p>Paste the URL of your chatbot page (GitHub Pages, Vercel, or our hosted demo). Use the shortcode <code>[nyyo_docs_chat]</code> or enable auto-inject.</p>
      <form method="post" action="options.php">
        <?php settings_fields('nyyo_docs_chat_group'); ?>
        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><label for="nyyo_bot_url">Bot URL</label></th>
            <td><input name="<?php echo esc_attr(NYYO_DOCS_CHAT_OPT); ?>[bot_url]" type="url" id="nyyo_bot_url" value="<?php echo esc_attr($s['bot_url']); ?>" class="regular-text" required /></td>
          </tr>
          <tr>
            <th scope="row">Auto-inject widget</th>
            <td><label><input name="<?php echo esc_attr(NYYO_DOCS_CHAT_OPT); ?>[auto_inject]" type="checkbox" value="1" <?php checked($s['auto_inject'], 1); ?> /> Show floating button on all public pages</label></td>
          </tr>
          <tr>
            <th scope="row"><label for="nyyo_position">Position</label></th>
            <td>
              <select name="<?php echo esc_attr(NYYO_DOCS_CHAT_OPT); ?>[position]" id="nyyo_position">
                <option value="bottom-right" <?php selected($s['position'], 'bottom-right'); ?>>Bottom right</option>
                <option value="bottom-left" <?php selected($s['position'], 'bottom-left'); ?>>Bottom left</option>
              </select>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="nyyo_label">Button label</label></th>
            <td><input name="<?php echo esc_attr(NYYO_DOCS_CHAT_OPT); ?>[button_label]" type="text" id="nyyo_label" value="<?php echo esc_attr($s['button_label']); ?>" class="regular-text" /></td>
          </tr>
        </table>
        <?php submit_button(); ?>
      </form>
    </div>
    <?php
}

function nyyo_docs_chat_enqueue_widget() {
    $s = nyyo_docs_chat_get_settings();
    if (is_admin()) {
        return;
    }
    wp_register_script(
        'nyyo-docs-chat-widget',
        plugins_url('assets/widget.js', __FILE__),
        array(),
        NYYO_DOCS_CHAT_VERSION,
        true
    );
    wp_localize_script('nyyo-docs-chat-widget', 'NyyoDocsChat', array(
        'botUrl'      => esc_url_raw($s['bot_url']),
        'autoInject'  => (bool) $s['auto_inject'],
        'position'    => $s['position'],
        'buttonLabel' => $s['button_label'],
    ));
    wp_enqueue_script('nyyo-docs-chat-widget');
    wp_enqueue_style(
        'nyyo-docs-chat-widget',
        plugins_url('assets/widget.css', __FILE__),
        array(),
        NYYO_DOCS_CHAT_VERSION
    );
}
add_action('wp_enqueue_scripts', 'nyyo_docs_chat_enqueue_widget');

function nyyo_docs_chat_shortcode($atts) {
    $s = nyyo_docs_chat_get_settings();
    $atts = shortcode_atts(array(
        'url'    => $s['bot_url'],
        'height' => '640',
    ), $atts, 'nyyo_docs_chat');
    $url = esc_url($atts['url']);
    $h   = absint($atts['height']);
    if ($h < 320) {
        $h = 640;
    }
    return '<div class="nyyo-docs-chat-embed"><iframe src="' . $url . '" title="Nyyo docs chatbot" style="width:100%;height:' . $h . 'px;border:0;border-radius:12px" loading="lazy"></iframe></div>';
}
add_shortcode('nyyo_docs_chat', 'nyyo_docs_chat_shortcode');
