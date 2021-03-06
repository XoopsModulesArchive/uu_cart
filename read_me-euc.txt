U-U ショッピングカート Lite for XOOP2 β1(0.5) (2004/08/02)
@unadon_v http://u-u-club.ddo.jp/~XOOPS/
unadon unadon@jobs.co.jp

※このドキュメントはすぐに改訂されます。

PHP 4.2.2 以上 必須。
mbstring 必須。
画像処理に GD または ImageMagic

Redhat Linux 9.0
Apache version 1.3.31
PHP 4.3.3
MySQL version 3.23.58

上記の環境で動作確認。インストール－アンインストールまで。

・カテゴリー別
・検索セレクタ－商品ジャンプ
・サムネイル、ブロック、メイン、最大画像の４種×２枚（別の画像として追加
　可能）
・注目商品機能（ブロック表示）
・おすすめ商品機能（ブロック表示－無制限ランダム－表示期間設定）
・売れ筋商品機能
・商品に対するご意見機能
・友人にメールでおすすめ機能
・運賃自動計算機能（クロネコ、佐川、ペリカン便）－ゆうパックは秋の料金改
　定後対応予定
・送料全国一律機能（全商品－商品個別）
・一定額送料無料機能
・郵便番号検索（データーベースのクラスタサイズで 11.7Mb-あります。インス
　トールはおすすめしません－選択）
・SSL 対応
・他

・カテゴリー【その他】
削除しないで下さい。
後から作成したカテゴリーを消去する際、そのカテゴリーに含まれる商品を仮置
きするために必要です。インストール時に同時に作成されます。管理ページでは
編集不可になっていますが、手動でも削除しないで下さい。

・運賃
１００％の正確さを保証しません。実際に各運送会社の運賃表をご確認ください。
管理ページの一般設定で料金固定にすると全商品に適用されます。個別の設定は
商品管理より設定して下さい。

・在庫数
在庫数は、追加、変更の場合は問題ありませんが削除はユーザー操作のタイミン
グによって数量が戻らない場合があります。また、買い物かごに入れたまま退出
されても１日以上数量が戻りません。買い物かごのゴミは２４時間経過しなけれ
ばデーターベースより削除されません。（ガベージコレクタは１００／１の確率
で起動します）
管理ページで強制的にゴミ掃除することで元に戻りますが正確に実際の在庫数と
一致するとは限りません。数量は安全側（実際より少ない）に推移しますので、
注文があって在庫が無いという状況にはなりにくいようになっています。
また在庫管理を目的としたスクリプトではありませんの在庫の正確さを保証する
ものでもありません。適宜棚卸し等で在庫数を修正して下さい。その際には「管
理ページで強制的にゴミ掃除」を行った上で調整して下さい。

・売り上げ管理
売り上げは、商品を発送した時点で売り上げ管理に入ります。未入金でも売り上
げです。財務ソフトではないので詳細な売り上げ管理はできません。単に集計す
るだけです。（もう少し改良しますが）商品別、カテゴリ別、などの集計はLite
版に含む予定はありません。

・メールテンプレート
language/language/mail_template/ 以下に配置されています。初期状態で
☆★☆ＴＥＳＴメール★☆★
{SITENAME} より自動送信されています。
このメールに心当たりのない場合やご不明な点がある場合は、
{ADMINMAIL} までご連絡ください。
☆★☆ＴＥＳＴメール★☆★
が記述されています。EUCコードを扱えるエディタで必要に応じ編集して下さい。
また{}で囲まれた変数にはスクリプトから渡される値が代入されます。

・郵便番号辞書のインストール
インストールしなくてもご利用いただけます。使用するのもお客様情報入力時の
みです。また、データーベースサイズで 11.7 MB の容量を使用します。容量に
制限がある場合のご利用はおすすめできません。
インストールは、別途郵便番号辞書をダウンロードし、/include 以下に配置し
て下さい。
インストールにかかる時間は、ご利用のコンピューターの性能によりますが、
Xeon 1.8MHz x 2CPU Memory 1M で実行時間約６０秒。タイムアウトしないよう
、中間で経過時間を出力していますが、表示されるのは完了後です。
何度もインストールアンインストールを繰り返すと autoincrement が桁あふれ
してインストール出来なくなることがあります。アンインストールではテーブル
を drop,create して AUTO_INCREMENT=1 としていますがだめなときは何をして
もだめなもので３度くらいでインストール出来なくなります。（青森県で止まり
ます）

・お約束
本スクリプトおよび付属文書について、その品質、性能または特定目的に対する
適合性を含め一切保証はいたしません。いかなる場合においても、本スクリプト
および付属文書の使用または使用不能から生じるコンピュータの故障または損傷、
情報の消失、その他あらゆる直接的および間接的損害に関し、一切責任を負いま
せん。
