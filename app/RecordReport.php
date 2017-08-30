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
    private $total_columns = [];
    private $mean_columns = [];

    private $loading_target;
    private $loading_total;
    private $loading_params = [];
    private $loadings = [];
    private $log = [];

    public function __construct($data = null)
    {
        if (isset($data)) {
            $this->columns = $data["columns"];
            $this->loading_target = $data["loading_target"];
            $this->loading_total = $data["loading_total"];
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
            "loading_target" => $this->loading_target,
            "loading_total" => $this->loading_total,
            "loading_params" => $this->loading_params,
            "loadings" => $this->loadings,
            "log" => $this->log];
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
     * @param bool $total
     * @param bool $mean
     */
    public function setColumn($columnName, $value, $total = false, $mean = false)
    {
        $this->columns[$columnName] = $value;
        if ($total) {
            $this->total_columns[$columnName] = true;
        }
        if ($mean) {
            $this->mean_columns[$columnName] = true;
        }
    }

    /**
     * Return values for columns that should have a mean calculated.
     * @return array
     */
    public function getMeanColumns()
    {
        $values = [];
        foreach ($this->mean_columns as $columnName => $duff) {
            if (isset($this->columns[$columnName])) {
                $values[$columnName] = $this->columns[$columnName];
            }
        }
        return $values;
    }

    /**
     * Return values for columns that should be totalled.
     * @return array
     */
    public function getTotalColumns()
    {
        $values = [];
        foreach ($this->total_columns as $columnName => $duff) {
            if (isset($this->columns[$columnName])) {
                $values[$columnName] = $this->columns[$columnName];
            }
        }
        return $values;
    }

    /**
     * @return float
     */
    public function getLoadingTarget()
    {
        return $this->loading_target;
    }

    /**
     * @param float $target
     */
    public function setLoadingTarget($target)
    {
        $this->loading_target = $target;
    }

    /**
     * @return float
     */
    public function getLoadingTotal()
    {
        return $this->loading_total;
    }

    /**
     * @param float $value
     */
    public function setLoadingTotal($value)
    {
        $this->loading_total = $value;
    }

    /**
     * @return array
     */
    public function getLoadings()
    {
        return $this->loadings;
    }


    /**
     * @param $loadItem
     */
    public function appendLoading($loadItem)
    {
        $this->loadings[] = $loadItem;
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

    /**
     * @param string $option
     * @param mixed $value
     */
    public function setOption($option, $value)
    {
        $this->loading_params[$option] = $value;
    }

    /**
     * @param string $option
     * @return mixed
     */
    public function getOption($option)
    {
        $value = @$this->loading_params[$option];
        if (!isset($value)) {
            if ($option == "units") {
                return "hours";
            }
        }
        return $value;
    }

    /**
     * Return the options for categories on this recordreport
     * @return array
     */
    function categories()
    {
        $categories = [];

        $options = $this->options();
        foreach ($options as $key => $value) {
            if (preg_match('/^category_exists_(.*)$/', $key, $parts)) {
                $category = $parts[1];
                $categories[$category]['exists'] = true;
                foreach ([
                             'background_color',
                             'text_color',
                             'description',
                             'show_column',
                             'label'] as $param) {
                    $pkey = "category_" . $param . "_" . $category;
                    if (array_key_exists($pkey, $options)) {
                        $categories[$category][$param] = $options[$pkey];
                    }
                }
            }
        }
        return $categories;
    }

    /**
     * @return array
     */
    public function options()
    {
        return $this->loading_params;
    }
}
