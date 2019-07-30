<?php
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

    $_SERVER['DOCUMENT_ROOT'] = str_replace('/exec/ajax','',dirname(realpath(__FILE__)));
    require_once($_SERVER['DOCUMENT_ROOT'].'/lib/globals.php');
    $sess = new Session();
    $sess->start();
    $sess->getmember();

    if(!empty($_REQUEST['f'])){
        $filename = str_replace(array('img/forall/', '../'), '', $_REQUEST['f']);
        $store_path = $_SERVER['DOCUMENT_ROOT'] . '/img/forall';
        $watermark = $_SERVER['DOCUMENT_ROOT'] . '/pub/watermark/watermark.png';
        $watermark_r = $_SERVER['DOCUMENT_ROOT'] . '/pub/watermark/' . md5($filename . time()) . '.png';
        $w_info = getimagesize($watermark);
        $w_width = $w_info[0];
        $w_heigth = $w_info[1];
        $w_ratio = $w_width / $w_heigth;

        if(file_exists($store_path . $filename)){
            $i_info = getimagesize($store_path . $filename);
            $i_width = $i_info[0];
            $i_heigth = $i_info[1];

            if($i_width > $i_heigth){
                $i_ration = $i_width / $i_heigth;

                $resize_h = intval($i_heigth * 0.2);
                $resize_w = $resize_h * $w_ratio;
            }else{
                $i_ration =  $i_heigth / $i_width;

                $resize_w = intval($i_width * 0.2);
                $resize_h = $resize_w * $w_ratio; 
            }

            // size of water mark 20%
            if($i_width > $i_heigth){
                $resize_h = intval($i_heigth * 0.2); // height
                $rw_ratio = $w_heigth / $resize_h; // resize coof
                $resize_w = $w_width / $rw_ratio; // size
            } else {
                $resize_w = intval($i_width * 0.2); // size
                $rw_ratio = $w_width / $resize_w; //  resize coof
                $resize_h = $w_heigth / $rw_ratio;  // height
            }

            $im_command  = SUNSITE_IM_PATH.'convert ';
            $im_command .= ' "'.$watermark.'" -resize '.$resize_w.'x'.$resize_h.'! -density 75 '.$watermark_r;
            passthru($im_command, $ret);

            if(file_exists($watermark_r)){
                $filedest = $store_path . $filename;
                $wpos = 'SouthEast -geometry +25+25';
                
                passthru(SUNSITE_IM_PATH."composite -compose atop -gravity ".$wpos." '".$watermark_r."' '".$filedest."' ".$filedest, $ret);
            
                unlink($watermark_r);

                exit(json_encode(array('success' => true)));
            }
        }
    }

    exit(json_encode(array('success' => false)));