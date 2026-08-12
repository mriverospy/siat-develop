<?php

function deleteForm($routeParams)
{
    $form = Form::open(['method' => 'DELETE', 'route' => $routeParams]);
    $form.= Form::submit('<i class="fa fa-times"></i>', ['class' => 'btn btn-danger btn-sm']);
    $form.= Form::close();

    return $form;
}

Form::macro('bootCheckbox', function($value, $label) {
    $html = '<div class="checkbox"><label><input type="checkbox" value="';
    $html .= $value;
    $html .= '">';
    $html .= $label;
    $html .= '</label></div>'
    return $html
}
