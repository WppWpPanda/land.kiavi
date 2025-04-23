<?php
class Field_Accordion {
    private $args;
    private $content = '';

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
    }

    public function addContent(string $content): void {
        $this->content .= $content;
    }

    public function render(): string {
        $is_open = $this->args['is_open'] ? ' is-open' : '';
        return sprintf(
            '<div class="accordion-wrapper %s"%s>
                <div class="accordion-header %s">
                    <span class="accordion-icon">%s</span>
                    <span class="accordion-title">%s</span>
                </div>
                <div class="accordion-content %s"%s>%s</div>
            </div>',
            esc_attr($this->args['wrapper_class']),
            $is_open,
            esc_attr($this->args['header_class']),
            $this->args['is_open'] ? esc_html($this->args['icon_open']) : esc_html($this->args['icon']),
            esc_html($this->args['title']),
            esc_attr($this->args['content_class']),
            $this->args['is_open'] ? '' : ' style="display:none;"',
            $this->content
        );
    }
}