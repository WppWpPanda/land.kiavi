<?php
/**
 * Class Field_Select - Поле выбора (select)
 */
class Field_Select {
    /**
     * @var array Аргументы поля
     */
    private $args;

    /**
     * Field_Select constructor.
     *
     * @param array $args Аргументы поля
     */
    public function __construct(array $args = []) {
        $defaults = [
            'name' => 'select_field',
            'value' => '',
            'options' => [],
            'label' => 'Select Field',
            'required' => false,
            'multiple' => false,
            'placeholder' => 'Выберите вариант'
        ];

        $this->args = array_merge($defaults, $args);
    }

    /**
     * Рендерит поле select
     *
     * @return string
     */
    public function render(): string {
        $required = $this->args['required'] ? ' required' : '';
        $multiple = $this->args['multiple'] ? ' multiple' : '';
        $name = $this->args['multiple'] ? $this->args['name'] . '[]' : $this->args['name'];
        
        $options = '';
        
        // Добавляем placeholder если есть
        if (!empty($this->args['placeholder']) && !$this->args['multiple']) {
            $options .= sprintf(
                '<option value="" disabled selected>%s</option>',
                esc_html($this->args['placeholder'])
            );
        }
        
        // Добавляем опции
        foreach ($this->args['options'] as $value => $label) {
            $selected = is_array($this->args['value']) 
                ? in_array($value, $this->args['value']) ? ' selected' : ''
                : ($value == $this->args['value'] ? ' selected' : '');
                
            $options .= sprintf(
                '<option value="%s"%s>%s</option>',
                esc_attr($value),
                $selected,
                esc_html($label)
            );
        }
        
        return sprintf(
            '<div class="form-field select-field">
                <label for="%1$s">%2$s</label>
                <select id="%1$s" name="%3$s"%4$s%5$s>
                    %6$s
                </select>
            </div>',
            esc_attr($this->args['name']),
            esc_html($this->args['label']),
            esc_attr($name),
            $required,
            $multiple,
            $options
        );
    }
}