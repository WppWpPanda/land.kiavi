<h3><?php _e('Вход', 'wpp-auth'); ?></h3>
<form id="wpp-login-form" method="post">
    <label for="wpp-username"><?php _e('Имя пользователя', 'wpp-auth'); ?></label>
    <input type="text" id="wpp-username" name="username" required>
    <label for="wpp-password"><?php _e('Пароль', 'wpp-auth'); ?></label>
    <input type="password" id="wpp-password" name="password" required>
    <button type="submit"><?php _e('Войти', 'wpp-auth'); ?></button>
    <div class="wpp-error-message"></div>
</form>

<p><a href="#" data-form="register"><?php _e('Регистрация', 'wpp-auth'); ?></a></p>
<p><a href="#" data-form="forgot-password"><?php _e('Забыли пароль?', 'wpp-auth'); ?></a></p>