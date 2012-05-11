<?php
/**
 * Document record model
 * 
 * @property int $document_id bigint(20)
 * @property string $name varchar(50)
 * @property string $type varchar(20)
 * @property string $status varchar(20)
 * @property string $function varchar(50)
 * @property string $view varchar(50)
 * @property bool $is_indexable tinyint(1)
 * @property bool $is_page tinyint(1)
 * @property int $parent_fk bigint(20)
 * @property int $user_fk bigint(20)
 * @property string $created_at timestamp
 * @property string $modified_at timestamp
 * @property int $sort_order int(10)
 * 
 */
class R_Document extends Ac_Model_Record_PDO{
    const TABLE_NAME = "documents";
    const PRIMARY_KEY = "document_id";

    public function hasParent() {
        
    }

    public function getParent() {
        
    }

    public function hasChildren($where = NULL, $orderBy = NULL, $limit = NULL) {
        
    }

    public function getChildren($where = NULL, $orderBy = NULL, $limit = NULL) {
        
    }
}