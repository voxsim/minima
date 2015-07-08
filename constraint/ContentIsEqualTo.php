<?php

class ContentIsEqualTo extends PHPUnit_Framework_Constraint
{
    protected $value;

    public function __construct($value)
    {
        parent::__construct();
        $this->value = $value;
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        if ($this->value === $other->getContent()) {
            return true;
        }

        $comparatorFactory = SebastianBergmann\Comparator\Factory::getInstance();

        try {
            $comparator = $comparatorFactory->getComparatorFor(
                $this->value,
                $other->getContent()
            );

            $comparator->assertEquals(
                $this->value,
                $other->getContent()
            );
        } catch (SebastianBergmann\Comparator\ComparisonFailure $f) {
            if ($returnResult) {
                return false;
            }

            throw new PHPUnit_Framework_ExpectationFailedException(
                trim($description . "\n" . $f->getMessage()),
                $f
            );
        }

        return true;
    }

    public function toString()
    {
        if (strpos($this->value, "\n") !== false) {
            return 'content is equal to <text>';
        } else {
            return sprintf(
                'content is equal to <string:%s>',
                $this->value
            );
        }
    }
}
