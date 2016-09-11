<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 10/09/2016
 * Time: 23:40
 *
 * This is a sub-part of a Report relating to a single
 * record.
 */

namespace App;

class RecordReport {

    private $columns = [];
    private $loading_targets = [];
    private $loading_totals = [];
    private $loadings = [];
    private $log = [];

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param array $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * @param string $columnName
     * @return mixed
     */
    public function getColumn($columnName)
    {
        return $this->columns[$columnName];
    }

    /**
     * @param string $columnName
     * @param mixed $value
     */
    public function setColumn($columnName,$value)
    {
        $this->columns[$columnName] = $value;
    }


    /**
     * @return array
     */
    public function getLoadingTargets()
    {
        return $this->loading_targets;
    }

    /**
     * @param array $loading_targets
     */
    public function setLoadingTargets($loading_targets)
    {
        $this->loading_targets = $loading_targets;
    }

    /**
     * @param string $loading
     * @return bool
     */
    public function hasLoadingTarget($loading)
    {
        return isset( $this->loading_targets[$loading]);
    }

    /**
     * @param string $loading
     * @return float
     */
    public function getLoadingTarget($loading)
    {
        return $this->loading_targets[$loading];
    }

    /**
     * @param string $loading
     * @param float $value
     */
    public function setLoadingTarget($loading,$value)
    {
        $this->loading_targets[$loading] = $value;
    }

    /**
     * @return array
     */
    public function getLoadingTotals()
    {
        return $this->loading_totals;
    }

    /**
     * @param string $loading
     * @return float
     */
    public function getLoadingTotal($loading)
    {
        if( !isset( $this->loading_totals[$loading])) {
            return 0;
        }
        return $this->loading_totals[$loading];
    }

    /**
     * @param string $loading
     * @param float $value
     */
    public function setLoadingTotal($loading,$value)
    {
        $this->loading_totals[$loading] = $value;
    }

    /**
     * @return array
     */
    public function getLoadings()
    {
        return $this->loadings;
    }

    /**
     * @param string $loading
     * @param float $value
     */
    public function setLoading($loading,$value)
    {
        $this->loadings[$loading] = $value;
    }

    /**
     * @param $loadItem
     */
    public function appendLoading($loadItem)
    {
        $this->loadings []= $loadItem;
    }

    /**
     * @return array
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param $logItem
     */
    public function appendLog($logItem)
    {
        $this->log []= $logItem;
    }


    /**
     * Turn this object into a structure to be serialised as JSON
     * as part of the report object.
     * @return array
     */
    public function toData()
    {
        return [
            "columns" => $this->columns,
            "loading_targets" => $this->loading_targets,
            "loading_totals" => $this->loading_totals,
            "loadings" => $this->loadings,
            "log" => $this->log];
    }
}