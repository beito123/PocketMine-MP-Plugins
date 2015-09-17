#SimpleOtu
荒らしを牢屋に入れたり動けなくしたりすることができます。</br>
</br>
このプラグインはyf001氏のotuプラグインの仕様を元に作ったプラグインです。</br>
(一応yf001氏にも許可を得ています)</br>

##otuプラグインとの変更点</br>
このプラグインの元となるotuプラグインとの変更点です。</br>
###機能等の追加
- かなり簡易的なAPIの追加
- 牢屋の複数設定できるように
- コンソールにotu、runaなどの実行メッセージが表示される機能の追加
- otuする際に入れる牢屋を特定の牢屋に優先できるモード機能の追加 </br>
auto-level 実行者と同じワールドにある牢屋を優先します。 jail-(牢屋名) 指定された牢屋を優先します。
- otu牢屋に名前を付けるように
- otupで座標でも牢屋を追加できる機能の追加
- ほとんどのメッセージを変更できるように

###変更

####otuの仕様変更
- 権限があればotuをされていても解除できるように
- 制限に他プレーヤーへの攻撃の不可を追加

####runaの仕様変更
- otuされていなくてもrunaにできるように
- これに伴い、制限にブロックの破壊、設置、タップ不可、コマンド実行不可を追加
- 制限に他プレーヤーへの攻撃の不可を追加
- runaされた場合ブロックの破壊、設置、タップ不可、他プレーヤーへの攻撃、コマンド実行不可になるように

####その他
表記の変更 乙 -> otu, ルナ -> runa</br>
その他、全コードの書き直しにより、内部の処理が大幅に変更されています。</br>

###廃止
jailは別のプラグインでだす予定です。</br>

#ライセンス
私的な利用での改変を行うのはいいですが、再配布などは必ずGNU LGPLv3に従い行って下さい。

本プラグインは以下のGNU LGPLv3ライセンス下で配布されています。
GNU LGPLv3ライセンスに同意した上で使用して下さい。
GNU LGPLv3ライセンスの原文は同梱のLICENSEファイル又は以下のサイトをご覧ください。

	SimpleOtu is free software: you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.

原文(License text)
http://www.gnu.org/licenses/lgpl.txt
http://www.gnu.org/licenses/lgpl-3.0.ja.html

参考日本語訳
https://osdn.jp/magazine/07/09/05/017211


Thank yf001, akaituki8126.</br>