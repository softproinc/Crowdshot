<?php 
require_once('../inc/crowdshot-db-apis.php');
	header("Content-Type:application/json");	
	// If method is passed and it exists, then call it.
	if(isset($_POST['method']) && !empty($_POST['method'])){
		if(function_exists($_POST['method'])){
			$_POST['method']($_POST['vdata'],$_POST['video_id']);
		}
		else{
			deliver_response(404,"Invalid Request","NA");
		}
	}
	
function get_asset_image($asset_id = '') {
	//***** Temporary Code - Start
	global $TABLE_ASSET;

	if ($asset_id) {
		$select_sql = sprintf("select id, asset_type, asset_url, asset_properties, asset_status, created_datetime, created_by from %s where id = %s", $TABLE_ASSET, $asset_id);

		$asset = DB::queryFirstRow($select_sql);

		return $asset['asset_url'];
	} else {
		return FALSE;
	} // if ($asset_id) else
	//***** Temporary Code - End
} // function get_asset
	
	function generatevideo($data,$video_id)
	{
	 
	//$rootpath=$_SERVER['DOCUMENT_ROOT']."/crowdshota02/";
	//$rootpath=realpath(dirname(__FILE__));
	//$instancePath="http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
	//$instancePath=$_SERVER['SERVER_NAME']."/crowdshota02/";
	
	$xml_data=""; 
	$fname="Content".time();
	$newFolderName="Content/".$fname;		
	 
	mkdir('../'.$newFolderName, 0755, true);
	copy('../'.'videoengine/Templates/1/Main_2.swf', '../'.$newFolderName.'/Main_2.swf');
	copy('../'.'videoengine/Templates/1/video.php', '../'.$newFolderName.'/video.php');
	copy('../'.'videoengine/Templates/1/preview.swf', '../'.$newFolderName.'/preview.swf');
	copy('../'.'videoengine/Templates/1/preview.php', '../'.$newFolderName.'/preview.php');
	//mkdir($rootpath.$newFolderName.'/images', 0755, true);
	mkdir('../'.$newFolderName.'/Snapshots', 0755, true);	
	//$imagesfolder=$rootpath.$newFolderName.'/images/';

 	
	$j_data = json_decode($data);
	 
	//  print_r($j_data);	
	   
	//$xml_data.='<?xml version="1.0" ? >';
	$bgaudio=get_asset_image($j_data->music->asset_id);
	$xml_data.='<timeline>';
	$xml_data.= '<bgaudio>'.get_asset_image($j_data->music->asset_id).'</bgaudio>
		<config><instancePath>'.$instancePath.'</instancePath></config>';
	
	FileUpload($bgaudio,$rootpath.$newFolderName.'/Snapshots/','bgaudio.mp3');
	
 	foreach($j_data->timeline as $timeline)
	{ 
		
		$sequence= $timeline->sequence;
		$type = $timeline->type;
		$properties_type=$timeline->properties->type;
		$xml_data.= '<block sequence="'.$sequence.'"><type>'.$type.'</type><properties>';
		
		$branded_title='0';
		if($properties_type=='branded_title')
		{
			//$properties_type='title';
			//$branded_title=1;
			
		}
		
		$xml_data.='<type>'.$properties_type.'</type>';  
		  if($properties_type=='album_cover')
			{
				//$xml_data.='<type>'.$properties_type.'</type>';
				$xml_data.='<background>
					<type></type>
					<color></color>
					<assetId></assetId>
					<IsLocked></IsLocked>
					<imgPath></imgPath>
				</background>
				<content>
              <type>image</type>
              <assetId></assetId>
              <IsLocked></IsLocked>
              <imgPath>'.get_asset_image($timeline->properties->content[0]->asset_id).'</imgPath>
            </content>';
}
else if($properties_type=='branded_caption_shot')
{	
//$xml_data.='<type>'.$properties_type.'</type>';
$xml_data.='<background>
					<type>'.$properties_type.'</type>
					<color></color>
					<assetId></assetId>
					<IsLocked></IsLocked>
					<imgPath></imgPath>
			</background>
			<content>
              <type>shot</type>
              <assetId></assetId>
              <IsLocked></IsLocked>
              <imgPath>'.get_asset_image($timeline->properties->content[0]->asset_id).'</imgPath>
            </content>
            <content>
              <type>text_with_logo</type>
              <assetId></assetId>
              <IsLocked></IsLocked>
              <logoImgPath>'.get_asset_image($timeline->properties->content[1]->asset_id).'</logoImgPath>
              <text>'.$timeline->properties->content[2]->text.'</text>
              <vertical_alignment></vertical_alignment>
              <horizontal_alignment></horizontal_alignment>
              <color></color>
              <logo_placement></logo_placement>
            </content>';
}
else if($properties_type=='shot')
{	
//$xml_data.='<type>'.$properties_type.'</type>';
$xml_data.='<background>
				<type>'.$properties_type.'</type>
					<color></color>
					<assetId></assetId>
					<IsLocked></IsLocked>
					<imgPath></imgPath>
			</background>
			<content>
              <type>shot</type>
              <assetId></assetId>
              <IsLocked></IsLocked>
              <imgPath>'.get_asset_image($timeline->properties->content[0]->asset_id).'</imgPath>
            </content>
            <content>
              <type>text_without_logo</type>
              <IsLocked></IsLocked>
              <text></text>
              <vertical_alignment></vertical_alignment>
              <horizontal_alignment></horizontal_alignment>
              <color></color>
            </content>';
}
 else if($properties_type=='title')
{
//$xml_data.='<type>'.$properties_type.'</type>';
$xml_data.='<background>
					<type>'.$properties_type.'</type>
					<color></color>
					<assetId></assetId>
					<IsLocked></IsLocked>
					<imgPath></imgPath>
			</background>
			<content>
				<type>text_without_logo</type>
				<assetId></assetId>
				<IsLocked></IsLocked>
				<text>'.$timeline->properties->content[$branded_title]->text.'</text>
				<vertical_alignment></vertical_alignment>
				<horizontal_alignment></horizontal_alignment>
				<color></color>
            </content>';
				
		}
		else if($properties_type=='branded_call_to_action')
		{
		//$xml_data.='<type>'.$properties_type.'</type>';
		$xml_data.='<background>
				<type>'.$properties_type.'</type>
					<color></color>
					<assetId></assetId>
					<IsLocked></IsLocked>
					<imgPath>'.get_asset_image($timeline->properties->background->asset_id).'</imgPath>
				</background>
				<content>
				<type>textarea</type>
				<activityTitle>'.$timeline->properties->content[1]->text.'</activityTitle>
				<activityDate>'.$timeline->properties->content[2]->text.'</activityDate>
				<activityLocation>'.$timeline->properties->content[3]->text.'</activityLocation>
				<vertical_alignment></vertical_alignment>
				<horizontal_alignment></horizontal_alignment>
				<IsLocked></IsLocked>				
		</content>
		<content>
			<type>link</type>
			<link>'.$timeline->properties->content[4]->text.'</link>				
			<IsLocked></IsLocked>
		</content>
		<content>
			<type>branded_link_label</type>
			<branded_link_label_placement></branded_link_label_placement>
			<logo>
				<include_logo></include_logo>
				<logoImgPath>'.get_asset_image($timeline->properties->content[0]->asset_id).'</logoImgPath>
				<IsLocked></IsLocked>
			</logo>
			<link_labels>
				<text>'.$timeline->properties->content[6]->text.'</text>
				<text_placement></text_placement>
				<IsLocked></IsLocked>
			</link_labels>
			<link_labels>
				<text>'.$timeline->properties->content[7]->text.'</text>
				<text_placement></text_placement>
				<IsLocked></IsLocked>
				</link_labels>
				</content>';
		}
		$xml_data.='</properties></block>';
	}
	
	$xml_data.="</timeline>";
	// echo $xml_data;
	 $xmlobj=new SimpleXMLElement($xml_data);
	 $xmlobj->asXML('../'.$newFolderName."/test.xml");
	 
	  $xml_user_data="<user_data>
<id></id>
<user_id></user_id>
<email_address></email_address>
<folder_name></folder_name>
<created_on></created_on>
</user_data>";

	 $xmluserobj=new SimpleXMLElement($xml_user_data);
	 $xmluserobj->asXML('../'.$newFolderName."/info.xml");
	echo $fname;
}

function FileUpload($file,$location,$filename)
{
	if($file<>"")
	{
		//$file_name=basename($file);
		//$file_ext=explode(".",$file_name);		
		//$new_filename=$filename.'.'.$file_ext[1];
		//$current_folder_path=getcwd(); 
		 copy($file,$location.$filename);
		 return $filename;
		//return $file;
	}
	else
	{
			return "NA";
	
	}
}
function deliver_response($status,$status_message,$video_id){
	header("HTTP/1.1 $status $status_message");
	$response['status'] = $status;
	$response['status_message'] = $status_message;
	$response['video_id'] = $video_id;
	$json_response = json_encode($response);
	echo $json_response;
}

?>