<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monitor extends Model
{
    //use HasFactory;
    public function returnDestinationDisplayedPostIdList($body)
    {
        $regular_expression = "/>>[1-9][0-9]{0,3}( |　|\n|\r|\r\n|\t)+/";
        $regular_expression_for_sentence = "/^>>[1-9][0-9]{0,3}$/";
        $goal_id_list = array();

        if (preg_match($regular_expression, $body)) {
            //全角スペースを半角スペースに変換(preg_splitが全角スペース対応してない)
            $hankaku_body = mb_convert_kana($body, 's');
            //改行を半角スペースに
            $plane_body = str_replace(["\r\n", "\r", "\n"], ' ', $hankaku_body);
            //センテンスごとに区切り配列へ
            $sentences = preg_split("/[\s\n]/", $plane_body);

            foreach ($sentences as $sentence) {
                if (preg_match($regular_expression_for_sentence, $sentence)) {
                    array_push($goal_id_list, substr($sentence, 2));
                } else {
                }
            }
            //(悪意のある)重複した>>番号を削除
            $unique_list = array_unique($goal_id_list);
            //配列を詰める
            $aligned_list = array_values($unique_list);

            return $aligned_list;
        }
        //「>>1」のような1語の場合
        else if (preg_match($regular_expression_for_sentence, $body)) {
            array_push($goal_id_list, substr($body, 2));
            return $goal_id_list;
        } else {
            return null;
        }
    }

    public function returnNgWordsRegularExpression()
    {
        $url = "https://ja.wikipedia.org/w/api.php?format=json&action=query&pageids=37870&prop=revisions&rvprop=content";

        $json = file_get_contents($url);
        $decoded = json_decode($json, true);
        $need_scope = $decoded['query']['pages'][37870]['revisions'][0]['*'];

        $re = "/\[.*?\]/";
        preg_match_all($re, $need_scope, $matches);
        $strings = implode($matches[0]);
        $piped = str_replace('][[', '|', $strings);
        $trimmed = mb_substr($piped, 2, -166, "UTF-8");
        $regular_expression = '/' . $trimmed . '/';

        return $regular_expression;
    }

    public function convertNgWordsIfExist($body)
    {
        $re = $this->returnNgWordsRegularExpression();
        $replace = '■■■';

        if (preg_match($re, $body)) {
            return preg_replace($re, $replace, $body);
        } else {
            return $body;
        }
    }

    public function callGoogleTranslateAPI($body)
    {
        //bodyから区切り文字を削除
        $lined_body = str_replace(array("\r\n", "\r", "\n"), "", $body);
        //日本語をnkfに変換
        $encoded_body = quoted_printable_encode($lined_body);
        //なぜか「=」になるので「=」を「%」に変換
        $replaced_body = str_replace('=', '%', $encoded_body);
        //区切り文字を削除
        $replaced_body_2 = str_replace(array("\r\n", "\r", "\n"), "", $replaced_body);
        //二重の%を取り除く
        $replaced_body_3 = str_replace(array("%%%", "%%"), "%", $replaced_body_2);
        $url = 'https://script.google.com/macros/s/AKfycbz3RkxPcHcW75Nmlu_dIPOBXBssxUalLWhhs30cSNihDxfXIQmhE0p2IJb8EUhbEPDn/exec?text="' . $replaced_body_3 . '"&source=ja&target=en';

        //cURLセッションを初期化する
        $ch = curl_init();
        //URLとオプションを指定する
        curl_setopt($ch, CURLOPT_URL, $url); //取得するURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //curl_exec()の返り値を文字列で返す。通常はデータを直接出力。
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

        //URLの情報を取得し、ブラウザに渡す
        $response =  curl_exec($ch);
        $decoded = json_decode($response, true);
        $translated_body = substr($decoded['text'], 1, -1);

        //セッションを終了する
        curl_close($ch);

        return $translated_body;
    }

    public function callPurgoMalumAPI($translated_body)
    {
        $url = 'https://www.purgomalum.com/service/json?text=' . $translated_body;

        //cURLセッションを初期化する
        $ch = curl_init();
        //URLとオプションを指定する
        curl_setopt($ch, CURLOPT_URL, $url); //取得するURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //curl_exec()の返り値を文字列で返す。通常はデータを直接出力。

        //URLの情報を取得し、ブラウザに渡す
        $response =  curl_exec($ch);
        $decoded = json_decode($response, true);
        $result_text = $decoded['result'];
        return $result_text;
    }
}
