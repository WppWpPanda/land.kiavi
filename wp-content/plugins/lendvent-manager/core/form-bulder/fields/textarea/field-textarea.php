<?php
/**
 * Class Field_Textarea - Текстовое поле многострочного ввода
 */
class Field_Textarea {
    /**
     * @var array Аргументы поля
     */
    private $args;

    /**
     * Field_Textarea constructor.
     *
     * @param array $args Аргументы поля
     */
    public function __construct(array $args = []) {
        $defaults = [
            'name' => 'textarea_field',
            'value' => '',
            'placeholder' => '',
            'label' => 'Текстовое поле',
            'rows' => 5,
            'cols' => 40,
            'required' => false,
            'wrapper_class' => '',
            'input_class' => ''
        ];

        $this->args = array_merge($defaults, $args);
    }

    /**
     * Рендерит текстовое поле
     *
     * @return string
     */
    public function render(): string {
        $required = $this->args['required'] ? ' required' : '';
        $wrapper_class = !empty($this->args['wrapper_class']) ? ' class="' . esc_attr($this->args['wrapper_class']) . '"' : '';
        $input_class = !empty($this->args['input_class']) ? ' class="' . esc_attr($this->args['input_class']) . '"' : '';


        return sprintf(
            '<div%s>
                <label for="%s">%s%s</label>
                <textarea id="%s" name="%s" rows="%d" cols="%d" placeholder="%s"%s%s>%s</textarea>
            </div>',
            $wrapper_class,
            esc_attr($this->args['name']),
            esc_html($this->args['label']),
            $this->args['required'] ? '<span class="required">*</span>' : '',
            esc_attr($this->args['name']),
            esc_attr($this->args['name']),
            absint($this->args['rows']),
            absint($this->args['cols']),
            esc_attr($this->args['placeholder']),
            $required,
            $input_class,
            esc_textarea($this->args['value'])
        );
    }
}