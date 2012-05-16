<?php

/**
 * Document record model
 * 
 * @property int $document_id bigint(20)
 * @property string $name varchar(50)
 * @property string $type varchar(20)
 * @property string $status varchar(20)
 * @property string $function varchar(50)
 * @property string $template varchar(50)
 * @property bool $is_indexable tinyint(1)
 * @property bool $is_page tinyint(1)
 * @property int $parent_fk bigint(20)
 * @property int $user_fk bigint(20)
 * @property string $created_at timestamp
 * @property string $modified_at timestamp
 * @property int $sort_order int(10)
 * 
 */
class R_Document extends Ac_Model_Record_PDO {

    const TABLE_NAME = "documents";
    const PRIMARY_KEY = "document_id";

    public function hasParent() {
        return ($this->parent_fk > 0);
    }

    public function getParent() {
        if ($this->hasParent()) {
            $parent = new static($this->parent_fk);
            if ($parent->exists()) {
                return $parent;
            }
        }
        return false;
    }

    public function hasChildren($where = NULL, $orderBy = NULL, $limit = NULL, $select = NULL) {
        return count($this->getChildren($where, $orderBy, $limit, $select)) > 0;
    }

    public function getChildren($where = NULL, $orderBy = NULL, $limit = NULL, $select = NULL) {
        if ($this->exists()) {
            if (!empty($where))
                $where = " AND (" . $where . ")";
            return static::find('(parent_fk=' . $this->id . ")" . $where, $orderBy, $limit, $select);
        }
        return array();
    }

}