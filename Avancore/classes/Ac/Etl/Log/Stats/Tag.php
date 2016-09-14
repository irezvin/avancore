<?php

class Ac_Etl_Log_Stats_Tag {
    
    protected $path = array();
    
    protected $time = 0;
    
    protected $memory = 0;
    
    protected $count = 0;
    
    protected $items = array();
    
    /**
     * @var Ac_Etl_Log_Stats
     */
    protected $stats = false;
    
    function __construct($path, Ac_Etl_Log_Stats $stats) {
       $this->path = is_array($path)? $path : explode("/", $path);
       $this->stats = $stats;
    }
    
    function getPath($asString = false) {
        return $asString? implode("/", $this->path) : $this->path;
    }
    
    function accept(Ac_Etl_Log_Item $item) {
        if (!in_array($this->getPath(true), $item->tags))
            return false;
        else {
            $this->items[] = $item;
            $this->count++;
            $this->time += $item->spentTime;
            $this->memory += $item->spentMemory;
        }
    }
    
    function getItems() {
        return $this->items;
    }
    
    function getCount() {
        return $this->count;
    }
    
    function getTime() {
        return $this->time;
    }
    
    function getMemory() {
        return $this->memory;
    }

    function isTop() {
        return count($this->path) == 1;
    }
    
    function isParentOf(Ac_Etl_Log_Stats_Tag $tag, $direct = false) {
        $d = count($tag->getPath()) - count($this->path);
        $res = ($direct? $d == 1 : $d > 0) && array_slice($tag->getPath(), 0, count($this->path)) == $this->path;
        return $res;
    }
    
    function getChildTags($direct = false) {
        $res = array();
        foreach ($this->stats->getTags() as $name => $tag) {
            if ($this->isParentOf($tag, $direct)) {
                $res[$name] = $tag;
            }
        }
        return $res;
    }

    static function sumStats(array $tags) {
        $count = 0;
        $time = 0;
        $memory = 0;
        foreach ($tags as $tag) {
            $count += $tag->getCount();
            $time += $tag->getTime();
            $memory += $tag->getMemory();
        }
        return compact('count', 'time', 'memory');
    }
    
    function getChildStats($direct = false, $withSelf = false) {
        $tags = $this->getChildTags($direct);
        if ($withSelf) $tags[] = $this;
        $res = self::sumStats($tags);
        return $res;
    }

    protected function averages(array $stats, $prefix) {
        if ($stats['count']) {
            $stats['avgTime'] = $stats['time'] / $stats['count'];
            $stats['avgMemory'] = $stats['memory'] / $stats['count'];
        } else {
            $stats['avgTime'] = '';
            $stats['avgMemory'] = '';
            $stats['time'] = '';
            $stats['memory'] = '';
        }
        $res = array();
        foreach ($stats as $k => $v) {
            $res[$prefix.ucfirst($k)] = $v;
        }
        return $res;
    }
    
    function getOwnStats() {
        return array(
            'count' => $this->count,
            'time' => $this->time,
            'memory' => $this->memory,
        );
    }
    
    function getExtendedStats($withIndirect) {
        $dcs = $this->getChildStats(true);
        $s = $this->getOwnStats();
        $res = array_merge(
            $this->averages($s, 'own'),
            $this->averages($dcs, 'child')
        );
        if ($withIndirect) {
            $idcs = $this->getChildStats(false);
            $res = array_merge($res, $this->averages($idcs, 'all'));
        }
        return $res;
    }
    
}