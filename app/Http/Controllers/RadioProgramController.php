<?php

namespace App\Http\Controllers;

use App\RadioProgram;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;

class RadioProgramController extends Controller
{
    //radikoの番組表から今放送されている番組を取得します
    public function fetchProgramGuide()
    {

        $entries = [];
        for ($i = 1; $i < 48; ++$i) {

            $url = 'http://radiko.jp/v3/program/now/JP' . $i . '.xml';
            $dom = new DOMDocument();
            @$dom->load($url);
            $xpath = new DOMXPath($dom);

            foreach ($xpath->query('//radiko/stations/station') as $node) {

                $entries[] = array(

                    'id' => $xpath->evaluate('string(./@id)', $node),
                    'station' => $xpath->evaluate('string(name)', $node),
                    'title' => $xpath->evaluate('string(progs/prog/title)', $node),
                    'cast' => $xpath->evaluate('string(progs/prog/pfm)', $node),
                    'start' => substr_replace(($xpath->evaluate('string(//prog/@ftl)', $node)), ':', 2, 0),
                    'end' => substr_replace(($xpath->evaluate('string(//prog/@tol)', $node)), ':', 2, 0),
                    'url' => $xpath->evaluate('string(progs/prog/url)', $node),

                );
            }
        }
        //放送局の重複を削除します
        $arr_tmp = [];
        $results = [];
        foreach ($entries as $entry => $value) {

            if (!in_array($value['station'], $arr_tmp)) {

                $arr_tmp[] = $value['station'];
                $results[] = $value;
            }
        }
        //放送局名から放送局IDを生成します。
        // foreach ($results as $result => &$value) {

        //     $station_id = $this->convertStationtToId($value['station']);
        //     $value['id'] = $station_id;
        // }

        return view('layouts.radioProgramSchedule', compact('results'));
    }

    // private function convertStationtToId($station)
    // {

    //     switch ($station) {

    //         case "ＨＢＣラジオ":
    //             $station_id = "HBC";
    //             return $station_id;
    //         case "ＳＴＶラジオ":
    //             $station_id = "STV";
    //             return $station_id;
    //         case "AIR-G'（FM北海道）":
    //             $station_id = "AIR-G";
    //             return $station_id;
    //         case "FM NORTH WAVE":
    //             $station_id = "NORTHWAVE";
    //             return $station_id;
    //         case "ＲＡＢ青森放送":
    //             $station_id = "RAB";
    //             return $station_id;
    //         case "エフエム青森":
    //             $station_id = "AFB";
    //             return $station_id;
    //         case "IBCラジオ":
    //             $station_id = "IBC";
    //             return $station_id;
    //         case "エフエム岩手":
    //             $station_id = "FMI";
    //             return $station_id;
    //         case "TBCラジオ":
    //             $station_id = "TBC";
    //             return $station_id;
    //         case "Date fm エフエム仙台":
    //             $station_id = "DATEFM";
    //             return $station_id;
    //         case "ＡＢＳ秋田放送":
    //             $station_id = "ABS";
    //             return $station_id;
    //         case "エフエム秋田":
    //             $station_id = "AFM";
    //             return $station_id;
    //         case "YBC山形放送":
    //             $station_id = "YBC";
    //             return $station_id;
    //         case "Rhythm Station　エフエム山形":
    //             $station_id = "RFM";
    //             return $station_id;
    //         case "RFCラジオ福島":
    //             $station_id = "RFC";
    //             return $station_id;
    //         case "ふくしまFM":
    //             $station_id = "FMF";
    //             return $station_id;
    //         case "NHKラジオ第1（札幌）":
    //             $station_id = "JOIK";
    //             return $station_id;
    //         case "NHKラジオ第1（仙台）":
    //             $station_id = "JOHK";
    //             return $station_id;
    //         case "TBSラジオ":
    //             $station_id = "TBS";
    //             return $station_id;
    //         case "文化放送":
    //             $station_id = "QRR";
    //             return $station_id;
    //         case "ニッポン放送":
    //             $station_id = "LFR";
    //             return $station_id;
    //         case "InterFM897":
    //             $station_id = "INT";
    //             return $station_id;
    //         case "TOKYO FM":
    //             $station_id = "FMT";
    //             return $station_id;
    //         case "J-WAVE":
    //             $station_id = "FMJ";
    //             return $station_id;
    //         case "ラジオ日本":
    //             $station_id = "JORF";
    //             return $station_id;
    //         case "bayfm78":
    //             $station_id = "BAYFM78";
    //             return $station_id;
    //         case "NACK5":
    //             $station_id = "NACK5";
    //             return $station_id;
    //         case "ＦＭヨコハマ":
    //             $station_id = "YFM";
    //             return $station_id;
    //         case "IBS茨城放送":
    //             $station_id = "IBS";
    //             return $station_id;
    //         case "CRT栃木放送":
    //             $station_id = "CRT";
    //             return $station_id;
    //         case "RadioBerry":
    //             $station_id = "RADIOBERRY";
    //             return $station_id;
    //         case "FM GUNMA":
    //             $station_id = "FMGUNMA";
    //             return $station_id;
    //         case "NHKラジオ第1（東京）":
    //             $station_id = "JOAK";
    //             return $station_id;
    //         case "ＢＳＮラジオ":
    //             $station_id = "BSN";
    //             return $station_id;
    //         case "FM NIIGATA":
    //             $station_id = "FMNIIGATA";
    //             return $station_id;
    //         case "FM PORT":
    //             $station_id = "FMPORT";
    //             return $station_id;
    //         case "ＫＮＢラジオ":
    //             $station_id = "KNB";
    //             return $station_id;
    //         case "ＦＭとやま":
    //             $station_id = "FMTOYAMA";
    //             return $station_id;
    //         case "MROラジオ":
    //             $station_id = "MRO";
    //             return $station_id;
    //         case "エフエム石川":
    //             $station_id = "HELLOFIVE";
    //             return $station_id;
    //         case "FBCラジオ":
    //             $station_id = "FBC";
    //             return $station_id;
    //         case "FM福井":
    //             $station_id = "FMFUKUI";
    //             return $station_id;
    //         case "ＹＢＳラジオ":
    //             $station_id = "YBS";
    //             return $station_id;
    //         case "FM FUJI":
    //             $station_id = "FM FUJI";
    //             return $station_id;
    //         case "SBCラジオ":
    //             $station_id = "SBC";
    //             return $station_id;
    //         case "ＦＭ長野":
    //             $station_id = "FMN";
    //             return $station_id;
    //         case "CBCラジオ":
    //             $station_id = "CBC";
    //             return $station_id;
    //         case "東海ラジオ":
    //             $station_id = "TOKAIRADIO";
    //             return $station_id;
    //         case "ぎふチャン":
    //             $station_id = "GBS";
    //             return $station_id;
    //         case "ZIP-FM":
    //             $station_id = "ZIP-FM";
    //             return $station_id;
    //         case "RADIO NEO":
    //             $station_id = "RADIONEO";
    //             return $station_id;
    //         case "FM AICHI":
    //             $station_id = "FMAICHI";
    //             return $station_id;
    //         case "ＦＭ ＧＩＦＵ":
    //             $station_id = "FMGIFU";
    //             return $station_id;
    //         case "SBSラジオ":
    //             $station_id = "SBS";
    //             return $station_id;
    //         case "K-MIX SHIZUOKA":
    //             $station_id = "K-MIX";
    //             return $station_id;
    //         case "レディオキューブ ＦＭ三重":
    //             $station_id = "FMMIE";
    //             return $station_id;
    //         case "NHKラジオ第1（名古屋）":
    //             $station_id = "JOCK";
    //             return $station_id;
    //         case "ABCラジオ":
    //             $station_id = "ABC";
    //             return $station_id;
    //         case "MBSラジオ":
    //             $station_id = "MBS";
    //             return $station_id;
    //         case "OBCラジオ大阪":
    //             $station_id = "OBC";
    //             return $station_id;
    //         case "FM COCOLO":
    //             $station_id = "CCL";
    //             return $station_id;
    //         case "FM802":
    //             $station_id = "802";
    //             return $station_id;
    //         case "FM大阪":
    //             $station_id = "FMO";
    //             return $station_id;
    //         case "Kiss FM KOBE":
    //             $station_id = "KISSFMKOBE";
    //             return $station_id;
    //         case "ラジオ関西":
    //             $station_id = "CRK";
    //             return $station_id;
    //         case "e-radio FM滋賀":
    //             $station_id = "E-RADIO";
    //             return $station_id;
    //         case "KBS京都ラジオ":
    //             $station_id = "KBS";
    //             return $station_id;
    //         case "α-STATION FM京都":
    //             $station_id = "ALPHA-STATION";
    //             return $station_id;
    //         case "wbs和歌山放送":
    //             $station_id = "WBS";
    //             return $station_id;
    //         case "NHKラジオ第1（大阪）":
    //             $station_id = "JOBK";
    //             return $station_id;
    //         case "BSSラジオ":
    //             $station_id = "BSS";
    //             return $station_id;
    //         case "エフエム山陰":
    //             $station_id = "FM-SANIN";
    //             return $station_id;
    //         case "ＲＳＫラジオ":
    //             $station_id = "RSK";
    //             return $station_id;
    //         case "ＦＭ岡山":
    //             $station_id = "FM-OKAYAMA";
    //             return $station_id;
    //         case "RCCラジオ":
    //             $station_id = "RCC";
    //             return $station_id;
    //         case "広島FM":
    //             $station_id = "HFM";
    //             return $station_id;
    //         case "ＫＲＹ山口放送":
    //             $station_id = "KRY";
    //             return $station_id;
    //         case "エフエム山口":
    //             $station_id = "FMY";
    //             return $station_id;
    //         case "ＪＲＴ四国放送":
    //             $station_id = "JRT";
    //             return $station_id;
    //         case "RNC西日本放送":
    //             $station_id = "RNC";
    //             return $station_id;
    //         case "エフエム香川":
    //             $station_id = "FMKAGAWA";
    //             return $station_id;
    //         case "RNB南海放送":
    //             $station_id = "RNB";
    //             return $station_id;
    //         case "FM愛媛":
    //             $station_id = "JOEU-FM";
    //             return $station_id;
    //         case "RKC高知放送":
    //             $station_id = "RKC";
    //             return $station_id;
    //         case "エフエム高知":
    //             $station_id = "HI-SIX";
    //             return $station_id;
    //         case "NHKラジオ第1（広島）":
    //             $station_id = "JOFK";
    //             return $station_id;
    //         case "NHKラジオ第1（松山）":
    //             $station_id = "JOZK";
    //             return $station_id;
    //         case "RKBラジオ":
    //             $station_id = "RKB";
    //             return $station_id;
    //         case "KBCラジオ":
    //             $station_id = "KBC";
    //             return $station_id;
    //         case "LOVE FM":
    //             $station_id = "LOVEFM";
    //             return $station_id;
    //         case "CROSS FM":
    //             $station_id = "CROSSFM";
    //             return $station_id;
    //         case "FM FUKUOKA":
    //             $station_id = "FMFUKUOKA";
    //             return $station_id;
    //         case "NBC長崎放送":
    //             $station_id = "NBC";
    //             return $station_id;
    //         case "FM長崎":
    //             $station_id = "FMNAGASAKI";
    //             return $station_id;
    //         case "RKKラジオ":
    //             $station_id = "RKK";
    //             return $station_id;
    //         case "FMKエフエム熊本":
    //             $station_id = "FMK";
    //             return $station_id;
    //         case "OBSラジオ":
    //             $station_id = "OBS";
    //             return $station_id;
    //         case "エフエム大分":
    //             $station_id = "FM_OITA";
    //             return $station_id;
    //         case "宮崎放送":
    //             $station_id = "MRT";
    //             return $station_id;
    //         case "エフエム宮崎":
    //             $station_id = "JOYFM";
    //             return $station_id;
    //         case "ＭＢＣラジオ":
    //             $station_id = "MBC";
    //             return $station_id;
    //         case "μＦＭ":
    //             $station_id = "MYUFM";
    //             return $station_id;
    //         case "RBCiラジオ":
    //             $station_id = "RBC";
    //             return $station_id;
    //         case "ラジオ沖縄":
    //             $station_id = "ROK";
    //             return $station_id;
    //         case "FM沖縄":
    //             $station_id = "FM_OKINAWA";
    //             return $station_id;
    //         case "NHKラジオ第1（福岡）":
    //             $station_id = "JOLK";
    //             return $station_id;
    //         case "ラジオNIKKEI第1":
    //             $station_id = "RN1";
    //             return $station_id;
    //         case "ラジオNIKKEI第2":
    //             $station_id = "RN2";
    //             return $station_id;
    //         case "放送大学":
    //             $station_id = "HOUSOU-DAIGAKU";
    //             return $station_id;
    //         case "NHKラジオ第2":
    //             $station_id = "JOAB";
    //             return $station_id;
    //         case "NHK-FM（東京）":
    //             $station_id = "JOAK-FM";
    //             return $station_id;
    //     }
    // }
}
