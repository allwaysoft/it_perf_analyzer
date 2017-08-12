<?php
/*
  $Id:  new_guestbook.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
  <h1><?php echo $osC_Template->getPageTitle(); ?></h1>
   
  <?php 
    if ($messageStack->size('guestbook') > 0) {
      echo $messageStack->output('guestbook');
    }
  ?>   
  
  <div class="moduleBox">
    <h6><?php echo $osC_Language->get('guestbook_new_heading'); ?></h6>
    
    <div class="content">
      <form name="guestbook_edit" action="<?php echo osc_href_link(FILENAME_INFO, 'guestbook&save'); ?>" method="post">
        <ol> 
          <li><?php echo osc_draw_label($osC_Language->get('field_title'), 'title', null, true) . osc_draw_input_field('title', null);  ?></li>
          <li><?php echo osc_draw_label($osC_Language->get('field_email'), 'email', null, true) . osc_draw_input_field('email');  ?></li>  
          <li><?php echo osc_draw_label($osC_Language->get('field_url'), 'url') . osc_draw_input_field('url');  ?></li>  
          <li><?php echo osc_draw_label($osC_Language->get('field_content'), 'content', null, true) . osc_draw_textarea_field('content', '', 29);  ?></li>          
        </ol>
        
        <div class="submitFormButtons">
          <span style="float: right"><?php echo osc_draw_image_submit_button('button_continue.gif', $osC_Language->get('button_continue')) ?></span>
          
            <?php echo osc_link_object(osc_href_link(FILENAME_INFO, 'guestbook'), osc_draw_image_button('button_back.gif', $osC_Language->get('button_back'))); ?>
        </div>
      </form>
    </div>
  </div>