<?php

// gtn_wc_admin_post_product_page.js -> 'Set product image' test
$response = array(
    'productImageID' => "test_product_image_id",
    'productImageHTML' => base64_encode("test_image_html"),
    'productGalleryHTML' => base64_encode('<div class="product_images"><input id="product_image_gallery" value="test_val"></input></div>')
);
echo json_encode($response);
return;
?>
<html>
<head>
<title>Gooten QUnit test Ajax</title>
</head>
<body>
<h1>Gooten QUnit test Ajax</h1>
</body>
</html>
