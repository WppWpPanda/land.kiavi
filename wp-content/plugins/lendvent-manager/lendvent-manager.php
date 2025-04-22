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

    /**
     * Добавляет шаг в форму
     * @param string $title Заголовок шага
     * @param array $fields Массив полей
     * @param string|null $step_key Ключ шага (опционально)
     * @return $this
     */
    public function wpp_add_step($title, $fields, $step_key = null) {
        $step = [
            'title' => sanitize_text_field($title),
            'fields' => $this->wpp_sanitize_fields($fields),
            'key' => $step_key ? sanitize_key($step_key) : 'step-' . count($this->steps)
        ];

        $this->steps[] = $step;
        return $this;
    }

    /**
     * Устанавливает зависимости между полями
     * @param string $field_name Имя поля
     * @param array $conditions Условия (значение => шаг)
     * @return $this
     */
    public function wpp_set_dependency($field_name, $conditions) {
        $this->dependencies[sanitize_key($field_name)] = $conditions;
        return $this;
    }

    /**
     * Рендерит текущий шаг формы
     * @return string HTML код формы
     */
    public function wpp_render() {
        if (empty($this->steps)) {
            return '<div class="wpp-error">Нет шагов для отображения</div>';
        }

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
     * Обрабатывает отправку формы
     * @param array $data Данные формы
     * @return array|bool Результат обработки
     */
    public function wpp_process($data) {
        if (!isset($data['wpp_current_step'])) {
            return false;
        }

        $this->current_step = intval($data['wpp_current_step']);
        $this->form_data = array_merge($this->form_data, $data);

        // Проверяем зависимости для определения следующего шага
        if (isset($data['wpp_next'])) {
            $next_step = $this->wpp_get_next_step();
            if ($next_step !== false) {
                $this->current_step = $next_step;
            }
        } elseif (isset($data['wpp_back'])) {
            $this->current_step = max(0, $this->current_step - 1);
        }

        return $this->form_data;
    }

    /**
     * Рендерит навигацию формы
     * @return string HTML код навигации
     */
    private function wpp_render_navigation() {
        $output = '<div class="wpp-form-navigation">';

        if ($this->current_step > 0) {
            $output .= '<button type="submit" name="wpp_back" class="wpp-button-back">Go Back</button>';
        }

        if ($this->current_step < count($this->steps) - 1) {
            $output .= '<button type="submit" name="wpp_next" class="wpp-button-next">Next</button>';
        } else {
            $output .= '<button type="submit" name="wpp_submit" class="wpp-button-submit">Submit</button>';
        }

        $output .= '<input type="hidden" name="wpp_current_step" value="' . esc_attr($this->current_step) . '">';
        $output .= '</div>';

        return $output;
    }

    /**
     * Рендерит поле формы
     * @param array $field Параметры поля
     * @return string HTML код поля
     */
    private function wpp_render_field($field) {
        $output = '';
        $value = isset($this->form_data[$field['name']]) ? $this->form_data[$field['name']] : '';

        switch ($field['type']) {
            case 'text':
            case 'number':
                $output .= '<div class="wpp-field-group">';
                $output .= '<label>' . esc_html($field['label']) . '</label>';
                $output .= '<input type="' . esc_attr($field['type']) . '" name="' . esc_attr($field['name']) . '" value="' . esc_attr($value) . '">';
                $output .= '</div>';
                break;

            case 'select':
                $output .= '<div class="wpp-field-group">';
                $output .= '<label>' . esc_html($field['label']) . '</label>';
                $output .= '<select name="' . esc_attr($field['name']) . '">';
                foreach ($field['options'] as $option) {
                    $selected = $value == $option['value'] ? ' selected' : '';
                    $output .= '<option value="' . esc_attr($option['value']) . '"' . $selected . '>' . esc_html($option['label']) . '</option>';
                }
                $output .= '</select>';
                $output .= '</div>';
                break;

            case 'radio':
            case 'checkbox':
                $output .= '<div class="wpp-field-group">';
                $output .= '<fieldset>';
                $output .= '<legend>' . esc_html($field['label']) . '</legend>';
                foreach ($field['options'] as $option) {
                    $checked = is_array($value) ? in_array($option['value'], $value) : $value == $option['value'];
                    $output .= '<label>';
                    $output .= '<input type="' . esc_attr($field['type']) . '" name="' . esc_attr($field['name']) . ($field['type'] == 'checkbox' ? '[]' : '') . '" value="' . esc_attr($option['value']) . '"' . ($checked ? ' checked' : '') . '>';
                    $output .= esc_html($option['label']);
                    $output .= '</label><br>';
                }
                $output .= '</fieldset>';
                $output .= '</div>';
                break;

            case 'section':
                $output .= '<div class="wpp-section">';
                $output .= '<h2>' . esc_html($field['title']) . '</h2>';
                if (!empty($field['description'])) {
                    $output .= '<p>' . esc_html($field['description']) . '</p>';
                }
                $output .= '</div>';
                break;
        }

        return $output;
    }

    /**
     * Определяет следующий шаг на основе зависимостей
     * @return int|false Номер следующего шага или false
     */
    private function wpp_get_next_step() {
        foreach ($this->dependencies as $field_name => $conditions) {
            if (isset($this->form_data[$field_name])) {
                $field_value = $this->form_data[$field_name];
                if (isset($conditions[$field_value])) {
                    $target_step = $this->wpp_find_step_by_key($conditions[$field_value]);
                    if ($target_step !== false) {
                        return $target_step;
                    }
                }
            }
        }

        // По умолчанию переходим на следующий шаг
        return $this->current_step < count($this->steps) - 1 ? $this->current_step + 1 : false;
    }

    /**
     * Находит шаг по ключу
     * @param string $step_key Ключ шага
     * @return int|false Номер шага или false
     */
    private function wpp_find_step_by_key($step_key) {
        foreach ($this->steps as $index => $step) {
            if ($step['key'] === $step_key) {
                return $index;
            }
        }
        return false;
    }

    /**
     * Санитизирует массив полей
     * @param array $fields Массив полей
     * @return array Санитизированный массив
     */
    private function wpp_sanitize_fields($fields) {
        $sanitized = [];
        foreach ($fields as $field) {
            $sanitized_field = [
                'type' => sanitize_key($field['type']),
                'name' => sanitize_key($field['name']),
                'label' => sanitize_text_field($field['label']),
            ];

            if (isset($field['options'])) {
                $sanitized_field['options'] = array_map(function($option) {
                    return [
                        'value' => sanitize_text_field($option['value']),
                        'label' => sanitize_text_field($option['label'])
                    ];
                }, $field['options']);
            }

            if (isset($field['title'])) {
                $sanitized_field['title'] = sanitize_text_field($field['title']);
            }

            if (isset($field['description'])) {
                $sanitized_field['description'] = sanitize_text_field($field['description']);
            }

            $sanitized[] = $sanitized_field;
        }
        return $sanitized;
    }
}