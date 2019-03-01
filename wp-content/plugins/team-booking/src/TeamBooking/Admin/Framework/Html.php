<?php

namespace TeamBooking\Admin\Framework;

class Html
{
    private static function basicTag($tag, $data)
    {
        $params = array(
            'text'   => NULL,
            'id'     => '',
            'class'  => '',
            'show'   => TRUE,
            'escape' => TRUE
        );
        $params = array_merge($params, array_intersect_key($data, $params));

        ob_start();
        echo '<' . $tag;
        if (!empty($params['id'])) echo ' id="' . $params['id'] . '"';
        if (!empty($params['class'])) echo ' class="' . $params['class'] . '"';
        if (!$params['show']) echo ' style="display:none;"';
        echo '>';
        if ($params['escape']) {
            echo esc_html($params['text']);
        } else {
            echo $params['text'];
        }
        echo '</' . $tag . '>';

        return ob_get_clean();
    }

    public static function paragraph($data)
    {
        if (!is_array($data)) $data = array('text' => $data);

        return Html::basicTag('p', $data);
    }

    public static function h2($data)
    {
        if (!is_array($data)) $data = array('text' => $data);

        return Html::basicTag('h2', $data);
    }

    public static function h3($data)
    {
        if (!is_array($data)) $data = array('text' => $data);

        return Html::basicTag('h3', $data);
    }

    public static function h4($data)
    {
        if (!is_array($data)) $data = array('text' => $data);

        return Html::basicTag('h4', $data);
    }

    public static function span($data = '')
    {
        if (!is_array($data)) $data = array('text' => $data);

        return Html::basicTag('span', $data);
    }

    public static function label($data)
    {
        if (!is_array($data)) $data = array('text' => $data);

        return Html::basicTag('label', $data);
    }

    public static function img($data)
    {
        if (!is_array($data)) $data = array('src' => $data);
        $params = array(
            'src'   => '',
            'alt'   => '',
            'class' => ''
        );
        $params = array_merge($params, array_intersect_key($data, $params));
        ob_start();
        echo '<img src="' . $params['src'] . '"';
        if (!empty($params['alt'])) echo ' alt="' . esc_attr($params['alt']) . '"';
        if (!empty($params['class'])) echo ' class="' . $params['class'] . '"';
        echo ' />';

        return ob_get_clean();
    }

    public static function anchor($data)
    {
        $params = array(
            'text'   => NULL,
            'href'   => '#',
            'id'     => '',
            'class'  => '',
            'target' => '',
            'style'  => '',
            'escape' => TRUE,
            'data'   => array()
        );
        $params = array_merge($params, array_intersect_key($data, $params));
        ob_start();
        echo '<a';
        if (!empty($params['id'])) echo ' id="' . $params['id'] . '"';
        if (!empty($params['class'])) echo ' class="' . $params['class'] . '"';
        if (!empty($params['target'])) echo ' target="' . $params['target'] . '"';
        if (!empty($params['style'])) echo ' style="' . $params['style'] . '"';
        foreach ($params['data'] as $key => $value) {
            echo ' data-' . $key . '="' . $value . '"';
        }
        echo ' href="' . $params['href'] . '">';
        if ($params['escape']) {
            echo esc_html($params['text']);
        } else {
            echo $params['text'];
        }
        echo '</a>';

        return ob_get_clean();
    }

    public static function select($data)
    {
        $params = array(
            'id'              => '',
            'class'           => '',
            'style'           => '',
            'options'         => array(),
            'data'            => array(),
            'selected_option' => NULL
        );
        $params = array_merge($params, array_intersect_key($data, $params));
        ob_start();
        echo '<select';
        if (!empty($params['id'])) echo ' id="' . $params['id'] . '"';
        if (!empty($params['class'])) echo ' class="' . $params['class'] . '"';
        if (!empty($params['style'])) echo ' style="' . $params['style'] . '"';
        if (!empty($data['data'])) {
            foreach ($params['data'] as $name => $val) {
                echo ' data-' . $name . '="' . $val . '"';
            }
        }
        echo '>';
        foreach ($params['options'] as $value => $content) {
            if (!is_array($content)) $content = array('text' => $content);
            $data = array(
                'text'      => '',
                'data'      => array(),
                'separator' => FALSE,
                'selected'  => FALSE,
                'value'     => NULL    // it overrides the key
            );
            $data = array_merge($data, array_intersect_key($content, $data));
            if (is_null($data['value'])) $data['value'] = $value;
            echo '<option';
            if ($data['selected']) echo ' selected';
            if ($data['separator']) {
                echo ' disabled';
            } else {
                echo ' value="' . esc_attr($data['value']) . '"';
            }
            if (!is_null($params['selected_option']) && $data['value'] == $params['selected_option']) echo ' selected="selected"';
            if (!empty($data['data'])) {
                foreach ($data['data'] as $name => $val) {
                    echo ' data-' . $name . '="' . $val . '"';
                }
            }
            echo '>' . esc_html($data['text']) . '</option>';
        }
        echo '<select>';

        return ob_get_clean();
    }

    public static function textfield($data)
    {
        $params = array(
            'id'          => '',
            'class'       => '',
            'style'       => '',
            'value'       => '',
            'data'        => array(),
            'name'        => '',
            'required'    => FALSE,
            'disabled'    => FALSE,
            'placeholder' => '',
            'hidden'      => FALSE
        );
        $params = array_merge($params, array_intersect_key($data, $params));
        ob_start();
        echo '<input';
        echo $params['hidden'] ? ' type="hidden"' : ' type="text"';
        echo ' class="regular-text ' . $params['class'] . '"';
        if (!empty($params['value'])) echo ' value="' . $params['value'] . '"';
        if (!empty($params['placeholder'])) echo ' placeholder="' . $params['placeholder'] . '"';
        if (!empty($params['name'])) echo ' name="' . $params['name'] . '"';
        if (!empty($params['id'])) echo ' id="' . $params['id'] . '"';
        if (!empty($params['style'])) echo ' style="' . $params['style'] . '"';
        if ($params['required']) echo ' required="required"';
        if ($params['disabled']) echo ' disabled="disabled"';
        if (!empty($data['data'])) {
            foreach ($params['data'] as $name => $val) {
                echo ' data-' . $name;
                if (!empty($val)) echo '="' . $val . '"';
            }
        }
        echo '/>';

        return ob_get_clean();
    }

    public static function textarea($data)
    {
        $params = array(
            'id'          => '',
            'class'       => '',
            'style'       => '',
            'value'       => '',
            'data'        => array(),
            'name'        => '',
            'required'    => FALSE,
            'disabled'    => FALSE,
            'rows'        => '',
            'cols'        => '',
            'placeholder' => ''

        );
        $params = array_merge($params, array_intersect_key($data, $params));
        ob_start();
        echo '<textarea';
        echo ' class="' . $params['class'] . '"';
        if (!empty($params['rows'])) echo ' rows="' . (int)$params['rows'] . '"';
        if (!empty($params['cols'])) echo ' cols="' . (int)$params['cols'] . '"';
        if (!empty($params['placeholder'])) echo ' placeholder="' . $params['placeholder'] . '"';
        if (!empty($params['name'])) echo ' name="' . $params['name'] . '"';
        if (!empty($params['id'])) echo ' id="' . $params['id'] . '"';
        if (!empty($params['style'])) echo ' style="' . $params['style'] . '"';
        if ($params['required']) echo ' required="required"';
        if ($params['disabled']) echo ' disabled="disabled"';
        if (!empty($data['data'])) {
            foreach ($params['data'] as $name => $val) {
                echo ' data-' . $name;
                if (!empty($val)) echo '="' . $val . '"';
            }
        }
        echo '>';
        if (!empty($params['value'])) echo $params['value'];
        echo '</textarea>';

        return ob_get_clean();
    }

    public static function radio($data)
    {
        $params = array(
            'id'       => '',
            'text'     => '',
            'class'    => '',
            'style'    => '',
            'value'    => '',
            'data'     => array(),
            'name'     => '',
            'disabled' => FALSE,
            'checked'  => FALSE
        );
        $params = array_merge($params, array_intersect_key($data, $params));
        ob_start();
        echo '<label title="' . esc_attr($params['text']) . '">';
        echo '<input type="radio"';
        if (!empty($params['value'])) echo ' value="' . $params['value'] . '"';
        if (!empty($params['name'])) echo ' name="' . $params['name'] . '"';
        if (!empty($params['id'])) echo ' id="' . $params['id'] . '"';
        if (!empty($params['style'])) echo ' style="' . $params['style'] . '"';
        if (!empty($data['data'])) {
            foreach ($params['data'] as $name => $val) {
                echo ' data-' . $name . '="' . $val . '"';
            }
        }
        if ($params['disabled']) echo ' disabled="disabled"';
        if ($params['checked']) echo ' checked="checked"';
        echo '/>';
        echo '<span';
        if (!empty($params['class'])) echo ' class="' . $params['class'] . '"';
        echo '>' . esc_html($params['text']) . '</span>';
        echo '</label>';

        return ob_get_clean();
    }

    public static function checkbox($data)
    {
        $params = array(
            'id'       => '',
            'text'     => '',
            'class'    => '',
            'style'    => '',
            'value'    => '',
            'data'     => array(),
            'name'     => '',
            'disabled' => FALSE,
            'checked'  => FALSE
        );
        $params = array_merge($params, array_intersect_key($data, $params));
        ob_start();
        echo '<label for="' . $params['name'] . '">';
        echo '<input type="checkbox"';
        if (!empty($params['value'])) echo ' value="' . $params['value'] . '"';
        if (!empty($params['name'])) echo ' name="' . $params['name'] . '"';
        if (!empty($params['id'])) echo ' id="' . $params['id'] . '"';
        if (!empty($params['style'])) echo ' style="' . $params['style'] . '"';
        if (!empty($data['data'])) {
            foreach ($params['data'] as $name => $val) {
                echo ' data-' . $name . '="' . $val . '"';
            }
        }
        if ($params['disabled']) echo ' disabled="disabled"';
        if ($params['checked']) echo ' checked="checked"';
        echo '/>';
        echo '<span';
        if (!empty($params['class'])) echo ' class="' . $params['class'] . '"';
        echo '>' . esc_html($params['text']) . '</span>';
        echo '</label>';

        return ob_get_clean();
    }
}