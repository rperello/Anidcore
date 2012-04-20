<?php

interface Ri_Cache{
    public function save($id, $value, $ttl = 0);

    public function add($id, $value, $ttl = 0);

    public function fetch($id);

    public function delete($id);

    public function clear();

    public function isValid($id);
}