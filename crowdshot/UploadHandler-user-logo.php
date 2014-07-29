<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(E_ALL | E_STRICT);
require('UploadHandler.php');
$upload_user_logo_handler = new UploadHandler(array('param_name' => 'user_logo', 'inline_file_types' => '/\.(gif|png)$/i', 'accept_file_types' => '/\.(gif|png)$/i', 'image_file_types' => '/\.(gif|png)$/i', 'asset_type' => 'user_logo'));
