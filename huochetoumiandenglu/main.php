<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action='root';
if (!$zbp->CheckRights($action)) {$zbp->ShowError(6);die();}
if (!$zbp->CheckPlugin('huochetoumiandenglu')) {$zbp->ShowError(48);die();}
$blogtitle='火车头免登录入库';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
if(isset($_POST['fabujiekoukg'])){
	$zbp->Config('huochetoumiandenglu')->fabujiekoukg = $_POST['fabujiekoukg'];
	$zbp->Config('huochetoumiandenglu')->biaotiquzhongkg = $_POST['biaotiquzhongkg'];
	if($_POST['biaotiquzhongkg'] !='1'){$_POST['gengxinwzkg']='0';}
	$zbp->Config('huochetoumiandenglu')->gengxinwzkg = $_POST['gengxinwzkg'];
	$zbp->Config('huochetoumiandenglu')->ruku_password = $_POST['ruku_password'];
	$zbp->Config('huochetoumiandenglu')->narongweikongkg = $_POST['narongweikongkg'];
	$zbp->Config('huochetoumiandenglu')->guolvpinglunkg = $_POST['guolvpinglunkg'];
	$zbp->SaveConfig('huochetoumiandenglu');
	$zbp->ShowHint('good');
}
?>
<div id="divMain">
  <div class="divHeader"  style="background-image: url('<?php echo $zbp->host; ?>zb_users/plugin/huochetoumiandenglu/logo.png');"><?php echo $blogtitle;?></div>
  <div class="SubMenu">
        <a href="http://wpa.qq.com/msgrd?v=3&uin=1334588325&site=qq&menu=yes" target="_blank" ><span style="color:#F00">QQ联系帮助</span></a>
		<a href="http://www.yaaibk.com/chajian/ds.gif" target="_blank"><span style="color:#6633ff">赞助</span></a>
   </div>

  <div id="divMain2">
<!-- 入库免登录密码  -->
	<form id="form2" name="form2" method="post">
    <table width="100%" style='padding:0px;margin:0px;' cellspacing='0' cellpadding='0' class="tableBorder">
  <tr>
    <th width='15%'><p align="center">是否开启</p></th>
    <td width='75%'><p align="left"><input type="text" name="fabujiekoukg" class="checkbox" value="<?php echo $zbp->Config('huochetoumiandenglu')->fabujiekoukg;?>"/>&nbsp;&nbsp;&nbsp;&nbsp;要使用此功能必须开启</p></td>
  </tr>
  <tr>
    <td><b><label><p align="center">标题去重</p></label></b></td>
    <td><p align="left"><input name="biaotiquzhongkg" type="text" value="<?php echo $zbp->Config('huochetoumiandenglu')->biaotiquzhongkg;?>" class="checkbox">&nbsp;&nbsp;&nbsp;&nbsp;启用后存在相同标题，则不入库。 &nbsp;&nbsp;&nbsp;&nbsp;<input name="gengxinwzkg" type="text" value="<?php echo $zbp->Config('huochetoumiandenglu')->gengxinwzkg;?>" class="checkbox">&nbsp;&nbsp;&nbsp;&nbsp;启用后存在相同标题，则更新文章，必须开启标题去重才有效</p>
	</td>
  </tr>
  <tr>
    <td><b><label><p align="center">内容为空</p></label></b></td>
    <td><p align="left"><input name="narongweikongkg" type="text" value="<?php echo $zbp->Config('huochetoumiandenglu')->narongweikongkg;?>" class="checkbox">&nbsp;&nbsp;&nbsp;&nbsp;启用后内容为空则不入库。</p>
	</td>
  </tr>
  <tr>
    <td><b><label><p align="center">过滤评论</p></label></b></td>
    <td><p align="left"><input name="guolvpinglunkg" type="text" value="<?php echo $zbp->Config('huochetoumiandenglu')->guolvpinglunkg;?>" class="checkbox">&nbsp;&nbsp;&nbsp;&nbsp;启用后开启zblog系统的过滤评论数据功能，过滤评论数据一般过滤html、图片等代码。</p>
	</td>
  </tr>
  <tr>
    <td><b><label><p align="center">入库免登录密码</p></label></b></td>
    <td><p align="left"><input name="ruku_password" type="text" size="20" value="<?php echo $zbp->Config('huochetoumiandenglu')->ruku_password;?>" />&nbsp;&nbsp;&nbsp;&nbsp;避免暴力破解密码建议设置位8位以上包含大小写字母数字或者修改<span style="color:#F00">fabujiekou_api.php文件名</span></p></td>
  </tr>
  <tr>
    <td><b><label><p align="center">使用教程</p></label></b></td>
   <td><p align="left">
   1、免登录入库插件（支持采集评论），安装好后可以用接口POST提交数据入库或者用于火车采集器采集入库。<br>
   2、接口地址：<?php echo $zbp->host ?>zb_users/plugin/huochetoumiandenglu/fabu_api.php<br>
   3、使用时需要加入密码判断字段<span style="color:#ff0000">ruku_password</span>该字段的值就是你插件里设置的 <span style="color:#ff0000">入库免登录密码</span>。<br>
   4、支持自定义域标签，如<span style="color:#ff0000">meta_linkurl</span>那么写采集时 在“Z-Blog雅爱笔记发布模块”里添加该标签，方法是在编辑发布模块 点击“内容发布参数”，点击“新建表单项”，表单名填写“meta.linkurl”表单值填写“[标签:&times;&times;]”即可，也就是自定义域标签你的模板里怎么写的这里就怎么写，采集规则的时候加上这个标签即可。<br>
<!--
   3、使用时需要加入密码判断字段
   <span style="color:#ff0000">$a['ruku_password']='yaaibk.com'; </span>支持自定义域标签如<span style="color:#ff0000">{$article.Metas.link_url}</span>那么写采集时<span style="color:#ff0000">$a['meta.link_url']='&times;&times;&times;&times;'; </span>如果是火车采集器里添加标签，在“Z-Blog雅爱笔记发布模块”里点击“内容发布参数”，点击“新建表单项”，表单名填写“meta.link_url”表单值填写“[标签:&times;&times;]”即可，也就是自定义域标签你的模板里怎么写的这里就怎么写<br>
   -->
   5、简介标签值为空会自动生成230个汉字，如果不想要简介请采集时不要简介标签或者简介标签值传一个空格<br>
   6、文章ID为空就自动添加文章，指定ID就是修改指定ID的文章,指定ID后<span style="color:#ff0000">标题去重和指定字段去重</span>功能无效<br>
   7、<span style="color:#ff0000">采集评论时评论内容最好不为空，如果采集的评论全部是代码并且关闭过滤评论开关可能会报错。以为发布评论内容是不允许为空或者太长</span><br>
   8、发布日期 为空为当前时间，如果要指定格式为2020-02-15 12:58:50<br>
   9、详细+火车头发布模块 <a href="https://yaaibk.com/post/31.html" target="_blank">https://yaaibk.com/post/31.html</a><br>
   10、QQ群获取更多帮助 953418367<br>

   </p></td>
  </tr>
</table>
 <br />
   <input name="" type="Submit" class="button" value="保存"/>
    </form>
  </div>
</div>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>