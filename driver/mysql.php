<?php
namespace Phiber\oosql\driver;
use Phiber\oosql\oogen;

class mysql extends oogen
{

    protected $queries = array(
        'tables' => 'SHOW TABLES',
        'columns' => 'SHOW COLUMNS FROM',
        'create' => 'show create table',
        'meta' => 'SELECT column_name, data_type, character_maximum_length FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "<tbl_name>" AND TABLE_SCHEMA = "<db_name>";',
        );


    protected function getMeta($table)
    {

        $fields = [];
        $query = $this->queries['meta'];

        $query = str_replace('<tbl_name>', $table, $query);
        $query = str_replace('<db_name>', $this->database, $query);

        $collection = $this->getCollection($query);

        if (!$collection) {

            foreach ($this->errors as $error) {
                print implode('|', $error) . PHP_EOL;
            }
            return false;
        }
        foreach ($collection as $meta) {
            $meta = (array)$meta;
            $fields[array_shift($meta)] = array_values($meta);
        }
        return $fields;
    }
    protected function createProps($fields, $tname, $cols)
    {
        $this->prepareKeys($fields, $tname, $cols);
        $this->getManyToMany();


        foreach ($cols as $col) {

            if (isset($col['Key'])) {

                if ($col['Key'] == 'UNI') {
                    $this->unique[$tname][] = $col['Field'];
                    if (isset($this->foreign[$tname][trim($col['Field'])])) {
                        $l_field = trim($this->foreign[$tname][$col['Field']][0]);
                        $f_table = trim($this->foreign[$tname][$col['Field']][1]);
                        $this->hasOne[$f_table][$l_field][] =  $tname . '.' . $col['Field'];

                        $this->hasMany[$f_table][$l_field] = array_diff($this->hasMany[$f_table][$l_field], $this->hasOne[$f_table][$l_field]);

                    }
                }
            }
        }

    }

    protected function getManyToMany()
    {
        foreach ($this->belongsTo as $table => $refTables) {
            if (count($refTables) > 1) {// If it belongs to more than one table those tables are related through this one
                foreach ($refTables as $tbl) {
                    $relatedTables = array_diff($refTables, array($tbl));
                    if (count($relatedTables) && array_key_exists($tbl, $this->belongsTo)) {
                        $relatedTables = array_diff($relatedTables, $this->belongsTo[$tbl]);
                    }
                    if (count($relatedTables) && array_key_exists($tbl, $this->hasMany)) {
                        $tables = array();
                        foreach ($this->hasMany[$tbl] as $key => $relation) {
                            foreach ($relation as $relationStr) {
                                $relationParts = explode('.', $relationStr);
                                $tables[] = $relationParts[0];
                            }
                        }

                        $relatedTables = array_diff($relatedTables, $tables);
                    }
                    $relatedTables = (null !== $relatedTables)?array_values($relatedTables):array();
                    if (count($relatedTables)) {
                        $this->manyThrough[$tbl] = array($table => $relatedTables);
                    }
                }
            }
        }
    }

    protected function prepareKeys($fields, $tname, $cols)
    {
        foreach ($cols as $col) {

            if (isset($col['constraints'])) {

                $cnt = count($fields[$tname]);
                for ($i = 0; $i < $cnt; $i++) {
                    foreach ($fields[$tname][$i] as $key => $val) {

                        if (!empty($val) && is_string($val) && isset($col['constraints'][$val])) {
                            $this->foreign[$tname][$val] = $col['constraints'][$val];

                            $this->belongsTo[$tname][] = $col['constraints'][$val][1];

                            $this->hasMany[$col['constraints'][$val][1]][trim($col['constraints'][$val][0])][] = $tname . '.' . $val;


                        }
                    }
                }
            }

            if (isset($col['Key']) && $col['Key'] == 'PRI') {
                $this->primary[$tname][] = $col['Field'];
            }

        }
    }
    protected function analyze($tbls)
    {
        $fields = [];
        foreach ($tbls as &$tbl) {

            $table = array_pop($tbl);

            print "Analyzing $table physical columns ..." . PHP_EOL;

            $query = $this->queries['columns'] . ' ' .$this->database.'.'.$table;

            $collection = $this->getCollection($query);

            if (!$collection) {

                foreach ($this->errors as $error) {
                    print implode('|', $error) . PHP_EOL;
                }
                return false;
            }
            foreach ($collection as $columns) {
                $fields[$table][] = (array)$columns;
            }

            print "Analyzing $table DDL ..." . PHP_EOL;

            $ddl = $this->getCollection($this->queries['create'] . ' ' . $this->database .'.' . $table);

            if (!$ddl) {

                foreach ($this->errors as $error) {
                    print implode('|', $error) . PHP_EOL;
                }
                return false;
            }

            $ks = array();
            foreach ($ddl as $ex) {
                $ks[] = (array)$ex;
            }

            $create = $ks[0]['Create Table'];

            $keys = explode(',', $create);
            $kcount = count($keys);
            while (substr(trim($keys[$kcount - 1]), 0, 10) === 'CONSTRAINT') {
                $key = array_pop($keys);
                $parts = explode(' ', trim($key));
                $constraint = trim($parts[1], '`');
                $fkey = trim($parts[4], '()`');
                $reftable = trim($parts[6], '`');
                $reffield = trim($parts[7], '()`');
                $reffield = str_replace(array(')', '`'), array('', ''), $reffield);
                print "Found constraint $constraint ..." . PHP_EOL;
                $fields[$table][]['constraints'][$fkey] = array($reffield, $reftable);
                unset($keys[$kcount - 1]);
                $kcount--;
            }
        }
        return $fields;
    }
}
