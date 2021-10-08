<?php

namespace Tests\Unit\FormRequests;

use Illuminate\Support\Facades\DB;
use App\Http\Requests\DestroyMuteWordRequest;
//ファサードを使用するときはPHPUnitではなくTestsを使う
//use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\MuteWord;

class DestroyMuteWordRequestTest extends TestCase
{
    //teardownでテーブルをtruncateするから使わない。use RefreshDatabaseだとid列をリセットしてくれない
    //use RefreshDatabase;

    /**
     * @test
     *
     * @param テーブルカラム名
     * @param 値
     * @param 期待結果(true or false)
     * @param エラーメッセージkey(不合格データ時のみ)
     * @param エラーメッセージ(不合格データ時のみ)
     *
     * @dataProvider formRequestInputData
     */
    public function ミュートワード削除のフォームリクエスト(string $key,  $value, bool $expect, string $error_key, string $error_message): void
    {
        //ユーザーをfactoryで作成
        $user = User::factory(1)->create()->first();
        $this->actingAs($user);
        //↑のユーザーでミュートワード作成
        $mute_word = MuteWord::create([
            'user_id' => $user->id,
            'mute_word' => 'XXX',
        ]);
        //factoryで他の人のミュートワード作成
        $other_mute_words = MuteWord::factory(2)->create();

        $destroy_mute_word_request  = new DestroyMuteWordRequest();
        $rules = $destroy_mute_word_request->rules();
        $messages = $destroy_mute_word_request->messages();
        $input = [$key => $value];

        $validator = new Validator();
        $validator = Validator::make($input, $rules);
        $fails = $validator->fails();

        $this->assertSame($expect, $fails);
        if ($fails) {
            $this->assertSame($messages[$error_key], $error_message);
        }
    }

    /**
     * データプロバイダ
     *
     * @return データプロバイダ
     *
     */
    public function formRequestInputData(): array
    {
        return [
            '合格'   => ['id', 1, false, 'unused_key', 'unused_message'],
            '不合格：exists(idは存在するが違うユーザーの登録)' => ['id', 2, true, 'id.exists', "【ミュートワード】このワードをミュートしていません。"],
            '不合格：exists(idが存在しない)' => ['id', 1000, true, 'id.exists', "【ミュートワード】このワードをミュートしていません。"],
            '不合格：required(null)' => ['id', null, true, 'id.required', "【ミュートワード】送信値の変更を検知したためキャンセルしました。"],
            '不合格：required(空文字)' => ['id', '', true, 'id.required', "【ミュートワード】送信値の変更を検知したためキャンセルしました。"],
            '不合格：not_in(文字列「null」)' => ['id', null, true, 'id.not_in', "【ミュートワード】送信値の変更を検知したためキャンセルしました。"],
            '不合格：not_in(空文字)' => ['id', 'null', true, 'id.not_in', "【ミュートワード】送信値の変更を検知したためキャンセルしました。"],
            '不合格：numeric' => ['id', str_repeat('a', 3), true, 'id.numeric', "【ミュートワード】送信値の変更を検知したためキャンセルしました。"],
        ];
    }
    public function teardown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('mute_words')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
