<?php
/*
  $Id: feature_products.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<!-- module feature_products start //-->

<div class="moduleBox">
    <div id="comments">
        <?php echo $osC_Box->getContent(); ?>

        <div id="respond">
            <h6 id="reply-title">Votre Commentaire
            </h6>

            <form name="contact"
                  action="<?php echo osc_href_link(FILENAME_INFO, 'contact=process', 'AUTO', true, false); ?>"
                  method="post">

                <div class="moduleBox">
                    <div class="content comment">
                        <ol id = "comment_form">
                            <li><?php echo osc_draw_label('Name', 'name', null, true) . osc_draw_input_field('name','', 'size="30"'); ?></li>
                            <li><?php echo osc_draw_label('Email', 'email', null, true) . osc_draw_input_field('email','', 'size="30"'); ?></li>
                            <li><?php echo osc_draw_label('Message', 'enquiry') . osc_draw_textarea_field('enquiry', null, 38, 5); ?></li>

                            <?php if (ACTIVATE_CAPTCHA == '1') { ?>
                            <li><?php echo osc_draw_label('Code', 'concat_code') . osc_draw_input_field('concat_code', '', 'size="30"'); ?> </li>
                            <li><img style="padding-left: 170px;"
                                     src="<?php echo osc_href_link(FILENAME_INFO, 'contact=showImage', 'AUTO', true, false); ?>"
                                     alt="Captcha"/></li>
                            <?php } ?>

                        </ol>
                    </div>
                </div>

                <?php
                    echo osc_draw_hidden_session_id_field();
                ?>

                <div class="submitFormButtons" style="text-align: right;">
                    <?php echo osc_draw_image_submit_button('button_continue.gif', 'Envoyer','id = "submit_btn"'); ?>
                </div>
            </form>
        </div>
        <!-- #respond -->

    </div>
</div>

<!-- module feature_products end //-->
