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
		footer {
	        position: fixed; 
	        bottom: -20px; 
	        left: 0px; 
	        right: 0px;
	        height: 80px; 
	    }

  </style>
  <body>
    

    
<div class="container">
	<div class="row">
		<div class="" style="float: left;width:50%;">
			<img width="250px" src="<?php echo base_path('html'); ?>/assets/images/BBA.png" alt="" style="">
		</div>
		<!--<div class="" style="float:right;line-height:17px;">
			<h4 style="margin-bottom:0px;color:#990100;">BBA Advertising Pvt Ltd.</h4>
			<p style="font-size:12px;">US West Coast 1110 112th Avenue NE <br>Suite 300C, Bellevue, WA. 98004<br>
				 Email: reach@billboardsamerica.com<br>Phone : 9550224488</p>
		</div>-->
		<div class="" style="float:right;line-height:17px;">
			<h4 style="margin-bottom:0px;color:#990100;">Advertising Marketplace</h4>
			<!--<p style="font-size:12px;">US West Coast 1110 112th Avenue NE <br>Suite 300C, Bellevue, WA. 98004<br>
				 Email: reach@billboardsamerica.com<br>Phone : 9550224488</p>-->	
			<!--<p style="font-size:11px;">629 Terminal Way Suite 1, <br>Costa Mesa, CA 92627<br>
				 Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>-->
				 
				<!--<p style="font-size:11px;">28832 Via Buena Vista, <br>San Juan Capistrano CA 92675<br>
				 Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p> -->
		</div>
	</div>
	<br />
	<br />
	
	
	
	<div class="row">
		<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
		<br/><br/><br/><br/>
			<h4 style="float:left;width:100%;color:#990100;letter-spacing:1px;">Campaign Name: <?php echo ucfirst($campaign_details->name); ?></h4>
			<br>
			<!--	<h4 style="float:left;width:100%;color:#990100;letter-spacing:1px;">Campaign ID: <?php echo ucfirst($campaign_details->cid); ?></h4>-->

			<br/><br/><br />
			<table>
				<tr>
				<th>S No.</th>
					<th style="text-align: left;">Date</th>
					<th>Mode of Payment</th>
					<th>Reference No.</th>
					<th>Received By</th>
					<th>Price Received $</th>
				</tr>
				<?php
                            $i = 1;
                            foreach ($campaign_payments as $payments) {
								$totalamount=$campaign_details['totalamount'];
								$amount=$payments['amount'];
                                ?>
                                <tr align="center" style="font-size: 11px;">
                                    <td><?php echo $i; ?></td>
                                    <td style="text-align: left;"><?php echo $payments['created_at']; ?></td>
                                    <!--<td><?php //echo $payments['type']; ?></td>-->
                                    <td><?php echo $payments['brand']; ?></td>
                                    <td><?php echo $payments['payment_method']; ?></td>
                                    <td><?php echo $payments['received_by']; ?></td>
									<?php if($user_mongo['user_type'] == 'owner'){ ?>
                                    <td style="text-align: right;"><?php if($new_stripe_percent_amount < 1){ echo number_format($total_paid, 2); }else{ echo number_format($new_stripe_percent_amount,2); } ?></td>
									<?php }else{ ?>
									<td style="text-align: right;"><?php echo number_format($payments['amount'], 2); ?></td>
									<?php } ?>
                                </tr>
                                <?php
                                $i++;
                            }
                            ?>
				<tr>
					<td colspan="4"></td>
					<td class="border_1" style="text-align: right;"><b>Tax</b> </td>
					<td style="text-align: right;"><?php echo number_format($campaign_details->tax_percentage_amount_total, 2); ?></td>
				 </tr>
				<tr>
					<td colspan="4"></td>
					<td class="border_1" style="text-align: right;"><b>Gross</b> </td>
					<td style="text-align: right;"><?php echo number_format($campaign_details->gross_fee_price, 2); ?></td>
				 </tr>
				 <tr>
					<td colspan="4"></td>
					<td class="border_1" style="text-align: right;"><b>Total</b> </td>
					<td style="text-align: right;"><?php echo number_format($totalamount-$campaign_details->tax_percentage_amount_total, 2); ?></td>
				 </tr>
				 <tr>
					<td colspan="4"></td>
					<td class="border_1" style="text-align: right;"><b>Credit card fee</b> </td>
					<td style="text-align: right;"><?php echo number_format($campaign_details->newprocessingfeeamtSum, 2); ?></td>
				 </tr>
				<tr>
					<td colspan="4"></td>
					<td class="border_1" style="text-align: right;"><b>Grand Total</b> </td>
					<td style="text-align: right;"><?php echo number_format($totalamount+$campaign_details->newprocessingfeeamtSum+$campaign_details->gross_fee_price, 2); ?></td>
				 </tr>
				 
				 <tr>
					<td colspan="4"></td>
					<td class="border_1" style="text-align: right;"><b>Paid</b> </td>
					<td style="text-align: right;"><?php echo number_format($total_paid, 2); ?></td>
				 </tr>
				
				<tr>
					<td colspan="4"></td>
					<!--<td class="border_1" style="color:#990100;"><b>Pending</b> </td>-->
					<td class="border_1" style="text-align: right;"><b>Balance</b> </td>
					<td style="text-align: right;"><?php echo number_format($total_pending, 2); ?></td>
				 </tr>
				
			</table>
			</div>
	</div>
	
	
	<!--<p style="color:#990100;">BBI Advertising Pvt Ltd.</p>-->
	
			 <footer><p style="color:#990100;">Advertising Market Place, LLC</p><p>All Rights Reserved</p></footer>
	
</div>
</body>
</html>