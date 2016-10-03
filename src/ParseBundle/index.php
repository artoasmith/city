<?php
require_once 'lib/api.php';
$result=get_price('http://stylus.ua/sokovyzhimalki/philips-hr-183202.html','#product-block .price');
print_r($result);
if(!$result){
    echo 'больше 1 результата';
}
?> 