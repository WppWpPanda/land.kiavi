<h3><?php _e('Регистрация', 'wpp-auth'); ?></h3>
<form id="wpp-register-form" method="post">
    <label for="wpp-reg-username"><?php _e('Имя пользователя', 'wpp-auth'); ?></label>
    <input type="text" id="wpp-reg-username" name="username" required>
    <label for="wpp-reg-email"><?php _e('Email', 'wpp-auth'); ?></label>
    <input type="email" id="wpp-reg-email" name="email" required>
    <label for="wpp-reg-password"><?php _e('Пароль', 'wpp-auth'); ?></label>
    <input type="password" id="wpp-reg-password" name="password" required>
    <button type="submit"><?php _e('Зарегистрироваться', 'wpp-auth'); ?></button>
    <div class="wpp-error-message"></div>
</form>

<p><a href="#" data-form="login"><?php _e('Войти', 'wpp-auth'); ?></a></p>