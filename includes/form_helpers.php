<?php
// includes/form_helpers.php - renders form fields from a FormValidator ($v)
// e(), csrf_field() etc. already come from config/autoload.php

/** Find the error message for a field, whatever the FormValidator calls its getter. */
if (!function_exists('form_error')) {
    function form_error($v, string $name): string
    {
        try {
            foreach (['error', 'getError', 'firstError'] as $m) {
                if (method_exists($v, $m)) {
                    $r = $v->$m($name);
                    if (is_array($r)) { $r = reset($r); }
                    if (is_string($r) && $r !== '') { return $r; }
                }
            }
            foreach (['errors', 'getErrors'] as $m) {
                if (method_exists($v, $m)) {
                    $all = $v->$m();
                    if (is_array($all) && isset($all[$name])) {
                        $r = $all[$name];
                        return is_array($r) ? (string) reset($r) : (string) $r;
                    }
                }
            }
        } catch (Throwable $ex) {
            // ignore and show no error text
        }
        return '';
    }
}

if (!function_exists('render_input')) {
    function render_input($v, string $name, string $label, string $type = 'text',
                          string $col = 'col-md-6', string $attrs = ''): void
    {
        $error = form_error($v, $name);
        $value = $type === 'password' ? '' : (string) $v->old($name);
        $class = 'form-control' . ($error !== '' ? ' is-invalid' : '');
        ?>
        <div class="<?= e($col) ?>">
            <label class="form-label" for="<?= e($name) ?>"><?= e($label) ?></label>
            <input class="<?= $class ?>" type="<?= e($type) ?>" id="<?= e($name) ?>"
                   name="<?= e($name) ?>" value="<?= e($value) ?>" <?= $attrs ?>>
            <?php if ($error !== ''): ?><div class="invalid-feedback"><?= e($error) ?></div><?php endif; ?>
        </div>
        <?php
    }
}

if (!function_exists('render_select')) {
    function render_select($v, string $name, string $label, array $options,
                           string $col = 'col-md-6'): void
    {
        $error    = form_error($v, $name);
        $selected = (string) $v->old($name);
        $class    = 'form-select' . ($error !== '' ? ' is-invalid' : '');
        $isList   = array_keys($options) === range(0, count($options) - 1);
        ?>
        <div class="<?= e($col) ?>">
            <label class="form-label" for="<?= e($name) ?>"><?= e($label) ?></label>
            <select class="<?= $class ?>" id="<?= e($name) ?>" name="<?= e($name) ?>">
                <option value="">Select...</option>
                <?php foreach ($options as $key => $text):
                    $val = $isList ? (string) $text : (string) $key; ?>
                    <option value="<?= e($val) ?>"<?= $val === $selected ? ' selected' : '' ?>>
                        <?= e((string) $text) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($error !== ''): ?><div class="invalid-feedback"><?= e($error) ?></div><?php endif; ?>
        </div>
        <?php
    }
}

if (!function_exists('render_textarea')) {
    function render_textarea($v, string $name, string $label, int $rows = 4,
                             string $col = 'col-12'): void
    {
        $error = form_error($v, $name);
        $class = 'form-control' . ($error !== '' ? ' is-invalid' : '');
        ?>
        <div class="<?= e($col) ?>">
            <label class="form-label" for="<?= e($name) ?>"><?= e($label) ?></label>
            <textarea class="<?= $class ?>" id="<?= e($name) ?>" name="<?= e($name) ?>"
                      rows="<?= $rows ?>"><?= e((string) $v->old($name)) ?></textarea>
            <?php if ($error !== ''): ?><div class="invalid-feedback"><?= e($error) ?></div><?php endif; ?>
        </div>
        <?php
    }
}