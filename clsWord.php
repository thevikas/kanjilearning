<?php

class clsWord
{
    var $id;
    var $word;
    var $means;
    var $shown;
    var $percentage;
    var $correct;
    var $wrong;
    /*
     * countdown jumpt difference currently assigned
     */
    var $difference;
    /*
     * When was it shown first time
     */
    var $firstdate;
    var $dated;
    /*
     * Countdown
     */
    var $countdown;


    static function getNextWord($newword=1,$oldid=0)
    {
        global $myi;
        global $new_kanji_ratio;
        global $old_kanji_ratio;
        #first get kanji with countdown=ZERO
        $r = doqueryi("SELECT id FROM vocab v
                         JOIN stats s ON s.kanji_id=v.id
                        AND date(nextdate)<=DATE(NOW())
                        -- AND countdown=0
                     LIMIT 0,$old_kanji_ratio");
        $ids = array();

        while($rs = $r->fetch_array())
        {
             debugprint("\n<!-- old -->");
            $ids[] = $rs['id'];
        }

        if($newword)
        {
            //debugprint(__FILE__ . ":" . __LINE__ . "; new word");
            #first get a new kanji
            $r = doqueryi("SELECT id,countdown,chapter FROM vocab v
                             left JOIN stats s ON s.kanji_id=v.id
                                where 1=1 
                                -- and book=10
                                and chapter between 1 and 10
                                and v.id != $oldid and countdown IS NULL
                                order by rand()
                         LIMIT 0,$new_kanji_ratio");

            while($rs = $r->fetch_array())
            {
                debugprint("\n<!-- new -->");
                if($rs['countdown']>0)
                    throw new Exception("lovely! countdown was ZERO?");
                $ids[] = array('id' =>$rs['id'], 'chapter' => $rs['chapter']);
            }
        }
        echo "<!-- ids array="; print_r($ids); echo "-->";
        if(count($ids)==0) return false;
        $ctr = count($ids);
        $r_id = $ids[rand(0,$ctr-1)];
    
        $word = new clsWord();
        if(is_array($r_id))
            $r_id = $r_id['id'];
        $word->load($r_id);
        return $word;
    }

    #TODO: the words are comming twice
    static function getRandomWords($word,$bEnglishOptions=1)
    {
        //debugprint(__FUNCTION__ . ":english:$bEnglishOptions");
        #select 5 random meanings
        $ids = array();
        if($word->percentage>=50)
        {
            debugprint(__FUNCTION__ . ":" . __LINE__);
            $r = doqueryi("select kanji_id from stats order by rand() limit 0,5");
            while($rs = $r->fetch_array())
            {
                //debugprint("fetch:" . $rs[0]);
                $ids[] = $rs[0];
            }
        }
        else
        {
            for($i=0; $i<25; $i++)
            {
                $ids[]=rand(1,840);
            }
        }
        $idstring = join(',',$ids);

        $r2 = doqueryi("select means,word,word2 from vocab where chapter between 1 and 10 order by rand() limit 0,5");
        $means = array();

        $kanji  = "";
        if(!empty($word->kanji))
            $kanji = " ({$word->kanji})";
            
        //fill the answer with correct answer
        if($bEnglishOptions)
            $means[] = $word->means;
        else
            $means[] = "{$word->word}{$kanji}";

        while($rs2 = $r2->fetch_array())
        {
            //fill with other wrong answer
            $kanji  = "";
            if(!empty($rs2['word2']))
                $kanji = " ({$rs2['word2']})";

	        if($bEnglishOptions)
                $addthis = $rs2['means'];
            else
                $addthis = $rs2['word'] . "$kanji";

            if($addthis == $means[0])
            {
                //debugprint(__FUNCTION__ . "; not adding this element {$means[0]}");
                //array_pop($means);
            }
            else
                $means[] = $addthis;

        }
        echo "<!--"; print_r($means); echo "--?>";
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
            $this->kanji = $rs['word2'];

            $this->chapter = $rs['chapter'];

            $this->book = $rs['book'];

            $this->dated = $rs['dated'];
            $this->shown = 0 + $rs['shown']; #how many times shown
            $this->correct = 0 + $rs['correct']; #how many times correctly answered
            $this->wrong = 0 + $rs['wrong']; #wrongly answered
            $this->difference = 0 + $rs['difference']; #the current jump difference
            $this->countdown = 0 + $rs['countdown']; #the countdown, counted after every attempt of any word
            $this->firstdate = $rs['firstdate'] == '0000-00-00 00:00:00' ? 0 : strtotime($rs['firstdate']); 
            $this->percentage = 0; #calculated percentage of correct answers

            $a = $this->getGrade($this->shown,$this->correct);
            
            $this->percentage = $a[0];
            $this->grade = $a[1];
        }
        else
            die("not found clsword:$id");

    }

    static function getGrade($shown,$correct)
    {
        $percentage = 0;
        
        if($shown>0)
            $percentage = round(100*$correct/$shown);

        if($percentage>=90)
            $grade = 'A';
        else if($percentage>=75)
            $grade = 'B';
        else if($percentage>=40)
            $grade = 'C';
        else if($percentage<40)
            $grade = 'D';
        return array($percentage,$grade);
    }

    public function getID()
    {
        return $this->id;
    }

    static function getQueueStats($level=1)    
    {
        $target_count = 0;
        $total_queue = 0;
        $ctr=0;
        $last_total_queue = -1;
        for($i=0; $i<=$level; $i++)
        {

            $ctr = getcount("select count(*) from stats where unix_timestamp(nextdate)<=now() && countdown<=$total_queue && countdown>$last_total_queue");
            echo "\n<!-- $ctr @ $target_count -->\n";
            
            if($ctr==0)
                break;
            $last_total_queue = $total_queue;
            $total_queue += $ctr;
            $targecout_count = $ctr;
        }
        return $total_queue;
    }

    static function getStatistics()
    {
        global $quiz_mode;
        //how many words attempted today, how many new, how many new words left
        $sql = "SELECT count( * )
                FROM `stats`
                WHERE date( dated ) = date( now( ) ) ";
        $sql = "
                SELECT count(*) FROM `score` 
                    where date(dated)=date(now())";
        $rt['today'] = getcount($sql);
        
        

        $sql = "SELECT count( * )
                FROM `stats`
                WHERE date( firstdate ) = date( now( ) ) ";
        $rt['new'] = getcount($sql);

        #does not work even after the first word
        $sql = "SELECT date( now( ) ) - date( max( dated ) )
                FROM `stats`";
        $days_since_last_play = getcount($sql);


        if($quiz_mode==2)
            $rt['newleft'] = 10;
        else
            $rt['newleft'] = 10 - $rt['new'];

        #moe than 3 days old, no new kanjis will be loaded
        if(isset($_SESSION['veryold']) || $days_since_last_play > 3)
        {
            $_SESSION['veryold'] = 1;
            debugprint("no new mode");
            $rt['newleft'] = 0;
        }
        
        $rt['sensex'] = getcount("select v from sensex order by dated desc limit 0,1");

        if(isset($_SESSION['started']))
        {
            $ses_length = time() - $_SESSION['started'];
            $s_mins = intval($ses_length / 60);
            $s_secs = $ses_length % 60;
            $rt['session'] = "$s_mins:$s_secs";
        }

        return $rt;


    }

    static function search($word)
    {
        debugprint(__FUNCTION__ . "($word)");
        global $myi;
        $word2 = $word;//$myi->real_escape_string($word);
        $r = doqueryi("SELECT id FROM vocab v
                        where word like '$word2'");
        if($r->num_rows == 0)
            $r = doqueryi("SELECT id FROM vocab v
                    where word like '%$word2%'");
        $ids = array();
        $w = new clsWord();

        while($rs = $r->fetch_array())
        {
            $id = $rs['id'];
            debugprint("found word id:$id");
            $w->load($id);
            return $w;                      
        }
    }

    static function changechapter($ids,$chapter)
    {
        debugprint(__FUNCTION__ . "($ids,$chapter)");
        global $myi;
        doqueryi("update vocab set chapter=$chapter where id in ($ids)");
    }

    static function addnew($word,$means,$kanji = "")
    {
        debugprint(__FUNCTION__ . "($word,$means,$kanji)called");
        $word  = addslashes($word);
        $word2 = addslashes($word);
        $kanji = addslashes($kanji);
        
        doqueryi("insert into vocab(word,means,word2) values('$word','$means','$kanji')");
        global $myi;
        debugprint(__FUNCTION__ . " returning {$myi->insert_id}\n");
        return $myi->insert_id;
    }
}


?>
