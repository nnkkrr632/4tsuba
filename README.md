<p align="center"><a href="https://4tsuba.site" target="_blank"><img src="https://user-images.githubusercontent.com/91203083/138402737-41a2df1a-4719-4218-87b8-0ca53f91c09e.png" width="400"></a></p>

## よつば

#### URL: [https://4tsuba.site](https://4tsuba.site)

#### twitter ぽくに使える掲示板です。

twitter や他の掲示板を参考に、こんな機能の掲示板があればと思いつくりました。  
よろしければ、書込をしていただけると嬉しいです(ゲストユーザーをご使用いただけます)。

## 使用技術

-   #### フロントエンド

    -   **言語**
        -   HTML5
        -   CSS3
        -   JavaScript (Vue.js)
    -   **フレームワークと主要パッケージ**

        | フレームワーク / パッケージ | バージョン | 用途                              |
        | --------------------------- | ---------: | --------------------------------- |
        | Vue.js                      |     2.6.14 | -                                 |
        | vuetify                     |     2.5.10 | UI                                |
        | vue-router                  |      3.5.2 | ルーティング                      |
        | vue-image-lightbox          |      7.2.0 | 画像クリック時の拡大表示          |
        | jaconv                      |      1.0.4 | ひらがな ⇔ カタカナ ⇔ ｶﾀｶﾅ の変換 |

-   #### バックエンド

    -   **言語**
        -   PHP (8.0.11)
    -   **フレームワークと主要パッケージ**

        | フレームワーク / パッケージ | バージョン | 用途                                 |
        | --------------------------- | ---------: | ------------------------------------ |
        | Laravel                     |     8.62.0 | -                                    |
        | sanctum                     |     2.11.2 | SPA 認証                             |
        | PHPUnit                     |     9.5.10 | テスト                               |
        | telescope                   |      4.6.4 | デバッグ / SQL の確認 (開発環境のみ) |

## ER 図

![ER図](https://user-images.githubusercontent.com/91203083/137972958-d213afd9-bb60-4648-b978-e3fb46a4a9d3.jpg)

## モデルの CRUD 対応表

| モデル           | 作成 | 読取 | 更新 | 削除 |
| ---------------- | :--: | :--: | :--: | :--: |
| ユーザー         |  ○   |  ○   |  ○   |  ○   |
| (ゲストユーザー) |  ×   |  ○   |  △   |  ×   |
| スレッド         |  ○   |  ○   |  ×   |  ×   |
| 書込             |  ○   |  ○   |  ○   |  ○   |
| 画像             |  ○   |  ○   |  ○   |  ○   |
| 返信             |  ○   |  ○   |  ○   |  ○   |
| いいね           |  ○   |  ○   |  -   |  ○   |
| ワードミュート   |  ○   |  ○   |  -   |  ○   |
| ユーザーミュート |  ○   |  ○   |  -   |  ○   |

## 使用例

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

## 工夫したところ

1.  **Laravel が生成する SQL を確認した**

    1. **Eloquent**  
       使用するメソッドにより SQL が異なる。

        - leftJoin() の場合  
           Eloquent:`Image::leftJoin('posts', 'images.post_id', '=', 'posts.id')->get();` <br>↓<br>
          SQL: `select * from images left join posts on images.post_id = posts.id;`
        - with() の場合  
           Eloquent:`Image::with('post')->get();` <br>↓<br>
          SQL1: `select * from images;`  
           SQL2: `select * from posts where posts.id in (1, 2, 3);`

    1. **FormRequest**
        - likes テーブルのユニーク制約  
          StoreLikeRequest:  
          `Rule::unique('likes')->where(function ($query) { return $query->where('user_id', Auth::id());})`
          <br>↓<br>
          SQL: `select count(*) as aggregate from likes where post_id = 1 and (user_id = 1);`

1.  **頻度の高いリクエストで生成される SQL が軽くなるテーブル設計を考えた**

    1. **正規化されていないカラムを用意した**
        - images テーブルの thread_id カラム  
          スレッドのサムネイル画像や LightBox 用画像の取得時に使用される。  
          正規化し毎度 posts テーブルと結合(or サブクエリ)することは、書込件数が多くなるほど重くなると考え、カラムとして用意した。
    1. **カウント用カラムをつくった**
        - threads テーブルの posts_count カラム  
          スレッドの書込数を求めたいとき、2 種類の方法がある。  
          <span>　</span>(1)書込数カラムをつくり、書込送信(post)時にインクリメントする  
          <span>　</span>(2)書込数カラムをつくらず、書込取得(get)時にレコード数をカウントする  
          リクエストの頻度として、書込送信 < 書込取得 であると思い、  
          頻度の高い書込取得の SQL が軽くなるよう、(1)を採用した。

1.  **PHPUnit を使用した**

    1. **通常発生しないリクエストをテストし、条件分岐やバリデーションの不足に気がついた**

        - 既にログインした状態でログインリクエストを送信する  
           → ログイン状態と既ログインユーザーの id を確認する条件を加え、  
           <span>　</span>未ログイン時・同一ユーザー時のみログインできるよう変更した

        - 画像ファイルとして null を送信する  
           → フォームリクエストにバリデーションを追加した

    1. **カバレッジ結果**  
       ![ER図](https://user-images.githubusercontent.com/91203083/138064094-6c3ce972-c55a-4358-b92c-8bf8ad33b7d7.png)
       ![ER図](https://user-images.githubusercontent.com/91203083/138063913-e9140bc6-7d13-4d01-b244-fb9132aa5674.png)
