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

class RecordReport
{

    private $columns = [];
    private $loading_targets = [];
    private $loading_totals = [];
    private $loading_params = [];
    private $loadings = [];
    private $log = [];

    public function __construct($data = null)
    {
        if (isset($data)) {
            $this->columns = $data["columns"];
            $this->loading_targets = $data["loading_targets"];
            $this->loading_totals = $data["loading_totals"];
            $this->loadings = $data["loadings"];
            $this->loading_params = $data["loading_params"];
            $this->log = $data["log"];
        }
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
            "loading_params" => $this->loading_params,
            "loadings" => $this->loadings,
            "log" => $this->log];
    }

    /**
     * Return the list of loadings this report has a target or total for.
     * @return array[string]
     */
    public function getLoadingTypes()
    {
        return array_unique(array_merge(array_keys($this->loading_targets), array_keys($this->loading_totals)), SORT_REGULAR);
    }

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
    public function setColumn($columnName, $value)
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
        return isset($this->loading_targets[$loading]);
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
    public function setLoadingTarget($loading, $value)
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
        if (!isset($this->loading_totals[$loading])) {
            return 0;
        }
        return $this->loading_totals[$loading];
    }

    /**
     * @param string $loading
     * @param float $value
     */
    public function setLoadingTotal($loading, $value)
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
     * @return array
     */
    public function getLoading($loading)
    {
        if (array_key_exists($loading, $this->loadings)) {
            return $this->loadings[$loading];
        }
        return null;
    }

    /**
     * @param string $loading
     * @param float $value
     */
    public function setLoading($loading, $value)
    {
        $this->loadings[$loading] = $value;
    }

    /**
     * @param $loadItem
     */
    public function appendLoading($loadItem)
    {
        $this->loadings[$loadItem["target"]][] = $loadItem;
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
        $this->log [] = $logItem;
    }

    public function setLoadingOption($loading, $option, $value)
    {
        $this->loading_params[$loading][$option] = $value;
    }

    public function getLoadingOption($loading, $option)
    {
        $value = @$this->loading_params[$loading][$option];
        if (!isset($value)) {
            if ($option == "units") {
                return "drogna";
            }
        }
        return $value;
    }

}