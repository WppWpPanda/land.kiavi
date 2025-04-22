<?php
/**
 * Class Field_Text - Текстовое поле
 */
class Field_Text {
    /**
     * @var array Аргументы поля
     */
    private $args;

    /**
     * Field_Text constructor.
     *
     * @param array $args Аргументы поля
     */
    public function __construct(array $args = []) {
        $defaults = [
            'name' => 'text_field',
            'value' => '',
            'placeholder' => '',
            'label' => 'Text Field',
            'required' => false
        ];

        $this->args = array_merge($defaults, $args);
    }

    /**
     * Рендерит поле
     *
     * @return string
     */
    public function render(): string {
        $required = $this->args['required'] ? ' required' : '';

        return sprintf(
            '<div class="form-field text-field">
                <label for="%1$s">%2$s</label>
                <input type="text" id="%1$s" name="%1$s" value="%3$s" placeholder="%4$s"%5$s>
            </div>',
            esc_attr($this->args['name']),
            esc_html($this->args['label']),
            esc_attr($this->args['value']),
            esc_attr($this->args['placeholder']),
            $required
        );
    }
}