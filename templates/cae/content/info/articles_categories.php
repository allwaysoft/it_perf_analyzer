<?php
/*
  $Id: articles_categories.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $Qarticles = toC_Articles::getListing($_GET['articles_categories_id']);
?>

<?php
  if ($Qarticles->numberOfRows() > 0) {
    $article =  $Qarticles->next();
?>

<h3><div class="title"><?php echo $article['content_name']; ?></div></h3>

<?php
    if (!osc_empty($article['articles_image'])) {
      echo '<p style="float: right; padding: 0px 5px 5px 5px">' . $osC_Image->show($article['articles_image'], $article['articles_image'], '', 'product_info', 'articles') . '</p>';
    }
?>
<div class="section_box2">
        <p><?php echo $article['articles_description']; ?></p>
      </div>

<?php
  }
?>