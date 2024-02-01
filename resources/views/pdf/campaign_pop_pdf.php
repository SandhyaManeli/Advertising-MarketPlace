<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <!-- Bootstrap CSS -->
    </head>
<style>
	.container{
	font-family: sans-serif;
	}
	@media {
		body {-webkit-print-color-adjust: exact;}
		}
	.proof{
	background: #990100;padding: 9px;font-size: 22px;color: #fff;padding-right: 70px;padding-left: 5px;
	}	
	.table_full{
		width:100%;
	}
	table, th, td {
		border-bottom: 1px solid black;
		border-collapse: collapse;
	}
	td{
		padding: 8px;
		font-size: 12px;
	}
	.border_1{
		border: none!important;
	}
	.tables{
	  width: 100%;
	  font-weight:500;
	}
	.tables_left{
	  float: left;
	  width: 50%;
	}
	.tables_left table{
	  width: 100%;
	}
	.tables_left table, th, td{
	  border-bottom: 1px solid black;
	  border-collapse: collapse;
	}
	.tables_right{
	  float: left;
	  width: 50%;
	}
	.tables_right table{
	  width: 100%;
	}
	.tables_left_1{
		  padding: 23px;
		  padding-left: 0px;
	}
	.text_td{
		text-align:right;
		font-weight: bold;
	}
	.table_full tr th{
	  background-color:#000;
	  color:#fff;
	  padding:10px;
	  font-size:12px;
	}
	.table_full tr.tr_color{
	  background-color:#e4e4e4;
	}
	.table_full tr th td{
		border: none!important;
	}
	.footer{
		width: 100%;
	}
	.footer_left{
	  float: left;
	  width: 50%;
	}
	.footer_right{
	  float: left;
	  width: 50%;
	}
</style>
  <body>
   

<div class="container">

	<div class="row">
		<div class="" style="float: left;width:50%;">
			<h1 style="margin-top:30px;margin-bottom:10px;color:#990100;">TMC Media Sales</h1>
		</div>
		<br/>
		<!--<div class="" style="float:right;line-height:17px;">
			<h4 style="margin-bottom:0px;color:#990100;">BBA Advertising Pvt Ltd.</h4>
			<p style="font-size:12px;">US West Coast 1110 112th Avenue NE <br>Suite 300C, Bellevue, WA. 98004<br>
				 Email: reach@billboardsamerica.com<br>Phone : 9550224488</p>
		</div>-->
		<div class="" style="float:right;line-height:17px;">
			<h4 style="margin-bottom:0px;color:#990100;">Advertising Marketplace</h4>
			<!--<p style="font-size:11px;">629 Terminal Way Suite 1, <br>Costa Mesa, CA 92627<br>
				 Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>-->
				 
				<p style="font-size:11px;">28832 Via Buena Vista, <br>San Juan Capistrano CA 92675<br>
				 Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>
		</div> 
		<br/><br/><br/><br/><br/><br/>
	</div>
	<hr style="width:100%;">
	<span class="proof" style>Proof-of-Performance Report</span><br/><br/>
	
	<div class="tables">
             <div class="tables_left">
                 <div class="tables_left_1">
                     <table>
						<tr>
							<td>Report Start Date</td>
							<td class="text_td"><?php echo $campaign->startDate;?></td>
						</tr>
						<tr>
							<td>Report End Date</td>
							<td class="text_td"><?php echo $campaign->endDate;?></td>
						</tr>
						<tr>
							<td>Product Type</td>
							<td class="text_td">Digital</td>
						</tr>
						<tr>
							<td>Number Displays</td>
							<td class="text_td"><?php echo count($poparray); ?></td>
						</tr>
						<tr>
							<td>Weeks in this POP</td>
							<td class="text_td"><?php echo $campaign->weeks; ?></td>
						</tr>		
					</table>
                 </div>
             </div>
		
             <div class="tables_right">
                 <div class="tables_left_1">
                     <table>
						<!--<tr>
							<td>Agency</td>
							<td class="text_td">Allied</td>
						</tr>-->
						<tr>
							<td>Advertiser</td>
							<td class="text_td"><?php echo $campaign->first_name.' '.$campaign->last_name ;?></td>
						</tr>
						<tr>
							<td>Client Contact</td>
							<td class="text_td"><?php echo $campaign->phone ;?></td>
						</tr>
						<tr>
							<td>Account Executive</td>
							<td class="text_td">Richard Mcclemmy</td>
						</tr>
						<tr>
							<td>Program Start Date</td>
							<td class="text_td"><?php echo $campaign->startDate;?></td>
						</tr>
						<tr>
							<td>Program End Date</td>
							<td class="text_td"><?php echo $campaign->endDate;?></td>
						</tr>		
					</table>
                 </div>
             </div>
         </div>
		 		<br/><br/><br/><br/><br/><br/><br/><br/>	<br/><br/><br/><br/>
			<table class="table_full" style="margin-top:30px;">
				<tr>
					<th style="text-align:left;width:10%;">Display</th>
					<th style="width:30%;">Thumbnail</th>
					<th style="width:15%;text-align: center;">Spots Gauranteed</th>
					<th style="width:15%;text-align: center;">Spots Delivered</th>
					<th style="width:10%;text-align: center;">Variance</th>
					<th style="width:10%;text-align: end;">Variance %</th>
				</tr>
				<?php
                         $total = 0;
						 foreach ($poparray as $pop) {
							
                                ?>
				<tr>
					<td style="text-align:left;width:10%;"><?php echo $pop['siteNo'] .'-'. $pop['title'];?> </td>
					<td style="width:30%;text-align: center;"><img src="<?php echo base_path('html').$pop['image'][0];?>" alt=""style="border: 1px solid #990100;width:125px; height:75px;" /></td>
					<td style="width:15%;text-align: center;"><?php echo $pop['acctual_spots']; ?></td>
					<td style="width:15%;text-align: center;"><?php echo $pop['deliverdSpots']; ?></td>
					<td style="width:10%;text-align: center;"><?php echo $pop['varience']; ?></td>
					<td style="width:10%;text-align: end;"><?php echo round($pop['varience_percentage'],2); ?></td>
				</tr>
							<?php 
							$total += $pop['deliverdSpots']; } ?>
				
			</table>
			<h4 style="text-align:end;margin-bottom:10px;color:#990100; float:right;">Total <span style="padding-left:30px;"><?php echo $total; ?></span></h4>
			<br />
			<p style="text-align:justify;font-size:12px;">The information in this report is according to TMC records, which may have been provided by a third party, and complies with the American Association of Advertsing Agencies completion report standards. If you need any additional informtion regarding the execution of this contract, please contact your representive. We appreciate your business and hope our service was satisfactory.</p>
	
	<div class="footer">
		<div class="footer_left">
		<p style="font-size: 12px;"><b>Date: </b><span><?php echo $current_date; ?></span></p>
		<p style="font-size: 12px;"><b>Comments: </b></p>
		</div>
		<div class="footer_right"> 
		<p style="text-align: end;margin-bottom: 0px;"></p><br/>
		<p style="text-align: end;padding-right:20px;margin-top: 0px;font-size: 12px;"><b>Signature</b></p>
		</div>
	</div>
	
	
</div>
</body>
</html>