<?php
/**
 * Class Field_Radio_Buttons - Группа радио-кнопок в виде визуальных кнопок
 */
class Field_Radio_Buttons {
    /**
     * @var array Аргументы поля
     */
    private $args;

    /**
     * Field_Radio_Buttons constructor.
     * @param array $args Аргументы поля
     */
    public function __construct(array $args = []) {
        $defaults = [
            'name' => 'radio_buttons',
            'value' => '',
            'options' => [],
            'label' => 'Выберите вариант',
            'required' => false,
            'multiple' => false,
            'button_style' => 'default', // 'default', 'pill', 'outline'
            'color' => '#0073aa',
            'wrap_class' => ''
        ];

        $this->args = array_merge($defaults, $args);
    }

    /**
     * Рендерит группу радио-кнопок в виде кнопок
     * @return string
     */
    public function render(): string {
        $required = $this->args['required'] ? ' required' : '';
        $wrap_class = !empty($this->args['wrap_class']) ? ' ' . esc_attr($this->args['wrap_class']) : '';
        $style_class = 'radio-button-style-' . esc_attr($this->args['button_style']);

        $buttons = '';
        foreach ($this->args['options'] as $value => $label) {
            $checked = $value == $this->args['value'] ? ' checked' : '';
            $id = sanitize_title($this->args['name'] . '_' . $value);

            $buttons .= sprintf(
                '<div class="radio-button-option">
                    <input type="radio" id="%s" name="%s" value="%s"%s%s class="radio-button-input">
                    <label for="%s" class="radio-button-label">%s</label>
                </div>',
                $id,
                esc_attr($this->args['name']),
                esc_attr($value),
                $checked,
                $required,
                $id,
                esc_html($label)
            );
        }

        // Инлайн стили для цвета
        $style = $this->args['color'] ? sprintf(
            '<style>
                .radio-button-field%s .radio-button-label:hover,
                .radio-button-field%s .radio-button-input:checked + .radio-button-label {
                    background-color: %s;
                    border-color: %s;
                }
            </style>',
            $wrap_class,
            $wrap_class,
            $this->args['color'],
            $this->args['color']
        ) : '';

        return sprintf(
            '<div class="radio-button-field%s %s">
                <div class="radio-button-title">%s%s</div>
                <div class="radio-button-group">%s</div>
                %s
            </div>%s',
            $wrap_class,
            $style_class,
            esc_html($this->args['label']),
            $this->args['required'] ? '<span class="required">*</span>' : '',
            $buttons,
            $this->args['required'] ? '<input type="hidden" name="'.esc_attr($this->args['name']).'_validator" value="1">' : '',
            $style
        );
    }
}