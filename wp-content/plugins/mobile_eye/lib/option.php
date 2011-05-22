<?php
if (basename($_SERVER['SCRIPT_FILENAME']) == 'option.php') {
	die;
}

// オプションページ
function mobile_options_page() {

	// 初期化
	$path = MB_DIR_PATH.'/settings.php';
	$list = array(
				'MB_THEME'     => 'default',
				'MB_CUT_PACKE' => '1',
				'MB_PERMALINK' => '1',
				'MB_URLGATE'   => '1',
				'MB_NO_IMG'    => '1',
				'MB_NO_IMGLINK'=> '0',
				'MB_IMG_NAME'  => '0',
				'MB_USE_SMILY' => '1',
		);

	mb_substitute_character('none');
	ob_start('mobile_decode');

	// 書き込み処理
	if (isset($_POST['write'])) {
		$_POST['MB_THEME']     = (@$_POST['MB_THEME'])     ? $_POST['MB_THEME'] : 'default';
		$_POST['MB_CUT_PACKE'] = (@$_POST['MB_CUT_PACKE']) ? '1' : '0';
		$_POST['MB_PERMALINK'] = (@$_POST['MB_PERMALINK']) ? '1' : '0';
		$_POST['MB_URLGATE']   = (@$_POST['MB_URLGATE'])   ? '1' : '0';
		$_POST['MB_NO_IMG']    = (@$_POST['MB_NO_IMG'])    ? '1' : '0';
		$_POST['MB_NO_IMGLINK']= (@$_POST['MB_NO_IMGLINK'])? '1' : '0';
		$_POST['MB_IMG_NAME']  = (@$_POST['MB_IMG_NAME'])  ? '1' : '0';
		$_POST['MB_USE_SMILY'] = (@$_POST['MB_USE_SMILY']) ? '1' : '0';

		echo '<div id="message" class="updated fade"><p><strong>';
		if ($fp = @fopen($path, 'w')) {
			fwrite($fp, "<?php\r\n");
			foreach ($list as $key => $value) {
				fwrite($fp, "define('".$key."', '".$_POST[$key]."');\r\n");
			}
			fwrite($fp, '?>');
			echo '設定ファイルを更新しました。';
			fclose($fp);
		} else {
			echo '書き込み権限がありません。<br />settings.phpのパーミッションを606に変更してください。';
		}
		echo '</strong></p></div>';

	}

	// 設定読み込み
	$settings = file($path);

	foreach ($settings as $value) {
		if (preg_match("/^define\('(.*?)', '(.*?)'\);/", $value, $match)
		&& isset($list[$match[1]])) {
			$list[$match[1]] = $match[2];
		}
	}

?>
	<div class="wrap">
		<p>
		<strong>Mobile Eye+ Ver <?php echo MB_EYE_P_VER;?></strong><br />
		最新情報は<a href="http://hrlk.com/" target="_blank">ハンターリンク</a>の<a href="http://hrlk.com/script/mobile-eye-plus/" target="_blank">Mobile Eye+</a>のページへ<br />
		不具合や質問などは<a href="http://phpbb.xwd.jp/viewtopic.php?t=630" target="_blank">WordPress Japanフォーラム</a>にお願いします。
		</p>
	</div>
	<div class="wrap">
		<form method="POST" action="<?php echo htmlSpChar($_SERVER['REQUEST_URI'], get_settings('blog_charset')); ?>">
		<h2>Mobile Eye+ Ver <?php echo MB_EYE_P_VER;?> の設定</h2>
		<p class="submit"><input type="submit" name="write" value="変更を保存 &raquo;" /></p>
		<fieldset class="options">
		<table class="optiontable">
			<tr>
				<th>テーマ：</th>
				<td>
					<select name="MB_THEME">
<?php
	$dir = dir(MB_DIR_PATH.'/themes/');
	while (false !== ($name = $dir->read())) {
		if(preg_match("/^\.+$/", $name)) continue;
?>
		  			<option <?php if ($list['MB_THEME'] == $name) echo 'selected'; ?> value="<?php echo $name; ?>"><?php echo $name; ?></option>
<?php
	}
	$dir->close();
?>
					</select>
				</td>
				<td>
					<p>Themesディレクトリにあるテーマの選択<br />
					選択したテーマが正常に読み込めないときは<strong>default</strong>のテーマが読み込まれる。<br />
					(default:<strong>default</strong>)</p>
				</td>
			</tr>
			<tr>
				<th>省パケ機能：</th>
				<td>
					<select name="MB_CUT_PACKE">
		  			<option <?php if ($list['MB_CUT_PACKE']) echo 'selected'; ?> value="1">使用する</option>
					<option <?php if (!$list['MB_CUT_PACKE']) echo 'selected'; ?> value="0">使用しない</option>
					</select>
				</td>
				<td>
					<p>全角英数字カナを半角にしたり、改行コード,連続する空白などを消したりして<br />
					見栄えが変わらない程度に容量を削減し、パケット数を減らす。<br />
					デメリットとしてはページソースが読みにくくなる。<br />
					(default:<strong>使用する</strong>)</p>
				</td>
			</tr>
			<tr>
				<th>ブログのパーマリンク：</th>
				<td>
					<select name="MB_PERMALINK">
		  			<option <?php if ($list['MB_PERMALINK']) echo 'selected'; ?> value="1">使用する</option>
					<option <?php if (!$list['MB_PERMALINK']) echo 'selected'; ?> value="0">使用しない</option>
					</select>
				</td>
				<td>
					<p>ブログで設定したパーマリンクを[使用する]かどうか<br />
					[使用しない]の場合は example.com/?p=1、example.com/?page_id=2 をできるだけ使用<br />
					(default:<strong>使用しない</strong>)</p>
				</td>
			</tr>
			<tr>
				<th>外部リンクのゲートウェイ：</th>
				<td>
					<select name="MB_URLGATE">
		  			<option <?php if ($list['MB_URLGATE']) echo 'selected'; ?> value="1">使用する</option>
					<option <?php if (!$list['MB_URLGATE']) echo 'selected'; ?> value="0">使用しない</option>
					</select>
				</td>
				<td>
					<p>URLGATE機能を[使用する]かどうか<br />
					携帯で見えないサイトや画像/動画を見るために外部のサービスを経由することができる。<br />
					(default:<strong>使用する</strong>)</p>
				</td>
			</tr>
			<tr>
				<th>本文中の画像：</th>
				<td>
					<select name="MB_NO_IMG">
		  			<option <?php if($list['MB_NO_IMG']) echo 'selected'; ?> value="1">表示しない</option>
					<option <?php if(!$list['MB_NO_IMG']) echo 'selected'; ?> value="0">表示する</option>
					</select>
				</td>
				<td>
					<p>サムネイル(縮小)画像を使っていない場合、[表示しない]を推奨<br />
					&lt;img&gt;タグをリンクにし携帯ブラウザの容量オーバーを防ぐことができる。<br />
					(default:<strong>表示しない</strong>)</p>
				</td>
			</tr>
			<tr>
				<th>画像のリンク：</th>
				<td>
					<select name="MB_NO_IMGLINK">
					<option <?php if(!$list['MB_NO_IMGLINK']) echo 'selected'; ?> value="0">そのまま</option>
		  			<option <?php if($list['MB_NO_IMGLINK']) echo 'selected'; ?> value="1">削除</option>
					</select>
				</td>
				<td>
					<p>画像にリンクがある場合、そのリンクを削除するかどうか<br />
					例) &lt;a ... &gt;&lt;img src= ... &gt;&lt;/a&gt; とある場合<br />
					[そのまま]は変更を加えない、[削除]はaタグを消して出力する。<br />
					<span style="color:#A00;">※｢本文中の画像｣が[表示する]の場合のみ有効な設定</span><br />
					(default:<strong>そのまま</strong>)</p>
				</td>
			</tr>
			<tr>
				<th>画像へのリンク名：</th>
				<td>
					<select name="MB_IMG_NAME">
					<option <?php if(!$list['MB_IMG_NAME']) echo 'selected'; ?> value="0">ALT</option>
		  			<option <?php if($list['MB_IMG_NAME']) echo 'selected'; ?> value="1">ファイル名</option>
					</select>
				</td>
				<td>
					<p>例) &lt;img src=&quot;./img/test.png&quot; alt=&quot;test画像&quot;&gt; とある場合<br />
					[ファイル名]は <span style="color:#00F;">[画:test.png]</span> 、[ALT]は <span style="color:#00F;">[画:test画像]</span> とリンクを出力<br />
					&lt;a&gt;でリンクしている画像のサムネイルと思われる場合 <span style="color:#00F;">[小]</span> と出力<br />
					[ALT]を選択していても &lt;img&gt; に alt= がなければ[ファイル名]を出力<br />
					<span style="color:#A00;">※｢本文中の画像｣が[表示しない]の場合のみ有効な設定</span><br />
					(default:<strong>ALT</strong>)</p>
				</td>
			</tr>
			<tr>
				<th>スマイリー画像：</th>
				<td>
					<select name="MB_USE_SMILY">
		  			<option <?php if($list['MB_USE_SMILY']) echo 'selected'; ?> value="1">表示する</option>
					<option <?php if(!$list['MB_USE_SMILY']) echo 'selected'; ?> value="0">表示しない</option>
					</select>
				</td>
				<td>
					<p>[表示する]でスマイリー画像をリンクに変換しない。<br />
					<span style="color:#A00;">※｢本文中の画像｣が[表示しない]の場合のみ有効な設定</span><br />
					(default:<strong>表示する</strong>)</p>
				</td>
			</tr>
		</table>
		</fieldset>
		<p class="submit"><input type="submit" name="write" value="変更を保存 &raquo;" /></p>
		</form>
	</div>
<?php
	ob_end_flush();
}

?>
