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

    // public function returnNgWordsRegularExpressionFromWiki()
    // {
    //     $url = "https://ja.wikipedia.org/w/api.php?format=json&action=query&pageids=37870&prop=revisions&rvprop=content";

    //     $json = file_get_contents($url);
    //     $decoded = json_decode($json, true);
    //     $need_scope = $decoded['query']['pages'][37870]['revisions'][0]['*'];

    //     $re = "/\[.*?\]/";
    //     preg_match_all($re, $need_scope, $matches);
    //     $strings = implode($matches[0]);
    //     $piped = str_replace('][[', '|', $strings);
    //     $trimmed = mb_substr($piped, 2, -166, "UTF-8");
    //     $regular_expression = '/' . $trimmed . '/';

    //     return $regular_expression;
    // }
    public function returnNgWordsRegularExpression()
    {
        $ng_word_list = '[せセｾ][くクｸ][しシｼ][ーーｰ]|[うウｳちチﾁまマﾏ][んンﾝ](ち|こ|ぽ|ま|チ|コ|ポ|マ|ﾁ|ｺ|ﾎﾟ|ﾏ|毛)[んンﾝ]?|[えエｴ工][っッｯ]*[ちチﾁろロﾛ口]|[おオｵちチﾁ][っッｯ](ぱ|パ|ﾊﾟ)[いイｲ]|[やヤﾔ][りリﾘ][ちチﾁまマﾏ][んンﾝ]|[巨貧美][根乳]|(し|シ|ｼ|タヒ|ﾀﾋ|死|氏|市)[ねネﾈ]|(殺|ころ|コロ|ｺﾛ)[すスｽ]|性(欲|行為|暴力|交|風俗|的|器)|[いイｲ][んンﾝ](ぽ|ポ|ﾎﾟ)|挿入|[すスｽ][かカｶ][とトﾄ][ろロﾛ]|' .
            '[くクｸ][りリﾘ][とトﾄ][りリﾘ][すスｽ]|AV[男女]優|[早遅]漏|(正常|後背|座|側|立)位|[あアｱおオｵ][なナﾅ][にニﾆ][ーー-]|[せセｾ][っッｯ][くクｸ][すスｽ]|手([まマﾏ][んンﾝ]|[こコｺ][きキｷ]|淫)|射精|中[だダﾀﾞ出][しシｼ]|([ろロﾛ][りリﾘ]|[しシｼ][ょョｮ][たタﾀ]|[まマﾏ](ざ|ザ|ｻﾞ)|[しシｼ][すスｽ]|(ぶ|ブ|ﾌﾞ)[らラﾗ])[こコｺ][んンﾝ]|' .
            '(ぷ|プ|ﾌﾟ)[れレﾚ][いイｲ]|[れレﾚ][いイｲ](ぷ|プ|ﾌﾟ)|[強|輪|青|獣|屍]姦|露出|膣|尿|[はハﾊ][めメﾒ][どドﾄﾞ撮][りリﾘ]|(ぱ|パ|ﾊﾟ)[いイｲ](ず|ズ|ｽﾞ)[りリﾘ]|[ふフﾌ][ぇェｪ][らラﾗ]|[くクｸ][んンﾝ][にニﾆ]|潮吹[きキｷ]|風俗|売春|((ぱ|パ|ﾊﾟ)(ぱ|パ|ﾊﾟ)|[まマﾏ][まマﾏ])活|娼婦|[ほホﾎ]別|[くクｸ][っッｯ]*[そソｿ]|糞|処女|童貞|変態|餓鬼|殺害|' .
            '野獣先輩|朝鮮人|支那|(が|ガ|ｶﾞ)[いイｲ](じ|ジ|ｼﾞ)|[きキｷ][ちチﾁ](が|ガ|ｶﾞ)[いイｲ]|[せセｾ][くクｸ][はハﾊ][らラﾗ]|[あアｱ][るルﾙ]中|[あアｱ][なナﾅ][るルﾙ]|(ご|ゴ|ｺﾞ)[みミﾐ]|淫夢|fuck|suck|shit|cunt|nigg(er|a)|negro|pussy|penis|dick|cock|vagina|(ぱ|パ|ﾊﾟ)[んンﾝ][つツﾂ]|[こコｺ][んンﾝ](ど|ド|ﾄﾞ)[ー-][むムﾑ]|避妊|勃起|' .
            '(ぶ|ブ|ﾌﾞ)[すスｽ]|(ぶ|ブ|ﾌﾞ)[っッｯ]*[さサｻ]|(ぶ|ブ|ﾌﾞ)[さサｻ][いイｲ][くクｸ]|自殺|原発|卑猥|成海[ 　]*瑠奈|核|天安門|sexy?|戦争|[てテﾃ][ろロﾛ]|[いイｲ][すスス][らラﾗ][むムﾑ]国|[しシｼ][っッｯ][こコｺ]|穢多|非人|[はハﾊ](げ|ゲ|ｹﾞ)|禿|部落|金玉|(が|ガ|ｶﾞ)[きキｷ]|(で|デ|ﾃﾞ)(ぶ|ブ|ﾌﾞ)|雑魚|(ざ|ザ|ｻﾞ)[っッｯ]*[こコｺ]|[まマﾏ](じ|ジ|ｼﾞ)[きキｷ][ちチﾁ]|(ば|バ|ﾊﾞ)[かカｶ]|' .
            '馬鹿|[あアｱ][ほホﾎ]|阿呆|[きキｷ][もモﾓ][いイｲ]|[きキｷ][っッｯ]*[しシｼ][ょョｮ]|[きキｷ][もモﾓ][ちチﾁ][わワﾜ][るルﾙ][いイｲ]|気持[ちチﾁ]悪[いイｲ]|北朝鮮|(ぱ|パ|ﾊﾟ)[いイｲ](ぱ|パ|ﾊﾟ)[んンﾝ]|愛液|[貝兜]合[わワﾜ][せセｾ]|[まマﾏ][んンﾝ][ぐグｸﾞ][りリﾘ]|(ぶ|ブﾌﾞ)[るルﾙ][せセｾ][らラﾗ]|(で|デ|ﾃﾞ)[りリﾘ][へヘﾍ][るルﾙ]|(で|デ|ﾃﾞ)[りリﾘ](ば|バ|ﾊﾞ)[りリﾘ][ー-][へヘﾍ][るルﾙ][すスｽ]|' .
            '[らラﾗ](ぶ|ブ|ﾌﾞ)[ほホﾎ]([てテﾃ][るルﾙ])?|[そソｿ][ー-](ぷ|プ|ﾌﾟ)|[おオｵ][なナﾅ][ほホﾎ]|(だ|ダ|ﾀﾞ)[っッｯ][ちチﾁ][わワﾜ][いイｲ][ふフﾌ]|(ば|バ|ﾊﾞ)[いイｲ](ぶ|ブ|ﾌﾞ)|(で|デ|ﾃﾞ)[ぃィｨ][るルﾙ](ど|ド|ﾄﾞ)|電[まマﾏ]|[あアｱ][だダﾀﾞ][るルﾙ][とトﾄ](び|ビ|ﾋﾞ)(で|デ|ﾃﾞ)[おオｵ]|(げ|ゲ|ｹﾞ)[いイｲ]|[れレﾚ](ず|ズ|ｽﾞ)|(ぶ|ブ|ﾌﾞ)[らラﾗ](じ|ジ|ｼﾞ)[ゃャｬ]|幼女';

        $regular_expression = '/' . $ng_word_list . '/iu';
        return $regular_expression;
    }
    public function convertNgWordsIfExist($body)
    {
        $re = $this->returnNgWordsRegularExpression();
        $replace = '🍀🍀🍀';

        return preg_replace($re, $replace, $body);
    }

    // public function callGoogleTranslateAPI($body)
    // {
    //     //bodyから区切り文字を削除
    //     $lined_body = str_replace(array("\r\n", "\r", "\n"), "", $body);
    //     //日本語をnkfに変換
    //     $encoded_body = quoted_printable_encode($lined_body);
    //     //なぜか「=」になるので「=」を「%」に変換
    //     $replaced_body = str_replace('=', '%', $encoded_body);
    //     //区切り文字を削除
    //     $replaced_body_2 = str_replace(array("\r\n", "\r", "\n"), "", $replaced_body);
    //     //二重の%を取り除く
    //     $replaced_body_3 = str_replace(array("%%%", "%%"), "%", $replaced_body_2);
    //     $url = 'https://script.google.com/macros/s/AKfycbz3RkxPcHcW75Nmlu_dIPOBXBssxUalLWhhs30cSNihDxfXIQmhE0p2IJb8EUhbEPDn/exec?text="' . $replaced_body_3 . '"&source=ja&target=en';

    //     //cURLセッションを初期化する
    //     $ch = curl_init();
    //     //URLとオプションを指定する
    //     curl_setopt($ch, CURLOPT_URL, $url); //取得するURL
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //curl_exec()の返り値を文字列で返す。通常はデータを直接出力。
    //     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    //     curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    //     curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

    //     //URLの情報を取得し、ブラウザに渡す
    //     $response =  curl_exec($ch);
    //     $decoded = json_decode($response, true);
    //     $translated_body = substr($decoded['text'], 1, -1);

    //     //セッションを終了する
    //     curl_close($ch);

    //     return $translated_body;
    // }

    // public function callPurgoMalumAPI($translated_body)
    // {
    //     $url = 'https://www.purgomalum.com/service/json?text=' . $translated_body;

    //     //cURLセッションを初期化する
    //     $ch = curl_init();
    //     //URLとオプションを指定する
    //     curl_setopt($ch, CURLOPT_URL, $url); //取得するURL
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //curl_exec()の返り値を文字列で返す。通常はデータを直接出力。

    //     //URLの情報を取得し、ブラウザに渡す
    //     $response =  curl_exec($ch);
    //     $decoded = json_decode($response, true);
    //     $result_text = $decoded['result'];
    //     return $result_text;
    // }
}
