<?php
if (! defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if (! class_exists('Podify_Events_Elementor_Widget')) {

    class Podify_Events_Elementor_Widget extends Widget_Base
    {

        public function get_name()
        {
            return 'podify-events';
        }

        public function get_title()
        {
            return esc_html__('Podify Events', 'podify-events');
        }

        public function get_icon()
        {
            return 'eicon-calendar';
        }

        public function get_categories()
        {
            return ['general'];
        }

        public function get_style_depends()
        {
            if (class_exists('Podify_Events_Widget_Styles')) {
                Podify_Events_Widget_Styles::register_handles();
            }
            return ['podify-events-css', 'podify-swiper-css'];
        }

        public function get_script_depends()
        {
            if (class_exists('Podify_Events_Widget_Styles')) {
                Podify_Events_Widget_Styles::register_handles();
            }
            return ['swiper', 'podify-swiper-js', 'podify-events-js', 'podify-events-swiper'];
        }

        public function is_reload_preview_required()
        {
            return false;
        }

        protected function register_controls()
        {
            $this->start_controls_section('section_settings', ['label' => esc_html__('Settings', 'podify-events'), 'tab' => Controls_Manager::TAB_CONTENT]);

            $this->add_control('show_option', [
                'label' => esc_html__('Show Event', 'podify-events'),
                'type' => Controls_Manager::SELECT,
                'default' => 'all',
                'options' => ['all' => esc_html__('All', 'podify-events'), 'select' => esc_html__('Select', 'podify-events')],
            ]);

            $repeater = new Repeater();
            $repeater->add_control('choose_event', [
                'label' => esc_html__('Event', 'podify-events'),
                'type' => Controls_Manager::SELECT2,
                'options' => $this->get_events_for_control(),
                'label_block' => true,
            ]);

            $this->add_control('events_list', [
                'label' => esc_html__('Events', 'podify-events'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'condition' => ['show_option' => 'select'],
            ]);

            $this->add_control('show_excerpt', ['label' => esc_html__('Show Excerpt', 'podify-events'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes']);
            $this->add_control('posts_per_page', ['label' => esc_html__('Posts Per Page', 'podify-events'), 'type' => Controls_Manager::NUMBER, 'default' => 6, 'min' => 1]);
            $this->add_control('orderby', ['label' => esc_html__('Order By', 'podify-events'), 'type' => Controls_Manager::SELECT, 'default' => 'date', 'options' => ['date' => esc_html__('Date', 'podify-events'), 'title' => esc_html__('Title', 'podify-events'), 'rand' => esc_html__('Random', 'podify-events')]]);
            $this->add_control('order', ['label' => esc_html__('Order', 'podify-events'), 'type' => Controls_Manager::SELECT, 'default' => 'DESC', 'options' => ['ASC' => 'ASC', 'DESC' => 'DESC']]);

            $this->add_control('filter_taxonomy', ['label' => esc_html__('Filter By', 'podify-events'), 'type' => Controls_Manager::SELECT, 'default' => 'none', 'options' => [
                'none' => esc_html__('None', 'podify-events'),
                'podify_event_category' => esc_html__('Category', 'podify-events'),
                'podify_event_tag' => esc_html__('Tag', 'podify-events')
            ]]);
            $this->add_control('filter_terms', ['label' => esc_html__('Categories', 'podify-events'), 'type' => Controls_Manager::SELECT2, 'multiple' => true, 'label_block' => true, 'options' => $this->get_terms_for_control('podify_event_category'), 'condition' => ['filter_taxonomy' => 'podify_event_category']]);
            $this->add_control('filter_tags', ['label' => esc_html__('Tags', 'podify-events'), 'type' => Controls_Manager::SELECT2, 'multiple' => true, 'label_block' => true, 'options' => $this->get_terms_for_control('podify_event_tag'), 'condition' => ['filter_taxonomy' => 'podify_event_tag']]);

            $this->add_control('block_style', ['label' => esc_html__('Block Style', 'podify-events'), 'type' => Controls_Manager::SELECT, 'default' => 'grid', 'options' => ['grid' => esc_html__('Grid', 'podify-events'), 'list' => esc_html__('List', 'podify-events')], 'prefix_class' => 'podify-events-style-', 'render_type' => 'template']);

            $this->add_group_control(Group_Control_Image_Size::get_type(), ['name' => 'image_thumbnail', 'default' => 'podify_events_card', 'exclude' => ['custom']]);

            $this->add_control('badge_type', ['label' => esc_html__('Badge Type', 'podify-events'), 'type' => Controls_Manager::SELECT, 'default' => 'date', 'options' => ['off' => esc_html__('Off', 'podify-events'), 'status' => esc_html__('Default', 'podify-events'), 'date' => esc_html__('Date', 'podify-events'), 'custom' => esc_html__('Custom', 'podify-events')]]);

            $this->add_control('badge_position_vertical', [
                'label' => esc_html__('Badge Vertical Position', 'podify-events'),
                'type' => Controls_Manager::CHOOSE,
                'default' => 'bottom',
                'options' => [
                    'top' => [
                        'title' => esc_html__('Top', 'podify-events'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'bottom' => [
                        'title' => esc_html__('Bottom', 'podify-events'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'condition' => ['badge_type!' => 'off'],
                'selectors' => [
                    '{{WRAPPER}} .podify-badge' => '{{VALUE}}: 12px;',
                ],
                'selectors_dictionary' => [
                    'top' => 'top',
                    'bottom' => 'bottom',
                ],
            ]);

            $this->add_control('badge_position_horizontal', [
                'label' => esc_html__('Badge Horizontal Position', 'podify-events'),
                'type' => Controls_Manager::CHOOSE,
                'default' => 'center',
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'podify-events'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'podify-events'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'podify-events'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'condition' => ['badge_type!' => 'off'],
            ]);

            $this->add_responsive_control('badge_offset_x', [
                'label' => esc_html__('Horizontal Offset', 'podify-events'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => ['min' => -100, 'max' => 100],
                    '%' => ['min' => -100, 'max' => 100],
                ],
                'default' => ['unit' => 'px', 'size' => 0],
                'selectors' => [
                    '{{WRAPPER}} .podify-badge' => 'left: calc(var(--badge-horizontal-position, 50%) + {{SIZE}}{{UNIT}});',
                ],
                'condition' => ['badge_type!' => 'off'],
            ]);

            $this->add_responsive_control('badge_offset_y', [
                'label' => esc_html__('Vertical Offset', 'podify-events'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => ['min' => -100, 'max' => 100],
                    '%' => ['min' => -100, 'max' => 100],
                ],
                'default' => ['unit' => 'px', 'size' => 0],
                'selectors' => [
                    '{{WRAPPER}} .podify-badge' => 'top: calc(var(--badge-vertical-position, auto) + {{SIZE}}{{UNIT}});',
                ],
                'condition' => ['badge_type!' => 'off'],
            ]);

            $this->add_control('badge_custom_text', ['label' => esc_html__('Custom Badge Text', 'podify-events'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Upcoming Event', 'podify-events'), 'condition' => ['badge_type' => 'custom']]);

            $this->end_controls_section();

            $this->start_controls_section('section_button', [
                'label' => esc_html__('Button', 'podify-events'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]);

            $this->add_control('show_button', [
                'label' => esc_html__('Show Button', 'podify-events'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'separator' => 'before',
            ]);

            $this->add_control('button_text', [
                'label' => esc_html__('Button Text', 'podify-events'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Learn more',
                'condition' => ['show_button' => 'yes'],
                'description' => esc_html__('Button label for all events', 'podify-events'),
            ]);

            $this->add_control('modal_popover', [
                'label' => esc_html__('Modal Popover', 'podify-events'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'no',
                'separator' => 'before',
                'description' => esc_html__('Open event details in modal instead of single page', 'podify-events'),
            ]);

            $this->end_controls_section();

            $this->start_controls_section('section_swiper_controls', ['label' => esc_html__('Carousel (Swiper)', 'podify-events'), 'tab' => Controls_Manager::TAB_CONTENT]);
            $this->add_control('enable_carousel', ['label' => esc_html__('Enable Carousel', 'podify-events'), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'return_value' => 'yes']);
            $this->add_control('slides_per_view', ['label' => esc_html__('Slides per view (desktop)', 'podify-events'), 'type' => Controls_Manager::NUMBER, 'default' => 1, 'min' => 1, 'condition' => ['enable_carousel' => 'yes']]);
            $this->add_control('slides_per_view_tablet', ['label' => esc_html__('Slides per view (tablet)', 'podify-events'), 'type' => Controls_Manager::NUMBER, 'default' => 1, 'min' => 1, 'condition' => ['enable_carousel' => 'yes']]);
            $this->add_control('slides_per_view_mobile', ['label' => esc_html__('Slides per view (mobile)', 'podify-events'), 'type' => Controls_Manager::NUMBER, 'default' => 1, 'min' => 1, 'condition' => ['enable_carousel' => 'yes']]);
            $this->add_control('carousel_space_between', ['label' => esc_html__('Space between (px)', 'podify-events'), 'type' => Controls_Manager::NUMBER, 'default' => 30, 'min' => 0, 'condition' => ['enable_carousel' => 'yes']]);
            $this->add_control('carousel_autoplay', ['label' => esc_html__('Autoplay', 'podify-events'), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '', 'condition' => ['enable_carousel' => 'yes']]);
            $this->add_control('carousel_autoplay_delay', ['label' => esc_html__('Autoplay delay (ms)', 'podify-events'), 'type' => Controls_Manager::NUMBER, 'default' => 5000, 'min' => 100, 'condition' => ['enable_carousel' => 'yes', 'carousel_autoplay' => 'yes']]);
            $this->add_control('carousel_loop', ['label' => esc_html__('Loop', 'podify-events'), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '', 'condition' => ['enable_carousel' => 'yes']]);
            $this->add_control('navigation', ['label' => esc_html__('Navigation', 'podify-events'), 'type' => Controls_Manager::SELECT, 'default' => 'both', 'options' => ['arrows' => esc_html__('Arrows', 'podify-events'), 'dots' => esc_html__('Dots', 'podify-events'), 'both' => esc_html__('Arrows and Dots', 'podify-events'), 'none' => esc_html__('None', 'podify-events')], 'condition' => ['enable_carousel' => 'yes']]);
            $this->add_control('arrow_mode', ['label' => esc_html__('Arrow Mode', 'podify-events'), 'type' => Controls_Manager::SELECT, 'default' => 'both', 'options' => ['both' => esc_html__('Prev + Next', 'podify-events'), 'next' => esc_html__('Only Next', 'podify-events'), 'prev' => esc_html__('Only Prev', 'podify-events')], 'conditions' => ['terms' => [['name' => 'enable_carousel', 'operator' => '==', 'value' => 'yes'], ['name' => 'navigation', 'operator' => 'in', 'value' => ['arrows', 'both']]]]]);
            $this->add_control('custom_navigation', ['label' => esc_html__('Custom Navigation', 'podify-events'), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'condition' => ['enable_carousel' => 'yes']]);
            $this->add_control('overflow_mode', ['label' => esc_html__('Overflow', 'podify-events'), 'type' => Controls_Manager::SELECT, 'default' => 'none', 'options' => ['none' => esc_html__('None', 'podify-events'), 'left' => esc_html__('Left', 'podify-events'), 'right' => esc_html__('Right', 'podify-events'), 'both' => esc_html__('Both', 'podify-events')], 'condition' => ['enable_carousel' => 'yes']]);

            $this->add_control('prev_arrow_icon', ['label' => esc_html__('Previous Arrow Icon', 'podify-events'), 'type' => Controls_Manager::ICONS, 'default' => ['value' => 'eicon-chevron-left', 'library' => 'eicons'], 'conditions' => ['terms' => [['name' => 'enable_carousel', 'operator' => '==', 'value' => 'yes'], ['name' => 'navigation', 'operator' => 'in', 'value' => ['arrows', 'both']]]]]);
            $this->add_control('next_arrow_icon', ['label' => esc_html__('Next Arrow Icon', 'podify-events'), 'type' => Controls_Manager::ICONS, 'default' => ['value' => 'eicon-chevron-right', 'library' => 'eicons'], 'conditions' => ['terms' => [['name' => 'enable_carousel', 'operator' => '==', 'value' => 'yes'], ['name' => 'navigation', 'operator' => 'in', 'value' => ['arrows', 'both']]]]]);
            $this->end_controls_section();

            // Carousel Dots (style)
            $this->start_controls_section('style_dots', ['label' => esc_html__('Carousel Dots', 'podify-events'), 'tab' => Controls_Manager::TAB_STYLE, 'condition' => ['enable_carousel' => 'yes', 'navigation' => ['dots', 'both']]]);
            $this->add_control('dots_style', ['label' => esc_html__('Style Dot', 'podify-events'), 'type' => Controls_Manager::SELECT, 'default' => '1', 'options' => ['1' => esc_html__('Style 1', 'podify-events')], 'prefix_class' => 'podify-dots-style-']);
            $this->add_control('dots_color', ['label' => esc_html__('Color', 'podify-events'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .podify-events-carousel-enabled .swiper-pagination-bullet' => 'background-color: {{VALUE}};']]);
            $this->add_control('dots_opacity', ['label' => esc_html__('Opacity', 'podify-events'), 'type' => Controls_Manager::SLIDER, 'range' => ['px' => ['min' => 0, 'max' => 1, 'step' => 0.05]], 'default' => ['size' => 1], 'selectors' => ['{{WRAPPER}} .podify-events-carousel-enabled .swiper-pagination-bullet' => 'opacity: {{SIZE}};']]);
            $this->add_responsive_control('dots_spacing', ['label' => esc_html__('Spacing', 'podify-events'), 'type' => Controls_Manager::SLIDER, 'size_units' => ['px'], 'range' => ['px' => ['min' => 0, 'max' => 40]], 'selectors' => ['{{WRAPPER}} .podify-events-carousel-enabled .swiper-pagination-bullet' => 'margin: 0 {{SIZE}}{{UNIT}};']]);
            $this->add_responsive_control('dots_align', ['label' => esc_html__('Alignment text', 'podify-events'), 'type' => Controls_Manager::CHOOSE, 'options' => ['left' => ['title' => esc_html__('Left', 'podify-events'), 'icon' => 'eicon-text-align-left'], 'center' => ['title' => esc_html__('Center', 'podify-events'), 'icon' => 'eicon-text-align-center'], 'right' => ['title' => esc_html__('Right', 'podify-events'), 'icon' => 'eicon-text-align-right']], 'default' => 'center', 'selectors' => ['{{WRAPPER}} .podify-events-carousel-enabled .swiper-pagination' => 'text-align: {{VALUE}};']]);
            $this->end_controls_section();

            // Columns
            $this->start_controls_section('section_columns', ['label' => esc_html__('Column Options', 'podify-events'), 'tab' => Controls_Manager::TAB_CONTENT]);
            $this->add_responsive_control('columns', ['label' => esc_html__('Columns', 'podify-events'), 'type' => Controls_Manager::SELECT, 'default' => '1', 'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4'], 'render_type' => 'template']);
            $this->add_responsive_control('column_spacing', ['label' => esc_html__('Column Spacing', 'podify-events'), 'type' => Controls_Manager::SLIDER, 'size_units' => ['px'], 'range' => ['px' => ['min' => 0, 'max' => 160]], 'default' => ['size' => 30], 'render_type' => 'template']);
            $this->end_controls_section();

            // Style simplified (image + content)
            $this->start_controls_section('style_image', ['label' => esc_html__('Image', 'podify-events'), 'tab' => Controls_Manager::TAB_STYLE]);
            $this->add_responsive_control('image_height', ['label' => esc_html__('Image Height', 'podify-events'), 'type' => Controls_Manager::SLIDER, 'range' => ['px' => ['min' => 80, 'max' => 800]], 'selectors' => ['{{WRAPPER}} .event-image' => 'height:{{SIZE}}{{UNIT}};', '{{WRAPPER}} .event-image img' => 'height:{{SIZE}}{{UNIT}};object-fit:cover;']]);
            $this->add_responsive_control('image_border_radius', ['label' => esc_html__('Image Border Radius', 'podify-events'), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => [
                '{{WRAPPER}} .event-image img' => 'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                '{{WRAPPER}} .podify-list-image img' => 'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
            ]]);
            $this->add_responsive_control('image_container_radius', ['label' => esc_html__('Placeholder Border Radius', 'podify-events'), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => [
                '{{WRAPPER}} .event-image' => 'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                '{{WRAPPER}} .podify-list-image' => 'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
            ]]);
            $this->add_responsive_control('list_image_size', ['label' => esc_html__('List Image Size', 'podify-events'), 'type' => Controls_Manager::SLIDER, 'range' => ['px' => ['min' => 100, 'max' => 360]], 'selectors' => [
                '{{WRAPPER}} .podify-list-image' => 'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .podify-list-image img' => 'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};'
            ], 'condition' => ['block_style' => 'list']]);
            $this->end_controls_section();

            // Block Style
            $this->start_controls_section('style_block', ['label' => esc_html__('Block Style', 'podify-events'), 'tab' => Controls_Manager::TAB_STYLE]);
            $this->add_control('block_bg_color', ['label' => esc_html__('Background Color', 'podify-events'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .podify-event-card' => 'background-color: {{VALUE}};']]);
            $this->add_group_control(Group_Control_Border::get_type(), ['name' => 'block_border', 'selector' => '{{WRAPPER}} .podify-event-card']);
            $this->add_responsive_control('block_border_radius', ['label' => esc_html__('Border Radius', 'podify-events'), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .podify-event-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
            $this->add_responsive_control('block_padding', ['label' => esc_html__('Padding', 'podify-events'), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .podify-event-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
            $this->end_controls_section();

            // Content Style
            $this->start_controls_section('style_content', ['label' => esc_html__('Content', 'podify-events'), 'tab' => Controls_Manager::TAB_STYLE]);
            $this->add_responsive_control('box_content_padding', ['label' => esc_html__('Box Content Padding', 'podify-events'), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .event-content' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
            $this->add_responsive_control('box_content_margin', ['label' => esc_html__('Box Content Margin', 'podify-events'), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .event-content' => 'margin:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);

            $this->add_control('title_align', ['label' => esc_html__('Title Align', 'podify-events'), 'type' => Controls_Manager::CHOOSE, 'options' => ['left' => ['title' => esc_html__('Left', 'podify-events'), 'icon' => 'eicon-text-align-left'], 'center' => ['title' => esc_html__('Center', 'podify-events'), 'icon' => 'eicon-text-align-center'], 'right' => ['title' => esc_html__('Right', 'podify-events'), 'icon' => 'eicon-text-align-right']], 'selectors' => ['{{WRAPPER}} .event-title' => 'text-align: {{VALUE}};']]);
            $this->add_responsive_control('title_margin', ['label' => esc_html__('Title Margin', 'podify-events'), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .event-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
            $this->add_control('title_color', ['label' => esc_html__('Title Color', 'podify-events'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .event-title a' => 'color: {{VALUE}};']]);
            $this->add_control('title_hover_color', ['label' => esc_html__('Title Hover Color', 'podify-events'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .event-title a:hover' => 'color: {{VALUE}};']]);
            $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'title_typography', 'selector' => '{{WRAPPER}} .event-title']);

            $this->add_responsive_control('meta_margin', ['label' => esc_html__('Meta Margin', 'podify-events'), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .podify-event-meta' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
            $this->add_control('meta_color', ['label' => esc_html__('Meta Color', 'podify-events'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .podify-event-meta' => 'color: {{VALUE}};']]);
            $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'meta_typography', 'selector' => '{{WRAPPER}} .podify-event-meta']);

            $this->add_responsive_control('excerpt_margin', ['label' => esc_html__('Excerpt Margin', 'podify-events'), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .event-excerpt' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
            $this->add_control('excerpt_color', ['label' => esc_html__('Excerpt Color', 'podify-events'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .event-excerpt' => 'color: {{VALUE}};']]);
            $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'excerpt_typography', 'selector' => '{{WRAPPER}} .event-excerpt']);

            $this->end_controls_section();

            $this->start_controls_section('style_button', ['label' => esc_html__('Button', 'podify-events'), 'tab' => Controls_Manager::TAB_STYLE]);
            $this->add_responsive_control('button_margin', ['label' => esc_html__('Margin', 'podify-events'), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .podify-read-more' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
            $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'button_typography', 'selector' => '{{WRAPPER}} .podify-read-more']);
            $this->start_controls_tabs('button_style_tabs');
            $this->start_controls_tab('button_tab_normal', ['label' => esc_html__('Normal', 'podify-events')]);
            $this->add_control('button_bg_color', ['label' => esc_html__('Background Color', 'podify-events'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .podify-read-more' => 'background-color: {{VALUE}};']]);
            $this->add_control('button_text_color', ['label' => esc_html__('Text Color', 'podify-events'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .podify-read-more' => 'color: {{VALUE}};']]);
            $this->end_controls_tab();
            $this->start_controls_tab('button_tab_hover', ['label' => esc_html__('Hover', 'podify-events')]);
            $this->add_control('button_bg_color_hover', ['label' => esc_html__('Background Color', 'podify-events'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .podify-read-more:hover' => 'background-color: {{VALUE}};']]);
            $this->add_control('button_text_color_hover', ['label' => esc_html__('Text Color', 'podify-events'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .podify-read-more:hover' => 'color: {{VALUE}};']]);
            $this->end_controls_tab();
            $this->end_controls_tabs();
            $this->end_controls_section();

            $this->start_controls_section('style_badge_grid', ['label' => esc_html__('Badge (Grid)', 'podify-events'), 'tab' => Controls_Manager::TAB_STYLE]);
            $this->add_control('badge_bg_color_grid', ['label' => esc_html__('Background', 'podify-events'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .podify-events-layout-grid .podify-badge' => 'background-color: {{VALUE}};']]);
            $this->add_control('badge_text_color_grid', ['label' => esc_html__('Text', 'podify-events'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .podify-events-layout-grid .podify-badge' => 'color: {{VALUE}};']]);
            $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'badge_typography_grid', 'selector' => '{{WRAPPER}} .podify-events-layout-grid .podify-badge']);
            $this->add_responsive_control('badge_padding_grid', ['label' => esc_html__('Padding', 'podify-events'), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .podify-events-layout-grid .podify-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
            $this->end_controls_section();

            $this->start_controls_section('style_badge_list', ['label' => esc_html__('Badge (List)', 'podify-events'), 'tab' => Controls_Manager::TAB_STYLE]);
            $this->add_control('badge_bg_color_list', ['label' => esc_html__('Background', 'podify-events'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .podify-events-layout-list .podify-badge' => 'background-color: {{VALUE}};']]);
            $this->add_control('badge_text_color_list', ['label' => esc_html__('Text', 'podify-events'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .podify-events-layout-list .podify-badge' => 'color: {{VALUE}};']]);
            $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'badge_typography_list', 'selector' => '{{WRAPPER}} .podify-events-layout-list .podify-badge']);
            $this->add_responsive_control('badge_padding_list', ['label' => esc_html__('Padding', 'podify-events'), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .podify-events-layout-list .podify-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
            $this->end_controls_section();

            // Navigation Style
            $this->start_controls_section('style_navigation', ['label' => esc_html__('Navigation (Carousel)', 'podify-events'), 'tab' => Controls_Manager::TAB_STYLE, 'condition' => ['enable_carousel' => 'yes']]);
            $this->add_responsive_control('arrow_size', ['label' => esc_html__('Arrow Size', 'podify-events'), 'type' => Controls_Manager::SLIDER, 'size_units' => ['px'], 'range' => ['px' => ['min' => 16, 'max' => 80]], 'selectors' => ['{{WRAPPER}} .podify-events-carousel-enabled .swiper-button-prev' => 'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};', '{{WRAPPER}} .podify-events-carousel-enabled .swiper-button-next' => 'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};', '{{WRAPPER}} .podify-events-carousel-enabled .podify-nav-prev' => 'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};', '{{WRAPPER}} .podify-events-carousel-enabled .podify-nav-next' => 'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};']]);
            $this->add_control('arrow_background', ['label' => esc_html__('Arrow Background', 'podify-events'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .podify-events-carousel-enabled .swiper-button-prev' => 'background-color: {{VALUE}};', '{{WRAPPER}} .podify-events-carousel-enabled .swiper-button-next' => 'background-color: {{VALUE}};', '{{WRAPPER}} .podify-events-carousel-enabled .podify-nav-prev' => 'background-color: {{VALUE}};', '{{WRAPPER}} .podify-events-carousel-enabled .podify-nav-next' => 'background-color: {{VALUE}};']]);
            $this->end_controls_section();
        }

        protected function get_events_for_control()
        {
            $res = [];
            $args = ['post_type' => 'podify_event', 'posts_per_page' => 200, 'post_status' => 'publish'];
            $q = get_posts($args);
            if ($q) {
                foreach ($q as $p) {
                    $res[$p->ID] = $p->post_title;
                }
            }
            return $res;
        }

        protected function get_terms_for_control($taxonomy)
        {
            $options = [];
            $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
            if (is_array($terms)) {
                foreach ($terms as $t) {
                    $options[$t->term_id] = $t->name;
                }
            }
            return $options;
        }

        protected function render()
        {
            $settings = $this->get_settings_for_display();

            if (class_exists('Podify_Events_Widget_Styles')) {
                Podify_Events_Widget_Styles::enqueue();
            }

            $ppp = 6;
            if (isset($settings['posts_per_page'])) {
                $ppp = absint($settings['posts_per_page']);
                if ($ppp < 1) {
                    $ppp = 6;
                }
            }

            $args = [
                'post_type' => 'podify_event',
                'posts_per_page' => $ppp,
                'orderby' => isset($settings['orderby']) ? $settings['orderby'] : 'date',
                'order' => isset($settings['order']) ? $settings['order'] : 'DESC',
                'post_status' => 'publish'
            ];

            if (! empty($settings['filter_taxonomy']) && $settings['filter_taxonomy'] !== 'none') {
                $tax = $settings['filter_taxonomy'];
                $terms = [];
                if ($tax === 'podify_event_category' && ! empty($settings['filter_terms'])) {
                    $terms = array_map('intval', (array)$settings['filter_terms']);
                }
                if ($tax === 'podify_event_tag' && ! empty($settings['filter_tags'])) {
                    $terms = array_map('intval', (array)$settings['filter_tags']);
                }
                if (! empty($terms)) {
                    $args['tax_query'] = [
                        [
                            'taxonomy' => $tax,
                            'field' => 'term_id',
                            'terms' => $terms,
                            'include_children' => true,
                            'operator' => 'IN'
                        ]
                    ];
                }
            }

            if (isset($settings['show_option']) && $settings['show_option'] === 'select' && ! empty($settings['events_list'])) {
                $ids = [];
                foreach ($settings['events_list'] as $item) {
                    if (! empty($item['choose_event'])) $ids[] = (int) $item['choose_event'];
                }
                if (! empty($ids)) {
                    $args['post__in'] = $ids;
                    $args['orderby'] = 'post__in';
                }
            }

            $query = new WP_Query($args);

            if (! $query->have_posts()) {
                echo '<p>' . esc_html__('No events found.', 'podify-events') . '</p>';
                wp_reset_postdata();
                return;
            }

            $widget_id = 'podify-events-' . $this->get_id();
            $layout = isset($settings['block_style']) ? $settings['block_style'] : 'grid';

            $columns = 1;
            if ($layout === 'grid') {
                if (isset($settings['columns']) && !empty($settings['columns'])) {
                    $columns = max(1, min(4, (int)$settings['columns']));
                } else {
                    $columns = 4;
                }
            } else {
                if (isset($settings['columns']) && !empty($settings['columns'])) {
                    $columns = max(1, (int)$settings['columns']);
                } else {
                    $columns = 1;
                }
            }

            $col_spacing = 30;
            if (isset($settings['column_spacing']['size'])) {
                $col_spacing = max(0, (int)$settings['column_spacing']['size']);
            }

            $do_carousel = (! empty($settings['enable_carousel']) && $settings['enable_carousel'] === 'yes');
            $overflow_mode = isset($settings['overflow_mode']) ? $settings['overflow_mode'] : 'none';
            $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
            $editor_class = $is_editor ? ' elementor-editor-active' : '';

            $wrapper_classes = 'podify-events-wrapper podify-events-layout-' . esc_attr($layout);
            $wrapper_classes .= ' ' . ($do_carousel ? 'podify-events-wrapper--swiper podify-events-carousel-enabled' : 'podify-events-wrapper--static');
            $wrapper_classes .= $editor_class;

            if ($do_carousel && $overflow_mode !== 'none') {
                if ($overflow_mode === 'left') {
                    $wrapper_classes .= ' podify-overflow-left';
                } elseif ($overflow_mode === 'right') {
                    $wrapper_classes .= ' podify-overflow-right';
                } elseif ($overflow_mode === 'both') {
                    $wrapper_classes .= ' podify-overflow-both';
                }
            }

            if (class_exists('Podify_Events_Widget_Styles')) {
                Podify_Events_Widget_Styles::enqueue_layout($layout, $do_carousel);
            }

            $slides_desktop = isset($settings['slides_per_view']) ? max(1, (int) $settings['slides_per_view']) : max(1, $columns);
            $slides_tablet  = isset($settings['slides_per_view_tablet']) ? max(1, (int) $settings['slides_per_view_tablet']) : $slides_desktop;
            $slides_mobile  = isset($settings['slides_per_view_mobile']) ? max(1, (int) $settings['slides_per_view_mobile']) : 1;
            $space_between  = isset($settings['carousel_space_between']) ? max(0, (int) $settings['carousel_space_between']) : max(0, $col_spacing);
            $autoplay_on    = (isset($settings['carousel_autoplay']) && $settings['carousel_autoplay'] === 'yes');
            $autoplay_delay = isset($settings['carousel_autoplay_delay']) ? (int) $settings['carousel_autoplay_delay'] : 5000;
            $loop_on        = (isset($settings['carousel_loop']) && $settings['carousel_loop'] === 'yes');
            $navigation_sel = isset($settings['navigation']) ? $settings['navigation'] : 'both';
            $show_arrows    = $do_carousel && $navigation_sel && in_array($navigation_sel, ['arrows', 'both'], true);
            $show_pagination = $do_carousel && $navigation_sel && in_array($navigation_sel, ['dots', 'both'], true);
            $arrow_mode     = isset($settings['arrow_mode']) ? $settings['arrow_mode'] : 'both';

            $prev_icon_data = ! empty($settings['prev_arrow_icon']) ? $settings['prev_arrow_icon'] : ['value' => 'eicon-chevron-left', 'library' => 'eicons'];
            $next_icon_data = ! empty($settings['next_arrow_icon']) ? $settings['next_arrow_icon'] : ['value' => 'eicon-chevron-right', 'library' => 'eicons'];
            $prev_icon = '<span class="podify-arrow-icon">' . $this->get_render_icon_string($prev_icon_data) . '</span>';
            $next_icon = '<span class="podify-arrow-icon">' . $this->get_render_icon_string($next_icon_data) . '</span>';

            // Get button settings
            $show_button = !empty($settings['show_button']) && $settings['show_button'] === 'yes';
            $button_text = !empty($settings['button_text']) ? $settings['button_text'] : __('Learn more', 'podify-events');

            // Get modal settings
            $modal_popover = !empty($settings['modal_popover']) && $settings['modal_popover'] === 'yes';

            // Generate inline styles for badge positioning
            $badge_inline_styles = '';
            if (!empty($settings['badge_type']) && $settings['badge_type'] !== 'off') {
                $badge_inline_styles = '<style>';
                $widget_selector = '#' . esc_attr($widget_id) . ' .podify-badge';

                // Horizontal positioning
                if (!empty($settings['badge_position_horizontal'])) {
                    if ($settings['badge_position_horizontal'] === 'left') {
                        $badge_inline_styles .= $widget_selector . ' { left: 12px !important; right: auto !important; transform: none !important; }';
                    } elseif ($settings['badge_position_horizontal'] === 'right') {
                        $badge_inline_styles .= $widget_selector . ' { right: 12px !important; left: auto !important; transform: none !important; }';
                    } else {
                        $badge_inline_styles .= $widget_selector . ' { left: 50% !important; right: auto !important; transform: translateX(-50%) !important; }';
                    }
                }

                // Vertical positioning
                if (!empty($settings['badge_position_vertical'])) {
                    if ($settings['badge_position_vertical'] === 'top') {
                        $badge_inline_styles .= $widget_selector . ' { top: 12px !important; bottom: auto !important; }';
                    } else {
                        $badge_inline_styles .= $widget_selector . ' { bottom: 12px !important; top: auto !important; }';
                    }
                }

                // Offset adjustments
                if (!empty($settings['badge_offset_x']['size'])) {
                    $offset_x = $settings['badge_offset_x']['size'] . $settings['badge_offset_x']['unit'];
                    if ($settings['badge_position_horizontal'] === 'center') {
                        $badge_inline_styles .= $widget_selector . ' { transform: translateX(calc(-50% + ' . $offset_x . ')) !important; }';
                    } elseif ($settings['badge_position_horizontal'] === 'left') {
                        $badge_inline_styles .= $widget_selector . ' { left: calc(12px + ' . $offset_x . ') !important; }';
                    } elseif ($settings['badge_position_horizontal'] === 'right') {
                        $badge_inline_styles .= $widget_selector . ' { right: calc(12px + ' . $offset_x . ') !important; }';
                    }
                }

                if (!empty($settings['badge_offset_y']['size'])) {
                    $offset_y = $settings['badge_offset_y']['size'] . $settings['badge_offset_y']['unit'];
                    if ($settings['badge_position_vertical'] === 'top') {
                        $badge_inline_styles .= $widget_selector . ' { top: calc(12px + ' . $offset_y . ') !important; }';
                    } else {
                        $badge_inline_styles .= $widget_selector . ' { bottom: calc(12px + ' . $offset_y . ') !important; }';
                    }
                }

                $badge_inline_styles .= '</style>';
            }

            // Generate editor styles for better preview
            $editor_inline_styles = '';
            if ($is_editor) {
                $editor_inline_styles = '<style>';

                // List layout specific styles
                if ($layout === 'list') {
                    $editor_inline_styles .= '
                #' . esc_attr($widget_id) . ' .podify-event-card {
                    display: flex !important;
                    flex-direction: row !important;
                    align-items: center !important;
                    gap: 20px !important;
                }
                #' . esc_attr($widget_id) . ' .event-image {
                    width: 150px !important;
                    height: 150px !important;
                    flex-shrink: 0 !important;
                    margin: 0 !important;
                }
                #' . esc_attr($widget_id) . ' .event-image img {
                    width: 100% !important;
                    height: 100% !important;
                    object-fit: cover !important;
                }
                #' . esc_attr($widget_id) . ' .event-content {
                    flex: 1 !important;
                }
            ';
                }

                // Carousel navigation styles for editor
                if ($do_carousel && $show_arrows) {
                    $editor_inline_styles .= '
                #' . esc_attr($widget_id) . ' .podify-nav-row {
                    position: absolute !important;
                    top: 50% !important;
                    left: 0 !important;
                    right: 0 !important;
                    transform: translateY(-50%) !important;
                    z-index: 10 !important;
                    display: flex !important;
                    justify-content: space-between !important;
                    align-items: center !important;
                    width: 100% !important;
                    padding: 0 12px !important;
                    pointer-events: none !important;
                    margin: 0 !important;
                    height: auto !important;
                    opacity: 1 !important;
                    visibility: visible !important;
                }
                #' . esc_attr($widget_id) . ' .podify-nav {
                    position: relative !important;
                    top: auto !important;
                    bottom: auto !important;
                    transform: none !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    width: 40px !important;
                    height: 40px !important;
                    border-radius: 50% !important;
                    background: #fff !important;
                    border: 1px solid #e5e5e5 !important;
                    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
                    cursor: pointer !important;
                    pointer-events: auto !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    transition: all 0.3s ease !important;
                }
                #' . esc_attr($widget_id) . ' .podify-nav:hover {
                    background: #f8f8f8 !important;
                    border-color: #ddd !important;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
                }
                #' . esc_attr($widget_id) . ' .podify-nav .podify-arrow-icon {
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    width: 100% !important;
                    height: 100% !important;
                    font-size: 16px !important;
                    color: #333 !important;
                }
                #' . esc_attr($widget_id) . ' .podify-nav.podify-nav-prev {
                    left: 0 !important;
                }
                #' . esc_attr($widget_id) . ' .podify-nav.podify-nav-next {
                    right: 0 !important;
                }
            ';
                }

                $editor_inline_styles .= '</style>';
            }

            printf(
                '%s%s<div id="%s" class="%s" data-swiper-enabled="%s" data-slides-desktop="%d" data-slides-tablet="%d" data-slides-mobile="%d" data-space-between="%d" data-autoplay="%s" data-autoplay-delay="%d" data-loop="%s" data-show-arrows="%s" data-show-pagination="%s" data-style="%s" data-columns="%d" data-col-gap="%d" data-arrow-mode="%s">',
                $badge_inline_styles,
                $editor_inline_styles,
                esc_attr($widget_id),
                esc_attr($wrapper_classes),
                $do_carousel ? 'yes' : 'no',
                esc_attr($slides_desktop),
                esc_attr($slides_tablet),
                esc_attr($slides_mobile),
                esc_attr($space_between),
                $autoplay_on ? 'yes' : 'no',
                esc_attr($autoplay_delay),
                $loop_on ? 'yes' : 'no',
                $show_arrows ? 'yes' : 'no',
                $show_pagination ? 'yes' : 'no',
                esc_attr($layout),
                esc_attr($columns),
                esc_attr($col_spacing),
                esc_attr($arrow_mode)
            );

            if ($do_carousel) {
                echo '<div class="podify-events-swiper swiper">';
                echo '<div class="swiper-wrapper">';
            } else {
                $grid_style = '';
                if ($layout === 'grid') {
                    $grid_style = sprintf(
                        ' style="display:grid; grid-template-columns: repeat(%d, 1fr); gap: %dpx;"',
                        max(1, $columns),
                        max(0, $col_spacing)
                    );
                }
                echo '<div class="podify-events-' . esc_attr($layout) . '" role="list"' . $grid_style . '>';
            }

            // Store posts to check for modals
            $posts = [];
            while ($query->have_posts()) {
                $query->the_post();
                $posts[] = get_the_ID();
            }

            // Rewind to process posts again
            $query->rewind_posts();

            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $date_start = get_post_meta($post_id, '_podify_event_date_start', true);
                $date_end   = get_post_meta($post_id, '_podify_event_date_end', true);
                $date = $date_start ? $date_start : get_post_meta($post_id, '_podify_event_date', true);
                $time = get_post_meta($post_id, '_podify_event_time', true);
                $address = get_post_meta($post_id, '_podify_event_address', true);

                // Check if event has modal enabled
                $event_use_modal = class_exists('Podify_Events_Meta') ? Podify_Events_Meta::should_use_modal($post_id) : false;

                // Apply modal setting: Widget setting overrides individual event setting
                $use_modal = $modal_popover ? true : $event_use_modal;

                $thumbnail_size = ! empty($settings['image_thumbnail_size']) ? $settings['image_thumbnail_size'] : 'podify_events_card';
                $thumb = has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, $thumbnail_size) : PODIFY_EVENTS_URL . 'assets/img/event-placeholder.png';

                // Badge rendering
                $badge_html = '';
                if (! empty($settings['badge_type']) && $settings['badge_type'] !== 'off') {
                    $badge_position_v = !empty($settings['badge_position_vertical']) ? $settings['badge_position_vertical'] : 'bottom';
                    $badge_position_h = !empty($settings['badge_position_horizontal']) ? $settings['badge_position_horizontal'] : 'center';

                    $badge_attrs = sprintf(
                        'data-position-v="%s" data-position-h="%s"',
                        esc_attr($badge_position_v),
                        esc_attr($badge_position_h)
                    );

                    if ($settings['badge_type'] === 'status' && $date) {
                        $now_ts   = current_time('timestamp');
                        $start_ts = strtotime($date);
                        $end_ts   = $date_end ? strtotime($date_end) : $start_ts;
                        $today    = gmdate('Y-m-d', $now_ts);
                        $start_day = gmdate('Y-m-d', $start_ts);
                        $end_day   = gmdate('Y-m-d', $end_ts);

                        if ($end_day < $today) {
                            $badge_html = '<div class="podify-badge" ' . $badge_attrs . '>' . esc_html__('Ended', 'podify-events') . '</div>';
                        } elseif ($start_day > $today) {
                            $badge_html = '<div class="podify-badge" ' . $badge_attrs . '>' . esc_html__('Upcoming', 'podify-events') . '</div>';
                        } else {
                            $badge_html = '<div class="podify-badge" ' . $badge_attrs . '>' . esc_html__('Ongoing', 'podify-events') . '</div>';
                        }
                    } elseif ($settings['badge_type'] === 'date' && $date) {
                        $badge_html = '<div class="podify-badge" ' . $badge_attrs . '>' . esc_html(date_i18n('j M', strtotime($date))) . '</div>';
                    } elseif ($settings['badge_type'] === 'custom') {
                        $custom = ! empty($settings['badge_custom_text']) ? $settings['badge_custom_text'] : esc_html__('Upcoming Event', 'podify-events');
                        $badge_html = '<div class="podify-badge" ' . $badge_attrs . '>' . esc_html($custom) . '</div>';
                    }
                }

                if ($do_carousel) echo '<div class="swiper-slide">';

                // Always use .podify-event-card class for both layouts
                echo '<article class="podify-event-card" role="listitem" aria-labelledby="event-title-' . esc_attr($post_id) . '">';

                // Determine link class based on modal setting
                $link_class = $use_modal ? 'podify-open-modal' : 'podify-direct-link';

                echo '<figure class="event-image"><a class="event-image__link ' . esc_attr($link_class) . '" data-event-id="' . esc_attr($post_id) . '" href="' . esc_url(get_permalink()) . '"><img src="' . esc_url($thumb) . '" alt="' . esc_attr(get_the_title()) . '" loading="eager"></a>';
                if ($badge_html) echo $badge_html;
                echo '</figure>';

                echo '<div class="event-content">';
                echo '<h3 id="event-title-' . esc_attr($post_id) . '" class="event-title"><a class="' . esc_attr($link_class) . '" data-event-id="' . esc_attr($post_id) . '" href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h3>';

                echo '<div class="podify-event-meta">';
                if ($date) {
                    $human = ! empty($date_end) && $date_end !== $date
                        ? date_i18n('F j', strtotime($date)) . '—' . date_i18n('j Y', strtotime($date_end))
                        : date_i18n('F j Y', strtotime($date));
                    echo '<div class="meta-item"><span class="dashicons dashicons-calendar"></span><span class="meta-text">' . esc_html($human) . '</span></div>';
                }
                if ($time) {
                    $time_human = date_i18n('g:i a', strtotime($time));
                    echo '<div class="meta-item"><span class="dashicons dashicons-clock"></span><span class="meta-text">' . esc_html($time_human) . '</span></div>';
                }
                if ($address) {
                    echo '<div class="meta-item"><span class="dashicons dashicons-location"></span><span class="meta-text">' . esc_html($address) . '</span></div>';
                }
                echo '</div>';

                if (! empty($settings['show_excerpt']) && $settings['show_excerpt'] === 'yes') {
                    $excerpt = wp_trim_words(wp_strip_all_tags(get_the_excerpt()), 20);
                    echo '<div class="event-excerpt">' . esc_html($excerpt) . '</div>';
                }

                if ($show_button) {
                    $event_btn_label = get_post_meta($post_id, '_podify_event_button_label', true);
                    $event_btn_enabled = get_post_meta($post_id, '_podify_event_button_enabled', true) === '1';
                    $event_btn_url = get_post_meta($post_id, '_podify_event_button_url', true);

                    $final_btn_text = !empty($event_btn_label) ? $event_btn_label : $button_text;
                    $final_btn_url = ($event_btn_enabled && !empty($event_btn_url)) ? $event_btn_url : get_permalink();

                    echo '<div class="event-actions"><a class="podify-read-more ' . esc_attr($link_class) . '" data-event-id="' . esc_attr($post_id) . '" href="' . esc_url($final_btn_url) . '">' . esc_html($final_btn_text) . '</a></div>';
                }

                echo '</div></article>';

                if ($do_carousel) echo '</div>';
            }

            echo '</div>';

            // Check if we need modal container
            $has_modal = false;
            if ($modal_popover) {
                $has_modal = true;
            } else {
                foreach ($posts as $post_id) {
                    $event_use_modal = class_exists('Podify_Events_Meta') ? Podify_Events_Meta::should_use_modal($post_id) : false;
                    if ($event_use_modal) {
                        $has_modal = true;
                        break;
                    }
                }
            }

            // Modal container - only add if modal is enabled
            if ($has_modal) {
                echo '<div class="podify-modal" id="podify-modal-' . esc_attr($widget_id) . '" hidden>
                <div class="podify-modal__overlay" data-close="true"></div>
                <div class="podify-modal__dialog" role="dialog" aria-modal="true">
                    <button type="button" class="podify-modal__close" data-close="true">&times;</button>
                    <div class="podify-modal__content"></div>
                </div>
              </div>';
            }

            if ($do_carousel) {
                if ($show_pagination) {
                    echo '<div class="swiper-pagination"></div>';
                }
                echo '</div>';

                if ($show_arrows) {
                    echo '<div class="podify-nav-row">';
                    if ($arrow_mode === 'prev' || $arrow_mode === 'both') {
                        echo '<button type="button" class="podify-nav podify-nav-prev" aria-label="' . esc_attr__('Previous slide', 'podify-events') . '">' . $prev_icon . '</button>';
                    } else {
                        echo '<span></span>';
                    }
                    if ($arrow_mode === 'next' || $arrow_mode === 'both') {
                        echo '<button type="button" class="podify-nav podify-nav-next" aria-label="' . esc_attr__('Next slide', 'podify-events') . '">' . $next_icon . '</button>';
                    } else {
                        echo '<span></span>';
                    }
                    echo '</div>';
                }
            }

            echo '</div>';

            wp_reset_postdata();

            if ($is_editor) {
?>
                <script>
                    (function($) {
                        'use strict';
                        $(document).ready(function() {
                            var $wrapper = $('#<?php echo esc_js($widget_id); ?>');
                            $wrapper.css({
                                'display': 'block',
                                'visibility': 'visible',
                                'opacity': '1',
                                'min-height': '300px'
                            });
                            <?php if ($do_carousel) : ?>
                                setTimeout(function() {
                                    var $swiper = $wrapper.find('.podify-events-swiper');
                                    if ($swiper.length && typeof Swiper !== 'undefined') {
                                        if ($swiper[0].swiper) {
                                            $swiper[0].swiper.destroy(true, true);
                                        }
                                        var swiperOptions = {
                                            slidesPerView: <?php echo $slides_mobile; ?>,
                                            spaceBetween: <?php echo $space_between; ?>,
                                            loop: <?php echo $loop_on ? 'true' : 'false'; ?>,
                                            <?php if ($autoplay_on) : ?>
                                                autoplay: {
                                                    delay: <?php echo $autoplay_delay; ?>,
                                                    disableOnInteraction: false,
                                                },
                                            <?php endif; ?>
                                            breakpoints: {
                                                640: {
                                                    slidesPerView: <?php echo $slides_mobile; ?>
                                                },
                                                768: {
                                                    slidesPerView: <?php echo $slides_tablet; ?>
                                                },
                                                1024: {
                                                    slidesPerView: <?php echo $slides_desktop; ?>
                                                }
                                            },
                                            <?php if ($show_arrows) : ?>
                                                navigation: {
                                                    prevEl: $wrapper.find('.podify-nav-prev')[0],
                                                    nextEl: $wrapper.find('.podify-nav-next')[0]
                                                },
                                            <?php endif; ?>
                                            <?php if ($show_pagination) : ?>
                                                pagination: {
                                                    el: $wrapper.find('.swiper-pagination')[0],
                                                    clickable: true,
                                                    dynamicBullets: true
                                                }
                                            <?php endif; ?>
                                        };

                                        var swiperInstance = new Swiper($swiper[0], swiperOptions);

                                        // Store swiper instance for editor refresh
                                        $wrapper.data('swiper', swiperInstance);
                                    }
                                }, 300);
                            <?php endif; ?>

                            // Handle modal links in editor
                            $wrapper.on('click', '.podify-open-modal', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                alert('Modal functionality works on the frontend. In the editor, this would open event ID: ' + $(this).data('event-id'));
                            });
                        });
                    })(jQuery);
                </script>
<?php
            }
        }

        protected function get_render_icon_string($icon_data)
        {
            if (empty($icon_data)) return '';
            if (is_array($icon_data)) {
                if (! empty($icon_data['value']) && is_string($icon_data['value'])) return '<i class="' . esc_attr($icon_data['value']) . '"></i>';
                if (! empty($icon_data['library']) && 'svg' === $icon_data['library']) {
                    if (! empty($icon_data['value']['svg'])) return $icon_data['value']['svg'];
                    if (! empty($icon_data['value']['data'])) return $icon_data['value']['data'];
                }
            }
            if (is_string($icon_data)) return $icon_data;
            return '';
        }
    }
}
