<?php
/**
 * Class Field_Checkbox - Чекбокс или группа чекбоксов
 */
class Field_Checkbox {
    /**
     * @var array Аргументы поля
     */
    private $args;

    /**
     * Field_Checkbox constructor.
     *
     * @param array $args Аргументы поля
     */
    public function __construct(array $args = []) {
        $defaults = [
            'name' => 'checkbox_field',
            'value' => [],
            'options' => [], // Для группы чекбоксов
            'label' => '', // Общая метка для группы
            'option_label' => '', // Метка для одиночного чекбокса
            'required' => false,
            'inline' => false,
            'single' => false, // Одиночный чекбокс
            'wrap_class' => ''
        ];

        $this->args = array_merge($defaults, $args);
    }

    /**
     * Рендерит чекбокс(ы)
     *
     * @return string
     */
    public function render(): string {
        $required = $this->args['required'] ? ' required' : '';
        $inline_class = $this->args['inline'] ? ' checkbox-inline' : '';
        $wrap_class = !empty($this->args['wrap_class']) ? ' ' . esc_attr($this->args['wrap_class']) : '';

        // Одиночный чекбокс
        if ($this->args['single'] || empty($this->args['options'])) {
            $checked = $this->args['value'] ? ' checked' : '';

            return sprintf(
                '<div class="form-field checkbox-field%4$s">
                    <label>
                        <input type="checkbox" name="%1$s" value="1"%5$s%6$s>
                        %2$s %3$s
                    </label>
                </div>',
                esc_attr($this->args['name']),
                esc_html($this->args['option_label']),
                $this->args['required'] ? '<span class="required">*</span>' : '',
                $wrap_class,
                $checked,
                $required
            );
        }

        // Группа чекбоксов
        $checkboxes = '';
        foreach ($this->args['options'] as $value => $option_label) {
            $checked = is_array($this->args['value']) && in_array($value, $this->args['value']) ? ' checked' : '';

            $checkboxes .= sprintf(
                '<div class="checkbox-option%4$s">
                    <input type="checkbox" id="%1$s_%2$s" name="%1$s[]" value="%2$s"%5$s%6$s>
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
            '<div class="form-field checkbox-group%4$s">
                <div class="field-label">%1$s</div>
                <div class="checkbox-options">%2$s</div>
                %3$s
            </div>',
            esc_html($this->args['label']),
            $checkboxes,
            $this->args['required'] ? '<span class="required">*</span>' : '',
            $wrap_class
        );
    }
}