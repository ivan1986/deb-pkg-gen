<?php

namespace Ivan1986\DebBundle\Model;

use Symfony\Component\Process\Process;

class DistList
{
    protected static $current = [];

    const DISTRS = [
        'lts',
        'stable',
        'testing',
    ];
    public $lts = '';
    public $stable = '';
    public $testing = '';
    public $all = [];

    /**
     * Обновляет список текущих дистрибутивов.
     *
     * @param array $inPpa Что есть в репозитории
     * @param array $current Текущие дистрибутивы
     *
     * @return array
     */
    public function update(array $inPpa, array $current = [])
    {
        $current = $current ? $current : $this->getCurrent();

        $data = [
            'all' => $inPpa,
        ];

        $maxPos = [];
        foreach (self::DISTRS as $type) {
            $maxPos[$type] = 0;
        }

        foreach ($inPpa as $in) {
            if ($in == 'devel') {
                $data['devel'] = ['devel'];
            }

            $pos = array_search($in, $current['all']);
            foreach (self::DISTRS as $type) {
                if ($pos > $current['pos-'.$type]) {
                    continue;
                }
                if ($pos > $maxPos[$type]) {
                    $data[$type] = [$in];
                    $maxPos[$type] = $pos;
                }
            }
        }

        return $data;
    }

    public function convert()
    {
        $data = [];
        if ($this->lts) {
            $data['lts'] = [$this->lts];
        }
        if ($this->stable) {
            $data['stable'] = [$this->stable];
        }
        if ($this->testing) {
            $data['testing'] = [$this->testing];
        }
        if (isset($this->all['devel'])) {
            $data['devel'] = ['devel'];
        }
        if ($this->all) {
            $data['all'] = $this->all;
        }
        return $data;
    }

    protected function getCurrent()
    {
        if (empty(static::$current)) {
            $p = new Process('ubuntu-distro-info --all');
            $p->start();
            $p->wait();
            static::$current['all'] = array_filter(explode("\n", $p->getOutput()));
            foreach (self::DISTRS as $type) {
                $p = new Process('ubuntu-distro-info --'.($type == 'testing' ? 'latest' : $type));
                $p->start();
                $p->wait();
                static::$current[$type] = trim($p->getOutput());
                static::$current['pos-'.$type] = array_search(static::$current[$type], static::$current['all']);
            }
        }

        return static::$current;
    }
}
