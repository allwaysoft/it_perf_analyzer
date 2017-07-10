<?php
/*
  $Id: categories.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

    class osC_Content_visits_countries extends osC_Modules
    {
        var $_title,
        $_code = 'visits_countries',
        $_author_name = 'Guy Fomi',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'content';

        function osC_Content_visits_countries()
        {
            $this->_title = 'Resume des visiteurs par pays';
        }

        function initialize()
        {
            $this->_content = '<div id="widgetIframe"><iframe width="100%" height="350" src="http://localhost/piwik/index.php?module=Widgetize&action=iframe&moduleToWidgetize=UserCountryMap&actionToWidgetize=worldMap&idSite=1&period=day&date=yesterday&disableLink=1&widget=1" scrolling="no" frameborder="0" marginheight="0" marginwidth="0"></iframe></div>';
        }

        function install()
        {
            parent::install();
        }

        function getKeys()
        {
            if (!isset($this->_keys)) {
                $this->_keys = array();
            }

            return $this->_keys;
        }
    }

?>