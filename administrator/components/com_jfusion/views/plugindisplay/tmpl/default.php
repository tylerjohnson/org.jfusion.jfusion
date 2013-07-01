<?php
/**
 * @package JFusion
 * @subpackage Views
 * @author JFusion development team
 * @copyright Copyright (C) 2008 JFusion. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 * @var $this jfusionViewplugindisplay
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
JFusionFunctionAdmin::displayDonate();

//load mootools
JHtml::_('behavior.framework', true);
$images = 'components/com_jfusion/images/';
$document = JFactory::getDocument();
$document->addScript('components/com_jfusion/js/File.Upload.js');
?>
<script type="text/javascript">
	//<![CDATA[
	JFusion.url = '<?php echo JURI::root() . 'administrator/index.php'; ?>';

	window.addEvent('domready',function() {
		var installGIT = $('installGIT');
		installGIT.set('send',
			{ onSuccess: function(JSONobject) {
				$('spinnerGIT').set('html','');
				if (JSON.validate(JSONobject)) {
					JSONobject = JSON.decode(JSONobject);
					JFusion.OnMessages(JSONobject.messages);

					JFusion.updateList(JSONobject.pluginlist);
				} else {
					JFusion.OnError(JSONobject);
				}
			}, data: {
				ajax: true
			}
			});
		installGIT.addEvent('submit', function(e) {
			e.stop();
			$('spinnerGIT').set('html','<img border="0" alt="loading" src="components/com_jfusion/images/spinner.gif">');
			this.send('?ajax=true');
		});

		var installURL = $('installURL');
		installURL.set('send',
			{ onSuccess: function(JSONobject) {
				$('spinnerURL').set('html','');
				if (JSON.validate(JSONobject)) {
					JSONobject = JSON.decode(JSONobject);
					JFusion.OnMessages(JSONobject.messages);

					JFusion.updateList(JSONobject.pluginlist);
				} else {
					JFusion.OnError(JSONobject);
				}
			}
			});
		installURL.addEvent('submit', function(e) {
			e.stop();
			$('spinnerURL').set('html','<img border="0" alt="loading" src="components/com_jfusion/images/spinner.gif">');
			this.send('?ajax=true');
		});

		var installDIR = $('installDIR');
		installDIR.set('send',
			{ onSuccess: function(JSONobject) {
				$('spinnerDIR').set('html','');
				if (JSON.validate(JSONobject)) {
					JSONobject = JSON.decode(JSONobject);
					JFusion.OnMessages(JSONobject.messages);

					JFusion.updateList(JSONobject.pluginlist);
				} else {
					JFusion.OnError(JSONobject);
				}
			}
			});
		installDIR.addEvent('submit', function(e) {
			e.stop();
			$('spinnerDIR').set('html','<img border="0" alt="loading" src="components/com_jfusion/images/spinner.gif">');
			this.send('?ajax=true');
		});

		var installZIP = $('installZIP');
		installZIP.addEvent('submit', function(e) {
			e.stop();
			$('spinnerZIP').set('html','<img border="0" alt="loading" src="components/com_jfusion/images/spinner.gif">');
			if (typeof FormData === 'undefined') {
				this.submit();
			} else {
				var upload = new File.Upload({
					url:  JFusion.url ,
					data: {
						option: 'com_jfusion',
						task : 'installplugin',
						installtype : 'upload',
						ajax : 'true' } ,
					images: ['install_package'],
					onComplete : function (JSONobject) {
						$('spinnerZIP').set('html','');
						if (JSON.validate(JSONobject)) {
							JSONobject = JSON.decode(JSONobject);
							JFusion.OnMessages(JSONobject.messages);

							JFusion.updateList(JSONobject.pluginlist);
						} else {
							JFusion.OnError(JSONobject);
						}
					}
				});
				upload.send();
			}
		});
		JFusion.initSortables();
	});
	//]]>
</script>
<div class="jfusion">
<form method="post" action="index.php" name="adminForm">
	<input type="hidden" name="option" value="com_jfusion" />
	<input type="hidden" name="task" value="saveorder" />

	<table class="adminlist" style="border-spacing:1px;" id="sortables">
		<thead>
		<tr>
			<th class="title" width="20px;">
			</th>
			<th class="title" align="left">
				<?php echo JText::_('NAME');?>
			</th>
			<th class="title" width="75px" align="center">
				<?php echo JText::_('ACTIONS');?>
			</th>
			<th class="title" align="center">
				<?php echo JText::_('DESCRIPTION');?>
			</th>
			<th class="title" width="40px" align="center">
				<?php echo JText::_('MASTER'); ?>
			</th>
			<th class="title" width="40px" align="center">
				<?php echo JText::_('SLAVE'); ?>
			</th>
			<th class="title" width="40px" align="center">
				<?php echo JText::_('CHECK_ENCRYPTION'); ?>
			</th>
			<th class="title" width="40px" align="center">
				<?php echo JText::_('DUAL_LOGIN');?>
			</th>
			<th class="title" align="center">
				<?php echo JText::_('STATUS');?>
			</th>
			<th class="title" align="center">
				<?php echo JText::_('USERS');?>
			</th>
			<th class="title" align="center">
				<?php echo JText::_('REGISTRATION');?>
			</th>
			<th class="title" align="center">
				<?php echo JText::_('DEFAULT_USERGROUP');?>
			</th>
		</tr>
		</thead>
		<tbody id="sort_table">
		<?php echo $this->generateListHTML($this->plugins); ?>
		</tbody>
	</table>
	<br />

	<table style="width:100%;">
		<tr>
			<td style="text-align: left;">
				<img src="<?php echo $images; ?>wizard_icon.png" border="0" alt="<?php echo JText::_('WIZARD');?>" style="margin-left: 10px;" /> = <?php echo JText::_('WIZARD');?>
				<img src="<?php echo $images; ?>edit.png" border="0" alt="<?php echo JText::_('EDIT');?>" /> = <?php echo JText::_('EDIT');?>
				<img src="<?php echo $images; ?>copy_icon.png" border="0" alt="<?php echo JText::_('COPY');?>" style="margin-left: 10px;" /> = <?php echo JText::_('COPY');?>
				<img src="<?php echo $images; ?>delete_icon.png" border="0" alt="<?php echo JText::_('DELETE');?>" style="margin-left: 10px;" /> = <?php echo JText::_('DELETE');?>
				<img src="<?php echo $images; ?>info.png" border="0" alt="<?php echo JText::_('INFO');?>" style="margin-left: 10px;" /> = <?php echo JText::_('INFO');?>
			</td>
			<td style="text-align: right;">
				<img src="<?php echo $images; ?>tick.png" border="0" alt="<?php echo JText::_('ENABLED'); ?>" /> = <?php echo JText::_('ENABLED'); ?>
				<img src="<?php echo $images; ?>cross.png" border="0" alt="<?php echo JText::_('DISABLED');?>" style="margin-left: 10px;" /> = <?php echo JText::_('DISABLED');?>
				<img src="<?php echo $images; ?>cross_dim.png" border="0" alt="<?php echo JText::_('CONFIG_FIRST');?>" style="margin-left: 10px;" /> = <?php echo JText::_('CONFIG_FIRST');?>
			</td>
		</tr>
	</table>

</form>
<br/><br/>

<?php echo JText::_('PLUGIN_INSTALL_INSTR'); ?><br/>

<?php if($this->VersionData) {
	//display installer data ?>

	<form id="installGIT" method="post" action="./index.php" enctype="multipart/form-data">
		<input type="hidden" name="option" value="com_jfusion" />
		<input type="hidden" name="task" value="installplugin" />
		<input type="hidden" name="installtype" value="url" />

		<table class="adminform">
			<tr>
				<td>
					<img src="components/com_jfusion/images/folder_url.png">
				</td>
				<td>
					<table>
						<tr>
							<th colspan="2">
								<?php echo JText::_('INSTALL') . ' ' . JText::_('FROM') . ' JFusion ' .JText::_('SERVER'); ?>
							</th>
						</tr>
						<tr>
							<td width="120">
								<label for="install_url2">
									<?php echo JText::_('PLUGIN') . ' ' . JText::_('NAME'); ?> :
								</label>
							</td>
							<td>
								<select name="install_url" id="install_url2">
									<?php
									/**
									 * @ignore
									 * @var $plugin JXMLElement
									 */
									foreach ($this->VersionData as $plugin): ?>
										<option value="<?php echo (string)$plugin->remotefile; ?>"><?php echo $plugin->name() . ' - ' . (string)$plugin->description; ?></option>
									<?php endforeach; ?>
								</select>
								<input type="submit" name="button" id="submitter" />
								<div id="spinnerGIT">
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</form>
<?php }  else { ?>
	<table class="adminform">
		<tr>
			<td>
				<img src="components/com_jfusion/images/folder_url.png">
			</td>
			<td>
				<table>
					<tr>
						<th colspan="2">
							<?php echo JText::_('INSTALL') . ' ' . JText::_('FROM') . ' JFusion ' .JText::_('SERVER'); ?>
						</th>
					</tr>
					<tr>
						<td width="120">
							<label for="install_url2">
								<?php echo JText::_('PLUGIN') . ' ' . JText::_('NAME'); ?> :
							</label>
						</td>
						<td>
							<?php echo JText::_('ERROR_LOADING_REMOTE_PLUGIN_DATA_FROM_JFUSION_SERVER'); ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<?php } ?>

<form id="installZIP" method="post" action="index.php" enctype="multipart/form-data">
	<input type="hidden" name="option" value="com_jfusion" />
	<input type="hidden" name="task" value="installplugin" />
	<input type="hidden" name="installtype" value="upload" />
	<table class="adminform">
		<tr>
			<td>
				<img src="components/com_jfusion/images/folder_zip.png">
			</td>
			<td>
				<table>
					<tr>
						<th colspan="2">
							<?php echo JText::_('UPLOAD_PACKAGE'); ?>
						</th>
					</tr>
					<tr>
						<td width="120">
							<label for="install_package">
								<?php echo JText::_('PACKAGE_FILE'); ?> :
							</label>
						</td>
						<td>
							<input class="input_box" id="install_package" name="install_package" type="file" size="57" />
							<input type="submit" value="<?php echo JText::_('UPLOAD_FILE'); ?> &amp; <?php echo JText::_('INSTALL'); ?>"/>
							<div id="spinnerZIP">
							</div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>

<form id="installDIR" method="post" action="index.php" enctype="multipart/form-data">
	<input type="hidden" name="option" value="com_jfusion" />
	<input type="hidden" name="task" value="installplugin" />
	<input type="hidden" name="installtype" value="folder" />
	<table class="adminform">
		<tr>
			<td>
				<img src="components/com_jfusion/images/folder_dir.png">
			</td>
			<td>
				<table>
					<tr>
						<th colspan="2">
							<?php echo JText::_('INSTALL_FROM_DIRECTORY'); ?>
						</th>
					</tr>
					<tr>
						<td width="120"><label for="install_directory">
								<?php echo JText::_('INSTALL_DIRECTORY'); ?> :
							</label>
						</td>
						<td>
							<input type="text" id="install_directory" name="install_directory" class="input_box" size="150" value="" />
							<input type="submit" value="<?php echo JText::_('INSTALL'); ?>"/>
							<div id="spinnerDIR">
							</div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>

<form id="installURL" method="post" action="index.php" enctype="multipart/form-data">
	<input type="hidden" name="option" value="com_jfusion" />
	<input type="hidden" name="task" value="installplugin" />
	<input type="hidden" name="installtype" value="url" />
	<table class="adminform">
		<tr>
			<td>
				<img src="components/com_jfusion/images/folder_url.png">
			</td>
			<td>
				<table>
					<tr>
						<th colspan="2">
							<?php echo JText::_('INSTALL_FROM_URL'); ?>
						</th>
					</tr>
					<tr>
						<td width="120">
							<label for="install_url">
								<?php echo JText::_('INSTALL_URL'); ?> :
							</label>
						</td>
						<td>
							<input type="text" id="install_url" name="install_url" class="input_box" size="150" value="http://" />
							<input type="submit" value="<?php echo JText::_('INSTALL'); ?>"/>
							<div id="spinnerURL">
							</div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>
</div>

