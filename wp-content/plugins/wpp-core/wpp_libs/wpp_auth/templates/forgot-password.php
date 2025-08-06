<h3><?php _e('Забыли пароль?', 'wpp-auth'); ?></h3>
<form id="wpp-forgot-password-form" method="post">
    <label for="wpp-forgot-email"><?php _e('Email', 'wpp-auth'); ?></label>
    <input type="email" id="wpp-forgot-email" name="email" required>
    <button type="submit"><?php _e('Сбросить пароль', 'wpp-auth'); ?></button>
    <div class="wpp-error-message"></div>
</form>

<p><a href="#" data-form="login"><?php _e('Войти', 'wpp-auth'); ?></a></p>