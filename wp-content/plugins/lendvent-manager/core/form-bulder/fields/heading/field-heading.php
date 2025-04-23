<?php
/**
 * Class Field_Heading - Поле заголовка
 */
class Field_Heading {
    /**
     * @var array Аргументы поля
     */
    private $args;

    /**
     * Field_Heading constructor.
     *
     * @param array $args Аргументы поля
     */
    public function __construct(array $args = []) {
        $defaults = [
            'text' => 'Заголовок',
            'type' => 'h2', // h1, h2, h3, h4, h5, h6
            'align' => 'left', // left, center, right
            'color' => '#333333',
            'extra_classes' => '',
            'wrapper' => true,
            'margin_top' => '',
            'margin_bottom' => '20px'
        ];

        $this->args = array_merge($defaults, $args);
    }

    /**
     * Рендерит заголовок
     *
     * @return string
     */
    public function render(): string {
        $styles = [
            'color' => $this->args['color'],
            'text-align' => $this->args['align'],
            'margin-top' => $this->args['margin_top'],
            'margin-bottom' => $this->args['margin_bottom']
        ];

        $style_string = '';
        foreach ($styles as $prop => $value) {
            if (!empty($value)) {
                $style_string .= $prop . ':' . $value . ';';
            }
        }

        $classes = 'form-heading ' . $this->args['extra_classes'];
        $heading = sprintf(
            '<%1$s class="%2$s" style="%3$s">%4$s</%1$s>',
            esc_attr($this->args['type']),
            esc_attr($classes),
            esc_attr($style_string),
            esc_html($this->args['text'])
        );

        if ($this->args['wrapper']) {
            return sprintf(
                '<div class="heading-field-wrapper">%s</div>',
                $heading
            );
        }

        return $heading;
    }
}