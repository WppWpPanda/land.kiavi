<?php
/**
 * Class Field_Radio - Группа радио-кнопок
 */
class Field_Radio {
    /**
     * @var array Аргументы поля
     */
    private $args;

    /**
     * Field_Radio constructor.
     *
     * @param array $args Аргументы поля
     */
    public function __construct(array $args = []) {
        $defaults = [
            'name' => 'radio_field',
            'value' => '',
            'options' => [],
            'label' => 'Radio Buttons',
            'required' => false,
            'inline' => false,
            'wrap_class' => ''
        ];

        $this->args = array_merge($defaults, $args);
    }

    /**
     * Рендерит группу радио-кнопок
     *
     * @return string
     */
    public function render(): string {
        $required = $this->args['required'] ? ' required' : '';
        $inline_class = $this->args['inline'] ? ' radio-inline' : '';
        $wrap_class = !empty($this->args['wrap_class']) ? ' ' . esc_attr($this->args['wrap_class']) : '';

        $radios = '';
        foreach ($this->args['options'] as $value => $option_label) {
            $checked = $value == $this->args['value'] ? ' checked' : '';

            $radios .= sprintf(
                '<div class="radio-option%4$s">
                    <input type="radio" id="%1$s_%2$s" name="%1$s" value="%2$s"%5$s%6$s>
                    <label for="%1$s_%2$s">%3$s</label>
                </div>',
                esc_attr($this->args['name']),
                esc_attr($value),
                esc_html($option_label),
                $inline_class,
                $checked,
                $required
            );
        }

        return sprintf(
            '<div class="form-field radio-field%4$s">
                <div class="field-label">%1$s</div>
                <div class="radio-options">%2$s</div>
                %3$s
            </div>',
            esc_html($this->args['label']),
            $radios,
            $this->args['required'] ? '<span class="required">*</span>' : '',
            $wrap_class
        );
    }
}