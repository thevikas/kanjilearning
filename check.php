<?php
require_once("db.php");
$ans = $_POST['ans'];
$qid = $_POST['id'];

$status=0;
#load lowest id kanji
$r = $myi->query("select id from vocab where id=$qid and means='$ans' limit 0,1");
if($rs = $r->fetch_array())
{
    $status=1;
}

$myi->query("insert into score(kanji_id,status,dated) values($qid,$status,now())");
$r = $myi->query("select * from stats where kanji_id=$qid");
if($rs = $r->fetch_array())
    $myi->query("update stats  set dated=now(),shown=shown+1,correct=correct+$status where kanji_id=$qid");
else
    $myi->query("insert into stats(kanji_id,shown,correct,dated) values($qid,1,$status,now())");
header("Location: ./");
?>
