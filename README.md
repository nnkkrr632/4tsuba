<p align="center"><a href="https://4tsuba.site" target="_blank"><img src="./images_for_README/4tsuba_er_diagram.jpg" width="400"></a></p>

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
        | telescope                   |      4.6.4 | デバッグ / sql の確認 (開発環境のみ) |

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

## 使用例と GIF

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

-   **[Vehikl](https://vehikl.com/)**
-   **[Tighten Co.](https://tighten.co)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Cubet Techno Labs](https://cubettech.com)**
-   **[Cyber-Duck](https://cyber-duck.co.uk)**
-   **[Many](https://www.many.co.uk)**
-   **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
-   **[DevSquad](https://devsquad.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
-   **[OP.GG](https://op.gg)**

## 工夫したところ

1.  **SQL の確認**
    1. Eloquent からどのような SQL ができるかを確認した。
        1. Eloquent:`leftjoin` <br>↓<br>SQL: `aaaaaaaaa`
        1. CSS3
        1. JavaScript (Vue.js)

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
