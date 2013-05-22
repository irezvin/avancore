<?php

final class Ac_Facet_ValueNotSet {
    static function is($something) {
        return is_object($something) && $something instanceof self;
    }
    static function instance() {
        return new self;
    }
}