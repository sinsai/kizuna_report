Kizuna leport project
=====================

WordPress( http://wordpress.com/ )をカスタマイズして絆レポート用の機能を追加します。
+ WordPressに記事を投稿すると、その内容をsinsai.infoにリポストする（とか
+ sinsai.infoにtrackbackを打つと、sinsai.infoからその記事を取りにくる（など


アイデアメモ
-----------
+ 既存のWordPressプラグインを活用する
    + 位置情報プラグイン
    + ソーシャルプラグイン
+ 足らない機能はWordPressプラグインとして追加する
+ ロゴ作りたい
+ テーマ決めたい


やったこと
---------
+ 環境準備した
+ テーマ設定してみた
+ プラグイン候補を入れてみた
    + http://hrlk.com/script/mobile-eye-plus/
    + http://sourceforge.jp/projects/wppluginsj/
        + Lightweight Google Maps
        + Ktai Location

環境まわり
---------
+ リポジトリ(github)
    + sinsai/kizuna_report
+ Webサーバ
    + http://kizuna.sinsai.info
    + http://kizuna.sinsai.info/wp-admin/
        + 管理用アカウント（ないしょ）
    + home: /home/hal_sk/htdocs/kizuna
        + kizuna.sinsai.info にログイン出来る人は、WPの設定ファイルのぞいて各種アカウント情報を知ろう。

https://cacoo.com/diagrams/WpGTrZSQzCuZuQZw


ローカルで開発するときどうすんだろ...
----------------------------------
+ ローカルにDB置いてやる/kizuna.sinsai.infoのDBをみんなでちくちくする？
+ wp-coinfig.php は .gitignore に登録しといたほうがいいんだろうか...。
+ とりあえず、ローカルのApacheにphpの設定しておこう。

