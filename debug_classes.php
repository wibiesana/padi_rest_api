<?php
require 'vendor/autoload.php';
use Wibiesana\Padi\Core\Query;
use Wibiesana\Padi\Core\Database;

$id = 2; // Target assignment ID
$q = Query::find()->from('assignment_class')->where(['assignment_id' => $id])->all();
echo "DATA FOR ASSIGNMENT $id IN assignment_class:\n";
print_r($q);

$q2 = Query::find()
    ->select('classroom.*')
    ->from('classroom')
    ->innerJoin('assignment_class', 'classroom.id = assignment_class.classroom_id')
    ->where(['assignment_class.assignment_id' => $id])
    ->all();
echo "\nJOINED CLASSROOM DATA:\n";
print_r($q2);
