<?php
include "pChart/pChart.class";
include "pChart/pData.class";

function wpr_cache_charts()
{
	global $wpdb;
	$results = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix."wpr_newsletters");
	foreach ($results as $row)
	{
		$nid = $row->id;
		generate_chart($nid);
	}
}

/*
   To Do:
   1. Add a vertical line for each newsletter mailout that is sent
   That way the user may knows if a particular mailout caused a lot of unsubscription

*/
function  generate_chart($nid)
{
	global $wpdb;
	$date = date("d");
	$time = time();
	$subscriptionsList = new pData; 
	$unsubscriptionsList = new pData; 
	$dateList = new pData;
	$time -= 2764800; //31 days in seconds
	for ($i=0;$i<=31;$i++)
	{
		$currTime = $time+($i*86400);
		$theDate = date("d",$currTime);
		$startOfDay = mktime(0,0,0,date("n",$currTime),$theDate,date("Y",$currTime));
		$endOfDay = $startOfDay + 86400;
		//fetch the number of subscriptions on that day
		$query = "SELECT count(*) num from ".$wpdb->prefix."wpr_subscribers where nid=$nid and date > $startOfDay and date < $endOfDay";
		$rows = $wpdb->get_results($query);
		$numberOfSubscriptions = $rows[0]->num;
		$subscriptionsList->AddPoint($currTime,"Date");
		$subscriptionsList->AddPoint($numberOfSubscriptions,"Subscriptions");
		$max = ($max < $numberOfSubscriptions)?$numberOfSubscriptions:$max;
		//fetch the number of unsubscriptions on that day
		$query = "SELECT count(*) num from ".$wpdb->prefix."wpr_subscribers where nid=$nid and unsubscription_date > $startOfDay and unsubscription_date < $endOfDay";
		$result = $wpdb->get_results($query);
	
		$numberOfUnsubscriptions = $result[0]->num;
		$max = ($max < $numberOfUnsubscriptions)?$numberOfUnsubscriptions:$max;
		$subscriptionsList->AddPoint($numberOfUnsubscriptions,"Unsubscriptions");
	
	}
	get_option("upload_path");
	$pluginBasePath = realpath(get_option("upload_path")."/../plugins/wpresponder");	
	$max = ($max <10)?10:$max;
	$subscriptionsList->AddAllSeries();
	$subscriptionsList->SetAbsciseLabelSerie("Date");
	$subscriptionsList->SetXAxisFormat("date");
	$theChart = new pChart(2200,400);
	$theChart->setFixedScale($min,$max);
	$uploadPath = get_option("upload_path");
	$pathToFontFile= "$pluginBasePath"."/arial.ttf";
	$theChart->setFontProperties($pathToFontFile,9);
	$theChart->setGraphArea(60,30,2180,380);
	$theChart->drawGraphArea(255,255,255,TRUE);
	$theChart->drawScale($subscriptionsList->GetData(),$subscriptionsList->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);   
	$theChart->drawLineGraph($subscriptionsList->GetData(),$subscriptionsList->GetDataDescription());   
	$theChart->drawLegend(75,35,$subscriptionsList->GetDataDescription(),255,255,255);   
	$theChart->drawPlotGraph($subscriptionsList->GetData(),$subscriptionsList->GetDataDescription(),3,2,255,255,255);
	$theChart->drawGrid(4,TRUE,230,230,230,50);
	$uploadPath= get_option("upload_path");
	$theFile = "$uploadPath/wpresponder/subscription_graph_".$nid.".png";
	$theChart->Render($theFile);
	
}
?>