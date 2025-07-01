<?php
/*
Plugin Name: WooCommerce User Achievement Badges
Description: Reward WooCommerce users with achievement badges based on purchase behavior.
Version: 1.8.4-Beta
Author: Aidus
*/

if (!defined('ABSPATH')) exit;

/** ==== SETTINGS ==== */
function tbc_badges_get_settings() {
    $defaults = [
        'full_page_enabled' => 'no',
        'show_title' => 'yes',
        'badge_bg' => '#ff0000',
        'badge_bg_none' => 'yes',
        'badges_per_row' => 4,
        'mobile_per_row' => 2,
        'badge_size' => 128,
        'badge_gap' => 10,
        'badge_border_color' => '#896666',
        'badge_border_width' => 0,
        'badge_border_radius' => 12,
        'badge_shape' => 'rounded',
        'tooltip_enabled' => 'yes',
        'tooltip_bg' => '#000000',
        'tooltip_text' => '#ffffff',
        'tooltip_bg_locked' => '#ffffff',
        'tooltip_text_locked' => '#000000',
        'tooltip_bg_unlocked' => '#ff0000',
        'tooltip_text_unlocked' => '#008000',
        'title_position' => 'below',
        'title_font_size' => 1.1,
        'title_color' => '#000000',
        'locked_opacity' => 0.2,
        'locked_grayscale' => 'yes',
        'locked_overlay' => '',
        'hover_animation' => 'scale',
        'show_xp_on_badge' => 'yes',
        'xp_bar_bg' => '#eeeeee',
        'xp_bar_fill' => '#ffc107',
        'xp_bar_text' => '#333333',
		'hide_children_until_parent' => '',
		'overlay_enabled' => 'yes', // NEW: overlay on by default
        'overlay_show_image' => 'yes',
        'overlay_show_title' => 'yes',
        'overlay_show_xp' => 'yes',
        'overlay_show_date' => 'yes',
        'overlay_show_desc' => 'yes',
    ];
    $opts = get_option('tbc_badges_settings', []);
    return array_merge($defaults, (array)$opts);
}


add_action('admin_head', function() {
    ?>
    <style>
    .tbc-badge-admin-table .select2-container {
        width: 100% !important;
        min-width: 250px !important;
        box-sizing: border-box;
    }
    .tbc-badge-admin-table .select2-selection--single {
        width: 100% !important;
        min-width: 250px !important;
        box-sizing: border-box;
        cursor: pointer;
    }
    .tbc-badge-admin-table .select2-selection__rendered {
        width: 100% !important;
        box-sizing: border-box;
        cursor: pointer;
    }
    .tbc-badge-admin-table select.wc-product-search {
        width: 100% !important;
        min-width: 250px !important;
        box-sizing: border-box;
    }
    </style>
    <?php
});

/** ==== ADMIN SETTINGS PAGE ==== */
add_action('admin_menu', function(){
    add_submenu_page(
        'edit.php?post_type=tbc_badge',
        'Badge Display Settings',
        'Display Settings',
        'manage_options',
        'tbc_badge_settings',
        'tbc_badge_settings_page'
    );
});
function tbc_badge_settings_page() {
    if (!current_user_can('manage_options')) return;
    $opts = tbc_badges_get_settings();
    if (isset($_POST['tbc_badges_settings_submit'])) {
        check_admin_referer('tbc_badges_settings');
        $opts['full_page_enabled'] = isset($_POST['full_page_enabled']) ? 'yes' : 'no';
        $opts['show_title'] = isset($_POST['show_title']) ? 'yes' : 'no';
        $opts['badge_bg_none'] = isset($_POST['badge_bg_none']) ? 'yes' : '';
        $opts['badge_bg'] = sanitize_text_field($_POST['badge_bg']);
        $opts['badges_per_row'] = min(max((int)$_POST['badges_per_row'],2),10);
        $opts['mobile_per_row'] = max(1, min(6, (int)$_POST['mobile_per_row']));
        $opts['badge_size'] = max(16, min(256, (int)$_POST['badge_size']));
        $opts['badge_gap'] = max(0, min(48, (int)$_POST['badge_gap']));
        $opts['badge_border_color'] = sanitize_hex_color($_POST['badge_border_color']);
        $opts['badge_border_width'] = max(0, min(10, (int)$_POST['badge_border_width']));
        $opts['badge_border_radius'] = max(0, min(64, (int)$_POST['badge_border_radius']));
        $opts['badge_shape'] = in_array($_POST['badge_shape'], ['square','rounded','circle']) ? $_POST['badge_shape'] : 'rounded';
        $opts['tooltip_enabled'] = isset($_POST['tooltip_enabled']) ? 'yes' : 'no';
        $opts['tooltip_bg'] = sanitize_hex_color($_POST['tooltip_bg']);
        $opts['tooltip_text'] = sanitize_hex_color($_POST['tooltip_text']);
        $opts['show_xp_on_badge'] = isset($_POST['show_xp_on_badge']) ? 'yes' : '';
$opts['xp_bar_bg'] = sanitize_hex_color($_POST['xp_bar_bg']);
$opts['xp_bar_fill'] = sanitize_hex_color($_POST['xp_bar_fill']);
$opts['xp_bar_text'] = sanitize_hex_color($_POST['xp_bar_text']);
        $opts['tooltip_bg_locked'] = sanitize_hex_color($_POST['tooltip_bg_locked']);
        $opts['tooltip_text_locked'] = sanitize_hex_color($_POST['tooltip_text_locked']);
        $opts['tooltip_bg_unlocked'] = sanitize_hex_color($_POST['tooltip_bg_unlocked']);
        $opts['tooltip_text_unlocked'] = sanitize_hex_color($_POST['tooltip_text_unlocked']);
        $opts['title_position'] = in_array($_POST['title_position'], ['below','overlay']) ? $_POST['title_position'] : 'below';
        $opts['title_font_size'] = floatval($_POST['title_font_size']);
        $opts['title_color'] = sanitize_hex_color($_POST['title_color']);
        $opts['locked_opacity'] = floatval($_POST['locked_opacity']);
        $opts['locked_grayscale'] = isset($_POST['locked_grayscale']) ? 'yes' : '';
        $opts['locked_overlay'] = sanitize_text_field($_POST['locked_overlay']);
        $opts['hover_animation'] = in_array($_POST['hover_animation'], ['none','scale','border']) ? $_POST['hover_animation'] : 'scale';
	$opts['hide_children_until_parent'] = isset($_POST['hide_children_until_parent']) ? 'yes' : '';
        $opts['overlay_enabled'] = isset($_POST['overlay_enabled']) ? 'yes' : 'no';
        $opts['overlay_show_image'] = isset($_POST['overlay_show_image']) ? 'yes' : 'no';
        $opts['overlay_show_title'] = isset($_POST['overlay_show_title']) ? 'yes' : 'no';
        $opts['overlay_show_xp'] = isset($_POST['overlay_show_xp']) ? 'yes' : 'no';
        $opts['overlay_show_date'] = isset($_POST['overlay_show_date']) ? 'yes' : 'no';
        $opts['overlay_show_desc'] = isset($_POST['overlay_show_desc']) ? 'yes' : 'no';
        update_option('tbc_badges_settings', $opts);
        echo '<div class="updated notice notice-success is-dismissible"><p>Settings saved.</p></div>';
    }
    ?>
    <div class="wrap">
        <h2>Badge Display Settings</h2>
        <form method="post">
            <?php wp_nonce_field('tbc_badges_settings'); ?>
            <table class="form-table" style="margin-bottom: 0">
                <tr>
                    <th><label>Shortcodes:</label></th>
                    <td>
                        <input id="wc_user_badges_shortcode" type="text" readonly value="[wc_user_badges]" style="width:180px;margin-right:4px;">
                        <button class="button" onclick="tbcCopyShortcode('wc_user_badges_shortcode');return false;">Copy</button>
                        <input id="wc_user_badges_full_page_shortcode" type="text" readonly value="[wc_user_badges_full_page]" style="width:220px;margin-left:20px;margin-right:4px;">
                        <button class="button" onclick="tbcCopyShortcode('wc_user_badges_full_page_shortcode');return false;">Copy</button>
                        <input id="wc_user_badges_unlocked_shortcode" type="text" readonly value="[wc_user_badges variation=&quot;unlocked&quot;]" style="width:280px;margin-left:20px;margin-right:4px;">
                        <button class="button" onclick="tbcCopyShortcode('wc_user_badges_unlocked_shortcode');return false;">Copy</button>
                    </td>
                </tr>
                <tr>
                    <th><label for="full_page_enabled">Enable full badges page?</label></th>
                    <td><input type="checkbox" name="full_page_enabled" id="full_page_enabled" <?php checked($opts['full_page_enabled'], 'yes'); ?>> <span class="description">If enabled, a [wc_user_badges_full_page] shortcode will display all badges in a grid.</span></td>
                </tr>
                <tr>
                    <th><label for="show_title">Show badge titles?</label></th>
                    <td><input type="checkbox" name="show_title" id="show_title" <?php checked($opts['show_title'], 'yes'); ?>></td>
                </tr>
                <tr>
                    <th><label for="badge_bg">Badge background</label></th>
                    <td>
                        <input type="color" name="badge_bg" id="badge_bg" value="<?php echo esc_attr($opts['badge_bg']); ?>">
                        <label><input type="checkbox" name="badge_bg_none" value="yes" <?php checked($opts['badge_bg_none'], 'yes'); ?>> No background (transparent)</label>
                    </td>
                </tr>
                <tr>
                    <th><label for="badges_per_row">Badges per row (desktop)</label></th>
                    <td>
                        <select name="badges_per_row" id="badges_per_row">
                            <?php for($i=2;$i<=10;$i++): ?>
                                <option value="<?php echo $i; ?>" <?php selected($opts['badges_per_row'],$i); ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                        Default is 6 (recommended).
                    </td>
                </tr>
                <tr>
                    <th><label for="mobile_per_row">Badges per row (mobile)</label></th>
                    <td>
                        <select name="mobile_per_row" id="mobile_per_row">
                            <?php for($i=1;$i<=6;$i++): ?>
                                <option value="<?php echo $i; ?>" <?php selected($opts['mobile_per_row'],$i); ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                        Default is 2.
                    </td>
                </tr>
                <tr>
                    <th><label for="badge_size">Badge icon size</label></th>
                    <td>
                        <input type="number" name="badge_size" id="badge_size" value="<?php echo esc_attr($opts['badge_size']); ?>" min="16" max="256" /> px
                        Default is 64px.
                    </td>
                </tr>
                <tr>
                    <th><label for="badge_gap">Badge grid gap</label></th>
                    <td>
                        <input type="number" name="badge_gap" id="badge_gap" value="<?php echo esc_attr($opts['badge_gap']); ?>" min="0" max="48" /> px
                        Space between badges. Default is 10px.
                    </td>
                </tr>
                <tr>
                    <th>Badge border</th>
                    <td>
                        Color: <input type="color" name="badge_border_color" value="<?php echo esc_attr($opts['badge_border_color']); ?>" />
                        Width: <input type="number" name="badge_border_width" value="<?php echo esc_attr($opts['badge_border_width']); ?>" min="0" max="10" /> px
                        Radius: <input type="number" name="badge_border_radius" value="<?php echo esc_attr($opts['badge_border_radius']); ?>" min="0" max="64" /> px
                    </td>
                </tr>
                <tr>
                    <th>Badge shape</th>
                    <td>
                        <label><input type="radio" name="badge_shape" value="square" <?php checked($opts['badge_shape'],'square'); ?>> Square</label>
                        <label><input type="radio" name="badge_shape" value="rounded" <?php checked($opts['badge_shape'],'rounded'); ?>> Rounded</label>
                        <label><input type="radio" name="badge_shape" value="circle" <?php checked($opts['badge_shape'],'circle'); ?>> Circle</label>
                    </td>
                </tr>
                <tr>
                    <th>Tooltip</th>
                    <td>
                        <label><input type="checkbox" name="tooltip_enabled" value="yes" <?php checked($opts['tooltip_enabled'],'yes'); ?>> Enable tooltip</label><br>
                        BG: <input type="color" name="tooltip_bg" value="<?php echo esc_attr($opts['tooltip_bg']); ?>" />
                        Text: <input type="color" name="tooltip_text" value="<?php echo esc_attr($opts['tooltip_text']); ?>" /><br>
                        Locked BG: <input type="color" name="tooltip_bg_locked" value="<?php echo esc_attr($opts['tooltip_bg_locked']); ?>" />
                        Text: <input type="color" name="tooltip_text_locked" value="<?php echo esc_attr($opts['tooltip_text_locked']); ?>" />
                        Unlocked BG: <input type="color" name="tooltip_bg_unlocked" value="<?php echo esc_attr($opts['tooltip_bg_unlocked']); ?>" />
                        Text: <input type="color" name="tooltip_text_unlocked" value="<?php echo esc_attr($opts['tooltip_text_unlocked']); ?>" />
                    </td>
                </tr>
                <tr>
                    <th>Badge title display</th>
                    <td>
                        <label><input type="radio" name="title_position" value="below" <?php checked($opts['title_position'],'below'); ?>> Below badge</label>
                        <label><input type="radio" name="title_position" value="overlay" <?php checked($opts['title_position'],'overlay'); ?>> Overlay</label>
                        Font size: <input type="number" step="0.1" min="0.6" max="3" name="title_font_size" value="<?php echo esc_attr($opts['title_font_size']); ?>" /> em
                        Color: <input type="color" name="title_color" value="<?php echo esc_attr($opts['title_color']); ?>" />
                    </td>
                </tr>
                <tr>
                    <th>Locked badge appearance</th>
                    <td>
                        Opacity: <input type="number" step="0.01" min="0" max="1" name="locked_opacity" value="<?php echo esc_attr($opts['locked_opacity']); ?>" />
                        <label><input type="checkbox" name="locked_grayscale" value="yes" <?php checked($opts['locked_grayscale'],'yes'); ?>> Grayscale</label>
                        Overlay text/icon: <input type="text" name="locked_overlay" value="<?php echo esc_attr($opts['locked_overlay']); ?>" placeholder="e.g. 🔒" />
                    </td>
                </tr>
                <tr>
                    <th>Animation</th>
                    <td>
                        <select name="hover_animation">
                            <option value="none" <?php selected($opts['hover_animation'],'none'); ?>>None</option>
                            <option value="scale" <?php selected($opts['hover_animation'],'scale'); ?>>Scale on hover</option>
                            <option value="border" <?php selected($opts['hover_animation'],'border'); ?>>Border color on hover</option>
                        </select>
                    </td>
                </tr>
                <tr>
    <th>XP Overlay on Badges</th>
    <td>
        <label>
            <input type="checkbox" name="show_xp_on_badge" value="yes" <?php checked($opts['show_xp_on_badge'], 'yes'); ?>>
            Show XP overlay (number) on badges
        </label>
    </td>
</tr>
<tr>
    <th>XP Bar Colours</th>
    <td>
        Background: <input type="color" name="xp_bar_bg" value="<?php echo esc_attr($opts['xp_bar_bg']); ?>" /> &nbsp;
        Fill: <input type="color" name="xp_bar_fill" value="<?php echo esc_attr($opts['xp_bar_fill']); ?>" /> &nbsp;
        Text: <input type="color" name="xp_bar_text" value="<?php echo esc_attr($opts['xp_bar_text']); ?>" />
        <br><span class="description">Affects the [wc_user_badges_xp_bar] shortcode.</span>
    </td>
</tr>
<tr>
    <th>Hide Child Badges</th>
    <td>
        <label>
            <input type="checkbox" name="hide_children_until_parent" value="yes" <?php checked($opts['hide_children_until_parent'], 'yes'); ?>>
            Hide child badges until parent badge is unlocked
        </label>
        <br>
        <span class="description">If enabled, any child badge will be hidden from the user until its parent is unlocked.</span>
    </td>
</tr>
 <tr>
                    <th>Badge Overlay (Lightbox)</th>
                    <td>
                        <label>
                            <input type="checkbox" name="overlay_enabled" value="yes" <?php checked($opts['overlay_enabled'], 'yes'); ?> id="tbc_overlay_enabled">
                            Enable badge overlay (lightbox)
                        </label>
                        <div id="tbc_overlay_options" style="margin-top:8px;<?php if($opts['overlay_enabled']!=='yes') echo 'display:none;'; ?>">
                            <label style="margin-right:18px;"><input type="checkbox" name="overlay_show_image" value="yes" <?php checked($opts['overlay_show_image'],'yes'); ?>> Show badge image</label>
                            <label style="margin-right:18px;"><input type="checkbox" name="overlay_show_title" value="yes" <?php checked($opts['overlay_show_title'],'yes'); ?>> Show title</label>
                            <label style="margin-right:18px;"><input type="checkbox" name="overlay_show_xp" value="yes" <?php checked($opts['overlay_show_xp'],'yes'); ?>> Show XP</label>
                            <label style="margin-right:18px;"><input type="checkbox" name="overlay_show_date" value="yes" <?php checked($opts['overlay_show_date'],'yes'); ?>> Show date</label>
                            <label style="margin-right:18px;"><input type="checkbox" name="overlay_show_desc" value="yes" <?php checked($opts['overlay_show_desc'],'yes'); ?>> Show description</label>
                        </div>
                        <script>
                        document.addEventListener('DOMContentLoaded',function(){
                            var cb = document.getElementById('tbc_overlay_enabled');
                            var opts = document.getElementById('tbc_overlay_options');
                            if(cb) cb.addEventListener('change',function(){
                                opts.style.display = this.checked ? '' : 'none';
                            });
                        });
                        </script>
                    </td>
                </tr>
                       </table>
            <p><input type="submit" name="tbc_badges_settings_submit" class="button-primary" value="Save Changes"></p>
        </form>
    </div>
    <script>
    function tbcCopyShortcode(id) {
        var input = document.getElementById(id);
        input.select();
        input.setSelectionRange(0, 99999);
        document.execCommand('copy');
        input.blur();
    }
    </script>
    <?php
}


/** ==== BADGE GRID DISPLAY ==== */
function tbc_badges_grid($atts = []) {
    $opts = tbc_badges_get_settings();
    $atts = shortcode_atts([
        'variation' => 'all', // unlocked|all
    ], $atts);

    if (!is_user_logged_in()) return '<p>Please log in to view your badges.</p>';
    $user_id = get_current_user_id();
    $badges = get_posts([
        'post_type' => 'tbc_badge',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_key' => 'tbc_badge_priority',
        'orderby' => 'meta_value_num',
        'order' => 'ASC'
    ]);

    // Get earned badges with unlock dates
    $earned_info = tbc_get_earned_badges_with_dates($user_id);

    $earned = array_keys($earned_info);
    update_user_meta($user_id, 'tbc_earned_badges', $earned);

    if ($opts['hide_children_until_parent'] === 'yes') {
        $badge_parents = [];
        foreach ($badges as $badge) {
            $parent_id = (int)get_post_meta($badge->ID, 'tbc_badge_parent_id', true);
            if ($parent_id) {
                $badge_parents[$badge->ID] = $parent_id;
            }
        }
        $badges = array_filter($badges, function($badge) use ($earned, $badge_parents) {
            $id = $badge->ID;
            if (!isset($badge_parents[$id])) return true; // not a child
            $parent_id = $badge_parents[$id];
            return in_array($parent_id, $earned);
        });
    }

    if ($atts['variation'] === 'unlocked') {
        $badges = array_filter($badges, fn($b) => in_array($b->ID, $earned));
    }
    $show_title = $opts['show_title'] === 'yes';
    $bg = ($opts['badge_bg_none'] === 'yes') ? 'transparent' : $opts['badge_bg'];
    $cols = max(min((int)$opts['badges_per_row'],10),2);
    $mobile_cols = max(min((int)$opts['mobile_per_row'],6),1);

    $badge_size = max(16, min(256, (int)$opts['badge_size']));
    $badge_gap = max(0, min(48, (int)$opts['badge_gap']));
    $border_width = max(0, min(10, (int)$opts['badge_border_width']));
    $border_color = esc_attr($opts['badge_border_color']);
    $badge_radius = max(0, min(64, (int)$opts['badge_border_radius']));
    $badge_shape = $opts['badge_shape'];
    $tooltip_enabled = $opts['tooltip_enabled'] === 'yes';
    $tooltip_bg = esc_attr($opts['tooltip_bg']);
    $tooltip_text = esc_attr($opts['tooltip_text']);
    $tooltip_bg_locked = esc_attr($opts['tooltip_bg_locked']);
    $tooltip_text_locked = esc_attr($opts['tooltip_text_locked']);
    $tooltip_bg_unlocked = esc_attr($opts['tooltip_bg_unlocked']);
    $tooltip_text_unlocked = esc_attr($opts['tooltip_text_unlocked']);
    $title_position = $opts['title_position'];
    $title_font_size = floatval($opts['title_font_size']);
    $title_color = esc_attr($opts['title_color']);
    $locked_opacity = floatval($opts['locked_opacity']);
    $locked_grayscale = $opts['locked_grayscale'] === 'yes';
    $locked_overlay = esc_html($opts['locked_overlay']);
    $hover_animation = $opts['hover_animation'];
    $title_below = $title_position === 'below';

    $radius_css = "border-radius: " . (
        $badge_shape === 'circle' ? "50%" :
        ($badge_shape === 'rounded' ? $badge_radius . "px" : "0")
    ) . ";";

    $hover_css = '';
    if ($hover_animation === 'scale') {
        $hover_css = ".tbc-badge:hover,.tbc-badge:focus { transform: scale(1.08); box-shadow:0 2px 6px #0002; }";
    } elseif ($hover_animation === 'border') {
        $hover_css = ".tbc-badge:hover,.tbc-badge:focus { border-color: #0073aa !important; }";
    }

 $overlay_enabled = ($opts['overlay_enabled'] ?? 'yes') === 'yes';
    $show_overlay_image = ($opts['overlay_show_image'] ?? 'yes') === 'yes';
    $show_overlay_title = ($opts['overlay_show_title'] ?? 'yes') === 'yes';
    $show_overlay_xp    = ($opts['overlay_show_xp'] ?? 'yes') === 'yes';
    $show_overlay_date  = ($opts['overlay_show_date'] ?? 'yes') === 'yes';
    $show_overlay_desc  = ($opts['overlay_show_desc'] ?? 'yes') === 'yes';

    ob_start();
    ?>

<style>
.tbc-badge-grid {
    display: grid;
    grid-template-columns: repeat(<?php echo $cols; ?>, 1fr);
    gap: <?php echo $badge_gap; ?>px;
    margin: 0 auto;
    max-width: 100%;
    padding: 10px 0;
}
.tbc-badge-outer {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}
.tbc-badge {
    aspect-ratio: 1 / 1;
    background: <?php echo esc_attr($bg); ?>;
    <?php echo $radius_css; ?>
    border: <?php echo $border_width; ?>px solid <?php echo $border_color; ?>;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: visible;
    padding: 0;
    width: <?php echo $badge_size; ?>px;
    height: <?php echo $badge_size; ?>px;
    max-width: none;
    cursor: pointer;
    z-index: 1;
    box-shadow: none !important;
    transition: all 0.18s cubic-bezier(.4,1.5,.5,1.08);
}
.tbc-badge.locked img {
    opacity: <?php echo $locked_opacity; ?>;
    <?php if ($locked_grayscale): ?>filter: grayscale(90%);<?php endif; ?>
}
.tbc-badge.unlocked img {
    opacity: 1;
}
.tbc-badge img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
    border-radius: 0;
}
.tbc-badge-title {
    display: <?php echo $title_below ? 'block' : 'none'; ?>;
    font-size: <?php echo $title_font_size; ?>em;
    color: <?php echo $title_color; ?>;
    text-align: center;
    margin-top: 6px;
}
.tbc-badge .tbc-badge-title-overlay {
    display: <?php echo !$title_below ? 'block' : 'none'; ?>;
    position: absolute;
    bottom: 6%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255,255,255,0.75);
    color: <?php echo $title_color; ?>;
    font-size: <?php echo $title_font_size; ?>em;
    padding: 0 6px;
    border-radius: 8px;
    z-index: 3;
    pointer-events: none;
    text-align: center;
}
.tbc-badge:focus { outline: 2px solid #0073aa; z-index: 10; }
<?php echo $hover_css; ?>

.tbc-badge-tooltip {
    <?php if (!$tooltip_enabled): ?>display:none!important;<?php else: ?>
    display: none;
    position: absolute;
    top: 105%;
    left: 50%;
    transform: translateX(-50%);
    padding: 8px 16px;
    border-radius: 8px;
    box-shadow: 0 2px 6px #0002;
    z-index: 100;
    white-space: pre-line;
    font-size: 1.08em;
    font-weight: 500;
    line-height: 1.4;
    min-width: 120px;
    pointer-events: none;
    margin-top: 4px;
    text-align: center;
    background: <?php echo $tooltip_bg; ?>;
    color: <?php echo $tooltip_text; ?>;
    <?php endif; ?>
}
.tbc-badge-tooltip-locked {
    background: <?php echo $tooltip_bg_locked; ?> !important;
    color: <?php echo $tooltip_text_locked; ?> !important;
    opacity: 0.95;
    border: 1px solid #ffbdbd;
    font-style: italic;
}
.tbc-badge-tooltip-unlocked {
    background: <?php echo $tooltip_bg_unlocked; ?> !important;
    color: <?php echo $tooltip_text_unlocked; ?> !important;
    opacity: 0.97;
    border: 1px solid #bdf5bd;
    font-weight: bold;
}
.tbc-badge:hover ~ .tbc-badge-tooltip,
.tbc-badge:focus ~ .tbc-badge-tooltip {
    display: <?php echo $tooltip_enabled ? 'block' : 'none'; ?>;
}

.tbc-badge .locked-overlay {
    position: absolute;
    left: 50%; top: 50%;
    transform: translate(-50%,-50%);
    z-index: 8;
    font-size: 2em;
    color: #b71c1c;
    opacity: 0.7;
    pointer-events: none;
    text-shadow: 0 1px 6px #fff, 0 0px 1px #222;
}

.tbc-badge-xp-bar-wrap {
    width: 100%;
    max-width: 420px;
    margin: 24px auto 16px;
    padding: 10px 0;
    text-align: center;
}
.tbc-badge-xp-bar-label {
    font-weight: bold;
    margin-bottom: 4px;
    display: block;
}
.tbc-badge-xp-bar-bg {
    position: relative;
    background: #eee;
    border-radius: 12px;
    width: 100%;
    height: 28px;
    margin: 0 auto;
    border: 1.5px solid #bbb;
    overflow: hidden;
    box-shadow: 0 2px 8px #0001;
}
.tbc-badge-xp-bar-fill {
    background: linear-gradient(90deg,#ff9800,#ffc107 80%,#fffde7);
    height: 100%;
    border-radius: 12px;
    width: 0;
    max-width: 100%;
    text-align: right;
    transition: width 0.7s cubic-bezier(.67,1.3,.5,1);
    font-weight: bold;
    color: #fff;
    padding-right: 12px;
    font-size: 1.15em;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    box-shadow: 0 0px 8px #ff980045;
}
.tbc-badge-xp-bar-text {
    position: absolute;
    left: 50%;
    top: 0; bottom: 0;
    transform: translateX(-50%);
    color: #333;
    font-size: 1.07em;
    font-weight: 500;
    line-height: 28px;
    width: 100%;
    text-shadow: 0 1px 6px #fff8, 0 0px 1px #2222;
    pointer-events: none;
}
@media (max-width: 750px) {
    .tbc-badge-grid {
        grid-template-columns: repeat(<?php echo $mobile_cols; ?>, 1fr);
        gap: <?php echo max(4, $badge_gap - 2); ?>px;
    }
    .tbc-badge-xp-bar-wrap { max-width: 96vw; }
}
@media (max-width: 500px) {
    .tbc-badge-grid {
        grid-template-columns: repeat(<?php echo min(2,$mobile_cols); ?>, 1fr);
        gap: <?php echo max(2, $badge_gap - 4); ?>px;
    }
}
#tbc-badge-lightbox-overlay {
    display: none;
    position: fixed;
    z-index: 9999;
    top:0; left:0; right:0; bottom:0;
    background: rgba(0,0,0,0.75);
    justify-content: center;
    align-items: center;
}
#tbc-badge-lightbox-overlay.active {
    display: flex;
}
#tbc-badge-lightbox {
    background: #fff;
    max-width: 350px;
    width: 98vw;
    border-radius: 18px;
    padding: 32px 18px 24px 18px;
    box-shadow: 0 6px 48px #0006;
    text-align: center;
    position: relative;
    animation: tbcLightboxFadeIn .18s cubic-bezier(.4,1.5,.5,1);
}
@keyframes tbcLightboxFadeIn { from { transform:scale(.92); opacity:0; } to { transform:scale(1); opacity:1; } }
#tbc-badge-lightbox-close {
    position: absolute;
    top: 9px;
    right: 14px;
    font-size: 1.6em;
    color: #b71c1c;
    background: none;
    border: none;
    cursor: pointer;
    z-index: 1001;
    font-weight: 900;
    opacity: 0.7;
}
#tbc-badge-lightbox img {
    display: block;
    margin: 0 auto 12px auto;
    width: 170px; /* Increased size */
    height: 170px;
    object-fit: contain;
    border-radius: 0;       
    background: transparent; 
    box-shadow: none;        
}

@media (max-width: 480px){
    #tbc-badge-lightbox { padding: 14px 5vw 12px 5vw; }
    #tbc-badge-lightbox img { width: 110px; height: 110px; }
}
#tbc-badge-lightbox-title {
    font-size: 1.2em;
    font-weight: bold;
    margin-bottom: 4px;
    color: #333;
}
#tbc-badge-lightbox-xp {
    font-size: 1.06em;
    font-weight: 500;
    margin-bottom: 4px;
    color: #ff9800;
}
#tbc-badge-lightbox-date {
    font-size: 0.99em;
    color: #555;
    margin-bottom: 8px;
}
#tbc-badge-lightbox-desc {
    font-size: 1.04em;
    color: #222;
    margin-bottom: 2px;
    margin-top: 7px;
}
@media (max-width: 480px){
    #tbc-badge-lightbox { padding: 14px 5vw 12px 5vw; }
    #tbc-badge-lightbox img { width: 68px; height: 68px; }
}
</style>

   <div class="tbc-badge-grid" role="list">
    <?php
    foreach ($badges as $badge) {
        $unlocked = in_array($badge->ID, $earned);
        $icon = get_post_meta($badge->ID, 'tbc_badge_icon', true) ?: wc_placeholder_img_src();
        $title = esc_html(get_the_title($badge->ID));
        $hint = esc_html(get_post_meta($badge->ID, 'tbc_badge_hint', true));
        $desc = esc_html(get_post_meta($badge->ID, 'tbc_badge_description', true));
        $tooltip = $unlocked ? $desc : $hint;
        $tooltip_class = $unlocked ? 'tbc-badge-tooltip-unlocked' : 'tbc-badge-tooltip-locked';
        $xp = (int)get_post_meta($badge->ID, 'tbc_badge_xp', true);
        if ($xp <= 0) $xp = 20;

        // For unlocked: get unlock date (as timestamp)
        $unlock_date = $unlocked && isset($earned_info[$badge->ID]['date']) ? $earned_info[$badge->ID]['date'] : '';

        echo '<div class="tbc-badge-outer">';
        echo '<div class="tbc-badge '.($unlocked?'unlocked':'locked').'" tabindex="0" aria-label="'.$title.'" role="listitem"';

        // Add lightbox data attributes for unlocked badges
       if ($unlocked && $overlay_enabled) {
            echo ' data-tbc-lightbox="1"';
            echo ' data-tbc-title="'.esc_attr($title).'"';
            echo ' data-tbc-icon="'.esc_url($icon).'"';
            echo ' data-tbc-xp="'.esc_attr($xp).'"';
            echo ' data-tbc-date="'.esc_attr($unlock_date ? date_i18n(get_option('date_format'), $unlock_date) : '').'"';
            echo ' data-tbc-desc="'.esc_attr($desc).'"';
        }
        echo '>';
        echo '<img src="'.esc_url($icon).'" alt="'.esc_attr($title).'" />';
        if (!$unlocked && $locked_overlay) {
            echo '<span class="locked-overlay">' . $locked_overlay . '</span>';
        }
        if ($show_title && !$title_below) {
            echo '<span class="tbc-badge-title-overlay">'.$title.'</span>';
        }
        if ($opts['show_xp_on_badge'] === 'yes') {
            echo '<span style="position:absolute;top:3px;right:7px;font-size:0.95em;font-weight:700;color:#ff9800;text-shadow:0 1px 6px #fff,0 0px 1px #222;pointer-events:none;z-index:12;">'.$xp.'<span style="font-size:0.7em;font-weight:600;">XP</span></span>';
        }
        echo '</div>';
        if ($tooltip_enabled) {
            echo '<div class="tbc-badge-tooltip '.$tooltip_class.'">'.esc_html($tooltip).'</div>';
        }
        if ($show_title && $title_below) {
            echo '<div class="tbc-badge-title">'.$title.'</div>';
        }
        echo '</div>';
    }
    echo '</div>';
    ?>

    <!-- Lightbox Markup -->
    <?php if ($overlay_enabled): ?>
    <!-- Lightbox Markup -->
    <div id="tbc-badge-lightbox-overlay" tabindex="-1" aria-modal="true" role="dialog">
        <div id="tbc-badge-lightbox">
            <button id="tbc-badge-lightbox-close" aria-label="Close">&times;</button>
            <?php if($show_overlay_image): ?>
                <img src="" id="tbc-badge-lightbox-img" alt="" />
            <?php endif; ?>
            <?php if($show_overlay_title): ?>
                <div id="tbc-badge-lightbox-title"></div>
            <?php endif; ?>
            <?php if($show_overlay_xp): ?>
                <div id="tbc-badge-lightbox-xp"></div>
            <?php endif; ?>
            <?php if($show_overlay_date): ?>
                <div id="tbc-badge-lightbox-date"></div>
            <?php endif; ?>
            <?php if($show_overlay_desc): ?>
                <div id="tbc-badge-lightbox-desc"></div>
            <?php endif; ?>
        </div>
    </div>
    <script>
    (function(){
        function openBadgeLightbox(data) {
            <?php if($show_overlay_image): ?>document.getElementById('tbc-badge-lightbox-img').src = data.icon || '';
            document.getElementById('tbc-badge-lightbox-img').alt = data.title || '';<?php endif; ?>
            <?php if($show_overlay_title): ?>document.getElementById('tbc-badge-lightbox-title').textContent = data.title || '';<?php endif; ?>
            <?php if($show_overlay_xp): ?>document.getElementById('tbc-badge-lightbox-xp').textContent = data.xp ? (data.xp + ' XP') : '';<?php endif; ?>
            <?php if($show_overlay_date): ?>document.getElementById('tbc-badge-lightbox-date').textContent = data.date ? ('Unlocked: ' + data.date) : '';<?php endif; ?>
            <?php if($show_overlay_desc): ?>document.getElementById('tbc-badge-lightbox-desc').textContent = data.desc || '';<?php endif; ?>
            document.getElementById('tbc-badge-lightbox-overlay').classList.add('active');
            document.getElementById('tbc-badge-lightbox-close').focus();
            document.body.style.overflow = "hidden";
        }
        function closeBadgeLightbox() {
            document.getElementById('tbc-badge-lightbox-overlay').classList.remove('active');
            document.body.style.overflow = "";
        }
        document.addEventListener('click', function(e){
            var t = e.target.closest('.tbc-badge.unlocked[data-tbc-lightbox]');
            if(t){
                e.preventDefault();
                openBadgeLightbox({
                    icon: t.getAttribute('data-tbc-icon'),
                    title: t.getAttribute('data-tbc-title'),
                    xp: t.getAttribute('data-tbc-xp'),
                    date: t.getAttribute('data-tbc-date'),
                    desc: t.getAttribute('data-tbc-desc')
                });
            }
            if(e.target.id === 'tbc-badge-lightbox-overlay' || e.target.id === 'tbc-badge-lightbox-close'){
                closeBadgeLightbox();
            }
        });
        document.addEventListener('keydown', function(e){
            if(e.key === "Escape" && document.getElementById('tbc-badge-lightbox-overlay').classList.contains('active')){
                closeBadgeLightbox();
            }
        });
    })();
    </script>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

/**
 * Get earned badges (ID => ['date'=>timestamp]) for a user, storing unlock date when first earned.
 * This will backdate to the order/item/condition met date.
 */
function tbc_get_earned_badges_with_dates($user_id) {
    // Get all badges
    $badges = get_posts([
        'post_type' => 'tbc_badge',
        'posts_per_page' => -1
    ]);

    $earned = [];
    $orders = wc_get_orders([
        'customer_id' => $user_id,
        'status' => 'completed',
        'limit' => -1
    ]);

    $product_ids = [];
    $category_ids = [];
    $badge_dates = get_user_meta($user_id, 'tbc_badge_unlock_dates', true);
    if (!is_array($badge_dates)) $badge_dates = [];

    // Map product and category to earliest unlocked date
    $product_dates = [];
    $category_dates = [];
    $spend_checkpoints = [];
    $total_spend = 0;

    foreach ($orders as $order) {
        $order_date = strtotime($order->get_date_completed() ? $order->get_date_completed() : $order->get_date_created());
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (!$product) continue;
            $pid = $product->get_id();
            $product_ids[] = $pid;
            if (!isset($product_dates[$pid]) || $order_date < $product_dates[$pid]) {
                $product_dates[$pid] = $order_date;
            }

            if ($product->is_type('variation')) {
                $parent_id = $product->get_parent_id();
                $cats = wp_get_post_terms($parent_id, 'product_cat', ['fields' => 'ids']);
            } else {
                $cats = wp_get_post_terms($pid, 'product_cat', ['fields' => 'ids']);
            }
            foreach ($cats as $cat_id) {
                $category_ids[] = (int) $cat_id;
                if (!isset($category_dates[$cat_id]) || $order_date < $category_dates[$cat_id]) {
                    $category_dates[$cat_id] = $order_date;
                }
            }
            $total_spend += $item->get_total();
            $spend_checkpoints[] = ['date' => $order_date, 'total' => $total_spend];
        }
    }
    $product_ids = array_unique(array_map('intval', $product_ids));
    $category_ids = array_unique(array_map('intval', $category_ids));

    foreach ($badges as $badge) {
        $type = get_post_meta($badge->ID, 'tbc_badge_type', true);

        if ($type === 'always') {
            $date = $badge_dates[$badge->ID] ?? strtotime(get_userdata($user_id)->user_registered);
            $earned[$badge->ID] = ['date' => $date];
            $badge_dates[$badge->ID] = $date;
        }

        if ($type === 'product') {
            $pids = get_post_meta($badge->ID, 'tbc_badge_product_id', true);
            $pids = array_filter(array_map('intval', (array)$pids));
            $match = array_intersect($pids, $product_ids);
            if ($match) {
                $date = $badge_dates[$badge->ID] ?? null;
                if (!$date) {
                    $date = PHP_INT_MAX;
                    foreach ($match as $pid) {
                        if (isset($product_dates[$pid]) && $product_dates[$pid] < $date) {
                            $date = $product_dates[$pid];
                        }
                    }
                    if ($date === PHP_INT_MAX) $date = time();
                }
                $earned[$badge->ID] = ['date' => $date];
                $badge_dates[$badge->ID] = $date;
            }
        }

        if ($type === 'category') {
            $cids = get_post_meta($badge->ID, 'tbc_badge_category_id', true);
            $cids = array_filter(array_map('intval', (array)$cids));
            $match = array_intersect($cids, $category_ids);
            if ($match) {
                $date = $badge_dates[$badge->ID] ?? null;
                if (!$date) {
                    $date = PHP_INT_MAX;
                    foreach ($match as $cid) {
                        if (isset($category_dates[$cid]) && $category_dates[$cid] < $date) {
                            $date = $category_dates[$cid];
                        }
                    }
                    if ($date === PHP_INT_MAX) $date = time();
                }
                $earned[$badge->ID] = ['date' => $date];
                $badge_dates[$badge->ID] = $date;
            }
        }

        if ($type === 'spend') {
            $thresh = floatval(get_post_meta($badge->ID, 'tbc_badge_spend_threshold', true));
            if ($thresh && $total_spend >= $thresh) {
                // Find the earliest checkpoint where spend >= threshold
                $date = $badge_dates[$badge->ID] ?? null;
                if (!$date) {
                    foreach ($spend_checkpoints as $c) {
                        if ($c['total'] >= $thresh) {
                            $date = $c['date'];
                            break;
                        }
                    }
                    if (!$date) $date = time();
                }
                $earned[$badge->ID] = ['date' => $date];
                $badge_dates[$badge->ID] = $date;
            }
        }
    }

    // Save back if changed
    update_user_meta($user_id, 'tbc_badge_unlock_dates', $badge_dates);

    return $earned;
}

/** ==== XP BAR SHORTCODE ==== */
function tbc_badges_xp_bar_shortcode($atts = []) {
    if (!is_user_logged_in()) return '';
    $user_id = get_current_user_id();

    // Get all badges
    $badges = get_posts([
        'post_type' => 'tbc_badge',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ]);
    if (!$badges) return '';

    // Sum unlocked XP and total XP
    $earned = tbc_get_earned_badges($user_id);
    if (!is_array($earned)) $earned = [];

    $total_xp = 0;
    $user_xp = 0;

    foreach ($badges as $badge) {
        $xp = (int)get_post_meta($badge->ID, 'tbc_badge_xp', true);
        if ($xp <= 0) $xp = 20;
        $total_xp += $xp;
        if (in_array($badge->ID, $earned)) $user_xp += $xp;
    }

    if ($total_xp <= 0) return '';

    $percent = min(100, round(($user_xp / $total_xp) * 100, 1));

    // Get XP bar colours from settings
    $opts = tbc_badges_get_settings();
    $bar_bg = esc_attr($opts['xp_bar_bg']);
    $bar_fill = esc_attr($opts['xp_bar_fill']);
    $bar_text = esc_attr($opts['xp_bar_text']);

    ob_start();
    ?>
    <div class="tbc-badge-xp-bar-wrap">
        <span class="tbc-badge-xp-bar-label">
            XP Progress
        </span>
        <div class="tbc-badge-xp-bar-bg" style="background:<?php echo $bar_bg; ?>;">
            <div class="tbc-badge-xp-bar-fill" style="background:<?php echo $bar_fill; ?>;width:<?php echo esc_attr($percent); ?>%;"></div>
            <span class="tbc-badge-xp-bar-text" style="color:<?php echo $bar_text; ?>;"><?php echo esc_html($user_xp); ?> / <?php echo esc_html($total_xp); ?> XP (<?php echo esc_html($percent); ?>%)</span>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('wc_user_badges_xp_bar', 'tbc_badges_xp_bar_shortcode');

/** ==== SHORTCODES ==== */
add_shortcode('wc_user_badges', function($atts){
    return tbc_badges_grid($atts);
});
add_shortcode('wc_user_badges_full_page', function($atts){
    $opts = tbc_badges_get_settings();
    if($opts['full_page_enabled']!=='yes') return '';
    return '<h2>User Badges</h2>' . tbc_badges_grid(['variation'=>'all']);
});

/** ==== BADGE LOGIC + CACHE ==== */
function tbc_get_earned_badges($user_id) {
    $badges = get_posts([
        'post_type' => 'tbc_badge',
        'posts_per_page' => -1
    ]);

    $earned = [];
    $orders = wc_get_orders([
        'customer_id' => $user_id,
        'status' => 'completed',
        'limit' => -1
    ]);

    $total_spend = 0;
    $product_ids = [];
    $category_ids = [];

    foreach ($orders as $order) {
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (!$product) continue;

            $product_ids[] = $product->get_id();

            if ($product->is_type('variation')) {
                $parent_id = $product->get_parent_id();
                $cats = wp_get_post_terms($parent_id, 'product_cat', ['fields' => 'ids']);
            } else {
                $cats = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'ids']);
            }

            foreach ($cats as $cat_id) {
                $category_ids[] = (int) $cat_id;
            }

            $total_spend += $item->get_total();
        }
    }

    $product_ids = array_unique(array_map('intval', $product_ids));
    $category_ids = array_unique(array_map('intval', $category_ids));

    foreach ($badges as $badge) {
        $type = get_post_meta($badge->ID, 'tbc_badge_type', true);

        if ($type === 'always') {
            $earned[] = $badge->ID;
        }

        if ($type === 'product') {
            $pids = get_post_meta($badge->ID, 'tbc_badge_product_id', true);
            $pids = array_filter(array_map('intval', (array)$pids));
            if ($pids && array_intersect($pids, $product_ids)) {
                $earned[] = $badge->ID;
            }
        }

        if ($type === 'category') {
            $cids = get_post_meta($badge->ID, 'tbc_badge_category_id', true);
            $cids = array_filter(array_map('intval', (array)$cids));
            if ($cids && array_intersect($cids, $category_ids)) {
                $earned[] = $badge->ID;
            }
        }

        if ($type === 'spend') {
            $thresh = floatval(get_post_meta($badge->ID, 'tbc_badge_spend_threshold', true));
            if ($thresh && $total_spend >= $thresh) {
                $earned[] = $badge->ID;
            }
        }
    }

    return array_unique($earned);
}

/** ==== CPT: tbc_badge ==== */
add_action('init', function () {
    register_post_type('tbc_badge', [
        'labels' => [
            'name' => 'Badges',
            'singular_name' => 'Badge',
            'add_new' => 'Add New Badge',
            'add_new_item' => 'Add New Badge',
            'edit_item' => 'Edit Badge',
            'new_item' => 'New Badge',
            'view_item' => 'View Badge',
            'search_items' => 'Search Badges',
        ],
        'public' => false,
        'show_ui' => true,
        'supports' => ['title'],
        'menu_icon' => 'dashicons-awards',
    ]);
});


/** ==== Admin UI: Badge Fields ==== */
add_action('add_meta_boxes', function () {
    add_meta_box('tbc_badge_fields', 'Badge Details', 'tbc_badge_fields_callback', 'tbc_badge', 'normal', 'default');
});
function tbc_badge_fields_callback($post) {
    $icon = get_post_meta($post->ID, 'tbc_badge_icon', true);
    $type = get_post_meta($post->ID, 'tbc_badge_type', true) ?: 'always';
    $product = get_post_meta($post->ID, 'tbc_badge_product_id', true);
    $product_ids = is_array($product) ? array_map('intval', $product) : ($product ? [intval($product)] : []);
    $category = get_post_meta($post->ID, 'tbc_badge_category_id', true);
    $category_ids = is_array($category) ? array_map('intval', $category) : ($category ? [intval($category)] : []);
    $spend = get_post_meta($post->ID, 'tbc_badge_spend_threshold', true);
    $xp = get_post_meta($post->ID, 'tbc_badge_xp', true);
    if ($xp === '' || $xp === false) $xp = 20;
    $hint = get_post_meta($post->ID, 'tbc_badge_hint', true);
    $desc = get_post_meta($post->ID, 'tbc_badge_description', true);
    $priority = get_post_meta($post->ID, 'tbc_badge_priority', true);

    wp_nonce_field('tbc_badge_save', 'tbc_badge_nonce');
    ?>
    <style>
    .tbc-badge-admin-table th { text-align: left; width: 140px; }
    .tbc-badge-admin-table input[type="text"], .tbc-badge-admin-table input[type="number"], .tbc-badge-admin-table select { width: 100%; }
    .tbc-badge-admin-table .tbc-badge-media-preview { max-width: 48px; max-height: 48px; display: block; }
    </style>
    <table class="form-table tbc-badge-admin-table">
        <tr>
            <th><label for="tbc_badge_icon">Badge Icon</label></th>
            <td>
                <img class="tbc-badge-media-preview" src="<?php echo esc_url($icon ?: ''); ?>" <?php if(!$icon) echo 'style="display:none"'; ?> />
                <input type="text" name="tbc_badge_icon" id="tbc_badge_icon" value="<?php echo esc_attr($icon); ?>" />
                <button class="button tbc-upload-badge-icon">Select Image</button>
            </td>
        </tr>
        <tr>
            <th><label for="tbc_badge_type">Unlock Type</label></th>
            <td>
                <select name="tbc_badge_type" id="tbc_badge_type">
                    <option value="always" <?php selected($type, 'always'); ?>>Always unlocked</option>
                    <option value="product" <?php selected($type, 'product'); ?>>By Product</option>
                    <option value="category" <?php selected($type, 'category'); ?>>By Category</option>
                    <option value="spend" <?php selected($type, 'spend'); ?>>By Spend</option>
                </select>
            </td>
        </tr>
        <tr class="tbc-badge-product" style="display:<?php echo $type == 'product' ? 'table-row' : 'none'; ?>;">
            <th><label for="tbc_badge_product_id">Product</label></th>
            <td>
                <select id="tbc_badge_product_id" name="tbc_badge_product_id[]" class="wc-product-search" multiple data-placeholder="Select a product">
                    <?php
                    $args = [
                        'post_type'      => 'product',
                        'posts_per_page' => 100,
                        'orderby'        => 'title',
                        'order'          => 'ASC',
                    ];
                    $products = get_posts($args);
                    foreach ($products as $product_post) {
                        printf(
                            '<option value="%d"%s>%s</option>',
                            $product_post->ID,
                            in_array($product_post->ID, $product_ids, true) ? ' selected' : '',
                            esc_html($product_post->post_title)
                        );
                    }
                    // Ensure any selected products beyond the query appear
                    foreach ($product_ids as $pid) {
                        if (!array_filter($products, function($p) use($pid){ return $p->ID == $pid; })) {
                            $post_obj = get_post($pid);
                            if ($post_obj) {
                                printf('<option value="%d" selected>%s</option>', $pid, esc_html($post_obj->post_title));
                            }
                        }
                    }
                    ?>
                </select>
                <span class="description">Please enter 3 or more characters</span>
            </td>
        </tr>
        <tr class="tbc-badge-category" style="display:<?php echo $type == 'category' ? 'table-row' : 'none'; ?>;">
            <th><label for="tbc_badge_category_id">Category</label></th>
            <td>
                <select id="tbc_badge_category_id" name="tbc_badge_category_id[]" class="wc-category-search" multiple data-placeholder="Select a category">
                    <?php
                    $terms = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
                    foreach ($terms as $term) {
                        printf('<option value="%d"%s>%s</option>', $term->term_id, in_array($term->term_id, $category_ids, true) ? ' selected' : '', esc_html($term->name));
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr class="tbc-badge-spend" style="display:<?php echo $type == 'spend' ? 'table-row' : 'none'; ?>;">
            <th><label for="tbc_badge_spend_threshold">Spend Threshold</label></th>
            <td>
                <input type="number" step="0.01" name="tbc_badge_spend_threshold" id="tbc_badge_spend_threshold" value="<?php echo esc_attr($spend); ?>" min="0" />
            </td>
        </tr>
        <tr>
            <th><label for="tbc_badge_xp">XP Value</label></th>
            <td>
                <input type="number" step="1" min="1" name="tbc_badge_xp" id="tbc_badge_xp" value="<?php echo esc_attr($xp); ?>" style="width:100px;" /> <span style="font-size:0.95em;color:#666;">Default 20 XP</span>
            </td>
        </tr>
        <tr>
            <th><label for="tbc_badge_hint">Hint (Locked)</label></th>
            <td><input type="text" name="tbc_badge_hint" id="tbc_badge_hint" value="<?php echo esc_attr($hint); ?>" /></td>
        </tr>
        <tr>
            <th><label for="tbc_badge_description">Description (Unlocked)</label></th>
            <td><input type="text" name="tbc_badge_description" id="tbc_badge_description" value="<?php echo esc_attr($desc); ?>" /></td>
        </tr>
        <tr>
            <th><label for="tbc_badge_priority">Priority</label></th>
            <td><input type="number" name="tbc_badge_priority" id="tbc_badge_priority" value="<?php echo esc_attr($priority); ?>" min="0" /></td>
        </tr>
<tr>
    <th><label for="tbc_badge_parent_id">Parent Badge</label></th>
    <td>
        <select id="tbc_badge_parent_id" name="tbc_badge_parent_id">
            <option value="">(No Parent)</option>
            <?php
            $badges_query = get_posts(['post_type'=>'tbc_badge','posts_per_page'=>-1,'post__not_in'=>[$post->ID],'orderby'=>'title','order'=>'ASC']);
            $current_parent = get_post_meta($post->ID, 'tbc_badge_parent_id', true);
            foreach ($badges_query as $badge_post) {
                printf('<option value="%d"%s>%s</option>',
                    $badge_post->ID,
                    selected($current_parent, $badge_post->ID, false),
                    esc_html(get_the_title($badge_post->ID))
                );
            }
            ?>
        </select>
        <span class="description">Optional: select a parent badge. Child badges can be hidden until parent is unlocked.</span>
    </td>
</tr>
    </table>
<script>
jQuery(function($){
    $('#tbc_badge_type').on('change',function(){
        $('.tbc-badge-product, .tbc-badge-category, .tbc-badge-spend').hide();
        if(this.value==='product') $('.tbc-badge-product').show();
        if(this.value==='category') $('.tbc-badge-category').show();
        if(this.value==='spend') $('.tbc-badge-spend').show();
    });
    $('.tbc-upload-badge-icon').on('click',function(e){
        e.preventDefault();
        var $input = $('#tbc_badge_icon');
        var $preview = $('.tbc-badge-media-preview');
        var frame = wp.media({title:'Select Badge Icon',button:{text:'Use this icon'},multiple:false});
        frame.on('select',function(){
            var url = frame.state().get('selection').first().toJSON().url;
            $input.val(url);
            $preview.attr('src',url).show();
        });
        frame.open();
    });
    if(typeof $.fn.select2 !== 'undefined'){
        $('.wc-product-search').select2({
            width: '100%',
            multiple: true,
            ajax: {
                url: tbc_badge_admin.ajax_url,
                dataType: 'json',
                delay: 250,
                data: function(params){
                    return {
                        term: params.term,
                        action: 'woocommerce_json_search_products_and_variations',
                        security: tbc_badge_admin.search_products_nonce
                    };
                },
                processResults: function(data){
                    var results = [];
                    $.each(data, function(id, text){ results.push({id:id, text:text}); });
                    return {results:results};
                }
            },
            allowClear: true,
            dropdownParent: $('#tbc_badge_fields')
        });
        // Add Select2 to the category select as well
        $('.wc-category-search').select2({
            width: '100%',
            multiple: true,
            placeholder: function(){
                return $(this).data('placeholder') || 'Select a category';
            }
        });
    }
});
</script>
<?php
}

add_action('admin_enqueue_scripts', function($hook){
    global $typenow;
    if($typenow === 'tbc_badge'){
        if (function_exists('wc_enqueue_js')) {
            wp_enqueue_script('wc-product-search');
        }
        wp_enqueue_script('select2');
        wp_enqueue_style('select2');
        wp_enqueue_media();
        // Localize nonce for product search
        wp_localize_script('wc-product-search', 'tbc_badge_admin', [
            'search_products_nonce' => wp_create_nonce('search-products'),
            'ajax_url' => admin_url('admin-ajax.php')
        ]);
    }
});

add_action('save_post_tbc_badge', function ($post_id) {
    if (!isset($_POST['tbc_badge_nonce']) || !wp_verify_nonce($_POST['tbc_badge_nonce'], 'tbc_badge_save')) return;
    foreach (['icon','type','product_id','category_id','spend_threshold','hint','description','priority','xp','parent_id'] as $field) {
        if (isset($_POST["tbc_badge_$field"])) {
            // Priority uniqueness enforcement
            if ($field === 'priority') {
                $new_priority = intval($_POST["tbc_badge_$field"]);
                // Find any other badge with this priority
                $args = [
                    'post_type' => 'tbc_badge',
                    'posts_per_page' => -1,
                    'post__not_in' => [$post_id],
                    'meta_key' => 'tbc_badge_priority',
                    'meta_value' => $new_priority,
                ];
                $conflicts = get_posts($args);
                // If conflict, shift priorities of all >= new_priority up by 1
                if ($conflicts) {
                    $badges = get_posts([
                        'post_type' => 'tbc_badge',
                        'posts_per_page' => -1,
                        'post__not_in' => [$post_id],
                        'meta_key' => 'tbc_badge_priority',
                        'orderby' => 'meta_value_num',
                        'order' => 'DESC',
                    ]);
                    foreach ($badges as $badge) {
                        $prio = intval(get_post_meta($badge->ID, 'tbc_badge_priority', true));
                        if ($prio >= $new_priority) {
                            update_post_meta($badge->ID, 'tbc_badge_priority', $prio + 1);
                        }
                    }
                }
                update_post_meta($post_id, "tbc_badge_$field", $new_priority);
            } else if ($field === 'xp') {
                $xp_val = intval($_POST["tbc_badge_$field"]);
                if ($xp_val < 1) $xp_val = 20;
                update_post_meta($post_id, "tbc_badge_$field", $xp_val);
            } else if ($field === 'product_id') {
                $vals = array_map('intval', (array)$_POST["tbc_badge_$field"]);
                update_post_meta($post_id, "tbc_badge_$field", $vals);
            } else if ($field === 'category_id') {
                $vals = array_map('intval', (array)$_POST["tbc_badge_$field"]);
                update_post_meta($post_id, "tbc_badge_$field", $vals);
            } else {
                update_post_meta($post_id, "tbc_badge_$field", sanitize_text_field($_POST["tbc_badge_$field"]));
            }
        } else {
            delete_post_meta($post_id, "tbc_badge_$field");
        }
    }
}, 10, 1);


/** ==== ADMIN LIST: PRIORITY COLUMN, CLICK TO MOVE ==== */
add_filter('manage_edit-tbc_badge_columns', function($columns) {
    $columns['priority'] = 'Priority';
    return $columns;
});
add_action('manage_tbc_badge_posts_custom_column', function($column, $post_id){
    if ($column === 'priority') {
        $prio = get_post_meta($post_id, 'tbc_badge_priority', true);
        echo '<span class="badge-priority-val">'.$prio.'</span> ';
        echo '<a href="'.esc_url(admin_url('edit.php?post_type=tbc_badge&action=up&post='.$post_id)).'" title="Move up">&#x25B2;</a> ';
        echo '<a href="'.esc_url(admin_url('edit.php?post_type=tbc_badge&action=down&post='.$post_id)).'" title="Move down">&#x25BC;</a>';
    }
}, 10, 2);
add_filter('manage_edit-tbc_badge_sortable_columns', function($cols){
    $cols['priority'] = 'priority';
    return $cols;
});
// Sort by priority by default
add_action('pre_get_posts', function($query){
    if (!is_admin() || $query->get('post_type') !== 'tbc_badge' || !$query->is_main_query()) return;
    $orderby = $query->get('orderby');
    if ($orderby === 'priority' || !$orderby) {
        $query->set('meta_key', 'tbc_badge_priority');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'ASC');
    }
});
// Click up/down to move
add_action('admin_init', function(){
    if(!is_admin() || !isset($_GET['post_type']) || $_GET['post_type'] !== 'tbc_badge') return;
    if(isset($_GET['action']) && in_array($_GET['action'], ['up','down']) && isset($_GET['post'])){
        $post_id = intval($_GET['post']);
        $current = get_post_meta($post_id, 'tbc_badge_priority', true);
        if ($current === '') $current = 0;
        $direction = $_GET['action'] === 'up' ? -1 : 1;
        $swap_with_id = null; $swap_with_prio = null;
        $badges = get_posts([
            'post_type' => 'tbc_badge',
            'posts_per_page' => -1,
            'meta_key' => 'tbc_badge_priority',
            'orderby' => 'meta_value_num',
            'order' => 'ASC'
        ]);
        $badges = array_values($badges);
        foreach ($badges as $i => $b) {
            if ($b->ID == $post_id) {
                $swap_with = $badges[$i+$direction] ?? null;
                if ($swap_with) {
                    $swap_with_id = $swap_with->ID;
                    $swap_with_prio = get_post_meta($swap_with_id, 'tbc_badge_priority', true);
                }
                break;
            }
        }
        if ($swap_with_id) {
            update_post_meta($post_id, 'tbc_badge_priority', $swap_with_prio);
            update_post_meta($swap_with_id, 'tbc_badge_priority', $current);
        }
        wp_redirect(remove_query_arg(['action','post']));
        exit;
    }
});

/** ==== OPTIONAL: My Account endpoint (off by default, enable if wanted) ==== */
if (apply_filters('wc_user_badges_add_account_endpoint', false)) {
    add_action('init', function () {
        add_rewrite_endpoint('badges', EP_ROOT | EP_PAGES);
    });
    add_filter('query_vars', function ($vars) {
        $vars[] = 'badges';
        return $vars;
    });
    add_filter('woocommerce_account_menu_items', function ($items) {
        $items['badges'] = 'My Badges';
        return $items;
    });
    add_action('woocommerce_account_badges_endpoint', function () {
        echo do_shortcode('[wc_user_badges variation="all"]');
    });
    register_activation_hook(__FILE__, function () { flush_rewrite_rules(); });
    register_deactivation_hook(__FILE__, function () { flush_rewrite_rules(); });
}

// -- END --