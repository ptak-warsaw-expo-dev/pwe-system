<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PWE_System_Admin_UI
{
    public static function notice(string $class, string $iconClass, string $contentHtml): void
    {
        echo '<div class="' . esc_attr($class) . '">';
        echo '<span class="dashicons ' . esc_attr($iconClass) . '"></span>';
        echo '<div>' . $contentHtml . '</div>';
        echo '</div>';
    }

    public static function status(string $tag, string $variant, string $iconClass, string $contentHtml): void
    {
        $tag = in_array($tag, ['p', 'span', 'div'], true) ? $tag : 'span';
        echo '<' . $tag . ' class="' . esc_attr('pwe-status pwe-status--' . $variant) . '">';
        echo '<span class="dashicons ' . esc_attr($iconClass) . '"></span>';
        echo $contentHtml;
        echo '</' . $tag . '>';
    }

    public static function checkbox(array $inputAttributes, string $containerClass = 'pwe-checkbox-container', string $wrapperTag = 'label'): void
    {
        $wrapperTag = in_array($wrapperTag, ['label', 'span'], true) ? $wrapperTag : 'label';
        echo '<' . $wrapperTag . ' class="' . esc_attr($containerClass) . '">';
        echo '<input' . self::attributes($inputAttributes) . '>';
        echo '<span class="pwe-checkmark"></span>';
        echo '</' . $wrapperTag . '>';
    }

    public static function button(array $attributes, string $innerHtml): void
    {
        echo '<button' . self::attributes($attributes) . '>' . $innerHtml . '</button>';
    }

    public static function attributes(array $attributes): string
    {
        $chunks = [];
        foreach ($attributes as $name => $value) {
            $chunks[] = sprintf(' %s="%s"', esc_attr((string) $name), esc_attr((string) $value));
        }
        return implode('', $chunks);
    }
}
