<?php
/*
Plugin Name: Lendvent Manager
*/

class WPP_MultiStepFormBuilder
{
    private $steps = [];
    private $current_step = 0;
    private $form_data = [];
    private $dependencies = [];
    private $form_id = '';
    private $ajax_url = '';

    /**
     * Конструктор класса
     * @param string $form_id Уникальный идентификатор формы
     */
    public function __construct($form_id = 'wpp-form')
    {
        $this->form_id = sanitize_key($form_id);
        $this->ajax_url = admin_url('admin-ajax.php');

        // Регистрируем AJAX обработчики
        add_action('wp_ajax_wpp_form_step', [$this, 'wpp_ajax_handler']);
        add_action('wp_ajax_nopriv_wpp_form_step', [$this, 'wpp_ajax_handler']);

        // Регистрируем скрипты
        add_action('wp_enqueue_scripts', [$this, 'wpp_register_scripts']);
    }

    /**
     * Регистрирует необходимые скрипты и стили
     */
    public function wpp_register_scripts()
    {
        wp_register_script(
            'wpp-form-builder',
            plugins_url('js/wpp-form-builder.js', __FILE__),
            ['jquery'],
            '1.0.0',
            true
        );

        wp_register_style(
            'wpp-form-builder',
            plugins_url('css/wpp-form-builder.css', __FILE__),
            [],
            '1.0.0'
        );

        // Локализация для AJAX
        wp_localize_script(
            'wpp-form-builder',
            'wpp_form_vars',
            [
                'ajax_url' => $this->ajax_url,
                'nonce' => wp_create_nonce('wpp_form_nonce'),
                'form_id' => $this->form_id
            ]
        );
    }

    /**
     * AJAX обработчик для загрузки шагов
     */
    public function wpp_ajax_handler()
    {
        check_ajax_referer('wpp_form_nonce', 'nonce');

        if (!isset($_POST['form_data']) || !isset($_POST['current_step'])) {
            wp_send_json_error('Invalid request');
        }

        $this->form_data = array_map('sanitize_text_field', $_POST['form_data']);
        $this->current_step = intval($_POST['current_step']);

        // Определяем следующий шаг
        if (isset($_POST['direction']) && $_POST['direction'] === 'next') {
            $next_step = $this->wpp_get_next_step();
            if ($next_step !== false) {
                $this->current_step = $next_step;
            }
        } elseif (isset($_POST['direction']) && $_POST['direction'] === 'prev') {
            $this->current_step = max(0, $this->current_step - 1);
        }

        ob_start();
        echo $this->wpp_render_step_content();
        $content = ob_get_clean();

        wp_send_json_success([
            'content' => $content,
            'current_step' => $this->current_step,
            'total_steps' => count($this->steps),
            'step_key' => $this->steps[$this->current_step]['key']
        ]);
    }

    /**
     * Рендерит содержимое текущего шага
     * @return string HTML код шага
     */
    private function wpp_render_step_content()
    {
        $step = $this->steps[$this->current_step];
        $output = '<div class="wpp-form-step" data-step="' . esc_attr($step['key']) . '">';
        $output .= '<h1>' . esc_html($step['title']) . '</h1>';

        foreach ($step['fields'] as $field) {
            $output .= $this->wpp_render_field($field);
        }

        $output .= $this->wpp_render_navigation();
        $output .= '</div>';

        return $output;
    }

    /**
     * Инициализирует форму
     * @return string HTML код формы
     */
    public function wpp_init_form()
    {
        wp_enqueue_script('wpp-form-builder');
        wp_enqueue_style('wpp-form-builder');

        $output = '<div id="' . esc_attr($this->form_id) . '" class="wpp-multistep-form">';
        $output .= '<form method="POST" class="wpp-form-container">';
        $output .= $this->wpp_render_step_content();
        $output .= '</form>';
        $output .= '</div>';

        return $output;
    }

    // ... (остальные методы из предыдущего примера остаются без изменений)
}