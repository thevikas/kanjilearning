<?php
require_once("db.php");
$ans = cleanvarp('ans');
$qid = cleanvarp('id');
$meanslike = cleanvarp('meanslike');
$qt = $_POST['qt'];

$halt = 0;
ob_start();
$word = new clsWord();
$word->load($qid);
print_r($word);

$wrongs = $corrects = $status=0;
print_r($_POST);
#checking right or wrong based on question type
if($qt==1)
{
    if(addslashes($word->means) == $ans)
        $corrects=1;
    else
        $wrongs=1;
}
else if($qt==2)
{
    $w_means = strtolower(addslashes($word->means));
    $w_given = strtolower(addslashes($meanslike));

    if(strstr($w_means,$w_given))
        $corrects=1;
    else
        $wrongs=1;
}

#handling correct answers
if($corrects)
{
    echo "<br/>correct";
    $corrects = 1;
    $jump = 2;

    if($word->correct>3 && $word->percentage==100)
        $jump = 20;

    if($word->correct>15)
        $jump+=5;

    if($word->correct>20)
        $jump+=10;

    if($word->correct>30)
        $jump+=15;

    if($word->shown==0)
    {
        echo "01";
        $word->difference = 10;
    }
    else if($word->difference < 10)
    {
        echo "02";
        #if($word->percentage>80)
        #    $halt=1;
        if($word->percentage==100 && $word->shown>=3)
            $word->difference+=10;
        else if($word->percentage>80 && $word->shown>5)
            $word->difference+=5;
        else
            $word->difference+=2;
    }
    else if($word->difference >= 10 && $word->correct>=1 && $word->correct<=3)
    {
        echo "03";
        $corrects=1;
    }
    else if($word->difference >= 10)
    {
        echo "07";
        $word->difference += $jump;
    }
    else
    {
        echo "95";
    }
}
else #handling wrong answers
{
    $wrong_diff2 = 0;
    $wrong_diff1 = 1;
    if($word->percentage>90 && $word->shown>15 && $word->correct>10)
    {
        echo "06";
        $wrong_diff2 = 5;
    }

    if($word->wrong>=0 && $word->wrong<=3)
    {
        echo "04";
        $word->difference = $wrong_diff1;
    }
    else if($word->wrong>3)
    {
        echo "05";
        $word->difference = $wrong_diff2;
    }
    else
    {
        echo "96";
    }
    $wrongs=1;
}

$myi->query("insert into score(kanji_id,status,dated) values($qid,$status,now())");
$myi->query("insert into sensex(v) select avg(100*correct/shown) from stats");

$r = $myi->query("select * from stats where kanji_id=$qid");
if(!$r->fetch_array())
    doqueryi("INSERT INTO `stats` (`kanji_id`, `shown`, `correct`, `dated`, `wrong`, `difference`, `countdown`) VALUES ($qid,0,0,now(),0,0,0);");
$myi->query("update stats set countdown=countdown-1 where countdown>0");
$myi->query($sql = "update stats  set 
                dated=now()
                ,shown=shown+1
                ,correct=correct+$corrects
                ,difference={$word->difference}
                ,countdown=countdown+{$word->difference}
                ,wrong=wrong+$wrongs
                
         where kanji_id=$qid");
echo "<br/><br/>" . $sql;



if(!$halt)
    header("Location: ./?last=$corrects");
?>
<script type="text/javascript">
//setTimeout('window.location.href="./?last=<?=$corrects?>"',5000);
</script>
