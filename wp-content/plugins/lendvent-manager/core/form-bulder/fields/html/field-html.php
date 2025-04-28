<?php
/**
 * Class Field_Html - Поле для произвольного HTML
 */
class Field_Html {
    /**
     * @var array Аргументы поля
     */
    private $args;

    /**
     * Field_Html constructor.
     * @param array $args Аргументы поля
     */
    public function __construct(array $args = []) {
        $this->args = wp_parse_args($args, [
            'content' => '',
            'wrapper_class' => '',
            'before' => '',
            'after' => ''
        ]);
    }

    /**
     * Рендерит HTML-поле
     * @return string
     */
    public function render(): string {
        $wrapper_class = !empty($this->args['wrapper_class']) ?
            ' class="' . esc_attr($this->args['wrapper_class']) . '"' : '';

        return sprintf(
            '<div%s>%s%s%s</div>',
            $wrapper_class,
            $this->args['before'],
            $this->args['content'], // Осторожно: не экранируется специально!
            $this->args['after']
        );
    }
}