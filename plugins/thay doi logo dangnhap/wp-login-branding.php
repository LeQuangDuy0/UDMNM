<?php
/**
 * Plugin Name: WP Login Branding
 * Description: Đổi logo & nền trang đăng nhập (logo + ảnh nền hoặc màu nền). Cấu hình tại Settings → Login Branding.
 * Version:     1.1.0
 * Author:      You
 */

if (!defined('ABSPATH')) exit;

class WP_Login_Branding {
    const OPT_GROUP = 'wlb_options';
    const OPT_LOGO  = 'wlb_logo_url';
    const OPT_BG    = 'wlb_bg_url';
    const OPT_BGC   = 'wlb_bg_color';

    public function __construct() {
        add_action('admin_menu',           [$this, 'menu']);
        add_action('admin_init',           [$this, 'register_settings']);
        add_action('admin_enqueue_scripts',[$this, 'enqueue_admin_assets']);
        add_action('login_enqueue_scripts',[$this, 'inject_css']);

        add_filter('login_headerurl', fn()=>home_url('/'));
        add_filter('login_headertext', fn()=>get_bloginfo('name'));
    }

    /* ---------------- Settings page ---------------- */

    public function menu() {
        add_options_page('Login Branding','Login Branding','manage_options','wp-login-branding',[$this,'render_page']);
    }

    public function register_settings() {
        register_setting(self::OPT_GROUP, self::OPT_LOGO, ['type'=>'string','sanitize_callback'=>'esc_url_raw','default'=>'']);
        register_setting(self::OPT_GROUP, self::OPT_BG,   ['type'=>'string','sanitize_callback'=>'esc_url_raw','default'=>'']);
        register_setting(self::OPT_GROUP, self::OPT_BGC,  ['type'=>'string','sanitize_callback'=>'sanitize_hex_color','default'=>'#f0f2f5']);
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_wp-login-branding') return;

        // Media uploader + color picker
        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        // JS nhỏ: mở media frame + colorpicker
        wp_add_inline_script('jquery', "
            jQuery(function($){
                function bindUpload(sel){
                    $(sel).on('click', function(e){
                        e.preventDefault();
                        var targetId = $(this).data('target');
                        var input    = $('#'+targetId);
                        var prev     = $('#'+targetId+'_preview');
                        var frame = wp.media({ title:'Chọn ảnh', multiple:false });
                        frame.on('select', function(){
                            var url = frame.state().get('selection').first().get('url');
                            input.val(url).trigger('input');
                            prev.attr('src', url).toggle(!!url);
                        });
                        frame.open();
                    });
                }
                bindUpload('.wlb-upload');
                $('#".self::OPT_BGC."').wpColorPicker();
            });
        ");

        // CSS preview nhẹ
        wp_add_inline_style('wp-admin', "
            .wlb-field img{max-width:260px;height:auto;display:block;margin:.5rem 0;border:1px solid #ddd;border-radius:6px}
            .wlb-field input[type=text]{width:480px}
            @media (max-width:782px){ .wlb-field input[type=text]{width:100%} }
        ");
    }

    public function render_page() {
        $logo = esc_url(get_option(self::OPT_LOGO, ''));
        $bg   = esc_url(get_option(self::OPT_BG, ''));
        $bgc  = get_option(self::OPT_BGC, '#f0f2f5');
        ?>
        <div class="wrap">
            <h1>Login Branding</h1>
            <form method="post" action="options.php">
                <?php settings_fields(self::OPT_GROUP); ?>
                <table class="form-table" role="presentation">
                    <tr class="wlb-field">
                        <th scope="row"><label for="<?php echo self::OPT_LOGO; ?>">Logo đăng nhập</label></th>
                        <td>
                            <input type="text" id="<?php echo self::OPT_LOGO; ?>" name="<?php echo self::OPT_LOGO; ?>" value="<?php echo $logo; ?>" placeholder="https://.../logo.png">
                            <button class="button wlb-upload" data-target="<?php echo self::OPT_LOGO; ?>">Chọn ảnh</button>
                            <img id="<?php echo self::OPT_LOGO; ?>_preview" src="<?php echo $logo; ?>" style="<?php echo $logo?'':'display:none'; ?>">
                            <p class="description">Nên dùng PNG nền trong suốt (gợi ý 320×100px).</p>
                        </td>
                    </tr>
                    <tr class="wlb-field">
                        <th scope="row"><label for="<?php echo self::OPT_BG; ?>">Ảnh nền (tuỳ chọn)</label></th>
                        <td>
                            <input type="text" id="<?php echo self::OPT_BG; ?>" name="<?php echo self::OPT_BG; ?>" value="<?php echo $bg; ?>" placeholder="https://.../background.jpg">
                            <button class="button wlb-upload" data-target="<?php echo self::OPT_BG; ?>">Chọn ảnh</button>
                            <img id="<?php echo self::OPT_BG; ?>_preview" src="<?php echo $bg; ?>" style="<?php echo $bg?'':'display:none'; ?>">
                            <p class="description">Nếu để trống, trang sẽ dùng màu nền bên dưới.</p>
                        </td>
                    </tr>
                    <tr class="wlb-field">
                        <th scope="row"><label for="<?php echo self::OPT_BGC; ?>">Màu nền (fallback)</label></th>
                        <td>
                            <input type="text" id="<?php echo self::OPT_BGC; ?>" name="<?php echo self::OPT_BGC; ?>" value="<?php echo esc_attr($bgc); ?>" />
                            <p class="description">Dùng khi không có ảnh nền — hoặc làm lớp dưới ảnh.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /* ---------------- Login page CSS ---------------- */

    public function inject_css() {
        $logo = esc_url(get_option(self::OPT_LOGO, ''));
        $bg   = esc_url(get_option(self::OPT_BG, ''));
        $bgc  = get_option(self::OPT_BGC, '#f0f2f5');

        // Nếu có ảnh → vừa màu vừa ảnh; nếu không → chỉ màu
        $bg_css = $bg
            ? "background: {$bgc} url('{$bg}') center/cover no-repeat;"
            : "background: {$bgc};";

        echo '<style id="wlb-login-css">
            body.login{ '.$bg_css.' }
            body.login #login{
                padding:2.2rem 1.6rem 1.6rem;
                backdrop-filter:saturate(110%);
            }
            body.login h1 a{
                '.($logo ? "background-image:url('{$logo}');" : '').'
                width:320px;height:100px;background-size:contain;background-repeat:no-repeat;background-position:center;
            }
            .login form{ border-radius:10px }
        </style>';
    }
}

new WP_Login_Branding();
