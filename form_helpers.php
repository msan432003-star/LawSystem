<?php
/**
 * includes/form_helpers.php
 * Small functions that print Bootstrap form fields with old values and error messages.
 * Needs a FormValidator object ($v) - create it as: $v = new FormValidator($_POST);
 */

function render_input(FormValidator $v, string $name, string $label, string $type = 'text',
                      string $col = 'col-md-6', string $extra = ''): void
{
    $value = ($type === 'password') ? '' : $v->old($name);   // never refill passwords
    $err   = $v->error($name);
    echo '<div class="' . e($col) . '">';
    echo '<label class="form-label" for="' . e($name) . '">' . e($label) . '</label>';
    echo '<input class="form-control' . ($err ? ' is-invalid' : '') . '" type="' . e($type) . '"'
       . ' id="' . e($name) . '" name="' . e($name) . '" value="' . e($value) . '" ' . $extra . '>';
    if ($err) {
        echo '<div class="field-error">' . e($err) . '</div>';
    }
    echo '</div>';
}

function render_select(FormValidator $v, string $name, string $label, array $options,
                       string $col = 'col-md-6', string $placeholder = 'Select'): void
{
    $current = $v->old($name);
    $err     = $v->error($name);
    echo '<div class="' . e($col) . '">';
    echo '<label class="form-label" for="' . e($name) . '">' . e($label) . '</label>';
    echo '<select class="form-select' . ($err ? ' is-invalid' : '') . '" id="' . e($name) . '" name="' . e($name) . '">';
    echo '<option value="">' . e($placeholder) . '</option>';
    foreach ($options as $value => $text) {
        if (is_int($value)) {          // plain list: value and text are the same
            $value = $text;
        }
        echo '<option value="' . e((string) $value) . '"' . ($current === (string) $value ? ' selected' : '') . '>'
           . e((string) $text) . '</option>';
    }
    echo '</select>';
    if ($err) {
        echo '<div class="field-error">' . e($err) . '</div>';
    }
    echo '</div>';
}

function render_textarea(FormValidator $v, string $name, string $label, int $rows = 5, string $col = 'col-12'): void
{
    $err = $v->error($name);
    echo '<div class="' . e($col) . '">';
    echo '<label class="form-label" for="' . e($name) . '">' . e($label) . '</label>';
    echo '<textarea class="form-control' . ($err ? ' is-invalid' : '') . '" id="' . e($name) . '" name="' . e($name)
       . '" rows="' . $rows . '">' . e($v->old($name)) . '</textarea>';
    if ($err) {
        echo '<div class="field-error">' . e($err) . '</div>';
    }
    echo '</div>';
}
