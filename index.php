<?php
/**
 * Created by PhpStorm.
 * User: linliangliu
 * Date: 16/6/22
 * Time: 15:16
 */

$root = realpath('./') . '/';
include_once './libs/Template.php';

$conf = [
    'tpl_path' => $root . 'tpl_path/',
    'com_path' => $root . 'com_path/'
];

$tplObj = Template::getInstance($conf);
$tplObj->setVars([
    'name' => 'zhangsan',
    'age' => 18
]);

$html = $tplObj->render('index');
echo $html;