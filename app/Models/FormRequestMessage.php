<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormRequestMessage extends Model
{
    use HasFactory;
    //よく使う
    public function required($head)
    {
        return  '【' . $head . '】' . '入力必須です。';
    }
    public function not_in($head)
    {
        return  '【' . $head . '】' . '入力必須です。(not_in)';
    }
    public function onlyOwnerCanEdit($head)
    {
        return  '【' . $head . '】' . '書込者以外は変更できません。';
    }
    public function onlyOwnerCanDestroy($head)
    {
        return  '【' . $head . '】' . '書込者以外は削除できません。';
    }

    public function cancel($head)
    {
        return  '【' . $head . '】' . '送信値の変更を検知したためキャンセルしました。';
    }

    //画像系
    public function image($head)
    {
        return  '【' . $head . '】' . '画像ファイルを指定してください。';
    }
    public function imageMime($head)
    {
        return  '【' . $head . '】' . '画像ファイルは「jpeg」「jpg」「png」「gif」形式のみ可能です。';
    }
    public function imageMaxSize($head)
    {
        return  '【' . $head . '】' . '3.0MBを超える画像は添付できません。';
    }

    //文字系
    public function between($min, $max, $head)
    {
        return  '【' . $head . '】' . $min . '文字~' . $max . '文字で入力してください。';
    }
    public function forbidHtmlTag($head)
    {
        return  '【' . $head . '】' . 'HTMLタグを入力できません。';
    }

    //パスワード
    public function passwordRule($head)
    {
        return  '【' . $head . '】' . '半角英数字混合の8~24文字でお願いいたします。';
    }
    public function passwordConfirm($head)
    {
        return  '【' . $head . '】' . 'パスワードと一致しません。';
    }

    //メール
    public function email($head)
    {
        return  '【' . $head . '】' . 'メールアドレス形式で入力してください。';
    }
    public function emailAlreadyRegistered($head)
    {
        return  '【' . $head . '】' . '既に登録済みのメールアドレスです。';
    }
    public function emailNotRegistered($head)
    {
        return  '【' . $head . '】' . '未登録のメールアドレスです。';
    }
    //ミュートユーザー
    public function canNotMuteMe($head)
    {
        return  '【' . $head . '】' . '自分をミュートすることはできません。';
    }
    public function canNotMuteCancelMe($head)
    {
        return  '【' . $head . '】' . '自分をミュート解除することはできません。';
    }
    public function hasNotMutedThisUser($head)
    {
        return  '【' . $head . '】' . 'このユーザーをミュートしていません。';
    }
    //ミュートワード
    public function muteWordAlreadyRegistered($head)
    {
        return  '【' . $head . '】' . '既に登録済みです。';
    }
}
