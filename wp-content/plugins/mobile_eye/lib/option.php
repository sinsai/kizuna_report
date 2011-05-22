<?php
if (basename($_SERVER['SCRIPT_FILENAME']) == 'option.php') {
	die;
}

// �I�v�V�����y�[�W
function mobile_options_page() {

	// ������
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

	// �������ݏ���
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
			echo '�ݒ�t�@�C�����X�V���܂����B';
			fclose($fp);
		} else {
			echo '�������݌���������܂���B<br />settings.php�̃p�[�~�b�V������606�ɕύX���Ă��������B';
		}
		echo '</strong></p></div>';

	}

	// �ݒ�ǂݍ���
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
		�ŐV����<a href="http://hrlk.com/" target="_blank">�n���^�[�����N</a>��<a href="http://hrlk.com/script/mobile-eye-plus/" target="_blank">Mobile Eye+</a>�̃y�[�W��<br />
		�s��⎿��Ȃǂ�<a href="http://phpbb.xwd.jp/viewtopic.php?t=630" target="_blank">WordPress Japan�t�H�[����</a>�ɂ��肢���܂��B
		</p>
	</div>
	<div class="wrap">
		<form method="POST" action="<?php echo htmlSpChar($_SERVER['REQUEST_URI'], get_settings('blog_charset')); ?>">
		<h2>Mobile Eye+ Ver <?php echo MB_EYE_P_VER;?> �̐ݒ�</h2>
		<p class="submit"><input type="submit" name="write" value="�ύX��ۑ� &raquo;" /></p>
		<fieldset class="options">
		<table class="optiontable">
			<tr>
				<th>�e�[�}�F</th>
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
					<p>Themes�f�B���N�g���ɂ���e�[�}�̑I��<br />
					�I�������e�[�}������ɓǂݍ��߂Ȃ��Ƃ���<strong>default</strong>�̃e�[�}���ǂݍ��܂��B<br />
					(default:<strong>default</strong>)</p>
				</td>
			</tr>
			<tr>
				<th>�ȃp�P�@�\�F</th>
				<td>
					<select name="MB_CUT_PACKE">
		  			<option <?php if ($list['MB_CUT_PACKE']) echo 'selected'; ?> value="1">�g�p����</option>
					<option <?php if (!$list['MB_CUT_PACKE']) echo 'selected'; ?> value="0">�g�p���Ȃ�</option>
					</select>
				</td>
				<td>
					<p>�S�p�p�����J�i�𔼊p�ɂ�����A���s�R�[�h,�A������󔒂Ȃǂ��������肵��<br />
					���h�����ς��Ȃ����x�ɗe�ʂ��팸���A�p�P�b�g�������炷�B<br />
					�f�����b�g�Ƃ��Ă̓y�[�W�\�[�X���ǂ݂ɂ����Ȃ�B<br />
					(default:<strong>�g�p����</strong>)</p>
				</td>
			</tr>
			<tr>
				<th>�u���O�̃p�[�}�����N�F</th>
				<td>
					<select name="MB_PERMALINK">
		  			<option <?php if ($list['MB_PERMALINK']) echo 'selected'; ?> value="1">�g�p����</option>
					<option <?php if (!$list['MB_PERMALINK']) echo 'selected'; ?> value="0">�g�p���Ȃ�</option>
					</select>
				</td>
				<td>
					<p>�u���O�Őݒ肵���p�[�}�����N��[�g�p����]���ǂ���<br />
					[�g�p���Ȃ�]�̏ꍇ�� example.com/?p=1�Aexample.com/?page_id=2 ���ł��邾���g�p<br />
					(default:<strong>�g�p���Ȃ�</strong>)</p>
				</td>
			</tr>
			<tr>
				<th>�O�������N�̃Q�[�g�E�F�C�F</th>
				<td>
					<select name="MB_URLGATE">
		  			<option <?php if ($list['MB_URLGATE']) echo 'selected'; ?> value="1">�g�p����</option>
					<option <?php if (!$list['MB_URLGATE']) echo 'selected'; ?> value="0">�g�p���Ȃ�</option>
					</select>
				</td>
				<td>
					<p>URLGATE�@�\��[�g�p����]���ǂ���<br />
					�g�тŌ����Ȃ��T�C�g��摜/��������邽�߂ɊO���̃T�[�r�X���o�R���邱�Ƃ��ł���B<br />
					(default:<strong>�g�p����</strong>)</p>
				</td>
			</tr>
			<tr>
				<th>�{�����̉摜�F</th>
				<td>
					<select name="MB_NO_IMG">
		  			<option <?php if($list['MB_NO_IMG']) echo 'selected'; ?> value="1">�\�����Ȃ�</option>
					<option <?php if(!$list['MB_NO_IMG']) echo 'selected'; ?> value="0">�\������</option>
					</select>
				</td>
				<td>
					<p>�T���l�C��(�k��)�摜���g���Ă��Ȃ��ꍇ�A[�\�����Ȃ�]�𐄏�<br />
					&lt;img&gt;�^�O�������N�ɂ��g�уu���E�U�̗e�ʃI�[�o�[��h�����Ƃ��ł���B<br />
					(default:<strong>�\�����Ȃ�</strong>)</p>
				</td>
			</tr>
			<tr>
				<th>�摜�̃����N�F</th>
				<td>
					<select name="MB_NO_IMGLINK">
					<option <?php if(!$list['MB_NO_IMGLINK']) echo 'selected'; ?> value="0">���̂܂�</option>
		  			<option <?php if($list['MB_NO_IMGLINK']) echo 'selected'; ?> value="1">�폜</option>
					</select>
				</td>
				<td>
					<p>�摜�Ƀ����N������ꍇ�A���̃����N���폜���邩�ǂ���<br />
					��) &lt;a ... &gt;&lt;img src= ... &gt;&lt;/a&gt; �Ƃ���ꍇ<br />
					[���̂܂�]�͕ύX�������Ȃ��A[�폜]��a�^�O�������ďo�͂���B<br />
					<span style="color:#A00;">����{�����̉摜���[�\������]�̏ꍇ�̂ݗL���Ȑݒ�</span><br />
					(default:<strong>���̂܂�</strong>)</p>
				</td>
			</tr>
			<tr>
				<th>�摜�ւ̃����N���F</th>
				<td>
					<select name="MB_IMG_NAME">
					<option <?php if(!$list['MB_IMG_NAME']) echo 'selected'; ?> value="0">ALT</option>
		  			<option <?php if($list['MB_IMG_NAME']) echo 'selected'; ?> value="1">�t�@�C����</option>
					</select>
				</td>
				<td>
					<p>��) &lt;img src=&quot;./img/test.png&quot; alt=&quot;test�摜&quot;&gt; �Ƃ���ꍇ<br />
					[�t�@�C����]�� <span style="color:#00F;">[��:test.png]</span> �A[ALT]�� <span style="color:#00F;">[��:test�摜]</span> �ƃ����N���o��<br />
					&lt;a&gt;�Ń����N���Ă���摜�̃T���l�C���Ǝv����ꍇ <span style="color:#00F;">[��]</span> �Əo��<br />
					[ALT]��I�����Ă��Ă� &lt;img&gt; �� alt= ���Ȃ����[�t�@�C����]���o��<br />
					<span style="color:#A00;">����{�����̉摜���[�\�����Ȃ�]�̏ꍇ�̂ݗL���Ȑݒ�</span><br />
					(default:<strong>ALT</strong>)</p>
				</td>
			</tr>
			<tr>
				<th>�X�}�C���[�摜�F</th>
				<td>
					<select name="MB_USE_SMILY">
		  			<option <?php if($list['MB_USE_SMILY']) echo 'selected'; ?> value="1">�\������</option>
					<option <?php if(!$list['MB_USE_SMILY']) echo 'selected'; ?> value="0">�\�����Ȃ�</option>
					</select>
				</td>
				<td>
					<p>[�\������]�ŃX�}�C���[�摜�������N�ɕϊ����Ȃ��B<br />
					<span style="color:#A00;">����{�����̉摜���[�\�����Ȃ�]�̏ꍇ�̂ݗL���Ȑݒ�</span><br />
					(default:<strong>�\������</strong>)</p>
				</td>
			</tr>
		</table>
		</fieldset>
		<p class="submit"><input type="submit" name="write" value="�ύX��ۑ� &raquo;" /></p>
		</form>
	</div>
<?php
	ob_end_flush();
}

?>
