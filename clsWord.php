<?php

class clsWord
{
    var $id;
    var $word;
    var $means;

    static function getNextWord()
    {
        global $myi;
        $r = doqueryi("select id from vocab v
                left join stats s on s.kanji_id=v.id  where v.chapter in (1,2,3)
            order by coalesce(countdown,0),id");
        $rs = $r->fetch_array();
        $word = new clsWord();
        $word->load($rs['id']);
        return $word;
    }

    static function getRandomWords($correct)
    {
        #select 5 random meanings
        $ids = array();
        for($i=0; $i<4; $i++)
        {
            $ids[]=rand(1,720);
        }
        $idstring = join(',',$ids);

        $r2 = doqueryi("select means from vocab where id in ($idstring)");
        $means = array();
        $means[] = $correct;
        while($rs2 = $r2->fetch_array())
        {
	        $means[] = $rs2['means'];
        }
        return $means;

    }

    function load($id)
    {
        global $myi;
        $r = doqueryi("select * from vocab v
                 left join stats s on s.kanji_id=v.id
                 where id=$id");
        if($rs = $r->fetch_array())
        {
            $this->id = $rs['id'];
            $this->means = $rs['means'];
            $this->word = $rs['word'];

            $this->shown = 0 + $rs['shown'];
            $this->correct = 0 + $rs['correct'];
            $this->wrong = 0 + $rs['wrong'];
            $this->difference = 0 + $rs['difference'];
            $this->countdown = 0 + $rs['countdown'];
            $this->percentage = 0;
            if($this->shown>0)
                $this->percentage = round(100*$this->correct/$this->shown);
    
            return true;
        }
        die("not found clsword:$id");
    }
}


?>
