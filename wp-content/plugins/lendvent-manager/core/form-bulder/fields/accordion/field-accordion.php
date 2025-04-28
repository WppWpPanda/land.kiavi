<?php
class Field_Accordion {
    private $args;
    private $content;

    public function __construct(array $args = []) {
        $this->args = wp_parse_args($args, [
            'title' => 'Аккордеон',
            'is_open' => false,
            'icon' => '▸',
            'icon_open' => '▾',
            'wrapper_class' => '',
            'header_class' => '',
            'content_class' => ''
        ]);

        $this->content = $args['content'] ?? '';
    }

    public function render(): string {
        $is_open = $this->args['is_open'] ? ' is-open' : '';

        return sprintf(
            '<div class="accordion-field %s"%s>
                <button type="button" class="accordion-header %s" aria-expanded="%s">
                    <span class="accordion-icon">%s</span>
                    <span class="accordion-title">%s</span>
                </button>
                <div class="accordion-content %s"%s>%s</div>
            </div>',
            esc_attr($this->args['wrapper_class']),
            $is_open,
            esc_attr($this->args['header_class']),
            $this->args['is_open'] ? 'true' : 'false',
            $this->args['is_open'] ? esc_html($this->args['icon_open']) : esc_html($this->args['icon']),
            esc_html($this->args['title']),
            esc_attr($this->args['content_class']),
            $this->args['is_open'] ? '' : ' hidden',
            $this->content
        );
    }
}