<!DOCTYPE html>
<html>
<head>
	<title>Installer</title>
	<style>
		#installPane{
			margin: 10px auto;
			position: relative;
			display: block;
			max-width: 1000px;
		}

		
		#nav li, #content, .button{
			background-color: rgb(238, 238, 238);
			border-radius: 5px;
			color: rgb(25, 57, 84);
			font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
			margin: 7px;
			line-height: 21px;
		}
		
		#content{
			display: block;
			font-size: 14px;
			padding: 20px;
			min-height: 300px;
			overflow: hidden;
			position: relative;
			width: 900px;
		}

		#nav{
			display: block;
			margin: 0;
			padding: 0;
		}
		
		#nav li, .button{
			float:left;
			display: block;
			padding: 14px;
			font-size: 18px;
			min-width: 100px;
			font-weight: 400;
		}
		
		#nav li.active, .button{
			background-color: rgb(33, 132, 190);
			color: white;
		}

		#buttons{
			width: 955px;
		}

		.button{
			padding: 10px 15px;
			float:right;
			text-decoration: none;
			text-align: center;
		}

		h2{
			margin:0;
			padding:0 0 10px 0;
		}

		/**
		 * Switch
		 */
		
		.switch {
		  position: relative;
		  display: inline-block;
		  width: 60px;
		  height: 34px;
		}

		.switch input { 
		  opacity: 0;
		  width: 0;
		  height: 0;
		}

		.slider {
		  position: absolute;
		  cursor: pointer;
		  top: 0;
		  left: 0;
		  right: 0;
		  bottom: 0;
		  background-color: #2196F3;
		  -webkit-transition: .4s;
		  transition: .4s;
		  box-shadow: 0 0 1px #2196F3;
		}

		.slider:before {
		  position: absolute;
		  content: "";
		  height: 26px;
		  width: 26px;
		  left: 4px;
		  bottom: 4px;
		  background-color: white;
		  -webkit-transition: .4s;
		  transition: .4s;

		  -webkit-transform: translateX(26px);
		  -ms-transform: translateX(26px);
		  transform: translateX(26px);
		}

		input:checked + .slider {
		  background-color: #ccc;
		}

		input:focus + .slider {
		  box-shadow: none;
		}

		input:checked + .slider:before {
		  -webkit-transform: translateX(0px);
		  -ms-transform: translateX(0px);
		  transform: translateX(0px);
		}

		/* Rounded sliders */
		.slider.round {
		  border-radius: 34px;
		}

		.slider.round:before {
		  border-radius: 50%;
		}

		/**
		 * Table
		 */
		td {
			width: 295px;
			vertical-align: top;
			padding: 5px 0;
		}

		input, select {
			width: 280px;
			margin: 0;
			padding: 3px 0 3px 5px;
			border-radius: 3px;
			border: 1px solid gray;
		}

		td.pluginName{
			text-align: right;
			padding-right:10px;
			font-weight: 400;
			font-size: 16px;
			padding: 12px 15px;
		}

		td.pluginError{
			padding: 0;
			max-width: 300px;
		}

		td.pluginSlider{
			width: 100px;
		}

		tr.newLine td{
			border-top: 3px dotted lightgray;
			padding-top: 5px;
		}

		.keyText i{
			font-size: 13px;
			opacity: 0.7;
		}

		.error{
			color: darkred;
			border-left: 2px solid darkred;
			padding: 5px 10px;
			margin: 0;
		}
	</style>
</head>
<body>
	<form action="?step=<?=(1+$step)?>" method="POST" id="installPane">
		<ul id="nav">
			<li class="<?=($step==1) ? "active" : ""?>">1. Выбор плагинов</li>
			<li class="<?=($step==2) ? "active" : ""?>">2. Установка параметров</li>
			<li class="<?=($step==3) ? "active" : ""?>">3. Завершение</li>
		</ul>

		<div id="content">
			<?php switch($step){
				// 1. Плагины
				case 1:?>
					<h2>Выберите плагины, которые будут включены в работу системы</h2>
					<table>
					<?php if(sizeof($errors) > 0):?>
						<tr>
							<td colspan="3"><p class="error">Решите ошибки, возникшие во врем установки. Возможно, необходимо отключить конфликтующие плагины!</p></td>	
						</tr>
					<?php endif?>
					<?php foreach ($plugins as $pluginName => $value):?>
						<tr>	
							<td class="pluginName">
							<?=ucfirst(str_replace('-', ' ', $pluginName))?>
							</td>
							<td class="pluginSlider">
								<label class="switch">
						  			<input id="plugin_<?=$pluginName?>" type="checkbox" name="param[plugins][disabled][<?=$pluginName?>]" <?=(!in_array($pluginName, $enabled)?'checked':'')?>>
						  			<span class="slider round"></span>
								</label>
							</td>
							<?php if(isset($errors[$pluginName])):?>	
							<td class="pluginError">
								<p class='error'>Ошибка: <?=$errors[$pluginName]?></p>
							</td>
							<?php endif?>
						</tr>
					<?php endforeach?>
					</table>
				<?php break;

				// 2. Параметры
				case 2:
					$lastPart = '';
					?>
					<h2>Укажите необходимые параметры</h2>
					<table>
					<?php foreach($fields as $param):
						$id = $param->getId();
						$part = $param->getConfigPart();
						$params = $param->getParams();
					?>
					<tr class="<?=($part!=$lastPart) ? "newLine": ""?>">
						<td><?php if($param->getType() != 'error'):?><label for="<?=$id?>"><?=$param->getDescription()?></label><?php endif?></td>
						<td>
						<?php switch ($param->getType()):
							case 'error':?>
								<b style="color:rgba(255,100,100)"><?=$param->getDescription()?></b>
								<?php break;

							case 'select':?>
								<select id="<?=$id?>" name="param[<?=$param->getKey()?>]" <?php  array_walk($param->getParams(), function($value, $key){ ?><?=$key?>="<?=$value?>"<?php  }) ?> <?=$param->getRequired() ? 'required': ''?>>
									<?php foreach($param->getValues() as $value => $valueText):?>
									<option value="<?=$value?>" <?=($value==$param->getValue()) ? "selected" : ''?>><?=$valueText?></option>
									<?php endforeach?>
								</select>

								<?php break;

							case 'helper-text':
								?><input type="text" name="" value="<?=$param->getValue()?>" readonly/><?php
							break;

							case 'text':
							case 'numeric':
							case 'email':
							default:?>
								<input type="<?=$param->getType()?>" id="<?=$id?>" name="param[<?=$param->getKey()?>]" <?php  array_walk($params, function($value, $key){ ?><?=$key?>="<?=$value?>"<?php  }) ?> placeholder="<?=$param->getPlaceholder()?>" value="<?=$param->getValue()?>" <?=$param->getRequired() ? 'required': ''?>/>
								<?php break;
							
						endswitch;
						?>
						</td>
						<td class="keyText"><?php if($param->getType() != 'error'):?><i>(<?=$param->getKey()?>)</i><?php endif?></td>
					</tr>
					<?php 
					$lastPart = $part;
					endforeach?>
					</table>
				<?php break;

				case 3:?>
					<h2>Установка успешно завершена!</h2>
					<p>Следующие плагины были отключены, т.к. либо вы их отметили на первом этапе установке, либо они использовали функционал отключенных плагинов, либо произошла ошибка во время установки.</p>
					<p><b>Отключенные плагины:</b><ul><li><?=implode('</li><li>', $disabled)?></li></ul></p>
					<p>Для предотвращения запуска процесса установки файл <b>install.php</b> будет переименован.</p>
				<?php 
			}
			?>
		</div>

		<div id="buttons">
		
		<?php if($step < 3):?>
			<?php if($step > 1):?>
				<a href="?step=<?=($step-1)?>" class="button" style="float:left;">Назад</a>
			<?php endif?>
			<button class="button">Далее</button>
		<?php else:?>
			<a href="./" class="button">На главную</a>
		<?php endif?>
		</div>
	</form>
</body>
</html>