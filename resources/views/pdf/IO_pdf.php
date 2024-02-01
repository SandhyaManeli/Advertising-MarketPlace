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
		 .pdf-alignment p{
        	font-size: 11px;
        	margin-top: 0px;
        	margin-bottom: 0px;
        }
        .page-break {
		    page-break-before: always;
		}
		.for_blue_clr{
    		color: #007bff;
		}
		.text_alignment p{
		   text-align: justify;
		}
		.bill-serv-txt p{
			font-size: 11px;
		}
		
		.pdf_bline{
		border-bottom: 1px solid red;
       padding-bottom: 5px;
		}
		.pdfnew_line{
			    color: #red;
    text-decoration: underline;
		}
		      
        .text_text {
    display: flex;
}

.underline {
    flex-grow: 1;
    border-bottom: 1px solid black;
    margin-left: 5px;
}
.des_text{
	text-align: justify;
}
  </style>
  
  <body>
 <div class="container">
 	<div class="row">
		<div class="" style="float: left;width:50%;">
			<img width="250px" src="<?php echo base_path('html'); ?>/assets/images/BBA.png" alt="" style="">
		</div>
		<div class="" style="float:right;line-height:15px;">
			<h4 style="margin-bottom:0px;color:#990100;">Advertising Marketplace</h4>
			<!--<p style="font-size:11px;">629 Terminal Way Suite 1, <br>Costa Mesa, CA 92627<br>
				 Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>-->
				 
				<!--<p style="font-size:11px;">28832 Via Buena Vista, <br>San Juan Capistrano CA 92675<br>
				 Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>-->
		</div>
	</div>
	<br />
	

	<div class="row">
		<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
			<br/><br/>
			<div class="row">
		<p><h4 style="text-align: center;color: #007bff;letter-spacing:1px;">Insertion Order</h4>
			<h4 style="color:#990100;letter-spacing:1px;font-size: 15px;float:right;">Date: <?php echo date('m-d-Y'); ?></h4></p>
			</div>
			<h4 style="float:left;width:100%;color:#990100;letter-spacing:1px;">Campaign Name: <?php echo ucfirst($campaign->name); ?> (<?php echo $campaign->cid; ?>)</h4>
			<br/>
			<table>
				<tr>
					<th>S No.</th>
					<!--<th style="text-align: left;padding:10px;">Tab ID</th>--> 
					<th style="text-align: left;padding:5px;">Product ID</th>
					<th>Type</th>
					<!--<th>Address</th>-->
					<th>DMA</th>
					<th>Size</th>
					<!--<th>Direction</th>--> 
					<th>Product Title</th>
					<th>Start Date</th>
					<th>End Date</th>
					<th>Unit Price<br>($)</th>
					<th>Quantity</th>
					<th>Tax amount<br>($)</th>
					<th>Net Total<br>($)</th>
					<?php if($campaign->offer_applied_res == 1){ ?>
					<th>New Price<br>($)</th>
					<?php } ?>
				</tr>
				<?php
                            $i = 1;
                            foreach ($products_arr as $product) {
                                ?>
                                <tr align="center" style="font-size: 10px;">
                                    <td><?php echo $i; ?></td>
                                    <td style="text-align: left;"><?php echo $product['siteNo']; ?></td>
                                    <td><?php echo $product['type']; ?></td>
                                    <!--<td><?php //echo $product['address']; ?></td>-->
                                    <!--<td><?php 
										//if(isset($product['address']) && !empty($product['address'])){
											//echo $product['address'];
										//}elseif(isset($product['addressone']) && !empty($product['addressone'])){
											//echo $product['addressone'];
										//}
									?></td>--> 
                                    <td><?php echo $product['city']; ?></td>
                                    <td><?php echo $product['panelSize']; ?></td>
                                    <!--<td><?php //echo $product['direction']; ?></td>--> 
                                    <td><?php echo $product['title']; ?></td>
                                  
								  <?php  $booked_from_zone = date('m-d-Y', strtotime($product['booked_from']));
										$booked_to_zone = date('m-d-Y', strtotime($product['booked_to']));
									if(!is_null($product['area_time_zone_type'])){
										$start_time = new DateTime( $product['booked_from'] );
										$end_time = new DateTime( $product['booked_to'] );
										$laTimezone = new DateTimeZone($product['area_time_zone_type']);
										$start_time->setTimeZone( $laTimezone );
										$end_time->setTimeZone( $laTimezone );
										$booked_from_zone = $start_time->format( 'm-d-Y' );
										$booked_to_zone = $end_time->format( 'm-d-Y' );								 
									} ?>
								  
									<td><?php echo $booked_from_zone; ?></td>
									<!--<td><?php //echo date('m-d-Y',strtotime($product['from_date'])); ?></td>--> 
									<td><?php echo $booked_to_zone; ?></td>
									<td><?php echo number_format($product['price'],2); ?></td>
									<td><?php if(isset($product['quantity']) && $product['quantity'] != 0 && $product['quantity'] != ''){ echo $product['quantity']; }else{ echo 'NA'; }?></td>
									<td><?php if(isset($product['tax_percentage'])){ echo '('.$product['tax_percentage'].'%)'; }else{ echo'(0%)'; } ?><br><?php echo $product['tax_percentage_amount']; ?></td>
									<!--<td><?php //echo date('m-d-Y',strtotime($product['to_date'])); ?></td>-->
                                    <!--<td><?php // if(isset($product['owner_price']) && $product['owner_price']!=''){ $price =$product['owner_price'];}else{$price =$product['price'];} echo $price;?></td>-->
                                    <td>$<?php  if(isset($product['owner_price']) && $product['owner_price']!='')
									{ 
										if(isset($product['quantity']) && $product['quantity'] != 0 && $product['quantity'] != ''){
										$price =$product['owner_price']*$product['quantity'];
										}else{
										$price =$product['owner_price'];	
										}
									}else{
										if(isset($product['quantity']) && $product['quantity'] != 0 && $product['quantity'] != ''){
										$price =$product['price']*$product['quantity'];
										}else{
										$price =$product['price'];	
										}
									} 
									echo number_format($price, 2);?></td>
									<?php if($campaign->offer_applied_res == 1){ ?>
													<td><?php echo number_format($product['offerprice'], 2); ?></td>
									<?php } ?>
                                </tr>
                                <?php
                                $i++;
                            }
                            ?>
					</table>
			<br/>
		</div>
		<!--<?php //foreach($client_details_billing as $key => $value){ ?>
		<div class="row">
		<div class="" style="width: 100%;"> 
		<div class="" style="width: 50%;float: left;">
		<p><b class="for_blue_clr">Billing:</b><?php //if($products_arr[$key]['billingNo']!=''){ ?> <?php //echo $value['name'] ;?><br>
		<b>Address</b> <?php // echo $value['address'] ;?><br>
		<b>Email</b> <?php //echo $value['email'] ;?><br>
		<b>Phone</b> <?php //echo $value['phone'] ; } ?>
		<?php //if($products_arr[$key]['billingYes']!=''){ ?> <?php //echo "<p>629 Terminal Way, Suite 1 <br>Costa Mesa CA 92627<br>
		//Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>" ;  } ?></p>
		</div>
		<div class="" style="width: 50%;float: left;">
		<p><b class="for_blue_clr">Servicing:</b> <?php //if($products_arr[$key]['servicingNo']!=''){ ?><?php //echo $value['name'] ;?><br>
		<b>Address</b> <?php //echo $value['address'] ;?><br>
		<b>Email</b> <?php //echo $value['email'] ;?><br>
		<b>Phone</b> <?php //echo $value['phone'] ; } ?>
		<?php// if($products_arr[$key]['servicingYes']!=''){ ?><?php //echo "<p>629 Terminal Way, Suite 1 <br>Costa Mesa CA 92627<br>
		//Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>" ;  } ?></p>
		</div>
		</div>
		</div>
		<?php// } ?>-->
	</div> 
	<div class="row">
			<div class="row">
				<div style="border:0px #000;border-style: double;border-width: 1px;" >	 	
					<table class="border_1">
						<tr style="width:100%"> 
							<!--<td class="border_1" style="font-size: 11px;font-weight: 100;width:60%;text-align: left;"><b>Terms:</b> <?php //echo number_format($paid_percentage, 2);?>% Due upon purchase (from purchase page)</td>-->
							<td class="border_1" style="font-size: 11px;font-weight: 100;width:60%;text-align: left;"><b>Terms:</b> <?php echo number_format($campaign->percentagevalue, 2);?>% Due upon purchase (from purchase page)</td>
							<td class="border_1" style="font-size: 11px;font-weight: 100;width:20%;text-align: right;"><b>Tax</b></td>
							<td class="border_1" style="font-size: 11px;font-weight: 100;width:20%;text-align: right;">$ <?php echo number_format($tax_percentage_amount_sum, 2);?></td>
						</tr> 
						<tr style="width:100%">
							<!--<td class="border_1" style="font-size: 11px;font-weight: 100;width:60%;text-align: left;"><?php //echo number_format(100-$paid_percentage, 2);?>% Due upon invoice</td>-->
							<td class="border_1" style="font-size: 11px;font-weight: 100;width:60%;text-align: left;"><?php echo number_format(100-$campaign->percentagevalue, 2);?>% Due upon invoice</td>
							<td class="border_1" style="font-size: 11px;font-weight: 100;width:20%;text-align: right;"><b>Total</b></td>
							<td class="border_1" style="font-size: 11px;font-weight: 100;width:20%;text-align: right;">$ <?php echo number_format($total_price, 2);?></td>
						</tr>
						<tr style="width:100%">
							<td class="border_1" style="font-size: 11px;font-weight: 100;width:60%;text-align: left;"><b>Art due date:</b> See Product details</td> 
							<td class="border_1" style="font-size: 11px;font-weight: 100;width:20%;text-align: right;"><b>Gross fee (<?php echo $campaign->gross_fee_percentage; ?> %)</b></td>
							<td class="border_1" style="font-size: 11px;font-weight: 100;width:20%;text-align: right;">$ <?php echo number_format($campaign->gross_fee_price, 2);?></td>
						</tr>
						<tr style="width:100%">
							<td class="border_1" style="font-size: 11px;font-weight: 100;width:60%;text-align: left;"><b>Restrictions:</b> See Product Details</td> 
							<td class="border_1" style="font-size: 11px;font-weight: 100;width:20%;text-align: right;"><b>Credit card Fee</b></td>
							<td class="border_1" style="font-size: 11px;font-weight: 100;width:20%;text-align: right;">$ <?php echo number_format($processing_fee_sum, 2);?></td>
						</tr>
						<tr style="width:100%">  
							<td class="border_1" style="font-size: 11px;font-weight: 100;width:60%;text-align: left;"><b>Cancellation:</b> See Product Details</td>
							<td class="border_1" style="font-size: 11px;font-weight: 100;width:20%;text-align: right;"><b>Grand Total</b></td>
							<td class="border_1" style="font-size: 11px;font-weight: 100;width:20%;text-align: right;">$ <?php echo number_format($total_price+$processing_fee_sum+$campaign->gross_fee_price+$tax_percentage_amount_sum, 2);?></td>
						</tr> 
						<!--<tr>  
							<td class="border_1" colspan="4" style="font-size: 12px;font-weight: 100;width:100%;text-align: cen;"><b>Terms:</b> <?php //echo number_format($paid_percentage, 2);?>% Due upon purchase (from purchase page)</td>
						</tr>
						<tr> 
							<td class="border_1" colspan="4" style="font-size: 12px;font-weight: 100;width:100%;text-align: right;"><?php //echo number_format(100-$paid_percentage, 2);?>% Due upon invoice</td>
						</tr>
						<tr> 
							<td class="border_1" colspan="4" style="font-size: 12px;font-weight: 100;width:100%;text-align: left;"><b>Art due date:</b> See Product details</td>
						</tr>
						<tr> 
							<td class="border_1" colspan="4" style="font-size: 12px;font-weight: 100;width:100%;text-align: left;"><b>Restrictions:</b> See Product Details</td>
						</tr>
						<tr> 
							<td class="border_1" colspan="4" style="font-size: 12px;font-weight: 100;width:100%;text-align: left;"><b>Cancelation:</b> See Product Details</td>
						</tr>-->
					</table> 
				</div>
			</div>
			<br>
			<div class="row">
				<div style="border:0px #000;border-style: double;border-width: 1px;font-size: 11px;" >	 	
					<table class="border_1">
						<tr>
							<td class="border_1" colspan="4" style="font-weight: 100;width:100%;text-align: left;">2.Addresses. All notices, correspondence and invoices shall be sent to the addresses below. </td>
						</tr>  
						<tr> 			 
							<td class="border_1" style="text-align: right;"><b>If to buyer: </b></td>
							<td class="border_1" style="text-align: left;"><b>  <?php echo $campaign->company_name;?> </b></td>
							<td class="border_1" style="text-align: right;"><b>If to AMP: </b></td>
							<td class="border_1" style="text-align: left;"><b>  Advertising Marketplace, LLC </b></td>
						</tr>
						<tr> 			 
							<td class="border_1" style="text-align: right;"><b>Address: </b></td>
							<td class="border_1" style="text-align: left;"> <span style="border-bottom:1px solid #000;"><?php if(isset($campaign->address)){ echo $campaign->address;} else { echo '';}?></span></td>
							<td class="border_1" style="text-align: right;"> </td>
							<td class="border_1" style="text-align: left;"><b> 28832 Via Buena Vista </b></td> 
						</tr>
						<tr> 			 
							<td class="border_1" style="text-align: right;"></td>
							<td class="border_1" style="text-align: left;"> <span style="border-bottom:1px solid #000;"><?php if(isset($user_mongo_report['city'])){ echo $user_mongo_report['city']; }elseif(isset($user_mongo_report['city_name'])){ echo $user_mongo_report['city_name']; }else{ echo''; }?></span></td>
							<td class="border_1" style="text-align: right;"></td>
							<td class="border_1" style="text-align: left;"><b> San Juan Cap, CA 92675 </b></td> 
						</tr>
						<tr> 			 
							<td class="border_1" style="text-align: right;"><b>Attn: </b></td>
							<td class="border_1" style="text-align: left;"> <span style="border-bottom:1px solid #000;"><?php echo $campaign->first_name;?></span></td>
							<td class="border_1" style="text-align: right;"></td>
							<td class="border_1" style="text-align: left;"><b> Attn: Richard McClemmy </b></td> 
						</tr>
						<tr> 			 
							<td class="border_1" style="text-align: right;"><b>Email: </b></td>
							<td class="border_1" style="text-align: left;"> <span style="border-bottom:1px solid #000;"><?php echo $campaign->email;?></span></td>
							<td class="border_1" style="text-align: right;"></td>
							<td class="border_1" style="text-align: left;"><b>mcclemmy.richard@gmail.com</b></td> 
						</tr> 
						<tr>
							<td class="border_1" colspan="4" style="font-weight: 100;width:100%;text-align: left;">3. The attached Standard Terms and Conditions (“Standard Terms”) are incorporated as part of this Agreement, and Sponsor
									  acknowledges that Sponsor has reviewed the Standard Terms and agrees to be bound by the provisions thereof. Should the
									  <!--Standard Terms conflict with the Main Agreement above then the Main Agreement shall govern</td>-->
									  Standard Terms conflict with the Main Agreement above then the IO shall govern</td>
						</tr>
						<tr>
							<td class="border_1" colspan="4" style="font-weight: 100;width:100%;text-align: left;">4. This Agreement, once executed is not cancelable except by expiration or termination pursuant to the Standard Terms and Conditions.IN WITNESS WHEREOF, the parties, through their representatives, have executed this Agreement.
							</td>
						</tr>
						<tr> 			 
							<td class="border_1" colspan="1" style="border-right-style: hidden;"><b>Buyer: </b></td>
							<td class="border_1" colspan="1" style="border-right-style: hidden;"><b>AMP</b></td>
							<td class="border_1" colspan="1" style="border-right-style: hidden;"><b>Seller</b></td>
						</tr>
						<tr> 			 
							<td class="border_1" colspan="1" style="border-right-style: hidden;"><b>By: </b><span style="text-decoration: underline; white-space: pre;"><?php echo $user_mongo_report['first_name'];?></span></td>
							<td class="border_1" colspan="1" style="border-right-style: hidden;"><b>BY: </b><span style="text-decoration: underline; white-space: pre;">Richard McClemmy</span></td>
							<td class="border_1" colspan="1" style="border-right-style: hidden;"><b>BY: </b><span style="text-decoration: underline; white-space: pre;">Per User Agreement</span></td>
						</tr>
						<tr> 			 
							<td class="border_1" colspan="1" style="border-right-style: hidden;"><b>Date: </b><span style="text-decoration: underline; white-space: pre;"><?php echo date('m-d-Y'); ?></span></td>
							<td class="border_1" colspan="1" style="border-right-style: hidden;"><b>Date: </b><span style="text-decoration: underline; white-space: pre;"><?php echo date('m-d-Y'); ?></span></td>
							<td class="border_1" colspan="1" style="border-right-style: hidden;"><b>Date: </b><span style="text-decoration: underline; white-space: pre;"><?php echo date('m-d-Y'); ?></span></td>
						</tr>
						<tr> 			 
							<td class="border_1" colspan="1" style="border-right-style: hidden;"><b>Its: </b><span style="text-decoration: underline; white-space: pre;"></span></td>
							<td class="border_1" colspan="1" style="border-right-style: hidden;"><b>Its: </b><span style="text-decoration: underline; white-space: pre;"></span></td>
							<td class="border_1" colspan="1" style="border-right-style: hidden;"><b>Its: </b><span style="text-decoration: underline; white-space: pre;"></span></td>
						</tr>
					</table> 
				</div>
			</div>
	</div>
		<div class="row">
				<?php
				$temp = array_unique(array_column($products_arr, 'siteNo'));
				$unique_arr = array_intersect_key($products_arr, $temp);
				foreach($unique_arr as $key => $value){
					$j = 0;
					foreach ($products as $product) {
						if($value['siteNo'] == $product['siteNo']){
						$rateCard=$product['rateCard'];
						//$cpm=$product['cpm'];
						//$prod_addr = isset($product->address) && !empty($product->address) ? $product->address : ""; 
						$prod_addr = isset($product->addressone) && !empty($product->addressone) ? $product->addressone : "";

							//$page_break = 'page-break';$pb = $j+1;
						?>
						<div class="page-break">
						<!--<p style="text-align:center;"><?php //echo ($j + 1) . ". " .ucfirst($prod_addr); ?> </p>--> 
						<h4 style="margin-bottom:5px;color:#990100;margin-top:0px;">Product Details</h4>
						
						<div class="" style="width: 100%; margin-top:0px; font-size: 11px;">
							<div class="pdf-alignment">	
								<p class=""><b>AMP Product:</b> <?php echo $product['siteNo'] ;?></p>
								<p class=""><b>Type:</b> <?php echo $product['type'] ;?></p>
								<p class=""><b>Title:</b> <?php echo $product['title'] ;?></p>
								<p class="des_text"><b>Description:</b> <?php echo $product['description'] ;?></p>
				<!--<p class=""><b>Description:</b> 
				<?php //if(strlen($product['description']) > 45){
					//echo substr($product['description'],0,45) . " ...";
				//}
				//else{
					//echo $product['description']; 
				//} ?></p>-->
				</div>
				</div>
											
						
				<div class="" style="width: 100%; font-size: 11px;">	
				<div class="" style="width: 50%; float: left;margin-top:0px;">				
				<div class="pdf-alignment textb_clr">					
				
				<p class=""><b>Address 1:</b> <?php echo $product['address'] ;?> </p>
				<p class=""><b>Address 2:</b> <?php echo $product['city'] ;?>,<?php echo $product['state'] ;?>,<?php echo $product['zipcode'] ;?></p>
				
				<p class=""><b>Country:</b> <?php echo $product['country_name'] ;?> </p>
                <p class=""><?php if($product['locationDesc']!=''){ ?><b>Location Description:</b> <?php echo $product['locationDesc'];  } ?></p>
				<p class=""> <b>Unit Quantity:</b> <?php echo $product['unitQty']; ?></p>
			
				<p class=""><b>Height:</b> <?php echo $product['height'] ;?> <b>Width:</b> <?php echo $product['width'] ;?></p>
				<!--<p class=""><?php //if($product['length']!=''){ ?><b>Length:</b> <?php //echo $product['length'] ; } ?></p>-->
				<p class=""><b>Panel Size:</b> <?php echo $product['panelSize'] ;?> </p>
				<p class=""><b>Available 4-week Periods:</b> <?php echo sprintf('%.2f', $product['weekPeriod']) ;?> <b>Rate Card (4 weeks):</b> $<?php echo number_format($rateCard, 2) ;?>  </p>
				<p class=""><b>Fixed/Variable:</b> <?php echo $product['fix']; ?> <?php if($product['minimumdays']!=''){ ?><b>Minimum Days:</b> <?php echo $product['minimumdays'];  } ?>  </p>
				<p class=""><b>Installation Cost:</b> $<?php echo $product['installCost'] ;?> <b>Production Cost:</b> $<?php echo $product['productioncost'] ;?>  </p>
				<p class=""><b>Product Direction:</b> <?php echo $product['imgdirection'] ;?></p>
			
				<!--<p class=""><?php //if($product['lighting']!=''){ ?><b>Lighting:</b> <?php //echo $product['lighting'];  } ?></p>-->
				<p class=""><?php if($product['lighting']!=''){ ?><b>Illumination:</b> <?php echo $product['lighting'];  } ?>  </p>
				<?php if($product['direction']!=''){ ?><p class=""><b>Facing:</b> <?php echo $product['direction'] ; ?> </p><?php } ?>
				
				<p class=""><b>Latitude:</b> <?php echo $product['lat'] ;?>  <b>Longitude:</b> <?php echo $product['lng'] ;?></p>
				
				<!--<p class=""><b>Restrictions:</b> <?php //echo $product['notes']; ?></p>-->
				<p class=""><b>Restrictions:</b> 
				<?php 
					echo $product['notes']; 
				 ?></p>
				<p class=""><b>Cancellation Policy:</b> 
				<?php 
					echo $product['cancellation_policy']; 
				 ?></p>
				<p class=""><b>Payment Terms:</b> 
				<?php 
					echo $product['cancellation_terms']; 
				 ?></p>
				<!--<p class=""><b>Cancellation Policy:</b> <?php //echo $product['cancellation_policy']; ?></p>-->
				<p class=""><b>Median HHI:</b> $<?php echo $product['mediahhi']; ?></p>
				<p class=""><b>Impression 18 - 49:</b> <?php echo $product['firstImpression']; ?> </p>
				<p class=""> <b>CPM Adults 18 - 49:</b> $<?php echo sprintf('%.2f', $product['firstcpm']); ?></p>
				
				<p class=""><b>Impression 18 - Plus:</b> <?php echo $product['secondImpression'] ;?> </p>
				<p class=""> <b>CPM Adults 18 - Plus:</b> $<?php echo sprintf('%.2f', $product['cpm']) ;?></p>
				
				<p class=""><b>Impression 25 - 54:</b> <?php echo $product['thirdImpression']; ?>  </p>
				<p class=""><b>CPM Adults 25 - 54:</b> $<?php echo sprintf('%.2f', $product['thirdcpm']); ?></p>
				
				<p class=""><b>Impression (HA25 - HA54):</b> <?php echo $product['forthImpression']; ?> </p>
				<p class=""> <b>CPM Hispanic 25 - 54:</b> $<?php echo sprintf('%.2f', $product['forthcpm']); ?></p>
				
				<p class=""><b>Audited:</b> <?php echo $product['audited']; ?> <b>Seller Id:</b> <?php echo $product['sellerId']; ?></p>
				<p class=""><?php if($product['Comments']!=''){ ?><b>Comments:</b> <?php echo $product['Comments']; } ?></p>
			
				 
				<!--<p class=""><b>4-week Negotiated Net Cost:</b> $<?php //echo $product['negotiatedCost'] ;?></p>-->
				
				<!--<p class=""><b>Billing:</b><?php //echo $product['billingYes'] ;?></p>-->
				<p class=""><?php if($product['billingYes']!=''){ ?><b>AMP Bills:</b> <?php echo $product['billingYes'];  } ?><?php if($product['billingNo']!=''){ ?> <b>AMP Bills:</b> <?php echo $product['billingNo'];  } ?><?php if($product['servicingNo']!=''){ ?> <b>AMP Services:</b> <?php echo $product['servicingNo'];  } ?><?php if($product['servicingYes']!=''){ ?> <b>AMP Services:</b> <?php echo $product['servicingYes'];  } ?></p>
			
				
				
				<p class=""><?php if($product['spotLength']!=''){ ?><b>Spot Length:</b> <?php echo $product['spotLength'];  } ?> <?php if($product['ageloopLength']!=''){ ?><b>Loop Length:</b> <?php echo $product['ageloopLength'];  } ?> </p>
				<p class=""> <?php if($product['fliplength']!=''){ ?><b>Flip Length:</b> <?php echo $product['fliplength'];  } ?></p>
				
				<p class=""> <?php if($product['medium']!=''){ ?><b>Medium:</b> <?php echo $product['medium'];  } ?></p>
				
				<p class=""><?php if($product['product_newAge']!=''){ ?><b>Product:</b> <?php echo $product['product_newAge'];  } ?> </p>
				<p class=""> <?php if($product['file_type']!=''){ ?><b>File Type:</b> <?php echo $product['file_type'];  } ?></p>

             
				<p class=""><?php if($product['staticMotion']!=''){ ?><b>Static Motion:</b> <?php echo $product['staticMotion'];  } ?> </p>
				<p class=""> <?php if($product['sound']!=''){ ?><b>Sound:</b> <?php echo $product['sound'];  } ?> <?php if($product['network']!=''){ ?><b>Network:</b> <?php echo $product['network'];  } ?> </p>
				<p class=""> <?php if($product['nationloc']!=''){ ?><b>National Local:</b> <?php echo $product['nationloc'];  } ?></p>
				
				<p class=""><?php if($product['daypart']!=''){ ?><b>Day Part:</b> <?php echo $product['daypart'];  } ?>  <?php if($product['genre']!=''){ ?><b>Genre:</b> <?php echo $product['genre'];  } ?></p>
		
				<p class=""><?php if($product['costperpoint']!=''){ ?><b>Cost Per Point:</b> <?php echo $product['costperpoint'];  } ?>  <?php if($product['costperpoint']!=''){ ?><b>Cost Per Point:</b> <?php echo $product['costperpoint'];  } ?></p>
				<!--<p class=""><?php //if($product['length']!=''){ ?><b>Length:</b> <?php //echo $product['length'];  } ?></p>-->
				
				<!--<p class=""><?php //if($product['length']!=''){ ?><b>Length:</b> <?php //echo $product['length'];  } ?></p>-->
				<p class=""><?php if($product['placement']!=''){ ?><b>Ad Type:</b> <?php echo $product['placement'];  } ?></p>
				
				
				</div>
				<?php if(!empty($value['offer_comments'] )){ ?>
					<div class="row">
						<p><b class="for_blue_clr">Deal Information:</b></p>
						<table class="table table-bordered">
							<thead>
							  <tr>
								<th>Offer Type</th>
								<th>Sender</th>
								<th>Comments</th>
								<th>Status</th>
							  </tr>
							</thead>
							<tbody>
								<?php foreach($value['offer_comments'] as $keys => $values){ ?>
								  <tr>
									<td><?php echo $values['offer_type']; ?></td>
									<td><?php echo $values['Sender']; ?></td>
									<td><?php echo $values['comment']; ?></td>
									<td><?php echo $values['final_status']; ?></td>
								  </tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				<?php } ?>
				</div>

				<div class="" style="width: 50%; float: right;margin-top:0px;">
							<div>								<?php
                    if (is_array($product->image)) {
                        foreach ($product->image as $k => $v) {
                            $img_src = base_path('html') . $v;
                            ?>
								<p>
									<img style="width:380px;height:280px;" src="<?php echo $img_src; ?>">  
								</p>
                            <?php
                        }
                    } 
					//else { 
                        //$img_src = base_path('html') . $product->image;
                        ?>
						<!--<p>
							 <img style="width:400px;height:400px;float:right;" src="<?php //echo $img_src; ?>"/>  
						</p> -->
                        <?php
                    //}
                    ?>
                   
					</div>
					<div>
						<?php $lat="";$lng=""; 
							if($product['lat']!=''){ 
								$lat=str_replace('°', '', $product['lat']);
							} 
							if($product['lng']!=''){ 
								$lng=str_replace('°', '', $product['lng']);
							} 
							if($lat!='' && $lng!=''){ 
								$is_lat = preg_match('/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/', $lat);
								$is_lng = preg_match('/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/', $lng);
								if($is_lat && $is_lng){ ?>
									<p>
										<img src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo $lat; ?>,<?php echo $lng; ?>&markers=color:red%7Clabel:C%7C<?php echo $lat; ?>,<?php echo $lng; ?>&zoom=12&size=380x280&key=<?php echo env('GOOGLE_MAP_KEY'); ?>"/>
									</p>
						<?php } } ?>
					</div>	
					 </div>	
					 
					 
				</div>
				<br/>

			<!--<div class="row" style="margin-top: 690px;">  
					<div class="bill-serv-txt" style="width: 100%;"> 
						<div class="" style="width: 50%;float: left;">
							<p><b class="for_blue_clr">Billing:</b><?php //if($product['billingNo']!=''){ ?> 
							<?php //if(is_array($product['vendor'])){ ?>
								<?php //echo "<p>629 Terminal Way, Suite 1 <br>Costa Mesa CA 92627<br>
								//Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>"; ?><br>
							<?php //}else{ ?>
								<p><?php //if(isset($client_details_billing[$key]['company_name'])){ ?><?php //echo $client_details_billing[$key]['company_name']; ?><br><?php //}else{ ?><br><?php //} ?>
								<?php //if(isset($client_details_billing[$key]['address'])){ ?><b>Address</b> <?php //echo $client_details_billing[$key]['address']; ?><br><?php //}else{ ?><br><?php //} ?>
								<?php //if(isset($client_details_billing[$key]['contact_email'])){ ?><b>Email</b> <?php //echo $client_details_billing[$key]['contact_email']; ?><br><?php //}else{ ?><br><?php //} ?>
								<?php //if(isset($client_details_billing[$key]['phone'])){ ?><b>Phone</b> <?php //echo $client_details_billing[$key]['phone']; ?><?php //}else{ ?><br><?php //} ?></p>
							<?php //} } ?>
							<?php //if($product['billingYes']!=''){ ?> <?php //echo "<p>629 Terminal Way, Suite 1 <br>Costa Mesa CA 92627<br>
							//Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>";  } ?></p>
						</div>
						<div class="" style="width: 50%;float: left;">
							<p><b class="for_blue_clr">Servicing:</b> <?php //if($product['servicingNo']!=''){ ?>
							<?php //if(is_array($product['vendor'])){ ?>
								<?php //echo "<p>629 Terminal Way, Suite 1 <br>Costa Mesa CA 92627<br>
								//Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>"; ?><br>
							<?php //}else{ ?>
								<p><?php //if(isset($client_details_billing[$key]['company_name'])){ ?><?php //echo $client_details_billing[$key]['company_name']; ?><br><?php //}else{ ?><br><?php //} ?>
								<?php //if(isset($client_details_billing[$key]['address'])){ ?><b>Address</b> <?php //echo $client_details_billing[$key]['address']; ?><br><?php //}else{ ?><br><?php //} ?>
								<?php //if(isset($client_details_billing[$key]['contact_email'])){ ?><b>Email</b> <?php //echo $client_details_billing[$key]['contact_email'] ; ?><br><?php //}else{ ?><br><?php //} ?>
								<?php //if(isset($client_details_billing[$key]['phone'])){ ?><b>Phone</b> <?php //echo $client_details_billing[$key]['phone']; ?><?php //}else{ ?><br><?php //} ?></p>
							<?php //} } ?>
							<?php //if($product['servicingYes']!=''){ ?><//?php echo "<p>629 Terminal Way, Suite 1 <br>Costa Mesa CA 92627<br>
							//Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>";  } ?></p>
						</div>
					</div>
				</div>-->   
				
				<div class="row" style="margin-top: 640px; ">
					<div class="bill-serv-txt" style="width: 100%;"> 
						<div class="" style="width: 50%;float: left;">
							<p><b class="for_blue_clr">Billing:</b><?php if($product['billingNo']!=''){ ?> 
							<?php if(is_array($product['vendor'])){ ?>
								<?php echo "<p>Richard Mcclemmy <br><b>Email</b> mcclemmy.richard@gmail.com <br><b>Phone</b> +(714)293-3883<br><b>Address</b> - <br></p>"; ?><br>
							<?php }else{ ?>
								<p><?php if(isset($client_details_billing[$key]['company_name'])){ ?><?php echo $client_details_billing[$key]['company_name']; ?><br><?php }else{ ?> &nbsp;&nbsp; <br><?php } ?>
								<?php if(isset($client_details_billing[$key]['contact_email'])){ ?><b>Email</b> <?php echo $client_details_billing[$key]['contact_email']; ?><br><?php }else{ ?> &nbsp;&nbsp; <br><?php } ?>
								<?php if(isset($client_details_billing[$key]['phone'])){ ?><b>Phone</b> <?php echo $client_details_billing[$key]['phone']; ?><br><?php }else{ ?> &nbsp;&nbsp; <br><?php } ?>
								<?php if(isset($client_details_billing[$key]['address'])){ ?><b>Address</b> <?php echo $client_details_billing[$key]['address']; ?><br><?php }else{ ?> &nbsp;&nbsp; <br><?php } ?></p><br>
							<?php } } ?>
							<?php if($product['billingYes']!=''){ ?> <?php echo "<p>Richard Mcclemmy <br><b>Email</b> mcclemmy.richard@gmail.com <br><b>Phone</b> +(714)293-3883<br><b>Address</b> - <br><br></p>";  } ?></p>
						</div>
						<div class="" style="width: 50%;float: left;">
							<p><b class="for_blue_clr">Servicing:</b> <?php if($product['servicingNo']!=''){ ?>
							<?php if(is_array($product['vendor'])){ ?>
								<?php echo "<p>Richard Mcclemmy <br><b>Email</b> mcclemmy.richard@gmail.com <br><b>Phone</b> +(714)293-3883<br><b>Address</b> - <br></p>"; ?><br>
							<?php }else{ ?>
								<p><?php if(isset($client_details_billing[$key]['company_name'])){ ?><?php echo $client_details_billing[$key]['company_name']; ?><br><?php }else{ ?> &nbsp;&nbsp; <br><?php } ?>
								<?php if(isset($client_details_billing[$key]['contact_email'])){ ?><b>Email</b> <?php echo $client_details_billing[$key]['contact_email'] ; ?><br><?php }else{ ?> &nbsp;&nbsp; <br><?php } ?>
								<?php if(isset($client_details_billing[$key]['phone'])){ ?><b>Phone</b> <?php echo $client_details_billing[$key]['phone']; ?><br><?php }else{ ?> &nbsp;&nbsp; <br><?php } ?>
								<?php if(isset($client_details_billing[$key]['address'])){ ?><b>Address</b> <?php echo $client_details_billing[$key]['address']; ?><br><?php }else{ ?> &nbsp;&nbsp; <br><?php } ?></p><br>
							<?php } } ?>
							<?php if($product['servicingYes']!=''){ ?><?php echo "<p>Richard Mcclemmy <br><b>Email</b> mcclemmy.richard@gmail.com <br><b>Phone</b> +(714)293-3883<br><b>Address</b> - <br><br></p>";  } ?></p>
						</div>
					</div>
				</div>
			
			 <div style="margin-top:1px;">  
				<p style="color:#990100;font-size: 15px;margin-bottom: 0px;">Advertising Marketplace, LLC</p>
				<p style="font-size: 15px;margin-bottom: 0px; margin-top: 5px;">All Rights Reserved</p>
			</div>
</div> 
				

				<?php
					}
					$j++;
				}
				}
				?>
				
		</div>

<div class="row">
	<div class="page-break">
	<div class="pdf-alignment">	
	<div class="text_alignment">
		<h4 style="text-align: center; width:100%;color: #007bff;letter-spacing:1px; font-size: 15px;">AMP – Insertion Order - Standard Terms and Conditions</h4>
		<p style="font-size: 11px;">1. Media Placement: (a) Advertising Marketplace, LLC (“Agency”) is authorized to act as agent for its Buyer (“Buyer”) in dealing with media
		vendor (“Seller”) or vendors (“Sellers”) and other applicable parties and is authorized to enter into contracts and schedules for placement of
		media on behalf of Buyer for Sellers products and/or services. Unless otherwise agreed, all placements will be made in Buyer’s name and
		Agency represents it has the authority to act and is acting on behalf of its Buyer as an “agent for a disclosed principal”. (b) If media product
		included in this contract is no longer available for any reason, the Seller will offer a product or service of equal or better advertising value to be
		approved by Buyer and Agency. In the event that Buyer approves the new product, the term of this contract will be extended after the
		expiration date of this contract for a period that is equal to the time in which Buyer’s advertisement was not on display or delivered. If Buyer
		or Agency do not approve the new product, Agency, on behalf of its Buyer, reserves the right to, in its sole decision, cancel this contract in its
		entirety and or the portion of the contract Seller or Sellers are not able to deliver. Upon cancellation, the Seller will be responsible for
		reimbursing Agency or Buyer a sum equal to: (i) the pro-rata media cost paid by Agency/Buyer for the period of time in which Buyer’s
		advertisement was not on display; and (ii) the out of pocket costs incurred by Agency/Buyer prior to the date of termination for production
		and delivery of the advertising material, art or copy hereunder which was not delivered. (c) If Sellers product includes illumination of any type
		and illumination is suspended for any reason during the term of the contract, Agency/Buyer is entitled to a pro-rata credit equal to 25% of the
		daily value of the contracted time period during which illumination is suspended. If illumination is suspended in excess of three (3) days,
		Agency/Buyer shall have the right to terminate the contract and Seller or Sellers shall be obligated to reimburse Agency/Buyer a sum equal to
		the out of pocket costs incurred by Agency/Buyer prior to the date of termination for production and delivery of the advertising material.
		(d)All media must be posted within two (2) business days of the contracted start date pending receipt of materials by established deadline.</p>
		<p style="font-size: 11px;">2. Representations and Warranties: (a) Seller represents and warrants that (i) it has the full power and authority to enter into this contract and to
		perform the obligations contained in this agreement; and that (ii) it has conducted proper due diligence ensuring that necessary permission and/or
		permits for advertising messages are valid; (iii) that all advertiser messages are acceptable based on any permits/permissions required through the
		contract duration; and (iv) it will perform the services using professional care and skill. (b) Agency represents and warrants that (i) it has full power
		and authority to enter into this contract and (ii) Buyer has the necessary licenses and clearances to use the content contained in the advertising
		materials.</p>
		<p style="font-size: 11px;">3. Financial Terms: Buyer is solely liable for all such media billings placed and properly run on its behalf. Agency will have no financial liability for
		the media placed with Seller, except to the extent Agency has received payment from Buyer designated for Seller. Upon receipt of such funds,
		Agency will assume sole liability. Payment terms are net sixty (60) days unless otherwise agreed in writing but the parties. Except as otherwise
		provided in this contract, Agency is not responsible for payment of any taxes, late payment fees, or additional charges incurred by Seller. Agency
		does not issue payment prior to receipt of appropriate proof of performance including signed affidavits of performance, photos of executed media
		and all other applicable items.</p>
		<p style="font-size: 11px;">4. Confidentiality: Each party agrees to take reasonable precautions to protect from disclosure the confidential information that it receives from
		the other party. "Confidential information" includes Buyer information disclosed by Agency and any information that is disclosed in a manner that
		would reasonably suggest that the information is confidential, but does not include any information that: (i) is, or later rightfully becomes,
		available to the public; (ii) was in the recipient's rightful possession prior to receipt of the information from the discloser; (iii) is later disclosed to
		the recipient by a third party who, to the best of receiving party’s knowledge, has no obligation of confidentiality; (iv) is independently developed
		by the recipient without the use or benefit of the confidential information; or (v) is required to be disclosed under court order or other legal
		process.</p>
		<p style="font-size: 11px;">5. Cancellation: (a) Agency reserves the right to cancel all media up until fourteen (14) days prior to the commencement date of the contracted
		product(s). Once the contract is in effect, Agency reserves the right to cancel the remainder of the contract starting sixty (60) days from the date of
		notice without penalty or short rate. (b) Agency requires sixty (60) day notice of renewal from all Sellers. If this notice is not given by the Seller in
		writing and accepted by Agency in writing, the contracted media will expire on the final day of the contract. The terms herein supersede any
		automatic media renewal provisions that may be contained in any other documents executed between the parties.</p>
		<p style="font-size: 11px;">6. Responsibility: Buyer agrees that it will be solely responsible for the content of any and all advertising placed on its behalf by Agency, including
		but not limited to, ensuring that all advertisements supplied are not misleading, inaccurate, indecent, libelous or unlawful and that their placement
		or publication will not violate the rights of any third party. Seller agrees to facilitate copy approval with appropriate entities such as owner,
		landlord or any other authority necessary prior to posting date on behalf of Agency/Buyer.</p>
		<p style="font-size: 11px;">7. Indemnification: Seller and Buyer will indemnify, defend and hold the other party and Agency harmless from any and all claims, suits, actions,
		liabilities, expenses and costs relating to losses, claims,
		damages, liabilities, judgments, settlements or costs and expenses (including reasonable attorneys' fees) against the other party and Agency, their
		employees, directors or affiliates, arising out of or in connection with a breach of this contract (including its representations under Section 2(a) or
		its gross negligence or willful misconduct under this contract.</p>
		<p style="font-size: 11px;">8. Force Majeure: Each party will be excused from performing obligations contained in this contract while such performance is prevented by an
		act of God, fire, flood, earthquake, transportation disruption, war, insurrection, labor dispute, or any other occurrence beyond the reasonable
		control of that party. If such Force Majeure event continues for a period of thirty (30) days or longer, Agency and or Buyer shall have the right to
		terminate this contract and Seller will reimburse Agency an or Buyer for the period of time in which Buyer’s advertisement is not on display.</p>
		<p style="font-size: 11px;">9. General: (a) This contract contains the entire agreement between the parties relating to the advertising services and supersedes any previous
		agreements or understanding whether written or oral. No amendment, modification or waiver shall be made to this contract unless made in
		writing and signed by both parties. (b) If any provision of this contract is held to be invalid, illegal or unenforceable, the remaining provisions of the
		contract will remain in full force and effect. (c) This contract is also subject to the User Agreement and all Policies that the parties agreed to when
		registering with Agency. If there is a conflict between the User Agreement, Agencies Policies as outlined on Agencies website
		(www.advertisingmarketplace.com) and these Standard Terms and Conditions, these Standard Terms and Conditions shall prevail.</p>

		 <br/> 
		 <div style="margin-top: 30px;"> 
			<p style="color:#990100;font-size: 15px;margin-bottom: 0px;">Advertising Marketplace, LLC</p>
			<p style="font-size: 15px;margin-bottom: 0px; margin-top: 5px;">All Rights Reserved</p>
		</div>
	</div> 	
	</div> 	
	</div> 	

	</div>
	
 </div>  
</body>
</html>