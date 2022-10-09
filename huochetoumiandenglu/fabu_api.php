<?php
/*2022/10/09 支持最新1.7
*免登陆发布文章+发布评论
*雅爱笔记 yaaibk.com 
*QQ 1334588325
*QQ群 953418367
*/
header("Content-Type:text/html;charset=UTF-8");
date_default_timezone_set('Asia/Shanghai'); //设置中国时区
require '../../../zb_system/function/c_system_base.php';
$zbp->Load();
global $bloghost; 
$pl = $zbp->option['ZC_USING_PLUGIN_LIST'];
$apl = explode('|', $pl);
$apl = array_unique($apl);
if(in_array('huochetoumiandenglu', $apl)){
//	echo "ok";
}else{
	die('请启用火车免登录入库功能');
}
$sql = "SELECT MAX(comm_ID) AS comm_ID FROM zbp_comment";
$comm_ID = $zbp->db->Query($sql);
$comm_ID = $comm_ID[0]['comm_ID'];
//print_r($comm_ID);
//die();

if($zbp->Config('huochetoumiandenglu')->fabujiekoukg !='1'){
	die('请打开火车免登录入库开关');
}

//采集时传递一个字段ruku_password=入库免登录密码
$ruku_password=$zbp->Config('huochetoumiandenglu')->ruku_password ;
if (empty($_POST['ruku_password']) || $_POST['ruku_password'] != $ruku_password){
	die('提交的发布密码错误');
}
if (empty($_POST['Title'])) {
	die('标题不能为空');
}
if($zbp->Config('huochetoumiandenglu')->narongweikongkg =='1'){
	if (empty($_POST['Content'])||$_POST['Content']==' ') {
		die('内容不能为空');
	}
}
if(isset($_POST['CommNums']) && $_POST['CommNums'] < 1 ){
	unset($_POST['CommNums']);
}
$log_ID=$_POST['ID'];
$pinglun=json_decode($_POST['pinglun'],true);
if(empty($pinglun)){
	$pinglun[0]=array('Name'=>'');
}
if($_POST['ID'] > 0){
	$p=PostArticle_api();//入库
	$ii='0';
	foreach($pinglun as $key => $val){
		if(empty($val['Name'])){
			continue;
		}
		$ii++;
		$_POST=$val;
		if($val['RootID'] > 0){
			$_POST['RootID']=($comm_ID + $ii) - $val['RootID'];
			$_POST['ParentID']=($comm_ID + $ii) - $val['RootID'];
		}
		if($val['ParentID'] > 0){
			$_POST['ParentID']=($comm_ID + $ii)-$val['ParentID'];
		}		
		$_POST['LogID']=$log_ID;  //你的文章ID
		$pa=PostComment_api();//评论入库
	}
	echo $p.'------'.$pa;
	die();
}


if($zbp->Config('huochetoumiandenglu')->biaotiquzhongkg=='1'){
	$p = $zbp->db->sql->Select($zbp->table['Post'], 'log_ID', array(array('=', 'log_Title', stripslashes($_POST['Title']))), null, null, null);
	$wz_id = GetValueInArrayByCurrent($zbp->db->Query($p), 'log_ID');		//只返回id
	if(!empty($wz_id) && $zbp->Config('huochetoumiandenglu')->gengxinwzkg=='1'){
		$_POST['ID']=$wz_id;
	}else if(!empty($wz_id)){
		die('标题重名');		
	}
}

$sql = "SELECT MAX(log_ID) AS log_ID FROM zbp_post";
$id_a = $zbp->db->Query($sql);
$id_a = $id_a[0]['log_ID'];

$p=PostArticle_api();//入库
$id_b = $zbp->db->Query($sql);
$id_b = $id_b[0]['log_ID'];
$pa='';
if($id_b >$id_a){
	$ii='0';
	foreach($pinglun as $key => $val){
		if(empty($val['Name'])){
			continue;
		}
		$ii++;
		$_POST=$val;
		if($val['RootID'] > 0){
			$_POST['RootID']=($comm_ID + $ii) - $val['RootID'];
			$_POST['ParentID']=($comm_ID + $ii) - $val['RootID'];
		}
		if($val['ParentID'] > 0){
			$_POST['ParentID']=($comm_ID + $ii)-$val['ParentID'];
		}		
		$_POST['LogID']=$id_b;  //你的文章ID
		$pa=PostComment_api();//评论入库
	}
}
echo $p;

/*提交文章数据*/
function PostArticle_api(){
	global $zbp;
	if (!isset($_POST['ID'])) {
		return false;
	}
	if (isset($_COOKIE['timezone'])) {
		$tz = GetVars('timezone', 'COOKIE');
		if (is_numeric($tz)) {
			date_default_timezone_set('Etc/GMT' . sprintf('%+d', -$tz));
		}
		unset($tz);
	}
	if (isset($_POST['Tag'])) {
		$_POST['Tag'] = TransferHTML($_POST['Tag'], '[noscript]');
		$_POST['Tag'] = PostArticle_CheckTagAndConvertIDtoString_aa($_POST['Tag']);
	}
	if (isset($_POST['Content'])) {
		$_POST['Content'] = str_replace('<hr class="more" />', '<!--more-->', $_POST['Content']);
		$_POST['Content'] = str_replace('<hr class="more"/>', '<!--more-->', $_POST['Content']);
		if (strpos($_POST['Content'], '<!--more-->') !== false) {
			if (isset($_POST['Intro'])) {
			$_POST['Intro'] = GetValueInArray(explode('<!--more-->', $_POST['Content']), 0);
			}
		} else {
			if (isset($_POST['Intro'])) {
				if ($_POST['Intro'] == '' || (stripos($_POST['Intro'], '<!--autointro-->') !== false)) {
					$_POST['Intro'] = TransferHTML($_POST['Content'], "[nohtml]");
					$_POST['Intro'] = SubStrUTF8_Html($_POST['Intro'], (int) $zbp->option['ZC_ARTICLE_EXCERPT_MAX']);
					$_POST['Intro'] .= '<!--autointro-->';
				}
				$_POST['Intro'] = CloseTags($_POST['Intro']);
			}
		}
	}
	if (!isset($_POST['AuthorID'])) {
		$_POST['AuthorID'] = 1;
	}
	if (isset($_POST['Alias'])) {
		$_POST['Alias'] = TransferHTML($_POST['Alias'], '[noscript]');
	}

	//发布日期处理
	$log_PostTime  = isset($_POST['PostTime']) ? $_POST['PostTime'] : '';
	$post_date = intval($log_PostTime);
	if(empty($log_PostTime) || $post_date<=0){
		$_POST['PostTime']=time();
	}else {
		$_POST['PostTime'] = strtotime($_POST['PostTime']);
	}
	$article = new Post();
	$pre_author = null;
	$pre_tag = null;
	$pre_category = null;
	$pre_istop = null;
	$pre_status = null;
	$orig_id = 0;
	if (GetVars('ID', 'POST') == 0) {
	} else {
		$article->LoadInfoByID(GetVars('ID', 'POST'));
		$orig_id = $article->ID;
		$pre_author = $article->AuthorID;
		$pre_tag = $article->Tag;
		$pre_category = $article->CateID;
		$pre_istop = $article->IsTop;
		$pre_status = $article->Status;
	}
	foreach ($zbp->datainfo['Post'] as $key => $value) {
		if ($key == 'ID' || $key == 'Meta') {
			continue;
		}
		if (isset($_POST[$key])) {
			$article->$key = GetVars($key, 'POST');
		}
	}
	$article->Type = GetVars('Type', 'POST');
	//过滤扩展数据.
	FilterMeta($article);
	foreach ($GLOBALS['hooks']['Filter_Plugin_PostArticle_Core'] as $fpname => &$fpsignal) {
		$fpname($article);
	}
	//过滤文章数据.
	//FilterPost($article);
	$article->Save();
	//更新统计信息
	$pre_arrayTag = $zbp->LoadTagsByIDString($pre_tag);
	$now_arrayTag = $zbp->LoadTagsByIDString($article->Tag);
	$pre_array = $now_array = array();
	foreach ($pre_arrayTag as $tag) {
		$pre_array[] = $tag->ID;
	}
	foreach ($now_arrayTag as $tag) {
		$now_array[] = $tag->ID;
	}
	$del_array = array_diff($pre_array, $now_array);
	$add_array = array_diff($now_array, $pre_array);
	$del_string = $zbp->ConvertTagIDtoString($del_array);
	$add_string = $zbp->ConvertTagIDtoString($add_array);
	if ($del_string) {
		CountTagArrayString($del_string, -1, $article->ID);
	}
	if ($add_string) {
		CountTagArrayString($add_string, +1, $article->ID);
	}
	if ($pre_author != $article->AuthorID) {
		if ($pre_author > 0) {
			CountMemberArray(array($pre_author), array(-1, 0, 0, 0));
		}
		CountMemberArray(array($article->AuthorID), array(+1, 0, 0, 0));
	}
	if ($pre_category != $article->CateID) {
		if ($pre_category > 0) {
			CountCategoryArray(array($pre_category), -1);
		}
		CountCategoryArray(array($article->CateID), +1);
	}
	/*
	if ($zbp->option['ZC_LARGE_DATA'] == false) {
		//CountPostArray(array($article->ID));//官方的评论统计
		CommNums_PLS($article->ID,$_POST['CommNums']);
	}
	*/
	if ($orig_id == 0 && $article->IsTop == 0 && $article->Status == ZC_POST_STATUS_PUBLIC) {
		CountNormalArticleNums(+1);
	} elseif ($orig_id > 0) {
		if (($pre_istop == 0 && $pre_status == 0) && ($article->IsTop != 0 || $article->Status != 0)) {
			CountNormalArticleNums(-1);
		}
		if (($pre_istop != 0 || $pre_status != 0) && ($article->IsTop == 0 && $article->Status == 0)) {
			CountNormalArticleNums(+1);
		}
	}
	if ($article->IsTop == true && $article->Status == ZC_POST_STATUS_PUBLIC) {
		CountTopArticle_a($article->Type, $article->ID, null);
	} else {
		CountTopArticle_a($article->Type, null, $article->ID);
	}
	$zbp->AddBuildModule('previous');
	$zbp->AddBuildModule('calendar');
	$zbp->AddBuildModule('comments');
	$zbp->AddBuildModule('archives');
	$zbp->AddBuildModule('tags');
	$zbp->AddBuildModule('authors');
	foreach ($GLOBALS['hooks']['Filter_Plugin_PostArticle_Succeed'] as $fpname => &$fpsignal) {
		$fpname($article);
	}
	return 'ok';
}
function CommNums_PLS($id,$CommNums){
	global $zbp;
	$article = $zbp->GetPostByID($id);
    if ($article->ID > 0) {
       $article->CommNums = $CommNums;
       $article->Save();
     }
}
function PostArticle_CheckTagAndConvertIDtoString_aa($tagnamestring){
	global $zbp;
	$s = '';
	$tagnamestring = str_replace(';', ',', $tagnamestring);
	$tagnamestring = str_replace('，', ',', $tagnamestring);
	$tagnamestring = str_replace('、', ',', $tagnamestring);
	$tagnamestring = strip_tags($tagnamestring);
	$tagnamestring = trim($tagnamestring);
	if ($tagnamestring == '') {
		return '';
	}
	if ($tagnamestring == ',') {
		return '';
	}
	$a = explode(',', $tagnamestring);
	$b = array();
	foreach ($a as &$value) {
		$value = trim($value);
		if ($value) {
			$b[] = $value;
		}
	}
	$b = array_unique($b);
	$b = array_slice($b, 0, 20);
	$c = array();
	$t = $zbp->LoadTagsByNameString($tagnamestring);
	foreach ($t as $key => $value) {
		$c[] = $key;
	}
	$d = array_diff($b, $c);
		foreach ($d as $key) {
			$tag = new Tag();
			$tag->Name = $key;
			foreach ($GLOBALS['hooks']['Filter_Plugin_PostTag_Core'] as $fpname => &$fpsignal) {
				$fpname($tag);
			}
			FilterTag($tag);
			$tag->Save();
			$zbp->tags[$tag->ID] = $tag;
			$zbp->tagsbyname[$tag->Name] = &$zbp->tags[$tag->ID];
			foreach ($GLOBALS['hooks']['Filter_Plugin_PostTag_Succeed'] as $fpname => &$fpsignal) {
				$fpname($tag);
			}
		}
	foreach ($b as $key) {
		if (!isset($zbp->tagsbyname[$key])) {
			continue;
		}
		$s .= '{' . $zbp->tagsbyname[$key]->ID . '}';
	}
	return $s;
}

/*提交评论数据*/
function PostComment_api(){
    global $zbp;
	//发布日期处理
	$log_PostTime  = isset($_POST['PostTime']) ? $_POST['PostTime'] : '';
	$post_date = intval($log_PostTime);
	if(empty($log_PostTime) || $post_date<=0){
		$_POST['PostTime']=time();
	}else{
		$_POST['PostTime'] = strtotime($_POST['PostTime']);
	}
	if(empty($_POST['IP'])){
		$_POST['IP'] = GetGuestIP();
	}
	if(empty($_POST['Agent'])){
		$_POST['Agent'] = GetGuestAgent();
	}
	$authorid=$_POST['AuthorID'];
    if ($authorid > 0) {
		$R=$zbp->members;
        $_POST['Name'] = $R[$authorid]->Name;
		$_POST['Email'] = $R[$authorid]->Email;
    }
    $cmt = new Comment();
    foreach ($zbp->datainfo['Comment'] as $key => $value) {
        if ($key == 'Meta') {
            continue;
        }
        if (isset($_POST[$key])) {
            $cmt->$key = GetVars($key, 'POST');
        }
    }
	//审核评论开关，可以不要
    if ($zbp->option['ZC_COMMENT_AUDIT'] && !$zbp->CheckRights('root')) {
        $cmt->IsChecking = true;
    }
    foreach ($GLOBALS['hooks']['Filter_Plugin_PostComment_Core'] as $fpname => &$fpsignal) {
        $fpname($cmt);
    }

	if($zbp->Config('huochetoumiandenglu')->guolvpinglunkg =='1'){
		FilterComment($cmt);// 过滤评论数据
	}
    $cmt->Save();
    if ($cmt->IsChecking) {
		echo '你的评论已进入审核过程！';
        return false;
    }
    return true;
}

/**为了配合新版1.7添加的
 *统计置顶文章数组.
 *
 * @param int  $type
 * @param null $addplus
 * @param null $delplus
 */
function CountTopArticle_a($type = 0, $addplus = null, $delplus = null)
{
    global $zbp;
    $varname = 'top_post_array_' . $type;
    $array = unserialize($zbp->cache->$varname);
    if (!is_array($array)) {
        $array = array();
    }

    if ($addplus === null && $delplus === null) {
        $s = $zbp->db->sql->Select($zbp->table['Post'], 'log_ID', array(array('=', 'log_Type', $type), array('>', 'log_IsTop', 0), array('=', 'log_Status', 0)), null, null, null);
        $a = $zbp->db->Query($s);
        foreach ($a as $id) {
            $array[(int) current($id)] = (int) current($id);
        }
    } elseif ($addplus !== null && $delplus === null) {
        $addplus = (int) $addplus;
        $array[$addplus] = $addplus;
    } elseif ($addplus === null && $delplus !== null) {
        $delplus = (int) $delplus;
        unset($array[$delplus]);
    }

    $zbp->cache->$varname = serialize($array);
}
?>