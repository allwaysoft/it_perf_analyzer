<?php
/*
  $Id: info.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<h3>
    <div class="title"><?php echo $article['content_name']; ?></div>
</h3>
<div class="content">
<?php
    $content = '<table border="0" align="left" width="100%"><tbody><tr><td><table border="0" align="left"><tbody><tr>';
    $content .= '<td align="left" valign="top">' . $osC_Image->showArticleImage($article['events_image'], $article['events_name'], '', 'product_info', 'events') . '</td>';
    $content .= '<td align="left" valign="top">' . $article['events_intro'] . ' </td>';
    $content .= '</tr></tbody></table></td></tr><tr>';
    $content .= '<td>' . $article['events_description'] . '</td></tr><tr><td><span style="font-style: italic;">' . $article['events_date'] . ' - ' . $article['events_location'] . '</span></td></tr></tbody></table>';
    echo $content;
?>
</div>
