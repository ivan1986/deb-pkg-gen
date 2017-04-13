<?php

namespace Ivan1986\DebBundle\Model;

class DistList
{
    public $lts = '';
    public $stable = '';
    public $testing = '';
    public $all = [];

    /**
     * Обновляет список текущих дистрибутивов.
     *
     * @param array $inPpa Что есть в репозитории
     * @param $current Текущие дистрибутивы
     *
     * @return DistList Себя или клон
     */
    public function update(array $inPpa, $current)
    {
        $up = false;
        $lts = $stable = $testing = $inPpa[0];
        foreach ($inPpa as $in) {
            if ($in <= $current['lts']) {
                $lts = $in;
            }
            if ($in <= $current['stable']) {
                $stable = $in;
            }
            if ($in <= $current['testing']) {
                $testing = $in;
            }
        }
        if ($this->lts != $lts) {
            $this->lts = $lts;
            $up = true;
        }
        if ($this->stable != $stable) {
            $this->stable = $stable;
            $up = true;
        }
        if ($this->testing != $testing) {
            $this->testing = $testing;
            $up = true;
        }
        if (count(array_diff($this->all, $inPpa)) || count(array_diff($inPpa, $this->all))) {
            $this->all = $inPpa;
            $up = true;
        }

        return $up ? clone $this : $this;
    }
}
