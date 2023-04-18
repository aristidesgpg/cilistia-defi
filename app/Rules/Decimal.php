<?php

namespace App\Rules;

use Brick\Math\BigDecimal;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Decimal implements InvokableRule
{
    /**
     * Max precision
     *
     * @var int
     */
    protected int $maxPrecision = 36;

    /**
     * Max scale
     *
     * @var int
     */
    protected int $maxScale = 18;

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): PotentiallyTranslatedString  $fail
     * @return PotentiallyTranslatedString|void
     */
    public function __invoke($attribute, $value, $fail)
    {
        if (!is_numeric($value)) {
            return $fail('validation.numeric')->translate([
                'attribute' => $attribute,
            ]);
        }

        $decimal = BigDecimal::of($value)->abs();
        $precision = strlen((string) $decimal->getUnscaledValue());

        if ($this->maxPrecision && $precision > $this->maxPrecision) {
            $fail('validation.decimal_max_precision')->translate([
                'attribute' => $attribute,
                'max' => $this->maxPrecision,
            ]);
        }

        if ($this->maxScale && $decimal->getScale() > $this->maxScale) {
            $fail('validation.decimal_max_scale')->translate([
                'attribute' => $attribute,
                'max' => $this->maxScale,
            ]);
        }
    }

    /**
     * Set max precision
     *
     * @param  int  $maxPrecision
     * @return $this
     */
    public function maxPrecision(int $maxPrecision): static
    {
        $this->maxPrecision = $maxPrecision;

        return $this;
    }

    /**
     * Set max scale
     *
     * @param  int  $maxScale
     * @return $this
     */
    public function maxScale(int $maxScale): static
    {
        $this->maxScale = $maxScale;

        return $this;
    }

    /**
     * Return new instance
     *
     * @return Decimal
     */
    public static function instance(): Decimal
    {
        return new self;
    }
}
