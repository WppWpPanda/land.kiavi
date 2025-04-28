<?php
class Field_Accordion_Repeater_Block {
    private $args;
    private $formFieldsManager;

    public function __construct(array $args = [], FormFieldsManager $formFieldsManager = null) {
        $defaults = [
            'name' => 'accordion_repeater_block',
            'title' => 'Блоки с аккордеоном',
            'add_button_text' => 'Добавить блок',
            'remove_button_text' => 'Удалить блок',
            'min_blocks' => 0,
            'max_blocks' => 0,
            'fields' => [],
            'values' => [],
            'default_open' => false,
            'icon' => '▸',
            'icon_open' => '▾'
        ];

        $this->args = wp_parse_args($args, $defaults);
        $this->formFieldsManager = $formFieldsManager ?? FormFieldsManager::getInstance();
    }

    public function render(): string {
        $html = '<div class="accordion-block-field" 
                data-name="' . esc_attr($this->args['name']) . '" 
                data-min="' . (int)$this->args['min_blocks'] . '" 
                data-max="' . (int)$this->args['max_blocks'] . '">';

        $html .= '<label class="accordion-block-label">' . esc_html($this->args['title']) . '</label>';
        $html .= '<div class="accordion-block-rows">';

        // Рендер существующих блоков
        if (is_array($this->args['values'])) {
            foreach ($this->args['values'] as $index => $values) {
                $html .= $this->render_block($index, $values);
            }
        }

        $html .= '</div>';

        // Кнопка добавления
        $html .= '<button type="button" class="accordion-block-add">' . esc_html($this->args['add_button_text']) . '</button>';

        // Шаблон нового блока
        $html .= '<script type="text/template" class="accordion-block-template">';
        $html .= $this->render_block('{{index}}');
        $html .= '</script>';

        $html .= '</div>';

        return $html;
    }

    private function render_block($index, $values = []): string {
        $block_title = $values['_title'] ?? 'Блок ' . ((int)$index + 1);
        $is_open = $this->args['default_open'] ? ' is-open' : '';

        $html = '<div class="accordion-block-row' . $is_open . '">';
        $html .= '<div class="accordion-block-header">
                    <span class="accordion-block-icon">' . esc_html($this->args['icon_open']) . '</span>
                    <span class="accordion-block-title">' . esc_html($block_title) . '</span>
                  </div>';
        $html .= '<div class="accordion-block-content">';
        $html .= '<div class="accordion-block-fields">';

        // Поле для заголовка блока
        $html .= $this->formFieldsManager->renderField('text', [
            'name' => $this->args['name'] . '[' . (int)$index . '][_title]',
            'label' => 'Заголовок блока',
            'value' => $block_title
        ]);

        // Рендер полей контента
        if (is_array($this->args['fields'])) {
            foreach ($this->args['fields'] as $field) {
                if (!isset($field['name'], $field['type'], $field['args'])) {
                    continue;
                }

                $field['args']['name'] = $this->args['name'] . '[' . (int)$index . '][' . $field['name'] . ']';

                if (isset($values[$field['name']])) {
                    $field['args']['value'] = $values[$field['name']];
                }

                $html .= $this->formFieldsManager->renderField($field['type'], $field['args']);
            }
        }

        $html .= '</div>';
        $html .= '<button type="button" class="accordion-block-remove">' . esc_html($this->args['remove_button_text']) . '</button>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}