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
   .for_scroll{
    height: 393px;
    overflow: auto;
 }
	table {
    		width:100%;
			font-weight:500;
			font-size:11px;
			border-collapse: collapse;
		}
		table, th {
    		border: 1px solid black;
			padding: 0px;
			text-align: center;
		}
		table, td {
    		border: 1px solid black;
			padding: 5px;
		}
		td{
		    padding: 5px;
			text-align: center;
		}
		.border_1{
			border: none!important;
		}
		.pay_now {
		  background-color: #BC2535;
		  border: none;
		  color: white;
		  padding: 10px 32px;
		  text-align: center;
		  text-decoration: none;
		  display: inline-block;
		  font-size: 12px;
		  margin: 4px 2px;
		  cursor: pointer;
		  font-weight:500;
		}

  </style>
  <body>
    

    
<div class="container">
	<div class="row">
		<div class="" style="float: left;width:50%;">
			<img width="250px" src="<?php echo base_path('html'); ?>/assets/images/BBA.png" alt="" style="">
		</div>
		<div class="" style="float:right;line-height:17px;">
			<!--<h4 style="margin-bottom:0px;color:#990100;">BBA Advertising Pvt Ltd.</h4>-->
			<h4 style="margin-bottom:0px;color:#990100;">Advertising Marketplace</h4>
			<!--<p style="font-size:12px;">US West Coast 1110 112th Avenue NE <br>Suite 300C, Bellevue, WA. 98004<br>
				 Email: reach@billboardsamerica.com<br>Phone : 9550224488</p>-->
			<!--<p style="font-size:11px;">629 Terminal Way Suite 1, <br>Costa Mesa, CA 92627<br>
				 Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>-->
				 
				<!--<p style="font-size:11px;">28832 Via Buena Vista, <br>San Juan Capistrano CA 92675<br>
				 Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>-->
		</div>
	</div>
	<br />
	<br />
	
	<!--<hr style="width:100%;">
	
	<div class="row">
		<div class="" style="float: left;width:50%;line-height:17px;">
			<h4 style="margin-bottom:10px;color:#990100;">To,</h4>
			<p style="font-size:12px;">James, <br>Zomato Pvt Ltd,<br>
				james@email.com<br>9876543210<p>
		</div>
		<div class="" style="float:right;">
			<h4 style="margin-bottom:10px;color:#990100;letter-spacing:1px;">Invoice</h4>
			<p style="font-size:12px;"><b>Invoice No:</b> #0215 / <b>Date:</b> 13-06-2019</p>
		</div>
	</div>-->
	
	<div class="row">
		<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
		<br/><br/><br/><br/>
			<h4 style="float:left;width:100%;color:#990100;letter-spacing:1px;">Campaign Name: <?php echo ucfirst($campaign->name); ?></h4>
			<br/><br/><br />
			
				<!-- <table>
				<tr> 			 
					 <td class="border_1" style="color:#990100; border-right-style: hidden; padding-left:10px;"><b>Impressions Total -</b><b>  <?php //echo number_format($campaign_impressionSum, 2);?></b></td>
					 <td class="border_1" style="color:#990100; border-right-style: hidden;"><b>CPM Total -</b><b>  $<?php //echo sprintf('%.2f', $campaign_cpmval); ?></b></td>
					 <td class="clr text-center" style="color:#990100;"><b><?php //echo '$'.$campaign_cpmval;?></b></td>
					 <td class="border_1" style="color:#990100; border-right-style: hidden;"><b>Total -</b>
					 <b> $<?php //echo number_format($price, 2);?></b></td>	
				</tr>
			
			</table>--> 
		</div>
	</div>
	 
	<!--<div style="margin-top:20px">
		<p style="font-size:12px;"><b>Total Number of Area Covered:</b> <?php //echo $areas_covered; ?></p>
		<p style="font-size:12px;"><b>No. of Mediums Types Used:</b> <?php //echo $format_types; ?>, No. of Mediums Covered: <?php //echo $mediums_covered; ?></p>
		<p style="font-size:12px;"><b>Reach of Audiences:</b> <?php //echo $audience_reach; ?>/week approx. (<?php //echo $audience_reach * 4; ?>/Month approx.)</p>
		
	</div>-->
	
	<!--<p class="mt-3 text-center">The price provided in this Quotation has been tailor-made for your requirements.</p>--> 
	<!--<p class="">For further queries, Contact <b style="color:#990100;">[ Chanikya, CEO, Call: 9550224488 ]</b></p>-->
	
	<!--<p style="color:#990100;">BBI Advertising Pvt Ltd.</p>--> 
	Visit:www.advertisingmarketplace.com for more details.
	<p style="color:#990100;">Advertising Marketplace, LLC</p>
	<p>All Rights Reserved</p>
	
</div>
</body>
</html>
