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
     */
    public function setColumn($columnName, $value)
    {
        $this->columns[$columnName] = $value;
    }


    /**
     * @return array
     */
    public function getLoadingTarget()
    {
        return $this->loading_target;
    }

    /**
     * @param array $target
     */
    public function setLoadingTarget($target)
    {
        $this->loading_target = $target;
    }

    /**
     * @return array
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
     * @return array
     */
    public function options()
    {
        return $this->loading_params;
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
}
