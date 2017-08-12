<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 10/2/11
 * Time: 11:15 AM
 * To change this template use File | Settings | File Templates.
 */
 
class task extends content{
    private $start_date;
    private $due_date;
    private $time_estimate;
    private $repeat_intervall;
    private $notification_channel;//email or sms
    private $periode_len_before_notification;
    private $periode_before_notification;
    private $contacts_to_notify;
    private $contacts_groups_to_notify;
    private $assigned_to;
    private $assigned_by;
    private $date_assigned;
    private $completed_by;
    private $date_completed;
    private $priority;
    private $state;

    public function setContactsGroupsToNotify($contacts_groups_to_notify)
    {
        $this->contacts_groups_to_notify = $contacts_groups_to_notify;
    }

    public function getContactsGroupsToNotify()
    {
        return $this->contacts_groups_to_notify;
    }

    public function setContactsToNotify($contacts_to_notify)
    {
        $this->contacts_to_notify = $contacts_to_notify;
    }

    public function getContactsToNotify()
    {
        return $this->contacts_to_notify;
    }

    public function setDueDate($due_date)
    {
        $this->due_date = $due_date;
    }

    public function getDueDate()
    {
        return $this->due_date;
    }

    public function setNotificationChannel($notification_channel)
    {
        $this->notification_channel = $notification_channel;
    }

    public function getNotificationChannel()
    {
        return $this->notification_channel;
    }

    public function setPeriodeBeforeNotification($periode_before_notification)
    {
        $this->periode_before_notification = $periode_before_notification;
    }

    public function getPeriodeBeforeNotification()
    {
        return $this->periode_before_notification;
    }

    public function setPeriodeLenBeforeNotification($periode_len_before_notification)
    {
        $this->periode_len_before_notification = $periode_len_before_notification;
    }

    public function getPeriodeLenBeforeNotification()
    {
        return $this->periode_len_before_notification;
    }

    public function setRepeatIntervall($repeat_intervall)
    {
        $this->repeat_intervall = $repeat_intervall;
    }

    public function getRepeatIntervall()
    {
        return $this->repeat_intervall;
    }

    public function setStartDate($start_fate)
    {
        $this->start_date = $start_fate;
    }

    public function getStartDate()
    {
        return $this->start_date;
    }

    public function setTimeEstimate($time_estimate_minutes)
    {
        $this->time_estimate = $time_estimate_minutes;
    }

    public function getTimeEstimate()
    {
        return $this->time_estimate;
    }

    public function setAssignedBy($assigned_by)
    {
        $this->assigned_by = $assigned_by;
    }

    public function getAssignedBy()
    {
        return $this->assigned_by;
    }

    public function setAssignedTo($assigned_to)
    {
        $this->assigned_to = $assigned_to;
    }

    public function getAssignedTo()
    {
        return $this->assigned_to;
    }

    public function setCompletedBy($completed_by)
    {
        $this->completed_by = $completed_by;
    }

    public function getCompletedBy()
    {
        return $this->completed_by;
    }

    public function setDateAssigned($date_assigned)
    {
        $this->date_assigned = $date_assigned;
    }

    public function getDateAssigned()
    {
        return $this->date_assigned;
    }

    public function setDateCompleted($date_completed)
    {
        $this->date_completed = $date_completed;
    }

    public function getDateCompleted()
    {
        return $this->date_completed;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }
}
