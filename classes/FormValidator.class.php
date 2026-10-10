<?php
/**
 * classes/FormValidator.class.php
 * Validates form input (usually $_POST) with chainable rules.
 *
 * Usage:
 *   $v = new FormValidator($_POST);
 *   $v->field('email', 'Email')->required()->email()->unique($db, 'client', 'email');
 *   $v->field('phone', 'Phone')->required()->phone();
 *   $v->field('pincode', 'Pincode')->pincode();
 *
 *   if ($v->passes()) { ... save ... } else { $errors = $v->errors(); }
 *
 * In the form:
 *   <input name="email" value="<?= e($v->old('email')) ?>">
 *   <span class="error"><?= e($v->error('email')) ?></span>
 */
class FormValidator
{
    private array $data;
    private array $errors = [];
    private ?string $current = null;   // field currently being validated
    private string $label = '';

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /** Start validating a field. Rules after this apply to it. */
    public function field(string $name, ?string $label = null): self
    {
        $this->current = $name;
        $this->label   = $label ?? ucfirst(str_replace('_', ' ', $name));
        return $this;
    }

    // ---------- Rules ----------

    public function required(): self
    {
        if ($this->value() === '') {
            $this->fail('is required');
        }
        return $this;
    }

    public function email(): self
    {
        if ($this->filled() && !filter_var($this->value(), FILTER_VALIDATE_EMAIL)) {
            $this->fail('must be a valid email address');
        }
        return $this;
    }

    /** Indian mobile number: 10 digits starting with 6-9 (optional +91). */
    public function phone(): self
    {
        if ($this->filled() && !preg_match('/^(\+91[\s-]?)?[6-9][0-9]{9}$/', $this->value())) {
            $this->fail('must be a valid 10-digit phone number');
        }
        return $this;
    }

    /** Indian PIN code: 6 digits, not starting with 0. */
    public function pincode(): self
    {
        if ($this->filled() && !preg_match('/^[1-9][0-9]{5}$/', $this->value())) {
            $this->fail('must be a valid 6-digit pincode');
        }
        return $this;
    }

    public function minLength(int $min): self
    {
        if ($this->filled() && mb_strlen($this->value()) < $min) {
            $this->fail("must be at least $min characters");
        }
        return $this;
    }

    public function maxLength(int $max): self
    {
        if ($this->filled() && mb_strlen($this->value()) > $max) {
            $this->fail("must be at most $max characters");
        }
        return $this;
    }

    /** Letters, spaces, dots, apostrophes and hyphens only (for names). */
    public function name(): self
    {
        if ($this->filled() && !preg_match("/^[\p{L} .'-]+$/u", $this->value())) {
            $this->fail('can contain only letters and spaces');
        }
        return $this;
    }

    public function numeric(): self
    {
        if ($this->filled() && !is_numeric($this->value())) {
            $this->fail('must be a number');
        }
        return $this;
    }

    public function min(float $min): self
    {
        if ($this->filled() && is_numeric($this->value()) && (float) $this->value() < $min) {
            $this->fail("must be at least $min");
        }
        return $this;
    }

    /** Date in Y-m-d format (what <input type="date"> sends). */
    public function date(): self
    {
        if ($this->filled()) {
            $d = DateTime::createFromFormat('Y-m-d', $this->value());
            if (!$d || $d->format('Y-m-d') !== $this->value()) {
                $this->fail('must be a valid date');
            }
        }
        return $this;
    }

    public function dateNotInFuture(): self
    {
        if ($this->filled() && $this->value() > date('Y-m-d')) {
            $this->fail('cannot be a future date');
        }
        return $this;
    }

    public function dateNotInPast(): self
    {
        if ($this->filled() && $this->value() < date('Y-m-d')) {
            $this->fail('cannot be a past date');
        }
        return $this;
    }

    /** Value must be one of the allowed options (for dropdowns / radio buttons). */
    public function in(array $allowed): self
    {
        if ($this->filled() && !in_array($this->value(), $allowed, true)) {
            $this->fail('has an invalid selection');
        }
        return $this;
    }

    /** Username: 4-30 characters, letters, numbers and underscore. */
    public function username(): self
    {
        if ($this->filled() && !preg_match('/^[A-Za-z0-9_]{4,30}$/', $this->value())) {
            $this->fail('must be 4-30 characters (letters, numbers, underscore)');
        }
        return $this;
    }

    /** Password: at least 8 characters with a letter and a number. */
    public function password(): self
    {
        $v = $this->value();
        if ($this->filled() && (strlen($v) < 8 || !preg_match('/[A-Za-z]/', $v) || !preg_match('/[0-9]/', $v))) {
            $this->fail('must be at least 8 characters with a letter and a number');
        }
        return $this;
    }

    /** Must equal another field, e.g. confirm password. */
    public function matches(string $otherField, string $otherLabel = 'the other field'): self
    {
        if ($this->value() !== trim((string) ($this->data[$otherField] ?? ''))) {
            $this->fail("must match $otherLabel");
        }
        return $this;
    }

    /**
     * Value must not already exist in the database.
     * $ignoreId lets an edit form keep its own value:  unique($db,'client','email','client_id',$id)
     */
    public function unique(DataAccess $db, string $table, string $column, ?string $idColumn = null, $ignoreId = null): self
    {
        if (!$this->filled()) {
            return $this;
        }
        $where  = "`$column` = ?";
        $params = [$this->value()];
        if ($idColumn !== null && $ignoreId !== null) {
            $where   .= " AND `$idColumn` <> ?";
            $params[] = $ignoreId;
        }
        if ($db->exists($table, $where, $params)) {
            $this->fail('is already in use');
        }
        return $this;
    }

    // ---------- Results ----------

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    /** First error message for a field, or '' if none. */
    public function error(string $name): string
    {
        return $this->errors[$name] ?? '';
    }

    /** Trimmed value for a field. Use it to refill the form after an error (wrap in e()). */
    public function old(string $name): string
    {
        return trim((string) ($this->data[$name] ?? ''));
    }

    /** Add your own error, e.g. $v->addError('username', 'Invalid username or password'). */
    public function addError(string $name, string $message): void
    {
        $this->errors[$name] ??= $message;
    }

    // ---------- Internals ----------

    private function value(): string
    {
        return trim((string) ($this->data[$this->current] ?? ''));
    }

    private function filled(): bool
    {
        return $this->value() !== '';
    }

    /** Keep only the first error per field. */
    private function fail(string $message): void
    {
        $this->errors[$this->current] ??= $this->label . ' ' . $message;
    }
}