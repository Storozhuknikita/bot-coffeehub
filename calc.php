<?php

class Calculator
{
    public function pow()
    {
        $rgArgs = $this->_get_args(func_get_args());
        if (count($rgArgs) < 2) {
            return null;
        }
        $sA = $rgArgs[0];
        $sB = $rgArgs[1];
        return $this->_pow_simple($sA, $sB);
    }

    public function factorial($sA)
    {
        if (!isset($sA) || !preg_match('/^[-+]{0,1}[0-9]+$/', $sA) || $this->_get_negative($sA)) {
            return null;
        }
        $sA = (string)$sA;
        return $this->_factorial_simple($sA);
    }

    public function abs($sA)
    {
        if (!isset($sA) || !preg_match('/^[-+]{0,1}[0-9]+$/', $sA)) {
            return null;
        }
        $sA = (string)$sA;
        return $sA[0] == '-' ? substr($sA, 1) : $sA;
    }

    public function mul()
    {
        $rgArgs = $this->_get_args(func_get_args());
        if (!count($rgArgs)) {
            return null;
        }
        if (count($rgArgs) == 1) {
            return (string)$rgArgs[0];
        }
        $sResult = '1';
        for ($i = 0; $i < count($rgArgs); $i++) {
            $sResult = $this->_mult_simple($sResult, $rgArgs[$i]);
        }
        return $sResult;
    }

    public function add()
    {
        $rgArgs = $this->_get_args(func_get_args());
        if (!count($rgArgs)) {
            return null;
        }
        if (count($rgArgs) == 1) {
            return (string)$rgArgs[0];
        }
        $sResult = '0';
        for ($i = 0; $i < count($rgArgs); $i++) {
            $sResult = $this->_get_negative($rgArgs[$i]) ?
                $this->_diff_simple($sResult, $this->abs($rgArgs[$i])) :
                $this->_sum_simple($sResult, $rgArgs[$i]);
        }
        return $sResult;
    }

    public function sub()
    {
        $rgArgs = $this->_get_args(func_get_args());
        if (count($rgArgs) < 2) {
            return null;
        }
        $sA = $rgArgs[0];
        $sB = $rgArgs[1];         //-|A| -(-|B|) = |B|-|A|         if($this->_get_negative($sA) && $this->_get_negative($sB))
        {
            return $this->_diff_simple($this->abs($sB), $this->abs($sA));
        }
        //|A| -(-|B|) = |A|+|B|
        if (!$this->_get_negative($sA) && $this->_get_negative($sB)) {
            return $this->add($this->abs($sA), $this->abs($sB));
        }
        //(-|A|) -(|B|) = -(|A|+|B|)
        if ($this->_get_negative($sA) && !$this->_get_negative($sB)) {
            return '-' . $this->add($this->abs($sA), $this->abs($sB));
        }
        //|A| -(|B|) = |A|-|B|
        if (!$this->_get_negative($sA) && !$this->_get_negative($sB)) {
            return $this->_diff_simple($this->abs($sA), $this->abs($sB));
        }
    }

    protected function _pow_simple($sA, $sB)
    {
        if ($sB == '0' || $sB == '1') {
            return $sA;
        }
        if ($sA == '1') {
            return '1';
        }
        $sD = $sA;
        $sI = '1';
        while ($sI != $sB) {
            $sA = $this->_mult_simple($sA, $sD);
            $sI = $this->_sum_simple($sI, '1');
        }
        return $sA;
    }

    protected function _factorial_simple($sA)
    {
        if ($sA == '1') {
            return '1';
        }
        return $this->mul($this->_factorial_simple($this->sub($sA, '1')), $sA);
    }

    protected function _mult_simple($sA, $sB)
    {
        $sSign = '';
        if ($this->_get_negative($sA) ^ $this->_get_negative($sB)) {
            $sSign = '-';
        }
        $sA = strrev($this->abs($sA));
        $sB = strrev($this->abs($sB));
        $rgC = array_fill(0, strlen($sA) + strlen($sB) - 1, 0);
        for ($i = 0; $i < strlen($sA); $i++) {
            for ($j = 0; $j < strlen($sB); $j++) {
                $iCR = (int)$sA[$i] * (int)$sB[$j];
                $k = $i + $j;//-1;                 while($iCR>0)
                {
                    $iCR += (isset($rgC[$k]) ? $rgC[$k] : 0);
                    $rgC[$k] = $iCR % 10;
                    $iCR = (int)($iCR / 10);
                    $k++;
                }
            }
        }
        return $sSign . preg_replace('/^[0]+/', '', strrev(join('', $rgC)));
    }

    protected function _diff_simple($sA, $sB)
    {
        $iMax = strlen($sA);
        if (strlen($sA) < strlen($sB)) {
            $iMax = strlen($sB);
            $sA = str_repeat('0', $iMax - strlen($sA)) . $sA;
        } elseif (strlen($sA) > strlen($sB)) {
            $sB = str_repeat('0', $iMax - strlen($sB)) . $sB;
        }
        $sSign = '';
        $iC = 0;
        if ($this->_compare_longs($sA, $sB) == -1) {
            $sA = $sA ^ $sB;
            $sB = $sA ^ $sB;
            $sA = $sA ^ $sB;
            $sSign = '-';
        }
        for ($i = $iMax - 1; $i >= 0; $i--) {
            $iC += (int)$sA[$i] - (int)$sB[$i] + 10;
            $sA[$i] = (string)($iC % 10);
            $iC = $iC < 10 ? -1 : 0;
        }
        return $sSign . preg_replace('/^[0]+/', '', $sA);
    }

    protected function _sum_simple($sA, $sB)
    {
        $iMax = strlen($sA);
        if (strlen($sA) < strlen($sB)) {
            $iMax = strlen($sB);
            $sA = str_repeat('0', $iMax - strlen($sA)) . $sA;
        } elseif (strlen($sA) > strlen($sB)) {
            $sB = str_repeat('0', $iMax - strlen($sB)) . $sB;
        }
        $iC = 0;
        for ($i = $iMax - 1; $i >= 0; $i--) {
            $iC += (int)$sA[$i] + (int)$sB[$i];
            $sA[$i] = (string)($iC % 10);
            $iC = (int)($iC / 10);
        }
        if ($iC > 0) {
            $sA = (string)$iC . $sA;
        }
        return $sA;
    }

    protected function _get_negative($sA)
    {
        return $sA[0] == '-';
    }

    protected function _compare_longs($sA, $sB)
    {
        $iA = strlen($sA);
        $iB = strlen($sB);
        if ($iA < $iB) {
            return -1;
        }
        if ($iA > $iB) {
            return 1;
        }
        for ($i = 0; $i < $iA; $i++) {
            if ($sA[$i] > $sB[$i]) {
                return 1;
            }
            if ($sA[$i] < $sB[$i]) {
                return -1;
            }
        }
        return 0;
    }

    protected function _get_args($rgArgs)
    {
        if (!count($rgArgs)) {
            return array();
        }
        if (is_array($rgArgs[0])) {
            $rgArgs = $rgArgs[0];
        }
        return $rgArgs;
    }
}


$calculator = new Calculator();
echo $calculator->add('1000000000000', '2000000001111');

?>