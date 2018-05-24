<?php 
/*
淘宝司法拍卖房产信息爬虫

by guanxin  26133507@qq.com

爬取了淘宝司法拍卖网信息，时间仓促，弄了个基本框架。
建议可以根据此框架扩充，建立综合性的拍卖房产数据库。
实现方式：人工+智能
通过爬虫，将房产基本信息和一些分析的详细信息，进行爬去，丢到自己数据库。
然后再做页面，可以同时展示两边信息，进行人工比对，修正爬取的信息。
存量数据一次性维护后，定期维护增量数据，工作量不大。
考虑其他平台，如京东，数据可以统一整合

基于此，应该可以做些有意思的分析。

*/

class ApiClass
{
	function __construct(){
		header('Access-Control-Allow-Origin:*'); // 指定允许其他域名访问  
		header('Access-Control-Allow-Methods:POST,GET'); // 响应类型   
		header('Access-Control-Allow-Headers:x-requested-with,content-type');  // 响应头设置 
		header("Content-type:text/html;charset=utf-8");
		$myfile = fopen(time().".csv", "w") or die("Unable to open file!");
		$allstr="";
		$allstr .= 'id'.",";
		$allstr .= "".'itemUrl'.",";
		$allstr .= "".'status'.",";
		$allstr .= "".'title'.",";
		$allstr .= "".'picUrl'.",";
		$allstr .= "".'initialPrice'.",";
		$allstr .= "".'currentPrice'.",";
		$allstr .= "".'consultPrice'.",";
		$allstr .= "".'marketPrice'.",";
		$allstr .= "".'sellOff'.",";
		$allstr .= "".'start'.",";
		$allstr .= "".'end'.",";
		$allstr .= "".'timeToStart'.",";
		$allstr .= "".'timeToEnd'.",";
		$allstr .= "".'viewerCount'.",";
		$allstr .= "".'bidCount'.",";
		$allstr .= "".'delayCount'.",";
		$allstr .= "".'applyCount'.",";
		$allstr .= "".'xmppVersion'.",";
		$allstr .= "".'buyRestrictions'.",";
		$allstr .= "".'supportLoans'.",";
		$allstr .= "".'supportOrgLoan'.",";
		$allstr .= "".'status'.",";
		$allstr .="\r\n";
		fwrite($myfile, $allstr);
		//根据搜索条件，修改$url和$i; $i最大值是搜索结果最大页面；默认$url爬取了北京地区、已结束的所有房产拍卖信息
		for( $i=1;$i<=112;$i++){
			$url = "https://sf.taobao.com/list/50025969__2__%B1%B1%BE%A9.htm?spm=a213w.7398504.pagination.3.Nd3FUl&auction_start_seg=-1&page=".$i;
			$tmpa=$this->get_data($url);
			fwrite($myfile, $tmpa);
		}
		fclose($myfile);
	}


	/*table 格式返回数据*/
	function table($data=null) {
		date_default_timezone_set('PRC');
		$str="";
		$str .= "".$data->{'id'}.",";
		$str .= "http:".$data->{'itemUrl'}.",";
		$str .= "".$data->{'status'}.",";
		$str .= "".$data->{'title'}.",";
		$str .= "".$data->{'picUrl'}.",";
		$str .= "".$data->{'initialPrice'}.",";
		$str .= "".$data->{'currentPrice'}.",";
		$str .= "".$data->{'consultPrice'}.",";
		$str .= "".$data->{'marketPrice'}.",";
		$str .= "".$data->{'sellOff'}.",";
		$str .= "".date("Y-m-d H:i:s",$data->{'start'}/1000).",";
		$str .= "".date("Y-m-d H:i:s",$data->{'end'}/1000).",";
		$str .= "".$data->{'timeToStart'}.",";
		$str .= "".$data->{'timeToEnd'}.",";
		$str .= "".$data->{'viewerCount'}.",";
		$str .= "".$data->{'bidCount'}.",";
		$str .= "".$data->{'delayCount'}.",";
		$str .= "".$data->{'applyCount'}.",";
		$str .= "".$data->{'xmppVersion'}.",";
		$str .= "".$data->{'buyRestrictions'}.",";
		$str .= "".$data->{'supportLoans'}.",";
		$str .= "".$data->{'supportOrgLoan'}.",";
		$str .= "".$this->get_status("http:".$data->{'itemUrl'}).",";
		$str .="\r\n";
		
		return $str;
	}
	
	/*获得详细数据*/
	function get_status($url){
		$allstr="";
		$ar = array();
		
		$a = file_get_contents($url);
		$a = mb_convert_encoding($a,"utf-8","gbk");
		//拍卖轮次
		if(preg_match("/【(.*?)卖(.*?)】/", $a, $arrtmp)){
			$ar[0] = $arrtmp[0];
		}else{
			$ar[0] = "none";
		}
		//详细信息
		if(preg_match("/<div id=\"J_desc\".*?\"\/\/(.*?)\">/", $a, $arrtmp)){
			$ar[1] = $this->get_desc("http://".$arrtmp[1]);
		}else{
			$ar[1] = "none";
		}
		
		return implode("|", $ar);
	}
	/*获取详情*/
	function get_desc($url){
		echo $url;
		$allstr="";
		$ar = array();
		
		$ao = file_get_contents($url);
		$ao = mb_convert_encoding($ao,"utf-8","gbk");
		
		$a = preg_replace("/<([a-zA-Z]+)[^>]*>/","",$ao);//正则替换函数
		$a = preg_replace("/<\/([a-zA-Z]+)[^>]*>/","",$a);//正则替换函数
		$a = preg_replace("/&nbsp;/","",$a);//正则替换函数
		
		//echo $a;
		//面积
		if(preg_match("/[0-9]*?\.[0-9]*?平方米/", $a, $arrtmp)){
			$ar[0] = $arrtmp[0];
		}else{
			$ar[0] = "none";
		}
		
		//租赁
		if(preg_match("/.{15}租赁.{15}/", $a, $arrtmp)){
			$ar[1] = $arrtmp[0];
		}else{
			$ar[1] = "none";
		}
		//腾空
		if(preg_match("/.{15}腾空.{15}/", $a, $arrtmp)){
			$ar[2] = $arrtmp[0];
		}else{
			$ar[2] = "none";
		}
		
		$b = preg_replace("/<([a-zA-Z]+)[^>]*>/","<\\1>",$ao);//正则替换函数
		//租赁-提取
		if(preg_match("/.{15}租赁.*?<td>(.*?)<\/td>/s", $b, $arrtmp)){
			$ar[3] = $arrtmp[1];
		}else{
			$ar[3] = "none";
		}
		$ar[3] = preg_replace("/<([a-zA-Z]+)[^>]*>/","",$ar[3]);//正则替换函数
		$ar[3] = preg_replace("/<\/([a-zA-Z]+)[^>]*>/","",$ar[3]);//正则替换函数
		$ar[3] = preg_replace("/&nbsp;/","",$ar[3]);//正则替换函数
		//腾空-提取
		if(preg_match("/.{15}腾空.*?<td>(.*?)<\/td>/s", $b, $arrtmp)){
			$ar[4] = $arrtmp[1];
		}else{
			$ar[4] = "none";
		}
		$ar[4] = preg_replace("/<([a-zA-Z]+)[^>]*>/","",$ar[4]);//正则替换函数
		$ar[4] = preg_replace("/<\/([a-zA-Z]+)[^>]*>/","",$ar[4]);//正则替换函数
		$ar[4] = preg_replace("/&nbsp;/","",$ar[4]);//正则替换函数
		return implode("|", $ar);
	}
	/*获得数据*/
	function get_data($url){
		$allstr="";
		
		$a = file_get_contents($url);
		
		if(preg_match("/\[{(.*?)}\]/", $a, $arr)){
			$b = $arr[0];//."}";
		}else{
			echo "none";
		}
		$b=str_replace("[", "", $b);
		$b=str_replace("]", "", $b);
		$arrtmp = explode("},{",$b);

		foreach ( $arrtmp as $tmpstr)
		{
			$tmpstr=str_replace("{", "", $tmpstr);
			$tmpstr=str_replace("}", "", $tmpstr);
			$tmpstr="{".$tmpstr."}";
			$tmpstr=mb_convert_encoding($tmpstr,"utf-8","gbk");

			$allstr = $allstr . $this->table(json_decode($tmpstr));
		}
				
		return $allstr;
	}
}
new ApiClass();