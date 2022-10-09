<?php
#注册插件
RegisterPlugin("huochetoumiandenglu","ActivePlugin_huochetoumiandenglu");

function ActivePlugin_huochetoumiandenglu() {}
function UninstallPlugin_huochetoumiandenglu() {}
function InstallPlugin_huochetoumiandenglu() {
	global $zbp;
	if(!$zbp->Config('huochetoumiandenglu')->HasKey('Version')){
		$zbp->Config('huochetoumiandenglu')->Version = '3.0';
		$zbp->Config('huochetoumiandenglu')->ruku_password = 'yaaibk.com';
		$zbp->Config('huochetoumiandenglu')->fabujiekoukg = '1';
		$zbp->Config('huochetoumiandenglu')->biaotiquzhongkg = '1';
		$zbp->Config('huochetoumiandenglu')->narongweikongkg = '1';
		$zbp->Config('huochetoumiandenglu')->guolvpinglunkg = '1';
		$zbp->SaveConfig('huochetoumiandenglu');
	}
}