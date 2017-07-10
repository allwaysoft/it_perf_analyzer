<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 10/2/11
 * Time: 9:55 AM
 * To change this template use File | Settings | File Templates.
 */
 
class workflow {
    private $steps;
    private $initiator;
    private $initiation_form;
    private $initiation_date;
    private $associations;
    private $status;
    private $start_date;
    
    function start()
    {
        
    }

    function initiate()
    {

    }

    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }

    public function getStartDate()
    {
        return $this->start_date;
    }
}
