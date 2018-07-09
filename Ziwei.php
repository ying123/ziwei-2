<?php

require  'Ziweistar.php';
require  'Calendar.php';

class Ziwei{


    public $y;//年
    public $m;//月
    public $d;//日
    public $h;//时
    public $g;//性别
    public $l;//命宫
    public $b;//身宮
    public $f;//五行局
    public $s4;//四化星
    public $z;
    public $yS;
    public $mS;
    public $dS;
    public $LunarDay;
    public $ShengXiao;
    public $y1Pos;
    public $y2Pos;
    public $hPos;
    public $lPos;
    public $bPos;
    public $zPos;
    public $Palce;

    public $Place12;

//y:年,m:月,d:日,h:時,g:性別,l:命宮,b:身宮,f:五行局,s:起紫微表,s4:四化星;

    public function computeZiWei($y_Solar, $m_Solar, $d_Solar, $h_Solar, $g_Solar){

        //y:年,m:月,d:日,h:時,g:性別,l:命宮,b:身宮,f:五行局,s:起紫微表,s4:四化星;
        $this->yS = $y_Solar;
        $this->mS = $m_Solar;
        $this->dS = $d_Solar;
        //取得農曆時辰，排紫微命盤
        $calendar = new Calendar();
        $lunar    = $calendar->solar($this->yS, $this->mS, $this->dS,$h_Solar);

        $this->y = Ziweistar::HeavenlyStems[($this->yS - 4) % 10] . Ziweistar::EarthlyBranches[($this->yS - 4) % 12];
        $this->m = $lunar['lunar_month'];
        $this->d = $lunar['lunar_day'];
        $this->h =  $lunar['ganzhi_hour'];;
        $this->g = $g_Solar;
        //年:天干地支


        $this->y1Pos = array_search(mb_substr($this->y,0,1), Ziweistar::HeavenlyStems);
        $this->y2Pos = array_search(mb_substr($this->y,1,1), Ziweistar::EarthlyBranches);
        //時:地支
        $this->hPos = array_search(mb_substr($this->h,1,1), Ziweistar::EarthlyBranches);
        //設定紫微斗數
        $this->setZiwei($this->d);
        //stepSetStar
        $this->stepSetStar($this->y, $this->m, $this->d, $this->h);
        return $this->Place12;

    }


    public function setZiwei($d){

        //安十二宮，安命宮、身宮
        $this->l    = Ziweistar::EarthlyBranches[((12 - $this->hPos) + 1 + $this->m * 1.0) % 12];
        $this->b    = Ziweistar::EarthlyBranches[(12 - ((22 - $this->hPos) + 1 - $this->m * 1.0) % 12) % 12];
        $this->lPos = array_search($this->l, Ziweistar::EarthlyBranches);
        $this->bPos = array_search($this->b, Ziweistar::EarthlyBranches);
        //安五行局
        $this->f = Ziweistar::FiveElements[Ziweistar::FiveEleArr[$this->y1Pos % 5][(($this->lPos - ($this->lPos % 2 == 0 ? 0 : 1)) / 2) % 6]];
        //起紫微表
        $this->z    = Ziweistar::EarthlyBranches[Ziweistar::FiveEleTable[array_search($this->f, Ziweistar::FiveElements)][$d - 1]];
        $this->zPos = array_search($this->z, Ziweistar::EarthlyBranches);

    }


    public function stepSetStar($y, $m, $d, $h){

        //準備星星
        //0:紫微,1:天機,2:太陽,3:武曲,4:天同,5:廉貞,6:天府,7:太陰,8:貪狼,9:巨門,10:天相,11:天梁,12:七殺,13:破軍
        $s14  = Ziweistar::Star_A14[$this->zPos];
        $sZ06 = $this->getStarArr(Ziweistar::Star_Z06, 7, $this->zPos);
        $sT08 = $this->getStarArr(Ziweistar::Star_T08, 8, $sZ06[6]);
        //0:文昌 1:文曲 (時) 2:左輔 3:右弼 (月) 4:天魁 5:天鉞 6:祿存(年干)
        $sG07 = $this->getStarArrByPosArr(Ziweistar::Star_G07, 7, [$this->hPos, $this->hPos, $this->m - 1, $this->m - 1, $this->y1Pos, $this->y1Pos, $this->y1Pos]);
        //四化星
        $sS04 = $this->getStarArr(Ziweistar::Star_S04, 4, $this->y1Pos);
        //六凶星
        $sB06 = [Ziweistar::Star_B06[0][$this->y1Pos], Ziweistar::Star_B06[1][$this->y1Pos], Ziweistar::Star_B06[2][$this->y2Pos % 4][$this->hPos], Ziweistar::Star_B06[3][$this->y2Pos % 4][$this->hPos], Ziweistar::Star_B06[4][$this->hPos], Ziweistar::Star_B06[5][$this->hPos]];
        //其他
        $OS05          = $this->getStarArr(Ziweistar::Star_OS5, 5, $this->y2Pos);
        $this->Place12 = [];
        //準備開始組星星
        for($i = 0; $i < 12; $i++){
            $StarA = $StarB = $StarC = $Star6 = $lenStar = [0, 0, 0, 0];
            //紫微星系 & 六凶星
            for($k = 0; $k < 6; $k++){
                if($sZ06[$k] == $i){
                    $StarA[$lenStar[0]] = Ziweistar::StarM_A14[$k] . $this->getS04Str(Ziweistar::StarM_A14[$k], $sS04);
                    $lenStar[0]         += 1;
                }
                if($sB06[$k] == $i){
                    $StarB[$lenStar[1]] = Ziweistar::StarM_B06[$k];
                    $lenStar[1]         += 1;
                }
            }
            //天府星系
            for($k = 0; $k < 8; $k++){
                if($sT08[$k] == $i){
                    $StarA[$lenStar[0]] = Ziweistar::StarM_A14[$k + 6] . $this->getS04Str(Ziweistar::StarM_A14[$k + 6], $sS04);
                    $lenStar[0]         += 1;
                }
            }
            //六吉星
            for($k = 0; $k < 7; $k++){
                if($sG07[$k] == $i){
                    $Star6[$lenStar[3]] = Ziweistar::StarM_A07[$k] . $this->getS04Str(Ziweistar::StarM_A07[$k], $sS04);
                    $lenStar[3]         += 1;
                }
            }
            //其他星矅StarO_S0.length
            for($k = 0; $k < 5; $k++){
                if($OS05[$k] == $i){
                    $StarC[$lenStar[2]] = Ziweistar::StarO_S05[$k];
                    $lenStar[2]         += 1;
                }
            }
            //塞入位置
            $this->Place12[$i] = [
                "MangA" => Ziweistar::HeavenlyStems[(($this->y1Pos % 5) * 2 + ($i < 2 ? $i + 2 : $i) % 10) % 10] . Ziweistar::EarthlyBranches[$i],
                "MangB" => Ziweistar::Palace[(12 - $this->lPos + $i) % 12],
                "MangC" => ($this->bPos == $i ?Ziweistar::Palace[12] : ""),
                "StarA" => $StarA, "StarB" => $StarB, "StarC" => $StarC, "Star6" => $Star6,
            ];
        }
    }


    public function getStarArr($STAR, $size, $pos){
        $starArray = [];
        for($i = 0; $i < $size; $i++){
            $starArray[$i] = $STAR[$i][$pos];
        }
        return $starArray;
    }

    public function getStarArrByPosArr($STAR, $size, $PosArr){
        $starArray = [];
        for($i = 0; $i < $size; $i++){
            $starArray[$i] = $STAR[$i][$PosArr[$i]];
        }
        return $starArray;
    }


    public function getS04Str($starName, $STAR){
        return (array_search($starName, $STAR) >= 0) ? Ziweistar::StarM_S04[array_search($starName, $STAR)] : "";
    }

}
