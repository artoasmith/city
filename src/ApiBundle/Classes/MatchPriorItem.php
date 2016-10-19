<?php

namespace ApiBundle\Classes;

class MatchPriorItem
{
    /**
     * @var string
     */
    private $field;

    /**
     * @var integer
     */
    private $price;

    /**
     * @var boolean
     */
    private $totalVariable = false;

    /**
     * Set field
     *
     * @param string $field
     * @return MatchPriorItem
     */
    public function setField($field = null)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set price
     *
     * @param integer $price
     * @return MatchPriorItem
     */
    public function setPrice($price = null)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set totalVariable
     *
     * @param bool $totalVariable
     * @return MatchPriorItem
     */
    public function setTotalVariable($totalVariable = null)
    {
        $this->totalVariable = boolval($totalVariable);

        return $this;
    }

    /**
     * Get totalVariable
     *
     * @return bool
     */
    public function getTotalVariable()
    {
        return $this->totalVariable;
    }

    /**
     * @param $val
     * @return array
     */
    public function buildPriceString($val){
        $resp = [];
        if(!is_array($val))
            $val = [$val];

        foreach ($val as $v){
            $resp[] = sprintf('IF(%s,%d,0)',$v,$this->price);
        }

        return $resp;
    }

    public function isValid(){
        return ($this->field && $this->price>=1);
    }
}